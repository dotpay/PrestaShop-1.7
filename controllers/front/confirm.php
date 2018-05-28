<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@dotpay.pl so we can send you a copy immediately.
 *
 * @author    Dotpay Team <tech@dotpay.pl>
 * @copyright Dotpay sp. z o.o.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Dotpay\Loader\Loader;
use Dotpay\Exception\Processor\ConfirmationInfoException;
use Dotpay\Exception\Processor\ConfirmationDataException;
use Dotpay\Model\CreditCard;
use Dotpay\Model\Operation;
use Dotpay\Action\UpdateCcInfo;
use Dotpay\Action\MakePaymentOrRefund;

require_once('dotpay.php');

/**
 * Controller for handling callback from Dotpay
 */
class DotpayConfirmModuleFrontController extends DotpayController
{
    /**
     * Confirm payment based on Dotpay URLC
     */
    public function displayAjax()
    {
        $loader = Loader::load();
        
        try {
            $confirmProcessor = $loader->get('Confirmation');
            $notification = $loader->get('Notification');
            $this->setCart(Cart::getCartByOrderId($notification->getOperation()->getControl()));
            $this->initializeOrderData(true);
            
            /* ---- UPDATE CC ---- */
            $updateCcAction = new UpdateCcInfo(
                function (CreditCard $cc) {
                    $cc->save();
                    return true;
                }
            );
            $confirmProcessor->setUpdateCcAction($updateCcAction);
            
            /* ---- PAYMENT ---- */
            $config = $this->getConfig();
            $paymentAction = new MakePaymentOrRefund(
                function (Operation $operation) use ($config, $loader) {
                    $control = explode('|', (string)$operation->getControl());
                    $order = new Order($control[0]);
                    $brotherOrders = array($order);
                    foreach ($order->getBrother() as $brotherOrder) {
                        $brotherOrders[] = $brotherOrder;
                    }
                    foreach ($brotherOrders as $order) {
                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $lastOrderState = new OrderState($order->getCurrentState());
                        $newOrderState = $this->getNewOrderState($operation, $lastOrderState);
                        if ($newOrderState === null) {
                            throw new ConfirmationDataException('PrestaShop - WRONG TRANSACTION STATUS');
                        }
                        if ($lastOrderState->id == _PS_OS_PAYMENT_ ||
                            $newOrderState == $config->getWaitingStatus()) {
                            return true;
                        }
                        if ($lastOrderState->id != $newOrderState) {
                            $history->changeIdOrderState($newOrderState, $history->id_order);
                            $history->addWithemail(true);
                            if ($newOrderState == _PS_OS_PAYMENT_ || $newOrderState == _PS_OS_OUTOFSTOCK_PAID_) {
                                $payments = OrderPayment::getByOrderId($order->id);
                                $numberOfPayments = count($payments);
                                if ($numberOfPayments >= 1) {
                                    if (empty($payments[$numberOfPayments - 1]->transaction_id)) {
                                        $payments[$numberOfPayments - 1]->transaction_id = $operation->getNumber();
                                        $payments[$numberOfPayments - 1]->payment_method = $this->module->displayName;
                                        $payments[$numberOfPayments - 1]->update();
                                    } else {
                                        $payment = $this->prepareOrderPayment($operation, $order);
                                        $payment->add();
                                    }
                                }
                                $loader->get('Instruction', array($order->id))->deleteForOrder();
                            }
                        } elseif ($lastOrderState->id == $newOrderState &&
                                  $newOrderState == _PS_OS_OUTOFSTOCK_UNPAID_) {
                            return true;
                        } else {
                            $stateName = "OTHER NAME";
                            if (is_array($lastOrderState->name) && count($lastOrderState->name)) {
                                $stateName = $lastOrderState->name[0];
                            } elseif (is_string($lastOrderState->name)) {
                                $stateName = $lastOrderState->name;
                            }
                            throw new
                            ConfirmationDataException('PrestaShop - THIS STATE ('.$stateName.') IS ALERADY REGISTERED');
                        }
                    }
                    return true;
                }
            );
            $confirmProcessor->setMakePaymentAction($paymentAction);
            
            /* ---- REFUND ---- */
            $refundAction = new MakePaymentOrRefund(
                function (Operation $operation) use ($config, $loader) {
                    if ($operation->getStatus() != $operation::STATUS_COMPLETE &&
                        $operation->getStatus() != $operation::STATUS_REJECTED) {
                        return true;
                    }
                    
                    $control = explode('|', (string)$operation->getControl());
                    $order = new Order($control[0]);
                    $payments = OrderPayment::getByOrderId($order->id);
                    $foundPaymet = false;
                    $sumOfPayments = 0.0;
                    
                    foreach ($payments as $payment) {
                        if ($payment->transaction_id == $operation->getNumber()) {
                            throw new
                            ConfirmationDataException(
                                'PrestaShop - PAYMENT '.$operation->getNumber().' IS ALREADY SAVED'
                            );
                        } elseif ($payment->transaction_id == $operation->getRelatedNumber()) {
                            $foundPaymet = true;
                        }
                        if ($payment->payment_method == $this->module->displayName) {
                            $sumOfPayments += (float)$payment->amount;
                        }
                    }
                    
                    if (!$foundPaymet) {
                        throw new
                        ConfirmationDataException('PrestaShop - PAYMENT '.$operation->getNumber().' IS NOT SAVED');
                    }
                    
                    $receivedAmount = (float)$operation->getOriginalAmount();
                    if ($receivedAmount - $sumOfPayments >= 0.01) {
                        throw new
                        ConfirmationDataException(
                            'PrestaShop - NO MATCH OR WRONG AMOUNT - '.$receivedAmount.' > '.$sumOfPayments
                        );
                    }

                    $lastOrderState = new OrderState($order->getCurrentState());
                    if ($lastOrderState->id != $config->getWaitingRefundStatus()) {
                        throw new ConfirmationDataException('PrestaShop - REFUND HAVEN\'T BEEN SUBMITTED');
                    }

                    if ($operation->getStatus() == $operation::STATUS_COMPLETE) {
                        $payment = $this->prepareOrderPayment($operation, $order, true);
                        $payment->add();

                        if ($receivedAmount < $sumOfPayments) {
                            $state = $config->getPartialRefundStatus();
                        } else {
                            $state = $config->getTotalRefundStatus();
                        }

                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->changeIdOrderState($state, $history->id_order);
                        $history->addWithemail(true);
                    } elseif ($operation->getStatus() == $operation::STATUS_REJECTED) {
                        $state = $config->getFailedRefundStatus();
                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->changeIdOrderState($state, $history->id_order);
                        $history->addWithemail(true);
                    }
                    return true;
                }
            );
            $confirmProcessor->setMakeRefundAction($refundAction);
            
            if ($confirmProcessor->execute($loader->get('PaymentModel'), $notification)) {
                die('OK');
            } else {
                die('PRESTASHOP - AN ERROR OCCURED');
            }
        } catch (ConfirmationInfoException $ex) {
            die($ex->getMessage());
        } catch (RuntimeException $ex) {
            die('EXCEPTION! '.get_class($ex).' '.$ex->getMessage());
        }
    }
    
    /**
     * Return a new order state for the given operation
     * @param Operation $operation Details of current operation
     * @param \OrderState $lastOrderState PrestaShop object with last order state
     * @return int
     */
    private function getNewOrderState(Operation $operation, $lastOrderState)
    {
        $actualState = null;
        switch ($operation->getStatus()) {
            case $operation::STATUS_NEW:
            case $operation::STATUS_PROCESSING:
            case 'processing_realization_waiting':
            case 'processing_realization':
                if ($lastOrderState->id == _PS_OS_OUTOFSTOCK_UNPAID_) {
                    $actualState = _PS_OS_OUTOFSTOCK_UNPAID_;
                } else {
                    $actualState = $this->getConfig()->getWaitingStatus();
                }
                break;
            case $operation::STATUS_COMPLETE:
                if ($lastOrderState->id == _PS_OS_OUTOFSTOCK_UNPAID_) {
                    $actualState = _PS_OS_OUTOFSTOCK_PAID_;
                } else {
                    $actualState = _PS_OS_PAYMENT_;
                }
                break;
            case $operation::STATUS_REJECTED:
                $actualState = _PS_OS_ERROR_;
        }
        return $actualState;
    }
    
    /**
     * Create and prepares payment for given order
     * @param Operation $operation Operation object
     * @param Order $order Order object
     * @param bool $minus Flag, if minus sign should be set
     */
    private function prepareOrderPayment(Operation $operation, $order, $minus = false)
    {
        $payment = new OrderPayment();
        $payment->order_reference = $order->reference;
        $payment->amount = (float)(($minus ? '-':'').Tools::getValue('operation_original_amount'));
        $payment->id_currency = $order->id_currency;
        $payment->conversion_rate = 1;
        $payment->transaction_id = $operation->getNumber();
        $payment->payment_method = $this->module->displayName;
        $payment->date_add = new \DateTime();
        return $payment;
    }
    
    /**
     * Return a correct and well-formatted amount, which is based on input parameter
     * @param float $amount Amount of order
     * @return float
     */
    private function getCorrectAmount($amount)
    {
        $count = 0;
        do {
            $amount = preg_replace("/(\d+)\.(\d{3,})/", "$1$2", $amount, -1, $count);
        } while ($count > 0);
        return $amount;
    }
}

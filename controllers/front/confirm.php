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
 * @copyright PayPro S.A.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Dotpay\Action\MakePaymentOrRefund;
use Dotpay\Action\UpdateCcInfo;
use Dotpay\Exception\Processor\ConfirmationDataException;
use Dotpay\Exception\Processor\ConfirmationInfoException;
use Dotpay\Loader\Loader;
use Dotpay\Model\CreditCard;
use Dotpay\Model\Operation;

require_once('dotpay.php');

/**
 * Controller for handling callback from Dotpay
 */
class DotpayConfirmModuleFrontController extends DotpayController
{
   
   
   
    //Remove UTF8 Bom, new lines and spaces

    public function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", "", $text);
        $text2 = preg_replace("/\r|\n|\s/", "", $text);
        return (trim($text2));
    }
   
   
   
    /**
     * Confirm payment based on Dotpay URLC
     */
    public function displayAjax()
    {
        $loader = Loader::load();
        
        try {
            $confirmProcessor = $loader->get('Confirmation');
            $notification = $loader->get('Notification');

            $notificationControl = $notification->getOperation()->getControl();
            $control_received  = explode('|', (string)$notificationControl);

            if(!isset($control_received[0]) || !(int)$control_received[0] > 0){
                throw new ConfirmationDataException('ERROR! NO MATCH ORDER ID IN THE CONTROL OR ORDER ID IS EMPTY: '.$control_received[0].'.');
            }

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
                        if ($newOrderState == $config->getWaitingStatus() ) {
                            return true;
                        }


                        if ($lastOrderState->id == _PS_OS_PAYMENT_  || $lastOrderState->id == $config->getOverpaidStatus() ) {
                           
                            $payments_id = OrderPayment::getByOrderId($order->id);

                            //check saved transactions
                            $query_transactions = 'SELECT DISTINCT transaction_id FROM '. _DB_PREFIX_ .'order_payment WHERE order_reference = "'.$order->reference.'" AND payment_method NOT LIKE "%refund%" AND payment_method NOT LIKE "%zwrot%" ';
							
                            $isTransactionExist = Db::getInstance()->ExecuteS($query_transactions);
							$count_transactions = count($isTransactionExist);

                            $payment_transactions= array();
										
                            foreach ($isTransactionExist as $TrX){
                                $payment_transactions[] = $TrX['transaction_id'];
                            }
                            
                            if($count_transactions > 0){

                                if(in_array($operation->getNumber(),$payment_transactions)){
                                    throw new ConfirmationDataException('PrestaShop - PAYMENT WITH THIS NUMBER '.$operation->getNumber().' IS ALREADY SAVED');
                                }else{

                                    if($operation->getStatus() != $operation::STATUS_REJECTED){

                                        if($lastOrderState->id == _PS_OS_PAYMENT_ ){

                                            $newOrderState == $config->getOverpaidStatus();
                                            $history->changeIdOrderState($newOrderState, $history->id_order);
                                        }                                        
    
                                        $history->id_order = $order->id;
                                        $payments_id = $this->prepareOrderPayment($operation, $order);
                                        $payments_id->add();
                                        
                                        $count_transactions_all = $count_transactions + 1;

                                        throw new ConfirmationDataException('PrestaShop - Another payment '.$operation->getNumber().' has been registered for the same order: '.$order->id.'; total: '.$count_transactions_all .', transactions registered before: '.json_encode($payment_transactions) );
                                    }else{
                                        
                                        $history = new OrderHistory();
                                        $history->id_order = $order->id;
                                        return true;

                                    }



                                }

                            }

                            if($operation->getStatus() == $operation::STATUS_REJECTED){

                                $history = new OrderHistory();
                                $history->id_order = $order->id;
                                return true;
                            }
      

                            return true;

                        } 
                        
                        if ($operation->getStatus() == $operation::STATUS_REJECTED && ( $lastOrderState->id != _PS_OS_PAYMENT_  || $lastOrderState->id != $config->getOverpaidStatus() ) ) {
                            $state = _PS_OS_ERROR_;
                            $history = new OrderHistory();
                            $history->id_order = $order->id;
                            $history->changeIdOrderState($state, $history->id_order);
                           // $history->addWithemail(true);
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
                            throw new ConfirmationDataException('PrestaShop - THIS STATE ('.$stateName.') IS ALERADY REGISTERED');
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
                    $order = new Order((int)$control[0]);

                    $payments = OrderPayment::getByOrderId($order->id);
                    $foundPaymet = false;
                    $sumOfPayments = 0.0;
                    
                    foreach ($payments as $payment) {
                        if ($payment->transaction_id == $operation->getNumber()) {
                            throw new ConfirmationDataException('PrestaShop - REFUND '.$operation->getNumber().' IS ALREADY SAVED');
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
                        throw new ConfirmationDataException('PrestaShop - NO MATCH OR WRONG AMOUNT - '.$receivedAmount.' > '.$sumOfPayments
                        );
                    }

                    $lastOrderState = new OrderState($order->getCurrentState());
                    if ($lastOrderState->id != $config->getWaitingRefundStatus()) {
                        throw new ConfirmationDataException('PrestaShop - REFUND HAVEN\'T BEEN SUBMITTED');
                    }

                    if ($operation->getStatus() == $operation::STATUS_COMPLETE) {
                        $payment = $this->prepareOrderPayment($operation, $order, true);
                        $payment->add();
                        $this->updateNegativeAmount($order->reference,$receivedAmount);

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
                die($this->remove_utf8_bom('OK'));
            } else {
                die('PRESTASHOP - AN ERROR OCCURED');
            }
        } catch (ConfirmationInfoException $ex) {
            die($ex->getMessage());
        } catch (RuntimeException $ex) {
            die('EXCEPTION! '.get_class($ex).' : '.$ex->getMessage());
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
                }else if($lastOrderState->id == _PS_OS_PAYMENT_){
                    $actualState = $this->getConfig()->getOverpaidStatus();
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
        $payment->amount = (float)(($minus ? '':'').Tools::getValue('operation_original_amount'));
        //$payment->amount = (float)(($minus ? '-':'').Tools::getValue('operation_original_amount')); //don't use: problem with negative amount in Prestashop
        $payment->id_currency = $order->id_currency;
        $payment->conversion_rate = 1;
        $payment->transaction_id = $operation->getNumber();
       // $payment->payment_method = $this->module->displayName;
        $payment->payment_method = $this->module->displayName.($minus ? ' (refund)':'');
        $payment->date_add = new \DateTime();
        $payment->card_number = (Tools::getValue('credit_card_masked_number') ? Tools::getValue('credit_card_masked_number'):'' );
        $payment->card_brand = (Tools::getValue('credit_card_brand_code') ? Tools::getValue('credit_card_brand_code'):'' );
        $payment->card_expiration = (Tools::getValue('credit_card_expiration_year') ? Tools::getValue('credit_card_expiration_year').'/'.Tools::getValue('credit_card_expiration_month'):'' );

        return $payment;
    }
    

	 /**
     * Update the value amount in database for refund (negative amount)
	 * Fix this problem: https://github.com/PrestaShop/PrestaShop/issues/23983
     */
    private function updateNegativeAmount($reference,$amount)
    {
        if (trim($reference) === null) {
            return false;
        }


        return Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'order_payment`
            SET
            amount = (amount * (-1))
            WHERE id_order_payment = (SELECT max(id_order_payment) as id_order_payment_last FROM `'._DB_PREFIX_.'order_payment` WHERE order_reference = "'.$reference.'" 
            ORDER BY id_order_payment DESC LIMIT 1) AND
			ROUND(amount,2) = "'.$amount.'" '
        );

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

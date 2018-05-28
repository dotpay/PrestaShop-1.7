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

require_once('dotpay.php');

use Dotpay\Processor\Status;

/**
 * Controller for handling return address
 */
class DotpayStatusModuleFrontController extends DotpayController
{
    /**
     * Checks a payment status of order in shop
     */
    public function init()
    {
        parent::init();
        header('Content-Type: application/json; charset=utf-8');
        $orderId = Tools::getValue('orderId');
        $status = new Status();
        if ($orderId != null) {
            $order = new Order($orderId);
            $lastOrderState = new OrderState($order->getCurrentState());
            $statusName = (gettype($lastOrderState->name) == 'array')?$lastOrderState->name[1]:$lastOrderState->name;
            $status->setStatus($statusName);
            switch ($lastOrderState->id) {
                case $this->config->getWaitingStatus():
                    $status->codePending();
                    break;
                case _PS_OS_PAYMENT_:
                    $payments = OrderPayment::getByOrderId($orderId);
                    if ((count($payments) - count($order->getBrother())) > 1) {
                        $status->codeTooMany();
                    } else {
                        $status->codeSuccess();
                    }
                    break;
                case _PS_OS_ERROR_:
                    $status->codeError();
                    break;
                default:
                    $status->codeOtherStatus();
            }
        } else {
            $status->codeNotExist();
        }
        die($status->getJson());
    }
}

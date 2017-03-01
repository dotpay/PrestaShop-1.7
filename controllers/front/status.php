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
 * @copyright Dotpay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once('dotpay.php');

/**
 * Controller for handling return address
 */
class dotpaystatusModuleFrontController extends DotpayController
{
    /**
     * Check a payment status of order in shop
     */
    public function initContent()
    {
        parent::initContent();
        $orderId = Tools::getValue('order');
        if ($orderId != null) {
            $order = new Order($orderId);
            $lastOrderState = new OrderState($order->getCurrentState());
            switch ($lastOrderState->id) {
                case $this->getConfig()->getWaitingStatus():
                    die('0');
                case _PS_OS_PAYMENT_:
                    $payments = OrderPayment::getByOrderId($orderId);
                    if (count($payments) > 1) {
                        die('2');
                    } else {
                        die('1');
                    }
                default:
                    die('-1');
            }
        } else {
            die('NO');
        }
    }
}
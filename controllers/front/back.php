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

use Dotpay\Processor\Back;

require_once('dotpay.php');

/**
 * Controller for handling return address
 */
class dotpaybackModuleFrontController extends DotpayController
{
    /**
     * Set additional media in template
     * @return boolean
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->registerJavascript(
            'dotpay-back-status',
            'modules/'.$this->module->name.'/views/js/checkStatus.js',
            [
                'position' => 'bottom',
                'priority' => 10
            ]
        );
        return true;
    }
    
    /**
     * Proces coming back from a Dotpay server
     */
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        $message = null;
        
        if ((bool)Context::getContext()->customer->is_guest) {
            $url=Context::getContext()->link->getPageLink('guest-tracking', true);
        } else {
            $url=Context::getContext()->link->getPageLink('history', true);
        }
        $orderId = Tools::getValue('order');
        $order = new Order($orderId);
        $backProcessor = new Back(Tools::getValue('error_code'));
        try {
            $backProcessor->execute();
        } catch (RuntimeException $e) {
            $message = $this->module->l($e->getMessage());
        }
        
        $this->context->smarty->assign([
            'message' => $message,
            'redirectUrl' => $url,
            'orderReference' => $order->reference,
            'orderId' => $orderId,
            'checkStatusUrl' => $this->context->link->getModuleLink($this->module->name, 'status', []),
            'waitingMessage' => $this->module->l('Waiting for confirm your payment...').'<br>'.$this->module->l('It make take up to 2 minutes.'),
            'successMessage' => $this->module->l('Thank you! The process of payment completed correctly. In a moment you will be able to check the status of your order.'),
            'tooManyPaymentsMessage' => $this->module->l('Warning! Payment for this order have already registered. If you bank account has been charged, please contact to seller and give him a name of the order:').' '.$order->reference,
            'errorMessage' => $this->module->l('Payment was rejected.'),
            'timeoutMessage' => $this->module->l('Time intended for waiting for payment confirmation has elapsed. When transaction will be confirmed we will notify you on email. If payment will not be confirmed, please contact with shop owner and give him the order number:').' '.$order->reference,
        ]);
        
        return $this->setTemplate('module:dotpay/views/templates/front/back.tpl');
    }
}
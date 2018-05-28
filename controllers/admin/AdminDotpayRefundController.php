<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Dotpay Team <tech@dotpay.pl>
*  @copyright Dotpay sp. z o.o.
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

use Dotpay\Loader\Loader;
use Prestashop\Dotpay\Model\Configuration;

class AdminDotpayRefundController extends ModuleAdminController
{
    /**
     * @var Loader Instance of SDK Loader
     */
    private $loader;
    
    /**
     * @var Configuration Instance of plugin configuration
     */
    private $config;
    
    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->loader = Loader::load();
        $this->config = $this->loader->get('Config');
    }
    
    /**
     * Make a refund request
     */
    public function display()
    {
        try {
            $sa = $this->loader->get('SellerResource');
            $sa->makeRefund(
                $this->loader->get('Refund', array(
                    Tools::getValue('payment'),
                    Tools::getValue('amount'),
                    Tools::getValue('order_id'),
                    Tools::getValue('description')
                ))
            );
            $status = 'success';
            $state = $this->config->getWaitingRefundStatus();
            $history = new OrderHistory();
            $history->id_order = Tools::getValue('order_id');
            $history->changeIdOrderState($state, $history->id_order);
            $history->addWithemail(true);
        } catch (RuntimeException $ex) {
            $this->context->cookie->dotpay_error = $this->l('Refund can not be sent to Dotpay').' '.$ex->getMessage();
            $this->context->cookie->write();
            $status = 'error';
        }
        Tools::redirectAdmin($this->getRedirectUrl($status));
    }
    
    /**
     * Confirm a result of refund request
     */
    public function displayAjax()
    {
        $order = new Order(Tools::getValue('order'));
        $payments = OrderPayment::getByOrderId(Tools::getValue('order'));
        $sumOfPayments = 0.0;
        foreach ($payments as $operation) {
            if ($operation->payment_method == $this->module->displayName) {
                $sumOfPayments += (float)$operation->amount;
            }
        }
        if (abs($sumOfPayments) < 0.01) {
            $sumOfPayments = 0.0;
        }
        $sa = $this->loader->get('SellerResource');
        try {
            $operation = $sa->getOperationByNumber(
                Tools::getValue('payment')
            );
            
            $details = array(
                'sum_of_payments' => $sumOfPayments,
                'description' => $this->l('Refund of order:').' '.$order->reference,
                'currency' => $operation->getCurrency(),
            );
            if (function_exists('json_encode')) {
                $data2display = json_encode($details, 320);
            } else {
                $data2display = \Tools::jsonEncode($details);
            }
            die($data2display);
        } catch (RuntimeException $ex) {
            die('{}');
        }
    }
    
    /**
     * Returns redirect URL where shop administrator is returned after requesting a refund
     * @param string $status Status string
     * @return string
     */
    private function getRedirectUrl($status)
    {
        $pathInfo = parse_url($_SERVER['HTTP_REFERER']);
        $queryString = $pathInfo['query'];
        $queryArray = array();
        parse_str($queryString, $queryArray);
        $queryArray['dotpay_refund'] = $status;
        $newQueryStr = http_build_query($queryArray);
        return $pathInfo['scheme'].'://'.$pathInfo['host'].$pathInfo['path'].'?'.$newQueryStr;
    }
}

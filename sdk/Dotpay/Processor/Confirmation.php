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
namespace Dotpay\Processor;

use \Db;
use Dotpay\Action\MakePaymentOrRefund;
use Dotpay\Action\UpdateCcInfo;
use Dotpay\Exception\Processor\ConfirmationDataException;
use Dotpay\Exception\Processor\ConfirmationInfoException;
use Dotpay\Exception\Processor\IncorrectRequestException;
use Dotpay\Exception\Processor\SellerNotRecognizedException;
use Dotpay\Model\Configuration;
use Dotpay\Model\Notification;
use Dotpay\Model\Payment;
use Dotpay\Model\Seller;
use Dotpay\Resource\Payment as PaymentResource;
use Dotpay\Resource\Seller as SellerResource;

/**
 * Processor of confirmation activity
 */
class Confirmation
{
    /**
     * @var string Container of a collected message
     */
    private $outputMessage;
    
    /**
     * @var Configuration ObjectNode of Dotpay configuration
     */
    protected $config;
    
    /**
     * @var PaymentResource ObjectNode of payment resource
     */
    protected $paymentApi;
    
    /**
     * @var SellerApi ObjectNode of seller resource
     */
    protected $sellerApi;
    
    /**
     * @var Payment ObjectNode with payment data
     */
    private $payment;
    
    /**
     * @var Notification ObjectNode with notification data
     */
    private $notification;
    
    /**
     * @var UpdateCcInfo Action object which is executed during updateing a credit card data
     */
    private $updateCcAction;
    
    /**
     * @var MakePaymentOrRefund Action object which is executed during making a payment
     */
    private $makePaymentAction;
    
    /**
     * @var MakePaymentOrRefund Action object which is executed during making a refund
     */
    private $makeRefundAction;
    
    /**
     * Initialize the processor
     * @param Configuration $config ObjectNode of Dotpay configuration
     * @param PaymentResource $paymentApi ObjectNode of payment resource
     * @param SellerResource $sellerApi ObjectNode of seller resource
     */
    public function __construct(Configuration $config, PaymentResource $paymentApi, SellerResource $sellerApi)
    {
        $this->config = $config;
        $this->paymentApi = $paymentApi;
        $this->sellerApi = $sellerApi;
        $this->outputMessage = '';
        $dp_debug_allow = false;
        $show_time_in_urlc = "";
    
    
        if( (int)$config->getNonProxyMode() == 1) {
            $clientIp = $_SERVER['REMOTE_ADDR'];
            $proxy_desc = 'FALSE';
        }else{
            $clientIp = $this->getClientIp();
            $proxy_desc = 'TRUE';
        }


        if( strtoupper($_SERVER['REQUEST_METHOD']) == 'GET' && isset($_GET['dp_debug']) ){
            $string_to_hash = 'h:'.$this->config->geShoptHost().',id:'.$config->getId().',d:'.date('YmdHi').',p:'.$config->getPin();
            
            if(trim($_GET['dp_debug']) == 'time'){
                $show_time_in_urlc = ", Time: ".date('YmdHi');
            }
            $dp_debug_hash = hash('sha256', $string_to_hash);
            if(trim($_GET['dp_debug']) == $dp_debug_hash){
                $dp_debug_allow = true;
            }else{
                $dp_debug_allow = false;
            }

        }else{
            $dp_debug_allow = false;
        }

    
        //GET
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') 
        {
            if ( $clientIp == $config::OFFICE_IP || $dp_debug_allow == true)  {

                $this->completeInformations();
                die($this->outputMessage);

            } else {

                throw new ConfirmationInfoException('IP: '.$this->getClientIp('true').'/'.$_SERVER['REMOTE_ADDR'].', PROXY: '.$proxy_desc.', METHOD: '.$_SERVER['REQUEST_METHOD'].$show_time_in_urlc);

            }
        }

        //POST
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

            if( !$this->isAllowedIp($clientIp, $config::DOTPAY_CALLBACK_IP_WHITE_LIST)) {

                throw new IncorrectRequestException('IP: '.$this->getClientIp('true').'/'.$_SERVER['REMOTE_ADDR'].', PROXY: '.$proxy_desc.', METHOD: '.$_SERVER['REQUEST_METHOD']);

            }



        }       

    }
    
    /**
     * Set an action which is executed during updateing a credit card data
     * @param UpdateCcInfo $updateCcAction Action object which is executed during updateing a credit card data
     * @return Confirmation
     */
    public function setUpdateCcAction(UpdateCcInfo $updateCcAction)
    {
        $this->updateCcAction = $updateCcAction;
        return $this;
    }
    
    /**
     * Set an action which is executed during making a payment
     * @param MakePaymentOrRefund $makePaymentAction Action object which is executed during making a payment
     * @return Confirmation
     */
    public function setMakePaymentAction(MakePaymentOrRefund $makePaymentAction)
    {
        $this->makePaymentAction = $makePaymentAction;
        return $this;
    }
    
    /**
     * Set an action which is executed during making a refund
     * @param MakePaymentOrRefund $makeRefundAction Action object which is executed during making a refund
     * @return Confirmation
     */
    public function setMakeRefundAction(MakePaymentOrRefund $makeRefundAction)
    {
        $this->makeRefundAction = $makeRefundAction;
        return $this;
    }
    
    /**
     * Execute the processor for making all confirmation's activities
     * @param Payment $payment Payment data
     * @param Notification $notification Notification data
     * @return boolean
     * @throws ConfirmationInfoException Thrown when info for customer service can be cought and displayed
     */
    public function execute(Payment $payment, Notification $notification)
    {
        $this->payment = $payment;
        $this->notification = $notification;
        
        $this->checkIp();
        $this->checkMethod();
        $this->checkPaymentControl();
        $this->checkSignature();
        $this->checkCurrency();
        
        $operation = $this->notification->getOperation();
        switch ($operation->getType()) {
            case $operation::TYPE_PAYMENT:
                return $this->makePayment();
            case $operation::TYPE_REFUND:
                return $this->makeRefund();
            default:
                return false;
        }
    }
    
    /**
     * Collect informations about shop which can be displayed for diagnostic
     */
    protected function completeInformations()
    {
        $config = $this->config;

            if($this->paymentApi->checkSeller($config->getId(),'receiver'))
            {
                $seller_receiver = " [ ".$this->paymentApi->checkSeller($config->getId(),'receiver'). "]";
            }else {
                $seller_receiver = "";
            }

            if(isset($this->paymentApi->checkSeller($config->getId(),'error_code')['error_code']) && $this->paymentApi->checkSeller($config->getId(),'error_code')['error_code'] !== null){
                $error_code = "[".$this->paymentApi->checkSeller($config->getId(),'error_code')['error_code']."] - ".$this->paymentApi->checkSeller($config->getId(),'error_code')['detail'];
            }else {
                $error_code = "";
            }

			if(trim($config->getId()) !== null && (int)$config->getEnable() == 1){
					$CorrectId = (int)$this->paymentApi->checkSeller($config->getId(),'check') .' '.$error_code;
			}else{
					$CorrectId = '&lt;empty or not active module&gt;';
				}
				
			if(trim($config->getPin()) !== null && (int)$config->getEnable() == 1){
					$CorrectPin = (int)$this->sellerApi->checkPin();
			}else{
					$CorrectPin = '&lt;empty or not active module&gt;';
				}

			
			if(trim($config->getFccId()) !== null && (int)$config->getFccVisible() == 1){
					$FCC_CorrectId = (int)$this->paymentApi->checkSeller($config->getFccId(),'check');
			}else{
					$FCC_CorrectId = '&lt;empty or not active function&gt;';
				}
				
			if(trim($config->getFccPin()) !== null && (int)$config->getFccVisible() == 1){
					$FCC_CorrectPin = (int)$this->sellerApi->checkFccPin();
			}else{
					$FCC_CorrectPin = '&lt;empty or not active function&gt;';
				}
		
        $this->addOutputMessage('--- Dotpay Diagnostic Information: date: '.date('YmdHi').' ---')
             ->addOutputMessage('    ', true)
             ->addOutputMessage('Module Version: '.$this->config->getPluginVersion())
             ->addOutputMessage('Enabled: '.(int)$config->getEnable(), true)
             ->addOutputMessage('--- System Info ---', true)
             ->addOutputMessage('PrestaShop Version: '._PS_VERSION_)
             ->addOutputMessage('Sdk Version: '.$config::SDK_VERSION) 	
             ->addOutputMessage('PHP Version: '.  phpversion())	 
             ->addOutputMessage('Memory limit in PHP (memory_limit): '. ((\Tools::getMemoryLimit())/1048576) .' M', true)	 
             ->addOutputMessage('--- Dotpay PLN ---', true)
             ->addOutputMessage('Id: '.$config->getId().' '.$seller_receiver)
             ->addOutputMessage('Correct Id: '. $CorrectId)
             ->addOutputMessage('Correct Pin: '.$CorrectPin)
             ->addOutputMessage('API Version: '.$config->getApi())
             ->addOutputMessage('Test Mode: '.(int)$config->getTestMode())
             ->addOutputMessage('Not Uses Proxy fo server: '.(int)$config->getNonProxyMode())
             ->addOutputMessage('&dollar;_SERVER&lbrack;&apos;REMOTE_ADDR&apos;&rbrack;: '.$_SERVER['REMOTE_ADDR'])
             ->addOutputMessage('Host: '.$this->config->geShoptHost())
             ->addOutputMessage('Default currency: '.$config->getDefaultCurrency())
             ->addOutputMessage('Refunds: '.(int)$config->getRefundsEnable())
             ->addOutputMessage('Widget: '.(int)$config->getWidgetVisible())
             ->addOutputMessage('Widget currencies: '.$config->getWidgetCurrencies())
             ->addOutputMessage('Instructions: '.(int)$config->getInstructionVisible(), true)
             ->addOutputMessage('--- Separate Channels ---', true)
             ->addOutputMessage('One Click: '.(int)$config->getOcVisible())
             ->addOutputMessage('Credit Card: '.(int)$config->getCcVisible())
             ->addOutputMessage('Blik: '.(int)$config->getBlikVisible(), true)
             ->addOutputMessage('--- Dotpay FCC ---', true)
             ->addOutputMessage('FCC Mode: '.(int)$config->getFccVisible())
             ->addOutputMessage('FCC Id: '.$config->getFccId())
  			 ->addOutputMessage('FCC Correct Id: '. $FCC_CorrectId)
  			 ->addOutputMessage('FCC Correct Pin: '. $FCC_CorrectPin)
             ->addOutputMessage('FCC Currencies: '.$config->getFccCurrencies(), true)
             ->addOutputMessage('--- Dotpay API ---', true)
             ->addOutputMessage('Data: '.(($config->isGoodApiData())?'&lt;given&gt;':'&lt;empty&gt;'))
             ->addOutputMessage('Login: '.$config->getUsername());
        $isAccountRight = false;
        try {
            $isAccountRight = $this->sellerApi->isAccountRight();
        } catch (Exception $ex) {
        }
        $this->addOutputMessage('Correct data: '.$isAccountRight, true);

    }
    
    /**
     * Add a new message to te collector
     * @param string $message Message to add
     * @param boolean $endOfSection Flag if the given message is last in a section
     * @return Confirmation
     */
    protected function addOutputMessage($message, $endOfSection = false)
    {
        $this->outputMessage .= $message."<br />";
        if ($endOfSection) {
            $this->outputMessage .= "<br />";
        }
        return $this;
    }
    
    /**
     * Check if the IP address of a notification is correct
     * @return boolean
     * @throws ConfirmationDataException Thrown when IP address of a notification is incorrect
     */

    protected function checkIp()
    {
        $config = $this->config;

        if( (int)$config->getNonProxyMode() == 1) {
            $clientIp = $_SERVER['REMOTE_ADDR'];
            $proxy_desc = 'FALSE';
        }else{
            $clientIp = $this->getClientIp();
            $proxy_desc = 'TRUE';
        }

        if (
            !( $this->isAllowedIp($clientIp, $config::DOTPAY_CALLBACK_IP_WHITE_LIST) )
           ) 
        {
            throw new ConfirmationDataException('ERROR (REMOTE ADDRESS: '.$clientIp.'/'.$_SERVER['REMOTE_ADDR'].'; PROXY:'.$proxy_desc.')');
        }

        return true;
    }


    
    /**
     * Check if a HTTP method used during confirmation is correct
     * @return boolean
     * @throws ConfirmationDataException Thrown when sed HTTP method is different than POST
     */
    protected function checkMethod()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            throw new ConfirmationDataException("ERROR (METHOD <> POST)");
        }
        return true;
    }
    
    /**
     * Check if the notification data is correct through calculating a signature
     * @return boolean
     * @throws ConfirmationDataException Thrown if the given signature is different than calculated based on the notification data
     */
    protected function checkSignature()
    {
        if ($this->notification->calculateSignature($this->getSeller()->getPin()) != $this->notification->getSignature()) {
            throw new ConfirmationDataException("ERROR SIGNATURE - CHECK PIN");
        }
        return true;
    }
    
    /**
     * Check if the given currency is compatible with a currency of the order
     * @return boolean
     * @throws ConfirmationDataException Thrown when the given currency is different than original currency
     */
    protected function checkCurrency()
    {
        $receivedCurrency = $this->notification->getOperation()->getOriginalCurrency();
        $orderCurrency = $this->payment->getOrder()->getCurrency();
        if ($receivedCurrency != $orderCurrency) {
            throw new ConfirmationDataException('ERROR NO MATCH OR WRONG CURRENCY - '.$receivedCurrency.' <> '.$orderCurrency);
        }
        return true;
    }
    
    /**
     * Check if the given currency is compatible with a currency of the order
     * @return boolean
     * @throws ConfirmationDataException Thrown when the given amount is different than original amount
     */
    protected function checkPaymentAmount()
    {
        $receivedAmount = $this->notification->getOperation()->getOriginalAmount();
        $orderAmount = $this->payment->getOrder()->getAmount();
        if ($receivedAmount != $orderAmount) {
            throw new ConfirmationDataException('ERROR NO MATCH OR WRONG AMOUNT - '.$receivedAmount.' <> '.$orderAmount);
        }
        return true;
    }


    protected function checkPaymentControl()
    {
        $receivedControl = $this->notification->getOperation()->getControl();
		$receivedDescription = $this->notification->getOperation()->getDescription();
		
        $control = explode('|', (string)$receivedControl);
        
        if(isset($control[0]) && (int)$control[0] > 0){
            $receivedIdOrder = (int)$control[0];
        }else{
            $receivedIdOrder = null;
        }


            $isOrderX = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'orders WHERE id_order = '.(int)$receivedIdOrder.' and module = "dotpay"');
            $order_reference = $isOrderX['reference'];
            $order_id = $isOrderX['id_order'];
		

			if ($receivedIdOrder == null || $receivedIdOrder != $order_id) {
				throw new ConfirmationDataException('ERROR! NO MATCH ORDER ID OR ORDER ID IS EMPTY: '.$receivedIdOrder.' <> '.$order_id);
			}
		
		if (isset($order_reference) && !empty($order_reference)) {

			//if id_order is ok, but reference is not ok for this order
			if (strpos((string)$receivedDescription, (string)$order_reference) == false) 
			{
				throw new ConfirmationDataException('ERROR! NO MATCH REFERENCE ('.(string)$receivedDescription.') FOR ORDER ID '.$receivedIdOrder.'.');
			}
		}else{
			throw new ConfirmationDataException('ERROR! INCORRECT REFERENCES  ('.(string)$receivedDescription.') FOR ORDER ID '.$receivedIdOrder.'.');
		}

		
        return true;
    }


    
    /**
     * Make a payment and execute all additional actions
     * @return boolean
     */
    protected function makePayment()
    {

        $config = $this->config;
        $this->checkPaymentAmount();

        $operation = $this->notification->getOperation();
        if (
           $operation->getStatus() == $operation::STATUS_COMPLETE &&
           $this->notification->getChannelId() == $config::OC_CHANNEL &&
		   // $this->config->isGoodApiData() === 1 &&
           $this->updateCcAction !== null
          ) {
            $creditCard = null;
            if ($this->notification->getCreditCard() !== null) {
                $creditCard = $this->notification->getCreditCard();
            } elseif($this->sellerApi->isAccountRight()) {
                $operationFromApi = $this->sellerApi->getOperationByNumber($operation->getNumber());
                $paymentMethod = $operationFromApi->getPaymentMethod();
                if ($paymentMethod !== null && $paymentMethod->getDetailsType() == $paymentMethod::CREDIT_CARD) {
                    $creditCard = $paymentMethod->getDetails();
                }
            }
            if ($creditCard !== null) {
                $this->updateCcAction->setCreditCard($creditCard);
                $this->updateCcAction->execute();
            }
        }
        if($this->makePaymentAction !== null) {
            $this->makePaymentAction->setOperation($operation);
            return $this->makePaymentAction->execute();
        } else {
            return true;
        }
    }
    
    /**
     * Make a refund and execute all additional actions
     * @return boolean
     */
    protected function makeRefund()
    {
        $this->makeRefundAction->setOperation($this->notification->getOperation());
        return ($this->makeRefundAction !== null)?$this->makeRefundAction->execute():true;
    }
    
    /**
     * Return a Seller object with a data of seller which applies the given notivication
     * @return Seller
     * @throws SellerNotRecognizedException Thrown when a seller is not recognized in configuration
     */
    protected function getSeller()
    {
        switch ($this->notification->getOperation()->getAccountId()) {
            case $this->config->getId():
                return new Seller($this->config->getId(), $this->config->getPin());
            case $this->config->getFccId():
                if (
                    $this->config->getFccVisible() &&
                    $this->config->isCurrencyForFcc(
                        $this->notification->getOperation()->getOriginalCurrency()
                    )
                  ) {
                    return new Seller($this->config->getFccId(), $this->config->getFccPin());
                } else {
                    throw new SellerNotRecognizedException($this->notification->getOperation()->getAccountId());
                }
            default:
                throw new SellerNotRecognizedException($this->notification->getOperation()->getAccountId());
        }
    }
    
    /**
     * Return ip address from is the confirmation request
     * @return string
     */
    public function getClientIp($list_ip = null)
    {
        $ipaddress = '';
        // CloudFlare support
        if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
            // Validate IP address (IPv4/IPv6)
            if (filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
                $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
                return $ipaddress;
            }
        }
        if (array_key_exists('X-Forwarded-For', $_SERVER)) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $_SERVER['X-Forwarded-For'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ipaddress = $ips[0];
            } else {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($list_ip) && $list_ip != null) {
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
                return  $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
                return $_SERVER["HTTP_CF_CONNECTING_IP"];
            } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
                return $_SERVER["REMOTE_ADDR"];
            }
        } else {
            return $ipaddress;
        }
    }


    /**
         * Returns if the given ip is on the given whitelist.
         *
         * @param string $ip        The ip to check.
         * @param array  $whitelist The ip whitelist. An array of strings.
         *
         * @return bool
     */
    public static function isAllowedIp($ip, array $whitelist)
    {
        $ip = (string)$ip;
        if (in_array($ip, $whitelist, true)) {
            return true;
        }

        return false;
    }
	
	
	
}

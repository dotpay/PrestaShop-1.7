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

use Dotpay\Channel\Channel;
use Dotpay\Channel\Oc;
use Dotpay\Channel\Fcc;
use Dotpay\Channel\Cc;
use Dotpay\Channel\Blik;
use Dotpay\Channel\Dotpay;
use Dotpay\Model\Customer as DotpayCustomer;
use Dotpay\Loader\Loader;
use Dotpay\Tool\AmountFormatter;
use Prestashop\Dotpay\Model\Configuration;

/**
 * Abstract controller, common for all other controllers in this plugin
 */
abstract class DotpayController extends ModuleFrontController
{
    /**
     * @var Loader Instance of SDK Loader
     */
    protected $loader;

    /**
     * @var Cart Prestashop Cart object
     */
    private $cartObject;

    /**
     * @var \Prestashop\Dotpay\Model\Order Plugin Order object
     */
    private $order;

    /**
     * @var Configuration Configuration of plugin
     */
    protected $config;

    /**
     * @var Channel ObjectNode of initialized payment channel
     */
    private $channel;


    /**
     * Initialize the constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->loader = Loader::load();
        $this->config = $this->loader->get('Config');
    }

    /**
     * Initialize data for Dotpay Order
     * @param boolean $afterOrder Flag if the initialization is done after creating an order
     */
    protected function initializeOrderData($afterOrder = false)
    {
        $originalCustomer = new Customer($this->getCart()->id_customer);
        $ordersCustomer = Order::getCustomerOrders((int)$originalCustomer->id);
        $this->loader->parameter('Customer:email', $originalCustomer->email);
        $this->loader->parameter('Customer:firstName', $originalCustomer->firstname);
        $this->loader->parameter('Customer:lastName', $originalCustomer->lastname);
        $address = new Address($this->getCart()->id_address_invoice);
        $address_delivery = new Address($this->getCart()->id_address_delivery);
        $country = new Country((int)($address->id_country));
        $country_delivery1 = new Country((int)($address_delivery->id_country));
        $customer = $this->loader->get('Customer');
        $customer->setId($originalCustomer->id)
                 ->setStreet($address->address1.' '.$address->address2)
                 ->setStreet($address_delivery->address1.' '.$address_delivery->address2,1)
                 ->setPostCode($address->postcode)
                 ->setPostCode($address_delivery->postcode,1)
                 ->setCity($address->city)
                 ->setCity($address_delivery->city,1)
                 ->setCountry($country->iso_code)
                 ->setCountry($country_delivery1->iso_code,1)
                 ->setCustomerCreateDate($originalCustomer->date_add)
                 ->setCustomerOrdersCount(count($ordersCustomer))
                 ->setLanguage($this->getLanguage());
        if ($address_delivery->phone) {
            $customer->setPhone($address_delivery->phone,1);
        }
        if ($address->phone) {
            $customer->setPhone($address->phone);
        }
        $currency = $this->getCurrencyObject();
        if ($afterOrder) {
            $order = new Order(Order::getOrderByCartId($this->getCart()->id));
            $orderAmount = $order->total_paid;
            foreach ($order->getBrother() as $brother) {
                $orderAmount += $brother->total_paid;
            }
            unset($order);
        } else {
            $orderAmount = $this->getCart()->getOrderTotal(true, Cart::BOTH);
        }
        $orderAmount = Tools::displayPrice($orderAmount, $currency, false);
        $orderAmount = (float)$this->getCorrectAmount(
            preg_replace("/[^-0-9\.]/", '', str_replace(',', '.', $orderAmount))
        );
        //$orderAmount = \Tools::convertPrice($orderAmount, $currency["id_currency"], false);
        $orderAmount = AmountFormatter::format($orderAmount, $currency->id);
        $this->loader->parameter('Order:id', null);
        $this->loader->parameter('Order:amount', $orderAmount);
        $this->loader->parameter('Order:currency', $currency->iso_code);
        $this->loader->parameter('PaymentModel:description', '');

        $this->order = $this->loader->get('Order');
        $this->order->setShippingAmount($this->getCart()->getOrderTotal(true, Cart::ONLY_SHIPPING));
    }




  	/**
     * Returns correct SERVER NAME or HOSTNAME
     * @return string
     */
public function getHost()
    {
		$possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
		$sourceTransformations = array(
			"HTTP_X_FORWARDED_HOST" => function($value) {
				$elements = explode(',', $value);
				return trim(end($elements));
			}
		);
		$host = '';
		foreach ($possibleHostSources as $source)
		{
			if (!empty($host)) break;
			if (empty($_SERVER[$source])) continue;
			$host = $_SERVER[$source];
			if (array_key_exists($source, $sourceTransformations))
			{
				$host = $sourceTransformations[$source]($host);
			}
		}
		// Remove port number from host
		$host = preg_replace('/:\d+$/', '', $host);
		return trim($host);
    }

    /**
	 * The validator checks if the given URL address is correct.
	 */
	public function validateHostname($value)
    {
        return (bool) preg_match('/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,10}$/', $value);
    }


	/**
     * Prepare the Dotpay Channel for the given order
     * @param Order $order Prestashop Order object
     */
    protected function prepareChannel($order)
    {
        switch (Tools::getValue('method')) {
            case Oc::CODE:
                $this->channel = $this->loader->get('Oc');
                if (Tools::getValue('dotpay_oc_mode') == 'register') {
                    $creditCard = $this->loader->get('CreditCard');
                    if ($ccFromDb = $creditCard::getCreditCardByOrder($order->id)) {
                        $creditCard = $ccFromDb;
                    }
                    $creditCard->setRegisterDate(new DateTime())
                               ->setOrderId($order->id);
                    $this->channel->setCard($creditCard);
                    $creditCard->save();
                } else {
                    $this->loader->parameter('CreditCard:id', Tools::getValue('dotpay_card_list'));
                    $creditCard = $this->loader->get('CreditCard');
                }
                $this->channel->setCard($creditCard);
                break;
            case Fcc::CODE:
                $this->channel = $this->loader->get('Fcc');
                break;
            case Cc::CODE:
                $this->channel = $this->loader->get('Cc');
                break;
            case Blik::CODE:
                $this->channel = $this->loader->get('Blik')->setBlikCode(Tools::getValue('blik_code'));
                break;
            case Dotpay::CODE:
                $this->channel = $this->loader->get('DotpayChannel');
                if (Tools::getValue('channel')) {
                    $this->channel->setChannelId(Tools::getValue('channel'));
                }
                break;
            case Channel::CODE:
                $this->channel = $this->loader->get('Channel', array(
                    Tools::getValue('channel'),
                    'channel',
                    $this->getConfig(),
                    $this->loader->get('Transaction'),
                    $this->loader->get('PaymentResource'),
                    $this->loader->get('SellerResource')
                ));
                break;
            default:
                die($this->module->l('Unrecognized method'));
        }

		if ($this->validateHostname($this->getHost()))
			{
				$server_name = $this->getHost();
			} else {
				$server_name = "HOSTNAME";
			}


        $this->getChannel()->getSeller()->setInfo(\Configuration::get('PS_SHOP_NAME'));
        $this->getOrder()->setId($order->id)
                         ->setReference($order->reference);
        $description = $this->module->l("Order ID:").' '.$order->reference;
        $control = $this->getOrder()->getId().'|'.$server_name.'|PrestaShop '._PS_VERSION_.' module: '.$this->module->version;
        if ($this->getConfig()->getSurcharge()) {
            $description .= ' ('.
                            $this->module->l("convenience fee - not included").
                            ': '.$this->getOrder()->getSurchargeAmount($this->getConfig(), $this->getCurrencyObject()).
                            ' '.$this->getOrder()->getCurrency().')';
            $control .= '|sur:+'.$this->getOrder()->getSurchargeAmount($this->getConfig(), $this->getCurrencyObject()).
                        ' '.$this->getOrder()->getCurrency();
        }
        if ($this->getConfig()->getExtracharge()) {
            $control .= '|fee:+'.$this->getOrder()->getExtrachargeAmount(
                $this->getConfig(),
                $this->getCurrencyObject()
            )
            .' '.$this->getOrder()->getCurrency();
        }
        if ($this->getConfig()->getReduction()) {
            $control .= '|disc:-'.$this->getOrder()->getReductionAmount(
                $this->getConfig(),
                $this->getCurrencyObject()
            )
            .' '.$this->getOrder()->getCurrency();
        }
        $this->getChannel()->getTransaction()->getPayment()->setDescription($description);
        $this->getChannel()->getTransaction()->setBackUrl(
            $this->context->link->getModuleLink(
                'dotpay',
                'back',
                array('order' => Order::getOrderByCartId($this->getCart()->id)),
                $this->module->isSSLEnabled()
            )
        )->setConfirmUrl($this->context->link->getModuleLink(
            'dotpay',
            'confirm',
            array('ajax' => '1'),
            $this->module->isSSLEnabled()
        ));

        $this->getChannel()->getTransaction()->setControl($control);
    }

    /**
     * Return the prepared channel
     * @return Channel
     */
    protected function getChannel()
    {
        return $this->channel;
    }

    /**
     * Return the Prestashop Cart
     * @return Cart
     */
    protected function getCart()
    {
        if ($this->cartObject === null) {
            $this->cartObject = Context::getContext()->cart;
        }
        return $this->cartObject;
    }

    /**
     * Return an object of Prestashop Currency
     * @return Currency
     */
    protected function getCurrencyObject()
    {
        return new Currency($this->getCart()->id_currency);
    }

    /**
     * Return configuration of the plugin
     * @return Configuration
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Return the Prestashop Order object
     * @return \Prestashop\Dotpay\Model\Order
     */
    protected function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns language code for customer language
     * @return string
     */
    protected function getLanguage()
    {
        $lang = Tools::strtolower(LanguageCore::getIsoById($this->context->cookie->id_lang));
        if (in_array($lang, DotpayCustomer::$LANGUAGES)) {
            return $lang;
        } else {
            return "en";
        }
    }

    /**
     * Set the given card object
     * @param Cart $cart Cart object
     */
    protected function setCart($cart)
    {
        $this->cartObject = $cart;
    }

    /**
     * Returns a correct and well-formatted amount, which is based on input parameter
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

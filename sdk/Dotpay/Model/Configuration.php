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
namespace Dotpay\Model;

use \DateTime;
use Dotpay\Validator\Id;
use Dotpay\Validator\Pin;
use Dotpay\Validator\Username;
use Dotpay\Exception\BadParameter\IdException;
use Dotpay\Exception\BadParameter\PinException;
use Dotpay\Exception\BadParameter\UsernameException;
use Dotpay\Exception\BadParameter\PasswordException;
use Dotpay\Exception\BadParameter\ApiVersionException;

/**
 * Storage of basic configuration
 */
class Configuration
{
    /**
     * Version of the SDK
     */
    const SDK_VERSION = '1.0.10';

    /**
     * Url of Dotpay payment production server
     */
    const PAYMENT_URL_PROD = 'https://dproxy.przelewy24.pl/t2/';

    // Dotpay Proxy in Przelewy24 URL
    const DPROXY_URL = 'https://dproxy.przelewy24.pl/t2/';


    /**
     * Url of Dotpay seller production server
     */
    const SELLER_URL_PROD = 'https://dproxy.przelewy24.pl/s2/login/';
    
    
    // Dotpay Proxy in Przelewy24 Seller Api URL
    const DPROXY_SELLER_API_URL = 'https://dproxy.przelewy24.pl/s2/login/';

   
    /**
     * Url of Dotpay payment test server
     */
    const PAYMENT_URL_DEV = 'https://ssl.dotpay.pl/test_payment/';
   
    /**
     * Url of Dotpay seller test server
     */
    const SELLER_URL_DEV = 'https://ssl.dotpay.pl/test_seller/';

    /**
     * Addresses IP of Dotpay/Przelewy24 confirmation server
     */
    //

    const DOTPAY_CALLBACK_IP_WHITE_LIST = array(
                                                '195.150.9.37',
                                                '5.252.202.254',
                                                '5.252.202.255',
                                                '20.215.81.124'
                                                );

    /**
     * Id of One Click card channel
     */
    const OC_CHANNEL = 248;

    /**
     * Id of card channel, used for foreign currencies
     */
    const FCC_CHANNEL = 248;

    /**
     * Id of standard card channel
     */
    const CC_CHANNEL = 246;

    /**
     * Id of BLIK channel
     */
    const BLIK_CHANNEL = 73;


    /**
     * Class name of the HTML container which contains aDotpay widget on a payment site
     */
    const WIDGET_CLASS_CONTAINER = 'dotpay-form-widget-container';

    public static $SPECIAL_CHANNELS = [
        248,
        246,
        73
    ];

    /**
     * List of all supported currencies
     */
    public static $CURRENCIES = [
        'EUR',
        'USD',
        'GBP',
        'JPY',
        'CZK',
        'SEK',
        'UAH',
        'RON',
        'PLN',
        'NOK',
        'BGN',
        'CHF',
        'HRK',
        'HUF',
        'RUB'
    ];

    /**
     * @var string Id of plugin where is used SDK
     */
    private $pluginId = '';

    /**
     * @var string Version of used plugin
     */
    private $pluginVersion = '';

    /**
     * @var boolean Flag which inform if Dotpay payment is enabled in a shop
     */
    private $enable = false;

    /**
     * @var boolean Flag if this account was migrated from Dotpay to Przelewy24 Api
     */
    private $DProxyP24Migrated = true;


    /**
     * @var int|null Seller id
     */
    private $id = null;

    /**
     * @var string Seller pin
     */
    private $pin = '';

    /**
     * @var string Username of Dotpay seller dashboard
     */
    private $username = '';

    /**
     * @var string Password of Dotpay seller dashboard
     */
    private $password = '';

    /**
     * @var boolean Flag if test mode is activated
     */
    private $testMode = false;

    /**
     * @var boolean Flag if 'server does not use a proxy' mode is activated
     */
    private $nonproxyMode = true;

    /**
     * @var boolean Default currency for ID
     */
    private $DefaultCurrency = 'PLN';
    


    /**
     * @var boolean Flag if One Click card channel is visible
     */
    private $ocVisible = false;

    /**
     * @var boolean Flag if card channel for foreign currencies is visible
     */
    private $fccVisible = false;

    /**
     * @var int|null Seller id for an account which is signed to support payment by card using foreign currencies
     */
    private $fccId = null;

    /**
     * @var string Seller pin for an account which is signed to support payment by card using foreign currencies
     */
    private $fccPin = '';

    /**
     * @var string Codes of currencies for which is allowed the FCC card channel.
     * Every code is separated by "," character.
     */
    private $fccCurrencies = '';

    /**
     * @var boolean Flag if normal card channel is visible
     */
    private $ccVisible = false;

    /**
     * @var boolean Flag if BLIK channel is visible
     */
    private $blikVisible = false;

    /**
     * @var boolean Flag if Dotpay widget is visible on a payment page
     */
    private $widgetVisible = true;

    /**
     * @var string Codes of currencies for which is disallowed the Dotpay main channel.
     * Every code is separated by "," character.
     */
    private $widgetCurrencies = '';

    /**
     * @var boolean Flag if payment instruction of cash or transfer channels should be visible on a shop site
     */
    private $instructionVisible = true;

    /**
     * @var boolean Flag if refunds requesting is enabled from a shop system
     */
    private $refundsEnable = false;

    /**
     * @var boolean Flag if renew payments are enabled for customers
     */
    private $renew = false;

    /**
     * @var int Number of days, how long after creating an order should be available renew option
     */
    private $renewDays = 0;

    /**
     * @var boolean Flag if special surcharge is enabled
     */
    private $surcharge = false;

    /**
     * @var float Amount which will be added as a surcharge
     */
    private $surchargeAmount = 0.0;

    /**
     * @var float Percent of value of order which will be added as a surcharge
     */
    private $surchargePercent = 0.0;

    /**
     * @var string Name of shop which is sent to Dotpay server
     */
    private $shopName = '';

    /**
     * @var string Payment API version
     */
        //private $api = 'dev'; // depreciated
    private $api = 'next'; // current - new method for calculating the chk parameter

    /**
     * @var array List of all visible channels id
     */
    private $visibleChannels = [];

    /**
     * Initialize the model
     * @param string $pluginId Name of the plugin which uses the Configuration
     */
    public function __construct($pluginId)
    {
        $this->setPluginId($pluginId);
    }

    /**
     * Return plugin id
     * @return string
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * Return plugin version
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->pluginVersion;
    }

    /**
     * Return an information if Dotpay payment is enabled on the shop site
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }


    /**
     * Checks if this account was migrated from Dotpay to Przelewy24 Api
     * @return boolean
     */
    public function getDProxyP24Migrated()
    {
        return $this->dproxyP24Migrated;
    }


    /**
     * Return seller id
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return seller pin
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Return username of Dotpay seller dashboard
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Return password of Dotpay seller dashboard
     * @return password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Check if seller id and pin are not empty
     * @return boolean
     */
    public function isGoodAccount()
    {
        return !(empty(trim($this->id)) || empty(trim($this->pin)));
    }

    /**
     * Check if username and password are not empty
     * @return boolean
     */
    public function isGoodApiData()
    {
        return !(empty(trim($this->username)) || empty(trim($this->password)));
    }

    /**
     * Check if test mode is enabled
     * @return boolean
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    
    /**
     * Check if non proxy is enabled
     * @return boolean
     */
    public function getNonProxyMode()
    {
        return $this->nonproxyMode;
    }

    /**
     * Get default currency for ID
     * @return boolean
     */
    public function getDefaultCurrency()
    {
        return $this->DefaultCurrency;
    }


    /**
     * Check if the One Click card channel is set as visible
     * @return boolean
     */
    public function getOcVisible()
    {
        return $this->ocVisible;
    }

    /**
     * Check if the One Click card channel is enabled to use
     * @return boolean
     */
    public function isOcEnable()
    {
        return $this->getOcVisible() &&
               !(empty(trim($this->username)) &&
                 empty(trim($this->password)));
    }

    /**
     * Check if card channel for foreign currency is set as visible
     * @return boolean
     */
    public function getFccVisible()
    {
        return $this->fccVisible;
    }

    /**
     * Return seller id for the account which is asigned to card channel for foreign currency
     * @return int|null
     */
    public function getFccId()
    {
        return $this->fccId;
    }

    /**
     * Return seller pin for the account which is asigned to card channel for foreign currency
     * @return string
     */
    public function getFccPin()
    {
        return $this->fccPin;
    }

    /**
     * Return a string which contains a list with currency codes for which card channel for foreign currencies is enabled
     * @return string
     */
    public function getFccCurrencies()
    {
        return $this->fccCurrencies;
    }

    /**
     * Check if card channel for foreign currencies is enabled
     * @return boolean
     */
    public function isFccEnable()
    {
        return $this->getFccVisible() &&
               !(empty(trim($this->fccId)) &&
                 empty(trim($this->fccPin)) &&
                 empty(trim($this->fccCurrencies)));
    }

    /**
     * Check if normal card channel is set as visible
     * @return boolean
     */
    public function getCcVisible()
    {
        return $this->ccVisible;
    }

    /**
     * Check if BLIK channel is set as visible
     * @return boolean
     */
    public function getBlikVisible()
    {
        return $this->blikVisible;
    }

    /**
     * Check if Dotpay widget is set as visible
     * @return boolean
     */
    public function getWidgetVisible()
    {
        return $this->widgetVisible;
    }

    /**
     * Return a string which contains a list with currency codes for which main Dotpay channel is disabled
     * @return string
     */
    public function getWidgetCurrencies()
    {
        return $this->widgetCurrencies;
    }

    /**
     * Check if payment instruction of cash or transfer channels should be visible on a shop site
     * @return boolean
     */
    public function getInstructionVisible()
    {
        return $this->instructionVisible;
    }

    /**
     * Check if refunds requesting is enabled from a shop system
     * @return boolean
     */
    public function getRefundsEnable()
    {
        return $this->refundsEnable;
    }

    /**
     * Check if payments renew option is enabled
     * @return boolean
     */
    public function getRenew()
    {
        return $this->renew;
    }

    /**
     * Return a number of days after creating an order when payment can be renewed
     * @return int
     */
    public function getRenewDays()
    {
        return $this->renewDays;
    }

    /**
     * Return a flag if special surcharge is enabled
     * @return boolean
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * Return an amount which will be added as a surcharge
     * @return float
     */
    public function getSurchargeAmount()
    {
        return $this->surchargeAmount;
    }

    /**
     * Return a percent of value of order which will be added as a surcharge
     * @return float
     */
    public function getSurchargePercent()
    {
        return $this->surchargePercent;
    }

    /**
     * Check if opayment of rder placed on a given date can be renewed.
     * If number of days is 0, then payment of order can be renewed always.
     * @param DateTime $orderAddDate A date when an order has been placed
     * @return boolean
     */
    public function ifOrderCanBeRenewed(DateTime $orderAddDate) {
        $now = new DateTime();
        $numberOfRenewDays = $this->getRenewDays();
        return ($numberOfRenewDays == 0 || ($orderAddDate < $now && $now->diff($orderAddDate)->format("%a") < $numberOfRenewDays));
    }


	/**
	 * prepare data for the name of the shop so that it would be consistent with the validation
	 */
	public function NewShopName($value)
		{
			$NewShop_name1 = preg_replace('/[^\p{L}0-9\s\"\/\\:\.\$\+!#\^\?\-_@]/u','',$value);
			return $this->encoded_substrParams($NewShop_name1,0,300,60);
		}


   /**
     * Return a name of shop which is sent to Dotpay server
     * @return string
     */
    public function getShopName()
    {
        return $this->NewShopName($this->shopName);
    }


    /**
     * Return a list of all visible channels id, separated by ","
     * @return string
     */
    public function getVisibleChannels()
    {
        return implode(',', $this->visibleChannels);
    }

    /**
     * Return an array of all visible channels id
     * @return array
     */
    public function getVisibleChannelsArray()
    {
        return $this->visibleChannels;
    }

    /**
     * Check if channel with the given id is set as visible
     * @param int $id Channel id
     * @return boolean
     */
    public function isChannelVisible($id)
    {
        $channels = explode(',', $this->visibleChannels);
        return in_array($id, $channels);
    }

    /**
     * Return a payment API version
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Return an URL to Dotpay server for payments
     * @return string
     */
    public function getPaymentUrl($P24Check=false)
    {

        if($P24Check == 'p24_check') {
             return self::DPROXY_URL;
        }

        if (!$this->getTestMode()) {
            if (!$this->getDProxyP24Migrated()) {
                return self::PAYMENT_URL_PROD;
            }else{
                return self::DPROXY_URL;
            }
            
        } else {
            return self::PAYMENT_URL_DEV;
        }
    }

    /**
     * Return an URL to Dotpay server for seller API
     * @return string
     */
    public function getSellerUrl()
    {
        if (!$this->getTestMode()) {
            if (!$this->getDProxyP24Migrated()) {
                return self::SELLER_URL_PROD;
            }else{
                return self::DPROXY_SELLER_API_URL;
            }

        } else {
            return self::SELLER_URL_DEV;
        }
    }

    /**
     * Check if Dotpay payments support the given currency
     * @param string $currency Currency code
     * @return boolean
     */
    public function isGatewayEnabled($currency)
    {
        return $this->isCurrencyOnList($currency, implode(',', self::$CURRENCIES));
    }

    /**
     * Check if card channel for foreigner currencies can be used for the given currency
     * @param string $currency Currency code
     * @return boolean
     */
    public function isCurrencyForFcc($currency)
    {
        return $this->isCurrencyOnList($currency, $this->getFccCurrencies());
    }

    /**
     * Check if Dotpay widget can be used for the given currency
     * @param string $currency Currency code
     * @return boolean
     */
    public function isWidgetEnabled($currency)
    {
        return !$this->isCurrencyOnList($currency, $this->getWidgetCurrencies());
    }

    /**
     * Return a shop IP or null if it's not possible to read
     * @return string|null
     */
    public function getShopIp()
    {
        $ip = null;
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (function_exists('php_sapi_name') && php_sapi_name() == 'cli') {
            $ip = gethostbyname(gethostname());
        }
        return $ip;
    }

    /**
     * Set the given plugin id
     * @param string $pluginId Plugin id
     * @return Configuration
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = (string)$pluginId;
        return $this;
    }

    /**
     * Set the given plugin version
     * @param string $pluginVersion Plugin version
     * @return Configuration
     */
    public function setPluginVersion($pluginVersion)
    {
        $this->pluginVersion = (string)$pluginVersion;
        return $this;
    }

    /**
     * Set the flag if Dotpay payment is enabled in a shop
     * @param bool $enable Flag of enabling Dotpay payment
     * @return Configuration
     */
    public function setEnable($enable)
    {
        $this->enable = (bool)$enable;
        return $this;
    }

    /**
     * Set a flag that informs whether the Dotpay account has been migrated to Przelewy24
     * @param bool $DProxyP24Migrated mode flag
     * @return Configuration
     */
    public function setDProxyP24Migrated($dproxyP24Migrated)
    {
        $this->dproxyP24Migrated = (bool)$dproxyP24Migrated;
        return $this;
    }

    /**
     * Set the given seller id
     * @param int $id Seller id
     * @return Configuration
     * @throws IdException Thrown when the given seller id is incorrect
     */
    public function setId($id)
    {
        if (!Id::validate($id)) {
            throw new IdException($id);
        }
        $this->id = (int)$id;
        return $this;
    }

    /**
     * Set the given seller pin
     * @param string $pin Seller pin
     * @return Configuration
     * @throws PinException Thrown when the given seller pin is incorrect
     */
    public function setPin($pin)
    {
        if (!Pin::validate($pin)) {
            throw new PinException($pin);
        }
        $this->pin = (string)trim($pin);
        return $this;
    }

    /**
     * Set the given username for Dotpay dashboard
     * @param string $username Seller username
     * @return Configuration
     * @throws UsernameException Thrown when the given username is incorrect
     */
    public function setUsername($username)
    {
        if (!empty($username) && !Username::validate($username)) {
            throw new UsernameException($username);
        }
        $this->username = (string)trim($username);
        return $this;
    }

    /**
     * Set the given password for Dotpay dashboard
     * @param string $password Seller password
     * @return Configuration
     * @throws PasswordException Thrown when the given password is incorrect
     */
    public function setPassword($password)
    {
        if (!empty($password) && empty($password)) {
            throw new PasswordException();
        }
        $this->password = (string)trim($password);
        return $this;
    }

    /**
     * Set the flag which informs if test mode is enabled or not
     * @param bool $testMode Test mode flag
     * @return Configuration
     */
    public function setTestMode($testMode)
    {
        $this->testMode = (bool)$testMode;
        return $this;
    }


    /**
     * Set the flag which informs if non proxy is enabled or not
     * @param bool $NonProxyMode mode flag
     * @return Configuration
     */
    public function setNonProxyMode($nonproxyMode)
    {
        $this->nonproxyMode = (bool)$nonproxyMode;
        return $this;
    }



    /**
     * Set the list of codes of default currencies for ID
     * @param string $DefaultCurrency
     * @return Configuration
     */
    public function setDefaultCurrency($DefaultCurrency)
    {
            $this->DefaultCurrency = trim(strtoupper($DefaultCurrency));

        return $this;
    }


    /**
     * Set the flag which informs if One Click card channel is visible
     * @param bool $ocVisible One Click card channel visible flag
     * @return Configuration
     */
    public function setOcVisible($ocVisible)
    {
        $this->ocVisible = (bool)$ocVisible;
        return $this;
    }

    /**
     * Set the flag which informs if card channel for foreign currencies is visible
     * @param bool $fccVisible Card channel for foreign currencies visible flag
     * @return Configuration
     */
    public function setFccVisible($fccVisible)
    {
        $this->fccVisible = (bool)$fccVisible;
        return $this;
    }

    /**
     * Set the given seller id for the second account
     * @param int $fccId Seller id for an account which is signed to support payment by card using foreign currencies
     * @return Configuration
     * @throws IdException Thrown when the given seller id for the second account is incorrect
     */
    public function setFccId($fccId)
    {
        if ($this->getFccVisible()) {
            if (!empty($fcc) && !Id::validate($fccId)) {
                throw new IdException($fccId);
            }
            $this->fccId = (int)$fccId;
        }
        return $this;
    }

    /**
     * Set the given seller pin for the second account
     * @param string $fccPin Seller pin for an account which is signed to support payment by card using foreign currencies
     * @return Configuration
     * @throws PinException Thrown when the given seller pin is incorrect
     */
    public function setFccPin($fccPin)
    {
        if ($this->getFccVisible()) {
            if (!empty(trim($fccPin)) && !Pin::validate($fccPin)) {
                throw new PinException($fccPin);
            }
            $this->fccPin = (string)$fccPin;
        }
        return $this;
    }

    /**
     * Set the list of codes of currencies for which is allowed the FCC card channel
     * @param string $fccCurrencies List of codes of currencies for which is allowed the FCC card channel.
     * Every code is separated by "," character.
     * @return Configuration
     */
    public function setFccCurrencies($fccCurrencies)
    {
        if ($this->getFccVisible()) {
            $this->fccCurrencies = trim(strtoupper($fccCurrencies));
        }
        return $this;
    }

    /**
     * Set the flag if normal card channel is visible
     * @param boolean $ccVisible Flag if normal card channel is visible
     * @return Configuration
     */
    public function setCcVisible($ccVisible)
    {
        $this->ccVisible = (bool)$ccVisible;
        return $this;
    }

    /**
     * Set the flag if BLIK channel is visible
     * @param boolean $blikVisible  Flag if BLIK channel is visible
     * @return Configuration
     */
    public function setBlikVisible($blikVisible)
    {
        $this->blikVisible = (bool)$blikVisible;
        return $this;
    }

    /**
     * Set the flag if Dotpay widget is visible on a payment page
     * @param boolean $widgetVisible Flag if Dotpay widget is visible on a payment page
     * @return Configuration
     */
    public function setWidgetVisible($widgetVisible)
    {
        $this->widgetVisible = (bool)$widgetVisible;
        return $this;
    }

    /**
     * Set the list of currency codes for which is disallowed the Dotpay main channel
     * @param string $widgetCurrencies List of currency codes.
     * Every code is separated by "," character.
     * @return Configuration
     */
    public function setWidgetCurrencies($widgetCurrencies)
    {
        $this->widgetCurrencies = trim(strtoupper($widgetCurrencies));
        return $this;
    }

    /**
     * Set a flag if payment instruction of cash or transfer channels should be visible on a shop site
     * @param boolean $instructionVisible Flag if payment instruction of cash or transfer channels should be visible on a shop site
     * @return Configuration
     */
    public function setInstructionVisible($instructionVisible)
    {
        $this->instructionVisible = (bool)$instructionVisible;
        return $this;
    }

    /**
     * Set a flag if refunds requesting is enabled from a shop system
     * @param boolean $refundsEnable Flag of refunds enabling from shop sites
     * @return Configuration
     */
    public function setRefundsEnable($refundsEnable)
    {
        $this->refundsEnable = (bool)$refundsEnable;
        return $this;
    }

    /**
     * Set a flag if payment of order can be renewed
     * @param boolean $renew
     * @return Configuration
     */
    public function setRenew($renew)
    {
        $this->renew = (bool)$renew;
        return $this;
    }

    /**
     * Set a number of days when after placed an order the payment can be renewed
     * @param type $renewDays
     * @return Configuration
     */
    public function setRenewDays($renewDays)
    {
        if ($this->getRenew()) {
            if(!empty(trim($renewDays))) {
                $this->renewDays = (int)$renewDays;
            } else {
                $this->renewDays = 0;
            }
        }
        return $this;
    }

    /**
     * Set a flag if special surcharge is enabled
     * @param boolean $surcharge Flag if special surcharge is enabled
     * @return Configuration
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = (bool)$surcharge;
        return $this;
    }

    /**
     * Set an amount which will be added as a surcharge
     * @param float $surchargeAmount Amount which will be added as a surcharge
     * @return Configuration
     */
    public function setSurchargeAmount($surchargeAmount)
    {
        $this->surchargeAmount = $surchargeAmount;
        return $this;
    }

    /**
     * Set a percent of value of order which will be added as a surcharge
     * @param float $surchargePercent Percent of value of order which will be added as a surcharge
     * @return Configuration
     */
    public function setSurchargePercent($surchargePercent)
    {
        $this->surchargePercent = $surchargePercent;
        return $this;
    }

    /**
     * Set the given name of shop which is sent to Dotpay server
     * @param string $shopName Shop name
     * @return Configuration
     */
    public function setShopName($shopName)
    {
        $this->shopName = (string)$shopName;
        return $this;
    }

    /**
     * Set a list of all visible channels id, separated by ","
     * @param string $visibleChannels List of all visible channels id
     * @return Configuration
     */
    public function setVisibleChannels($visibleChannels)
    {
        if (empty($visibleChannels)) {
            $this->visibleChannels = [];
        } else {
            $this->visibleChannels = explode(',', $visibleChannels);
        }
        return $this;
    }

    /**
     * Add channel id to list of visible channels
     * @param int $id Channel id
     * @return Configuration
     */
    public function addVisibleChannel($id)
    {
        if(!in_array($id, $this->visibleChannels)) {
            $this->visibleChannels[] = (int)$id;
        }
        return $this;
    }

    /**
     * Remove channel id from list of visible channels
     * @param int $id Channel id
     * @return Configuration
     */
    public function removeVisibleChannel($id)
    {
        if(($index = array_search($id, $this->visibleChannels)) !== false) {
            unset($this->visibleChannels[$index]);
        }
        return $this;
    }

    /**
     * Set the given API version.
     * @param string $api Api version. Only "dev" or "next" is allowed
     * @return Configuration
     * @throws ApiVersionException Thrown when the given payment API version is different than the "dev" or "next" string
     */
    public function setApi($api)
    {
        if (($api != 'next') && ($api != 'dev')) {
            $this->addError(new ApiVersionException($api));
            return $this;
        }
        $this->api = $api;

        return $this;
    }


    /**
     * Check if the given currency is on the given list
     * @param string $currency Currency code
     * @param string $list A string which contains a list of currency codes. Every code is separated by "," character
     * @return booleanean
     */
    private function isCurrencyOnList($currency, $list)
    {
        $result = false;

        $allowCurrency = str_replace(';', ',', $list);
        $allowCurrency = strtoupper(str_replace(' ', '', $allowCurrency));
        $allowCurrencyArray =  explode(",", trim($allowCurrency));

        if (in_array(strtoupper($currency), $allowCurrencyArray)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Set a private property from child class
     * @param string $name Name of the property
     * @param mixed $value Value of the property
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }


    /**
     * Returns correct SERVER NAME or HOSTNAME
     * @return string
     */
    public function geShoptHost()
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
    //    return trim($host);

            if((bool) preg_match('/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,10}$/', trim($host))){
                $server_name = trim($host);
            } else {
                $server_name = "HOSTNAME";
            }
            
     return $server_name;   

    }



}

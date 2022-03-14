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

use Dotpay\Validator\Email;
use Dotpay\Validator\ChannelId;
use Dotpay\Exception\BadParameter\EmailException;
use Dotpay\Exception\BadParameter\ChannelIdException;
use Dotpay\Exception\BadIdException;

/**
 * Informations about a notification from Dotpay server
 */
class Notification
{
    /**
     * @var Operation Operation object with details of operation which relates the notification
     */
    private $operation;
    
    /**
     * @var string Email of a seller
     */
    private $shopEmail = '';
    
    /**
     * @var string Name of a shop
     */
    private $shopName = '';
    
    /**
     * @var int Id of used payment channel
     */
    private $channelId;
    
    /**
     * @var string Codename of a country of the payment instrument from which payment was made
     */
    private $channelCountry  ='';
    
    /**
     * @var string Codename of a country resulting from IP address from which the payment was made
     */
    private $ipCountry = '';
    
    /**
     * @var string code for a rejected transaction that describes the possible reason for a transaction being refused (Optional parameter)
     */
    private $sellerCode = '';
    

    /**
     * @var CreditCard|null CreditCard object if payment was realize by credit card and this information is allowed to send
     */
    private $creditCard = null;
    

    /**
     * @var string A data for credit card
     */
    private $ccissuernumber = null;
    private $ccmasked = null;
    private $ccexpyear = null;
    private $ccexpmonth = null;
    private $ccbrandcodename = null;
    private $ccbrandcode = null;
    private $ccunique = null;
    private $ccardid = null;


    /**
     * @var string Checksum of a Dotpay notification
     */
    private $signature = '';
    
    /**
     * Initialize the model
     * @param Operation $operation Details of operation which relates the notification
     * @param int $channel Id of used payment channel
     */
    public function __construct(Operation $operation, $channel = null)
    {
        $this->setOperation($operation);
        if ($channel != null) {
            $this->setChannelId($channel);
        }
    }
    
    /**
     * Return an Operation object with details of operation which relates the notification
     * @return Operation
     */
    public function getOperation()
    {
        return $this->operation;
    }
    
    /**
     * Return an email of a seller
     * @return string
     */
    public function getShopEmail()
    {
        return $this->shopEmail;
    }
   


	/**
     * Return a credit card data
     * @return float|null
     */
    public function getCCissuerNumber()
    {
        return $this->ccissuernumber;
    }
	
    public function getCCmasked()
    {
        return $this->ccmasked;
    }
    
	public function getCCexpYear()
    {
        return $this->ccexpyear;
    }
    
	public function getCCexpMonth()
    {
        return $this->ccexpmonth;
    }
   
   public function getCcbrandCodename()
    {
        return $this->ccbrandcodename;
    }
   
   public function getCcbrandCode()
    {
        return $this->ccbrandcode;
    }
    
	public function getCCuniq()
    {
        return $this->ccunique;
    }
    
	public function getCCcardId()
    {
        return $this->ccardid;
    }





	/**
	 * prepare data for the name of the shop so that it would be consistent with the validation
	 */
	public function NewShopName($value)
		{	
			$NewShop_name1 = preg_replace('/[^\p{L}0-9\s\"\/\\:\.\$\+!#\^\?\-_@]/u','',$value);
			return Customer::encoded_substrParams($NewShop_name1,0,300,60);
		}

   
    /**
     * Return a name of a shop
     * @return string
     */
    public function getShopName()
    {
		return self::NewShopName($this->shopName);
    }
    
    /**
     * Return an id of used payment channel
     * @return int
     */
    public function getChannelId()
    {
        return $this->channelId;
    }
    
    /**
     * Return a codename of a country of the payment instrument from which payment was made
     * @return string
     */
    public function getChannelCountry()
    {
        return $this->channelCountry;
    }
    
    /**
     * Return a codename of a country resulting from IP address from which the payment was made
     * @return string
     */
    public function getIpCountry()
    {
        return $this->ipCountry;
    }
   
    /**
     * Return a code for a rejected transaction that describes the possible reason for a transaction being refused (Optional parameter)
     * @return string
     */
    public function getSellerCode()
    {
        return $this->sellerCode;
    }    


    /**
     * Return a CreditCard object if payment was realize by credit card and this information is allowed to send
     * @return CreditCard|null
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }
    
    /**
     * Return a checksum of a Dotpay notification
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
    
    /**
     * Calculate a signature based on data from the notification and the given seller pin
     * @param string $pin Seller pin
     * @return string
     * @throws BadIdException Thrown when the seller if from request is unrecognized
     */
    public function calculateSignature($pin)
    {
        $sign=
                $pin.
                $this->getOperation()->getAccountId().
                $this->getOperation()->getNumber().
                $this->getOperation()->getType().
                $this->getOperation()->getStatus().
                (is_null($this->getOperation()->getAmount()) ? null : number_format($this->getOperation()->getAmount(),2, '.', '')).
                $this->getOperation()->getCurrency().
                (is_null($this->getOperation()->getWithdrawalAmount()) ? null : number_format($this->getOperation()->getWithdrawalAmount(),2, '.', '')).
                (is_null($this->getOperation()->getCommissionAmount()) ? null : number_format($this->getOperation()->getCommissionAmount(),2, '.', '')).
                $this->getOperation()->isCompletedString().
                (is_null($this->getOperation()->getOriginalAmount()) ? null : number_format($this->getOperation()->getOriginalAmount(),2, '.', '')).
                $this->getOperation()->getOriginalCurrency().
                $this->getOperation()->getDateTime()->format('Y-m-d H:i:s').
                $this->getOperation()->getRelatedNumber().
                $this->getOperation()->getControl().
                $this->getOperation()->getDescription().
                $this->getOperation()->getPayer()->getEmail().
                $this->getShopName().
                $this->getShopEmail();

        $sign.=    
                (is_null($this->getCCissuerNumber()) ? null : $this->getCCissuerNumber()).
                (is_null($this->getCcmasked()) ? null : $this->getCcmasked()).
                (is_null($this->getCCexpYear()) ? null : $this->getCCexpYear()).
                (is_null($this->getCCexpMonth()) ? null : $this->getCCexpMonth()).
                (is_null($this->getCcbrandCodename()) ? null : $this->getCcbrandCodename()).
                (is_null($this->getCcbrandCode()) ? null : $this->getCcbrandCode()).
                (is_null($this->getCCuniq()) ? null : $this->getCCuniq()).
                (is_null($this->getCCcardId()) ? null : $this->getCCcardId());

        $sign.=
                $this->getChannelId().
                $this->getChannelCountry().
                $this->getIpCountry().
                $this->getSellerCode();


        return hash('sha256', $sign);


    }
    
    /**
     * Set an Operation object with details of operation which relates the notification
     * @param Operation $operation Operation object with details of operation which relates the notification
     * @return Notification
     */
    public function setOperation(Operation $operation)
    {
        $this->operation = $operation;
        return $this;
    }
    
    /**
     * Set an email of a seller
     * @param string $shopEmail Email of a seller
     * @return Notification
     * @throws EmailException Thrown when the given seller email is incorrect
     */
    public function setShopEmail($shopEmail)
    {
        if (!Email::validate($shopEmail)) {
            throw new EmailException($shopEmail);
        }
        $this->shopEmail = (string)trim($shopEmail);
        return $this;
    }
    
    /**
     * Set a name of a shop
     * @param string $shopName Name of a shop
     * @return Notification
     */
    public function setShopName($shopName)
    {
        $this->shopName = (string)$shopName;
        return $this;
    }
    
    /**
     * Set an id of used payment channel
     * @param int $channelId Id of used payment channel
     * @return Notification
     * @throws ChannelIdException Thrown when the given channel id is incorrect
     */
    public function setChannelId($channelId)
    {
        if (!ChannelId::validate($channelId)) {
            throw new ChannelIdException($channelId);
        }
        $this->channelId = (int)$channelId;
        return $this;
    }
    
    /**
     * Set a codename of a country of the payment instrument from which payment was made
     * @param string $channelCountry Codename of a country of the payment instrument from which payment was made
     * @return Notification
     */
    public function setChannelCountry($channelCountry)
    {
        $this->channelCountry = (string)$channelCountry;
        return $this;
    }
    
    /**
     * Set a codename of a country resulting from IP address from which the payment was made
     * @param string $ipCountry Codename of a country resulting from IP address from which the payment was made
     * @return Notification
     */
    public function setIpCountry($ipCountry)
    {
        $this->ipCountry = (string)$ipCountry;
        return $this;
    }
  
     /**
     * Set a code for a rejected transaction that describes the possible reason for a transaction being refused
     * @param string $sellerCode code for a rejected transaction that describes the possible reason for a transaction being refused
     * @return Notification
     */
    public function setSellerCode($sellerCode)
    {
        $this->sellerCode = (string)$sellerCode;
        return $this;
    }   


    /**
     * Set a CreditCard object if payment was realize by credit card and this information is allowed to send
     * @param CreditCard $creditCard CreditCard object if payment was realize by credit card and this information is allowed to send
     * @return Notification
     */
    public function setCreditCard(CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }
    
 /**
     * Set a PaymentMethod with details of a payment
     * @param PaymentMethod $paymentMethod A PaymentMethod with details of a payment
     * @return Operation
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * Set a Masked payment card issuer number with which payment has been made.
     * @param string $ccissuernumber A Masked payment card issuer number
     * @return Operation
     */
    public function setCCissuerNumber($ccissuernumber)
    {
        $this->ccissuernumber = (string)$ccissuernumber;
        return $this;
    }

    /**
     * Set a Masked payment card number with which payment has been made.
     * @param string $ccmasked A Masked payment card number
     * @return Operation
     */
    public function setCcmasked($ccmasked)
    {
        $this->ccmasked = (string)$ccmasked;
        return $this;
    }

    /**
     * Set a Year expiration date of a payment card, which payment has been made.
     * @param string $ccexpyear A year expiration date of a payment card
     * @return Operation
     */
    public function setCCexpYear($ccexpyear)
    {
        $this->ccexpyear = (string)$ccexpyear;
        return $this;
    }

    /**
     * Set a Month  expiration date of a payment card, which payment has been made.
     * @param string $ccexpyear A month expiration date of a payment card
     * @return Operation
     */
    public function setCCexpMonth($ccexpmonth)
    {
        $this->ccexpmonth = (string)$ccexpmonth;
        return $this;
    }


    /**
     * Set a Payment card brand with which payment has been made.
     * @param string $ccbrandcodename A Payment card brand 
     * @return Operation
     */
    public function setCcbrandCodename($ccbrandcodename)
    {
        $this->ccbrandcodename = (string)$ccbrandcodename;
        return $this;
    }

        /**
     * Set a Payment card brand code with which payment has been made.
     * @param string $ccbrandcode A Payment card brand code 
     * @return Operation
     */
    public function setCcbrandCode($ccbrandcode)
    {
        $this->ccbrandcode = (string)$ccbrandcode;
        return $this;
    }


    /**
     * Set a unique identifier of the card registered in Dotpay.
     * @param string $ccunique A unique identifier of the card registered 
     * @return Operation
     */
    public function setCCuniq($ccunique)
    {
        $this->ccunique = (string)$ccunique;
        return $this;
    }
	
	/**
     * Set a Payment card ID given by Dotpay system.
     * @param string $ccardid A Payment card ID. 
     * @return Operation
     */
    public function setCCcardId($ccardid)
    {
        $this->ccardid = (string)$ccardid;
        return $this;
    }



    /**
     * Set a checksum of a Dotpay notification
     * @param string $signature Checksum of a Dotpay notification
     * @return Notification
     */
    public function setSignature($signature)
    {
        $this->signature = (string)trim($signature);
        return $this;
    }



}

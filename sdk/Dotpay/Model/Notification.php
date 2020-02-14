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
     * @var CreditCard|null CreditCard object if payment was realize by credit card and this information is allowed to send
     */
    private $creditCard = null;
    
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
            $this->getOperation()->getAmount().
            $this->getOperation()->getCurrency().
            $this->getOperation()->getWithdrawalAmount().
            $this->getOperation()->getCommissionAmount().
            $this->getOperation()->isCompletedString().
            $this->getOperation()->getOriginalAmount().
            $this->getOperation()->getOriginalCurrency().
            $this->getOperation()->getDateTime()->format('Y-m-d H:i:s').
            $this->getOperation()->getRelatedNumber().
            $this->getOperation()->getControl().
            $this->getOperation()->getDescription().
            $this->getOperation()->getPayer()->getEmail().
            $this->getShopName().
            $this->getShopEmail();
        if ($this->getCreditCard() !== null) {
            $sign.=
                $this->getCreditCard()->getIssuerId().
                $this->getCreditCard()->getMask().
                $this->getCreditCard()->getBrand()->getCodeName().
                $this->getCreditCard()->getBrand()->getName().
                $this->getCreditCard()->getCardId();
        }
        $sign.=
            $this->getChannelId().
            $this->getChannelCountry().
            $this->getIpCountry();

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

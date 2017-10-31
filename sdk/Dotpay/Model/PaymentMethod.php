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

use Dotpay\Validator\ChannelId;
use Dotpay\Exception\BadParameter\ChannelIdException;

/**
 * Informations about a payment method
 */
class PaymentMethod
{
    /**
     * @var int|null Payment channel id
     */
    private $channelId = null;
    
    /**
     * @var mixed Details of payment method
     */
    private $details = null;
    
    /**
     * @var int|null Type of defails of payment method
     */
    private $detailsType = null;
    
    /**
     * Details with bank account
     */
    const BANK_ACCOUNT = 1;
    
    /**
     * Details with credit card
     */
    const CREDIT_CARD = 2;
    
    /**
     * Initialize the model
     * @param int $channelId Payment channel id
     * @param mixed $details Details of payment method
     * @param int|null $detailsType Type of defails of payment method
     */
    public function __construct($channelId, $details = null, $detailsType = null)
    {
        $this->setChannelId($channelId);
        $this->setDetails($details);
        $this->setDetailsType($detailsType);
    }
    
    /**
     * Return a payment channel id
     * @return int|null
     */
    public function getChannelId()
    {
        return $this->channelId;
    }
    
    /**
     * Return a details of payment method
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }
    
    /**
     * Return a type of defails of payment method
     * @return int|null
     */
    public function getDetailsType()
    {
        return $this->detailsType;
    }
    
    /**
     * Set a payment channel id
     * @param type $channelId Payment channel id
     * @return PaymentMethod
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
     * Set a details of payment method
     * @param mixed $details Details of payment method
     * @return PaymentMethod
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }
    
    /**
     * Set a type of defails of payment method
     * @param int|null $type Type of defails of payment method
     * @return PaymentMethod
     */
    public function setDetailsType($type)
    {
        $this->detailsType = (int)$type;
        return $this;
    }
}

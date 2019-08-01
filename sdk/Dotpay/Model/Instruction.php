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

use Dotpay\Validator\OpNumber;
use Dotpay\Validator\BankNumber;
use Dotpay\Validator\ChannelId;
use Dotpay\Exception\BadParameter\OperationNumberException;
use Dotpay\Exception\BadParameter\BankNumberException;
use Dotpay\Exception\BadParameter\ChannelIdException;

/**
 * Informations about an instruction of payments by cash or transfer
 */
class Instruction
{
    /**
     * Name of the recipient of payment
     */
    const RECIPIENT_NAME = 'Dotpay sp. z o.o.';
    
    /**
     * Street of the recipient of payment
     */
    const RECIPIENT_STREET = 'Wielicka 28B';
    
    /**
     * Post code and city of the recipient of payment
     */
    const RECIPIENT_CITY = '30-552 Kraków';
    
    /**
     * @var int|null Id of the instruction in a shop
     */
    private $id = null;
    
    /**
     * @var int|null Id of order which is connected with the instruction
     */
    private $orderId = null;
    
    /**
     * @var string Number of payment.
     * It can be considered as a title of payment.
     * Its format is like an operation number of Dotpay.
     */
    private $number = '';
    
    /**
     * @var string Bank account number of Dotpay if the instruction applies to transfers payment
     */
    private $bankAccount = '';
    
    /**
     * @var int|null Id of channel which is used to make a payment
     */
    private $channel = null;
    
    /**
     * @var string Hash of payment which is used on Dotpay server
     */
    private $hash = '';
    
    /**
     * @var boolean|null Flag which informs if the instruction applies to cash payment
     */
    private $isCash = null;
    
    /**
     * Return an id of the instruction in a shop
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Return id of order which is connected with the instruction
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Return a number of payment
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * Return a bank account number of Dotpay if the instruction applies to transfers payment
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }
    
    /**
     * Return an id of channel which is used to make a payment
     * @return int|null
     */
    public function getChannel()
    {
        return $this->channel;
    }
    
    /**
     * Return a hash of payment which is used on Dotpay server
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
    
    /**
     * Return a flag which informs if the instruction applies to cash payment
     * @return boolean|null
     */
    public function getIsCash()
    {
        return $this->isCash;
    }
    
    public function getPdfUrl(Configuration $config)
    {
        return $config->getPaymentUrl().'instruction/pdf/'.$this->getNumber().'/'.$this->getHash().'/';
    }
    
    /**
     * Return a page of bank, where customer can make his transfer
     * @param Configuration $config Configuration object
     * @return string|null
     */
    public function getBankPage(Configuration $config)
    {
        return null;
    }
    
    /**
     * Return ufr of the page with original payment instruction
     * @param Configuration $config Configuration object
     * @return string
     */
    public function getPage(Configuration $config)
    {
        return $config->getPaymentUrl().'instruction/'.$this->getNumber().'/'.$this->getHash().'/';
    }
    
    /**
     * Set an id of the instruction in a shop
     * @param int $id Id of the instruction in a shop
     * @return Instruction
     */
    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }
    
    /**
     * Set an Order id which is connected with the instruction
     * @param int $orderId Id of order which is connected with the instruction
     * @return Instruction
     */
    public function setOrderId($orderId)
    {
        $this->orderId = (int)$orderId;
        return $this;
    }
    
    /**
     * Set a number of payment
     * @param string $number Number of payment.
     * @return Instruction
     * @throws OperationNumberException Thrown when the given number is incorrect
     */
    public function setNumber($number)
    {
        if (!OpNumber::validate($number)) {
            throw new OperationNumberException($number);
        }
        $this->number = (string)trim($number);
        return $this;
    }
    
    /**
     * Set a bank account number of Dotpay if the instruction applies to transfers payment
     * @param string $bankAccount Bank account number of Dotpay if the instruction applies to transfers payment
     * @return Instruction
     * @throws BankNumberException  Thrown when the given bank account number is incorrect
     */
    public function setBankAccount($bankAccount)
    {
        if (preg_match('/^\d{26}$/', trim($bankAccount)) === 1) {
            $bankAccount = 'PL'.$bankAccount;
        }
        if (!empty($bankAccount) && !BankNumber::validate($bankAccount)) {
            throw new BankNumberException($bankAccount);
        }
        $this->bankAccount = (string)trim($bankAccount);
        return $this;
    }
    
    /**
     * Set an id of channel which is used to make a payment
     * @param int $channel Id of channel which is used to make a payment
     * @return Instruction
     * @throws ChannelIdException  Thrown when the given channel id is incorrect
     */
    public function setChannel($channel)
    {
        if (empty($channel) || !ChannelId::validate($channel)) {
            throw new ChannelIdException($channel);
        }
        $this->channel = (int)$channel;
        return $this;
    }
    
    /**
     * Set a hash of payment which is used on Dotpay server
     * @param string $hash Hash of payment which is used on Dotpay server
     * @return Instruction
     */
    public function setHash($hash)
    {
        $this->hash = (string)trim($hash);
        return $this;
    }

    /**
     * Set a flag which informs if the instruction applies to cash payment
     * @param boolean $isCash Flag which informs if the instruction applies to cash payment
     * @return Instruction
     */
    public function setIsCash($isCash)
    {
        $this->isCash = (bool)$isCash;
        return $this;
    }
}

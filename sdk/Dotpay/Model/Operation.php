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

use \DateTime;
use Dotpay\Model\Configuration;
use Dotpay\Validator\OpNumber;
use Dotpay\Validator\Url;
use Dotpay\Validator\Id;
use Dotpay\Validator\Amount;
use Dotpay\Exception\BadParameter\UrlException;
use Dotpay\Exception\BadParameter\OperationNumberException;
use Dotpay\Exception\BadParameter\IdException;
use Dotpay\Exception\BadParameter\AmountException;
use Dotpay\Exception\BadParameter\CurrencyException;
use Dotpay\Exception\BadParameter\OperationTypeException;
use Dotpay\Exception\BadParameter\OperationStatusException;

/**
 * Informations about an operation
 */
class Operation
{
    /**
     * @var string An Url where are located details about the operation
     */
    private $url = '';
    
    /**
     * @var string A number of the operation
     */
    private $number = '';
    
    /**
     * @var DateTime|null A date and a time of the last change status of the operation
     */
    private $dateTime = null;
    
    /**
     * @var string An identifier of a type of the operation
     */
    private $type = '';
    
    /**
     * @var string A status identifier of the operation
     */
    private $status = '';
    
    /**
     * @var float|null A transaction amount
     */
    private $amount = null;
    
    /**
     * @var string A code of a transaction currency
     */
    private $currency = '';
    
    /**
     * @var float|null An original amount which was sent from a shop
     */
    private $originalAmount = null;
    
    /**
     * @var string A code of an original currency which was sent from a shop
     */
    private $originalCurrency = '';
    
    /**
     * @var float|null A withdrawal amount
     */
    private $withdrawalAmount = null;
    
    /**
     * @var float|null An amount of a Dotpay commission.
     * It's presented as a negative amount.
     */
    private $commissionAmount = null;
    
    /**
     * @var boolean|null A flag if operation is marked as completed in Seller panel
     */
    private $completed = null;
    
    /**
     * @var int|null An account id of a seller
     */
    private $accountId = null;
    
    /**
     * @var string A number of an operation which is related to the operation
     */
    private $relatedNumber = '';
    
    /**
     * @var string A description of the operation
     */
    private $description  ='';
    
    /**
     * @var mixed A value which was given during making a payment
     */
    private $control = null;
    
    /**
     * @var Payer|null A Payer object which contains information about payer
     */
    private $payer = null;
    
    /**
     * @var PaymentMethod|null A PaymentMethod with details of a payment
     */
    private $paymentMethod = null;
    
    /**
     * Status identifier of new operation
     */
    const STATUS_NEW = 'new';
    
    /**
     * Status identifier of processing operation
     */
    const STATUS_PROCESSING = 'processing';
    
    /**
     * Status identifier of completed operation
     */
    const STATUS_COMPLETE = 'completed';
    
    /**
     * Status identifier of rejected operation
     */
    const STATUS_REJECTED = 'rejected';
    
    /**
     * Type identifier of payment operation
     */
    const TYPE_PAYMENT = 'payment';
    
    /**
     * Type identifier of refund operation
     */
    const TYPE_REFUND = 'refund';
    
    /**
     * Type identifier of payout operation
     */
    const TYPE_PAYOUT = 'payout';
    
    /**
     * @var array List of all possible operation types
     */
    public static $types = [
        'payment',
        'refund'
    ];
    
    /**
     * @var array List of all possible statuses of operation
     */
    public static $statuses = [
        'new',
        'processing',
        'completed',
        'rejected',
        'processing_realization_waiting',
        'processing_realization'
    ];
    
    /**
     * Initialize the model
     * @param string $type An identifier of a type of the operation
     * @param string $number A number of the operation
     */
    public function __construct($type, $number)
    {
        $this->setType($type);
        $this->setNumber($number);
    }
    
    /**
     * Return an Url where are located details about the operation
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Return a number of the operation
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * Return an identifier of a type of the operation
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Return a status identifier of the operation
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Return a transaction amount
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * Return a code of a transaction currency
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    /**
     * Return a withdrawal amount
     * @return float|null
     */
    public function getWithdrawalAmount()
    {
        return $this->withdrawalAmount;
    }
    
    /**
     * Return an amount of a Dotpay commission.
     * It's presented as a negative amount.
     * @return float|null
     */
    public function getCommissionAmount()
    {
        return $this->commissionAmount;
    }
    
    /**
     * Return an original amount which was sent from a shop
     * @return float|null
     */
    public function getOriginalAmount()
    {
        return $this->originalAmount;
    }
    
    /**
     * Return a code of an original currency which was sent from a shop
     * @return string
     */
    public function getOriginalCurrency()
    {
        return $this->originalCurrency;
    }
    
    /**
     * Return a DateTime object with date and a time of the last change status of the operation
     * @return DateTime|null
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
    
    /**
     * Return a flag if operation is marked as completed in Seller panel
     * @return boolean|null
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * Return a string representation of $this->isCompleted() for calculating signature.
     *
     * @return string
     */
    public function isCompletedString()
    {
        if($this->isCompleted() === null)
        {
            return "";
        }
        elseif($this->isCompleted() === true)
        {
            return "true";
        }
        else
        {
            return "false";
        }
    }
    
    /**
     * Return an account id of a seller
     * @return int|null
     */
    public function getAccountId()
    {
        return $this->accountId;
    }
    
    /**
     * Return a number of an operation which is related to the operation
     * @return string
     */
    public function getRelatedNumber()
    {
        return $this->relatedNumber;
    }
    
    /**
     * Return a description of the operation
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Return a value which was given during making a payment
     * @return mixed
     */
    public function getControl()
    {
        return $this->control;
    }
    
    /**
     * Return a Payer object which contains information about payer
     * @return Payer|null
     */
    public function getPayer()
    {
        return $this->payer;
    }
    
    /**
     * Return a PaymentMethod with details of a payment
     * @return PaymentMethod|null
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
    
    /**
     * Set an Url where are located details about the operation
     * @param string $url An Url where are located details about the operation
     * @return Operation
     * @throws UrlException Thrown when the given url is incorrect
     */
    public function setUrl($url)
    {
        if (!Url::validate($url)) {
            throw new UrlException($url);
        }
        $this->url = (string)trim($url);
        return $this;
    }
    
    /**
     * Set a number of the operation
     * @param string $number A number of the operation
     * @return Operation
     * @throws OperationNumberException Thrown when the given operation number is incorrect
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
     * Set a DateTime object with date and a time of the last change status of the operation
     * @param DateTime $dateTime A date and a time of the last change status of the operation
     * @return Operation
     */
    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
        return $this;
    }
    
    /**
     * Set an identifier of a type of the operation
     * @param string $type An identifier of a type of the operation
     * @return Operation
     * @throws OperationTypeException Thrown when the given operation type is incorrect
     */
    public function setType($type)
    {
        if (array_search($type, self::$types) === false) {
            throw new OperationTypeException($type);
        }
        $this->type = (string)trim($type);
        return $this;
    }
    
    /**
     * Set a status identifier of the operation
     * @param string $status A status identifier of the operation
     * @return Operation
     * @throws OperationStatusException Thrown when the given operation status is incorrect
     */
    public function setStatus($status)
    {
        if (array_search($status, self::$statuses) === false) {
            throw new OperationStatusException($status);
        }
        $this->status = (string)trim($status);
        return $this;
    }
    
    /**
     * Set a transaction amount
     * @param float $amount A transaction amount
     * @return Operation
     * @throws AmountException Thrown when the given amount is incorrect
     */
    public function setAmount($amount)
    {
        if (!Amount::validate($amount)) {
            throw new AmountException($amount);
        }
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Set an original amount which was sent from a shop
     * @param string $currency A code of a transaction currency
     * @return Operation
     * @throws CurrencyException Thrown when the given currency is incorrect
     */
    public function setCurrency($currency)
    {
        $currency = strtoupper($currency);
        if (!in_array($currency, Configuration::$CURRENCIES)) {
            throw new CurrencyException($currency);
        }
        $this->currency = (string)trim($currency);
        return $this;
    }
    
    /**
     * Set an original amount which was sent from a shop
     * @param float $originalAmount An original amount which was sent from a shop
     * @return Operation
     * @throws AmountException Thrown when the given original amount is incorrect
     */
    public function setOriginalAmount($originalAmount)
    {
        if (!Amount::validate($originalAmount)) {
            throw new AmountException($originalAmount);
        }
        $this->originalAmount = $originalAmount;
        return $this;
    }
    
    /**
     * Set a code of an original currency which was sent from a shop
     * @param string $originalCurrency A code of an original currency which was sent from a shop
     * @return Operation
     * @throws CurrencyException Thrown when the given original currency is incorrect
     */
    public function setOriginalCurrency($originalCurrency)
    {
        $originalCurrency = strtoupper($originalCurrency);
        if (!in_array($originalCurrency, Configuration::$CURRENCIES)) {
            throw new CurrencyException($originalCurrency);
        }
        $this->originalCurrency = (string)trim($originalCurrency);
        return $this;
    }
    
    /**
     * Set a withdrawal amount
     * @param float $withdrawalAmount A withdrawal amount
     * @return Operation
     * @throws AmountException Thrown when the given withdrawal amount is incorrect
     */
    public function setWithdrawalAmount($withdrawalAmount)
    {
        if (!Amount::validate($withdrawalAmount)) {
            throw new AmountException($withdrawalAmount);
        }
        $this->withdrawalAmount = $withdrawalAmount;
        return $this;
    }
    
    /**
     * Set an amount of a Dotpay commission.
     * @param float $commissionAmount An amount of a Dotpay commission
     * @return Operation
     * @throws AmountException Thrown when the given commission amount is incorrect
     */
    public function setCommissionAmount($commissionAmount)
    {
        if (!Amount::validate($commissionAmount)) {
            throw new AmountException($commissionAmount);
        }
        $this->commissionAmount = $commissionAmount;
        return $this;
    }
    
    /**
     * Set a flag if operation is marked as completed in Seller panel
     * @param $completed A flag if operation is marked as completed in Seller panel
     * @return Operation
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
        return $this;
    }
    
    /**
     * Set an account id of a seller
     * @param int $accountId An account id of a seller
     * @return Operation
     * @throws IdException Thrown when the given seller id is incorrect
     */
    public function setAccountId($accountId)
    {
        if (!Id::validate($accountId)) {
            throw new IdException($accountId);
        }
        $this->accountId = (int)$accountId;
        return $this;
    }
    
    /**
     * Set a number of an operation which is related to the operation
     * @param string $relatedNumber A number of an operation which is related to the operation
     * @return Operation
     * @throws OperationNumberException Thrown when the given related operation number is incorrect
     */
    public function setRelatedNumber($relatedNumber)
    {
        if (!OpNumber::validate($relatedNumber)) {
            throw new OperationNumberException($relatedNumber);
        }
        $this->relatedNumber = (string)trim($relatedNumber);
        return $this;
    }
    
    /**
     * Set a description of the operation
     * @param string $description A description of the operation
     * @return Operation
     */
    public function setDescription($description)
    {
        $this->description = (string)$description;
        return $this;
    }
    
    /**
     * Set a value which was given during making a payment
     * @param mixed $control A value which was given during making a payment
     * @return Operation
     */
    public function setControl($control)
    {
        $this->control = $control;
        return $this;
    }
    
    /**
     * Set a Payer object which contains information about payer
     * @param Payer $payer A Payer object which contains information about payer
     * @return Operation
     */
    public function setPayer(Payer $payer)
    {
        $this->payer = $payer;
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
}

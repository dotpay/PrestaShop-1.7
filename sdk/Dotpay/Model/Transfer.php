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

use Dotpay\Validator\Amount;
use Dotpay\Exception\BadParameter\AmountException;

/**
 * Informations about a payout transfer
 */
class Transfer
{
    /**
     * @var float Amount of money
     */
    private $amount;
    
    /**
     * @var mixed Control identifier for the transfer
     */
    private $control;
    
    /**
     * @var BankAccount Bank account of the recipient
     */
    private $recipient;
    
    /**
     * @var string Description of the transfer
     */
    private $description;
    
    /**
     * Initialize the model
     * @param float $amount
     * @param mixed $control
     * @param BankAccount $recipient
     */
    public function __construct($amount, $control, BankAccount $recipient)
    {
        $this->setAmount($amount);
        $this->setControl($control);
        $this->setRecipient($recipient);
    }
    
    /**
     * Return an amount of money
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * Return a control identifier for the transfer
     * @return mixed
     */
    public function getControl()
    {
        return $this->control;
    }
    
    /**
     * Return a bank account of the recipient
     * @return BankAccount
     */
    public function getRecipient()
    {
        return $this->recipient;
    }
    
    /**
     * Return a description of the transfer
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set an amount of money
     * @param float $amount Amount of money
     * @return Transfer
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
     * Set a control identifier for the transfer
     * @param mixed $control Control identifier for the transfer
     * @return Transfer
     */
    public function setControl($control)
    {
        $this->control = $control;
        return $this;
    }
    
    /**
     * Set a bank account of the recipient
     * @param BankAccount $recipient Bank account of the recipient
     * @return Transfer
     */
    public function setRecipient(BankAccount $recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }
    
    /**
     * Set a description of the transfer
     * @param string $description Description of the transfer
     * @return Transfer
     */
    public function setDescription($description)
    {
        $this->description = (string)$description;
        return $this;
    }
}

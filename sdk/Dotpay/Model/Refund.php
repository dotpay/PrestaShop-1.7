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
use Dotpay\Validator\OpNumber;
use Dotpay\Exception\BadParameter\AmountException;
use Dotpay\Exception\BadParameter\OperationNumberException;

class Refund
{
    /**
     * @var string Number of payment for which is the refund
     */
    private $payment;
    
    /**
     * @var float Amount of refund
     */
    private $amount;
    
    /**
     * @var string Value which is used by comfirmation of the operation
     */
    private $control = '';
    
    /**
     * @var string Descriptiion of the refund
     */
    private $description = '';
    
    /**
     * Initialize the object model
     * @param string $payment Number of payment which is refunded
     * @param float $amount Amount of the refund
     * @param string $control Value which is used by comfirmation of the operation
     * @param string $description Description of refund
     */
    public function __construct($payment, $amount, $control = '', $description = '') {
        $this->setPayment($payment);
        $this->setAmount($amount);
        $this->setControl($control);
        $this->setDescription($description);
    }
    
    /**
     * Return a number of payment for which is the refund
     * @return string
     */
    public function getPayment() {
        return $this->payment;
    }
    
    /**
     * Return an amount of refund
     * @return float
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Return a value which is used by comfirmation of the operation
     * @return mixed
     */
    public function getControl() {
        return $this->control;
    }
    
    /**
     * Return a descriptiion of the refund
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Set a number of payment for which is the refund
     * @param string $payment Number of payment for which is the refund
     * @return Refund
     * @throws OperationNumberException Thrown when the given operation number is incorrect
     */
    public function setPayment($payment) {
        if(!OpNumber::validate($payment)) {
            throw new OperationNumberException($payment);
        }
        $this->payment = (string)trim($payment);
        return $this;
    }
    
    /**
     * Set an amount of refund
     * @param float $amount Amount of refund
     * @return Refund
     * @throws AmountException Thrown when the given amount is incorrect
     */
    public function setAmount($amount) {
        if(!Amount::validate($amount)) {
            throw new AmountException($amount);
        }
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Set a value which is used by comfirmation of the operation
     * @param mixed $control Value which is used by comfirmation of the operation
     * @return Refund
     */
    public function setControl($control) {
        $this->control = (string)$control;
        return $this;
    }
    
    /**
     * Set a description of the refund
     * @param string $description Descriptiion of the refund
     * @return Refund
     */
    public function setDescription($description) {
        $this->description = (string)$description;
        return $this;
    }
}
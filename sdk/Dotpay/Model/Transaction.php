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

use Dotpay\Validator\Url;
use Dotpay\Exception\BadParameter\UrlException;

/**
 * Informations about a transaction during which is realized the payment
 */
class Transaction
{
    /**
     * @var Payment Payment which is realized by the transaction
     */
    private $payment;
    
    /**
     * @var string Url where Dotpay server should redirect a customer
     */
    private $backUrl = '';
    
    /**
     * @var string Url where dotpay server should send a notification with status of payment
     */
    private $confirmUrl = '';
    
    /*
     * @var string Id of transaction which helps to identify it
     */
    private $control = '';
    
    /**
     * Initialize the model
     * @param Payment $payment Payment which is realized by the transaction
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
    
    /**
     * Return a payment which is realized by the transaction
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
    
    /**
     * Return an url where Dotpay server should redirect a customer
     * @return string
     */
    public function getBackUrl()
    {
        return $this->backUrl;
    }
    
    /**
     * Return an url where dotpay server should send a notification with status of payment
     * @return string
     */
    public function getConfirmUrl()
    {
        return $this->confirmUrl;
    }
    
    /**
     * Return a control string for the order
     * @return string
     */
    public function getControl()
    {
        if ($this->control) {
            return $this->control;
        } else {
            return (string)$this->getPayment()->getOrder()->getId();
        }
    }
    
    /**
     * Set an url where Dotpay server should redirect a customer
     * @param string $backUrl Url where Dotpay server should redirect a customer
     * @return Transaction
     * @throws UrlException Thrown when the given url is incorrect
     */
    public function setBackUrl($backUrl)
    {
        if (!Url::validate($backUrl)) {
            throw new UrlException($backUrl);
        }
        $this->backUrl = (string)trim($backUrl);
        return $this;
    }
    
    /**
     * Set an url where dotpay server should send a notification with status of payment
     * @param string $confirmUrl Url where dotpay server should send a notification with status of payment
     * @return Transaction
     * @throws UrlException Thrown when the given url is incorrect
     */
    public function setConfirmUrl($confirmUrl)
    {
        if (!Url::validate($confirmUrl)) {
            throw new UrlException($confirmUrl);
        }
        $this->confirmUrl = (string)trim($confirmUrl);
        return $this;
    }
    
    /**
     * Set the given control string
     * @param string $control The given control string
     * @return Transaction
     */
    public function setControl($control)
    {
        $this->control = (string)$control;
        return $this;
    }
}

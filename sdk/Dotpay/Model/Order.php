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
use Dotpay\Exception\BadParameter\CurrencyException;

/**
 * Informations about an order
 */
class Order
{
    /**
     * @var int An id of the order
     */
    private $id;
    
    /**
     * @var float An amount of the order
     */
    private $amount;
    
    /**
     * @var string A currency code of the order
     */
    private $currency;
    
    /**
     * @var string A reference of the order
     */
    private $reference = '';
    
    /**
     * Initialize the model
     * @param int $id An id of the order
     * @param float $amount An amount of the order
     * @param string $currency A currency code of the order
     */
    public function __construct($id, $amount, $currency)
    {
        $this->setId($id);
        $this->setAmount($amount);
        $this->setCurrency($currency);
    }
    
    /**
     * Return an id of the order
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Return an amount of the order
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * Return a currency code of the order
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    /**
     * Return a reference of the order.
     * If the reference is empty, then it returns an order id converted into a string.
     * @return string
     */
    public function getReference()
    {
        return empty($this->reference)?(string)$this->id:$this->reference;
    }
    
    /**
     * Set an id of the order
     * @param int $id An id of the order
     * @return Order
     */
    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }
    
    /**
     * Set an amount of the order
     * @param float $amount An amount of the order
     * @return Order
     * @throws AmountException Thrown when the given amount is incorrect
     */
    public function setAmount($amount)
    {   
        $amount = number_format($amount, 2, '.', '');
        
        if (!Amount::validate($amount)) {
            throw new AmountException($amount);
        }
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Set a currency code of the order
     * @param string $currency A currency code of the order
     * @return Order
     * @throws CurrencyException Thrown when the given currency is incorrect
     */
    public function setCurrency($currency)
    {
        $currency = strtoupper($currency);
        if (!in_array($currency, Configuration::$CURRENCIES)) {
            throw new CurrencyException($currency);
        }
        $this->currency = (string)$currency;
        return $this;
    }
    
    /**
     * Set a reference of the order
     * @param string $reference A reference of the order
     * @return Order
     */
    public function setReference($reference)
    {
        $this->reference = (string)$reference;
        return $this;
    }
    
    /**
     * Return an amount of surcharge which is calculated for the order
     * @param Configuration $config Configuration object
     * @return float
     */
    public function getSurcharge(Configuration $config)
    {
        if (!$config->getSurcharge()) {
            return 0.0;
        }
        $exPercentage = $this->getAmount() * $config->getSurchargePercent()/100;
        $exAmount = $config->getSurchargeAmount();
        return max($exPercentage, $exAmount);
    }
}

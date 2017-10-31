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

use Dotpay\Exception\BadParameter\CurrencyException;

/**
 * Informations about a payout
 */
class Payout
{
    /**
     * @var string|null Currency code
     */
    private $currency = null;
    
    /**
     * @var array List of transfers to realize
     */
    private $transfers = [];
    
    /**
     * Initialize the model
     * @param string $currency Currency code
     */
    public function __construct($currency)
    {
        $this->setCurrency($currency);
    }
    
    /**
     * Return a currency code
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    /**
     * Return a list of transfers to realize
     * @return array
     */
    public function getTransfers()
    {
        return $this->transfers;
    }
    
    /**
     * Set a currency code
     * @param string $currency Currency code
     * @return Payout
     * @throws CurrencyException Thrown when the given currency code is incorrect
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
     * Add a new Transfer object to the list
     * @param Transfer $transfer A Transfer object
     * @return Payout
     */
    public function addTransfer(Transfer $transfer)
    {
        $this->transfers[] = $transfer;
        return $this;
    }
}

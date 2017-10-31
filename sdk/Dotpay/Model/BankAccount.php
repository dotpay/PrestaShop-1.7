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

use Dotpay\Validator\BankNumber;
use Dotpay\Exception\BadParameter\BankNumberException;

/**
 * Informations about a bank acount of payer
 */
class BankAccount
{
    /**
     * @var string|null Name of an owner of the bank account
     */
    private $name = null;
    
    /**
     * @var string|null Bank account number
     */
    private $number = null;
    
    /**
     * Initialize the model
     * @param string|null $name Name of an owner of the bank account
     * @param string|null $number Bank account number
     */
    public function __construct($name = null, $number = null)
    {
        $this->setName($name);
        $this->setNumber($number);
    }
    
    /**
     * Return a name of an owner of the bank account
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Return a bank account number
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * Set the given name of an owner of the bank account
     * @param string|null $name Name of an owner of the bank account
     * @return BankAccount
     */
    public function setName($name)
    {
        if (empty($name)) {
            $name = null;
        }
        $this->name = $name;
        return $this;
    }
    
    /**
     * Set the given bank account number
     * @param string|null $number Bank account number
     * @return BankAccount
     * @throws BankNumberException Thrown when the given bank account number is incorrect
     */
    public function setNumber($number)
    {
        if (preg_match('/^\d{26}$/', trim($number)) === 1) {
            $number = 'PL'.$number;
        }
        if (!empty($number) && !BankNumber::validate($number)) {
            throw new BankNumberException($number);
        }
        if (empty($number)) {
            $number = null;
        }
        $this->number = $number;
        return $this;
    }
}

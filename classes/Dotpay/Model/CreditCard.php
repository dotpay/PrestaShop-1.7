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

namespace Prestashop\Dotpay\Model;

use Dotpay\Loader\Loader;
use \DateTime;
use \Db;
use \Customer;

/**
 * Overriden class of credit card. It makes cards possible to save.
 */
class CreditCard extends \Dotpay\Model\CreditCard
{
    /**
     * Table name in a database where are saved credit cards
     */
    const TABLE_NAME = 'dotpay_credit_cards';
    
    /**
     * @var boolean Flag if the object was changed after loading from database
     */
    private $obtained = false;
    
    /**
     * Initialize the object
     * @param int|null $id Credit card object id
     * @param string $userId User's id of credit card
     */
    public function __construct($id, $userId = '')
    {
        if (!empty($userId)) {
            $this->setUserId($userId);
        }
        if ($id === null) {
            return;
        }
        $card = Db::getInstance()->ExecuteS(
            'SELECT *
            FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
            WHERE cc_id = '.(int)$id
        );
        if ($card == false || count($card) != 1) {
            return;
        }
        parent::__construct($card[0]['cc_id'], $card[0]['customer_id']);
        $this->setCustomerHash($card[0]['hash'])
             ->setRegisterDate(new DateTime($card[0]['register_date']))
             ->setOrderId($card[0]['order_id']);
        if (!empty($card[0]['mask'])) {
            $this->setMask($card[0]['mask']);
        }
        if (!empty($card[0]['card_id'])) {
            $this->setCardId($card[0]['card_id']);
        }
        $loader = Loader::load();
        if (!empty($card[0]['brand'])) {
            $this->setBrand($loader->get('CardBrand', array($card[0]['brand'])));
        }
        $this->obtained = true;
    }
    
    /**
     * Saves current object to database (add or update)
     * @return bool
     */
    public function save()
    {
        if ($this->getCustomerHash() == null) {
            $hash = $this->getUniqueCardHash();
            if ($hash) {
                $this->setCustomerHash($hash);
            } else {
                return false;
            }
        }
        if ($this->obtained) {
            if ($this->getBrand() !== null) {
                $this->getBrand()->save();
            }
            return $this->update();
        } else {
            return $this->add();
        }
    }
    
    /**
     * Add the credit card to database
     * @return bool
     */
    private function add()
    {
        $brand = ($this->getBrand()==null)?null:pSQL($this->getBrand()->getName());
        $cardId = ($this->getCardId())?'\''.pSQL($this->getRegisterDate()->format('Y-m-d')).'\'':'NULL';
        return Db::getInstance()->execute(
            'INSERT 
            INTO `'._DB_PREFIX_.self::TABLE_NAME.'`
            (
            order_id,customer_id,mask,brand,hash,card_id,register_date
            ) VALUES (
            '.(int)$this->getOrderId().',
            '.(int)$this->getUserId().',
            \''.pSQL($this->getMask()).'\',
            \''.$brand.'\',
            \''.pSQL($this->getCustomerHash()).'\','.
            $cardId
            .', \''.pSQL($this->getRegisterDate()->format('Y-m-d')).'\'
            )'
        );
    }
    
    /**
     * Update the credit card in database
     * @return boolean
     */
    private function update()
    {
        if ($this->getId() === null) {
            return false;
        }
        $brand = ($this->getBrand()==null)?null:pSQL($this->getBrand()->getName());
        $cardId = ($this->getCardId())?'\''.pSQL($this->getCardId()).'\'':'NULL';
        return Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'`
            SET
            order_id = '.(int)$this->getOrderId().',
            customer_id = '.(int)$this->getUserId().',
            mask = \''.pSQL($this->getMask()).'\',
            brand = \''.$brand.'\',
            hash = \''.pSQL($this->getCustomerHash()).'\',
            card_id = '.$cardId
            .', register_date = \''.pSQL($this->getRegisterDate()->format('Y-m-d')).'\'
            WHERE cc_id = '.(int)$this->getId()
        );
    }
    
    /**
     * Remove the credit card from database
     * @return boolean
     */
    public function delete()
    {
        if ($this->getId() === null) {
            return false;
        }
        return Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.self::TABLE_NAME.'`
            WHERE cc_id = '.(int)$this->getId()
        );
    }

    /**
     * Returns details of credit card
     * @param int $orderId Order id
     * @return CreditCard
     */
    public static function getCreditCardByOrder($orderId)
    {
        $card = Db::getInstance()->ExecuteS(
            'SELECT cc_id as id
            FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
            WHERE order_id = '.(int)$orderId
        );
        if (!count($card)) {
            return null;
        }
        return new self($card[0]['id']);
    }
    
    /**
     * Create table for this model
     * @return boolean
     */
    public static function install()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'` (
                `cc_id` BIGINT(20) UNSIGNED NOT null AUTO_INCREMENT,
                `order_id` INT UNSIGNED NOT null,
                `customer_id` INT UNSIGNED NOT null,
                `mask` varchar(20) DEFAULT null,
                `brand` varchar(20) DEFAULT null,
                `hash` varchar(100) NOT null,
                `card_id` VARCHAR(128) DEFAULT null,
                `register_date` DATE DEFAULT null,
                PRIMARY KEY (`cc_id`),
                UNIQUE KEY `hash` (`hash`),
                UNIQUE KEY `cc_order` (`order_id`),
                UNIQUE KEY `card_id` (`card_id`),
                KEY `customer_id` (`customer_id`),
                CONSTRAINT fk_customer_id
                    FOREIGN KEY (customer_id)
                    REFERENCES `'._DB_PREFIX_.Customer::$definition['table'].'` (`'.Customer::$definition['primary'].'`)
                    ON DELETE CASCADE
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
        );
    }
    
    /**
     * Drop table for this model
     * @return boolean
     */
    public static function uninstall()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'`;'
        );
    }
    
    /**
     * Get all cards for customer
     * @param int $customerId Id of the customer
     * @return array
     */
    public static function getAllCardsForCustomer($customerId)
    {
        $ids = Db::getInstance()->ExecuteS(
            'SELECT cc_id as id
            FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
            WHERE customer_id = '.(int)$customerId.' 
            AND 
            card_id IS NOT null'
        );
        $cards = array();
        foreach ($ids as $id) {
            $cards[] = new self($id['id']);
        }
        return $cards;
    }
    
    /**
     * Check, if generated card hash is unique
     * @return string|boolean
     */
    private function getUniqueCardHash()
    {
        $count = 200;
        $result = false;
        do {
            $cardHash = $this->generateUserId();
            $test = Db::getInstance()->ExecuteS(
                'SELECT count(*) as count  
                FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
                WHERE hash = \''.$cardHash.'\''
            );
            
            if ($test[0]['count'] == 0) {
                $result = $cardHash;
                break;
            }

            $count--;
        } while ($count);
        
        return $result;
    }
}

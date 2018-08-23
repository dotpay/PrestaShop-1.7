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

use \Db;

include_once(mydirname(dirname(__FILE__), 4).'/vendor/SimpleHtmlDom.php');

/**
 * Overriden class of payment instruction. It makes it possible to save.
 */
class Instruction extends \Dotpay\Model\Instruction
{
    /**
     * Table name in a database where are saved card brands
     */
    const TABLE_NAME = 'dotpay_instructions';
    
    /**
     * @var boolean Flag if the object was changed after loading from database
     */
    private $obtained = false;
    
    /**
     * @var Order ObjectNode of Order, assigned to this instruction
     */
    private $order;
    
    /**
     * Return the Order object which is assigned to this instrution
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set the Order object for the instruction
     * @param Order $order Order object
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
    
    /**
     * Initialize the model
     * @param int|null $orderId Id of order
     * @param int|null $channel Id of payment channel
     */
    public function __construct($orderId = null, $channel = null)
    {
        $this->initialize($orderId, $channel);
    }
    
    /**
     * Saves current object to database (add or update)
     * @return bool
     */
    public function save()
    {
        if ($this->obtained?$this->update():$this->add()) {
            $this->initialize($this->getOrderId(), $this->getChannel());
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Add the instruction to database
     * @return bool
     */
    private function add()
    {
        if ($this->getIsCash()) {
            $bankAccount = 'NULL,';
        } else {
            $bankAccount = '\''.pSQL($this->getBankAccount()).'\',';
        }
        return Db::getInstance()->execute(
            'INSERT 
            INTO `'._DB_PREFIX_.self::TABLE_NAME.'`
            (
            `order_id`, `number`, `hash`, `bank_account`, `is_cash`, `channel`
            ) VALUES (
            '.(int)$this->getOrderId().',
            \''.pSQL($this->getNumber()).'\',
            \''.pSQL($this->getHash()).'\','.
            $bankAccount.
            (int)$this->getIsCash().','.
            (int)$this->getChannel().'
            )'
        );
    }
    
    /**
     * Update the instruction in database
     * @return boolean
     */
    private function update()
    {
        if ($this->getId() === null) {
            return false;
        }
        if ($this->getIsCash()) {
            $bankAccount = 'bank_account = NULL,';
        } else {
            $bankAccount = 'bank_account = \''.pSQL($this->getBankAccount()).'\',';
        }
        return Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'`
            SET
            order_id = '.(int)$this->getOrderId().',
            number = \''.pSQL($this->getNumber()).'\',
            hash = \''.pSQL($this->getHash()).'\','.
            $bankAccount.
            'is_cash = '.(int)$this->getIsCash().',
            channel = '.(int)$this->getChannel().'
            WHERE instruction_id = '.(int)$this->getId()
        );
    }
    
    /**
     * Remove this instruction from database
     * @return boolean
     */
    public function delete()
    {
        if ($this->getId() !== null) {
            return Db::getInstance()->execute(
                'DELETE   
                FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
                WHERE instruction_id = '.(int)$this->getId()
            );
        } else {
            return false;
        }
    }
    
    /**
     * Remove all instructions for the order which is the same as in this instruction
     * @return boolean
     */
    public function deleteForOrder()
    {
        if ($this->getId() !== null) {
            return Db::getInstance()->execute(
                'DELETE   
                FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
                WHERE order_id = '.(int)$this->getOrderId()
            );
        } else {
            return false;
        }
    }
    
    /**
     * Return a page of bank, where customer can make his transfer
     * @param Configuration $config Configuration object
     * @return string|null
     */
    public function getBankPage(\Dotpay\Model\Configuration $config)
    {
        $html = \file_get_html($this->getPage($config));
        if ($html==false) {
            return null;
        }
        return $html->getElementById('channel_container_')->firstChild()->getAttribute('href');
    }

    /**
     * Create table for this model
     * @return boolean
     */
    public static function install()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'` (
                `instruction_id` INT UNSIGNED NOT null AUTO_INCREMENT,
                `order_id` INT UNSIGNED NOT null,
                `number` varchar(64) NOT null,
                `hash` varchar(128) NOT null,
                `bank_account` VARCHAR(64),
                `is_cash` int(1) NOT null,
                `channel` INT UNSIGNED NOT null,
                PRIMARY KEY (`instruction_id`)
            ) DEFAULT CHARSET=utf8;'
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
     * Initialize object from database if it exists there
     * @param int|null $orderId Id of order
     * @param int|null $channel Id of payment channel
     */
    protected function initialize($orderId, $channel)
    {
        if (!empty($orderId)) {
            $sql = 'SELECT *  
                FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
                WHERE order_id = '.(int)$orderId;
            if ($channel != null) {
                $sql .= ' AND channel = '.(int)$channel;
            }
            $instruction = Db::getInstance()->ExecuteS($sql);
            $index = count($instruction) - 1;
            if ($instruction !== false && $index >= 0) {
                $this->setId($instruction[$index]['instruction_id'])
                     ->setOrderId($orderId)
                     ->setNumber($instruction[$index]['number'])
                     ->setHash($instruction[$index]['hash'])
                     ->setChannel($instruction[$index]['channel'])
                     ->setIsCash($instruction[$index]['is_cash']);
                if (!$this->getIsCash()) {
                    $this->setBankAccount($instruction[$index]['bank_account']);
                }
                $this->obtained = true;
            }
        }
    }
}

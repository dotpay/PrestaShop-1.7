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

/**
 * Overriden class of card brand. It makes brands possible to save.
 */
class CardBrand extends \Dotpay\Model\CardBrand
{
    /**
     * Table name in a database where are saved card brands
     */
    const TABLE_NAME = 'dotpay_card_brands';
    
    /**
     * Prepares an object, if it's in a database
     * @param string $name Name of the brand of a credit card
     * @param string $image Url to the logo of the credit card brand
     * @param string|null $codeName Code name of the brand of a credit card
     */
    public function __construct($name, $image = '', $codeName = null)
    {
        $brand = Db::getInstance()->ExecuteS(
            'SELECT *  
            FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
            WHERE name = \''.$name.'\''
        );
        if ($brand != false && count($brand)) {
            parent::__construct($brand[0]['name'], $brand[0]['image'], $brand[0]['codename']);
        } elseif (!(empty($name) || empty($image))) {
            parent::__construct($name, $image, $codeName);
        }
    }
    
    /**
     * Saves current object to database (add or update)
     * @return bool
     */
    public function save()
    {
        return Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.self::TABLE_NAME.'`
                (name, image, codename)
            VALUES
                (\''.pSQL($this->getName()).'\', \''.pSQL($this->getImage()).'\', \''.pSQL($this->getCodeName()).'\')
            ON DUPLICATE KEY UPDATE
                name  = \''.pSQL($this->getName()).'\',
                image = \''.pSQL($this->getImage()).'\',
                codename = \''.pSQL($this->getCodeName()).'\''
        );
    }

    /**
     * Create table for this model
     * @return boolean
     */
    public static function install()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'` (
                `name` varchar(20) NOT null,
                `image` varchar(192) DEFAULT null,
                `codename` varchar(20) DEFAULT null,
                PRIMARY KEY (`name`),
                UNIQUE KEY `brand_img` (`image`)
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
}

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
namespace Dotpay\Html;

/**
 * Represent an abstract HTML element
 */
abstract class Element extends Node
{
    /**
     * @var string A type of the element
     */
    private $type;
    
    /**
     * Initialize the element
     * @param string $type A type of the element
     * @param string $name A name of the element
     */
    public function __construct($type = '', $name=null)
    {
        $this->setType($type);
        if ($name!==null) {
            $this->setName($name);
        }
    }
    
    /**
     * Return a type of the element
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Return a name of the element
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }
    
    /**
     * Return a class name of the element
     * @return string
     */
    public function getClass()
    {
        return $this->getAttribute('class');
    }
    
    /**
     * Return a data information
     * @param string $name A name of data information
     * @return string
     */
    public function getData($name)
    {
        return $this->getAttribute('data-'.$name);
    }
    
    /**
     * Set a type of the element
     * @param string $type A type of the element
     * @return Element
     */
    private function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Set a name of the element
     * @param string $name A name of the element
     * @return Element
     */
    public function setName($name)
    {
        return $this->setAttribute('name', $name);
    }
    
    /**
     * Set a class name of the element
     * @param string $className A class name
     * @return Element
     */
    public function setClass($className)
    {
        return $this->setAttribute('class', $className);
    }
    
    /**
     * Set the data value as the given name
     * @param string $name A name of a value
     * @param string $value A value to saving
     * @return Element
     */
    public function setData($name, $value)
    {
        return $this->setAttribute('data-'.$name, $value);
    }
    
    /**
     * Return a HTML string of the element
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }
}

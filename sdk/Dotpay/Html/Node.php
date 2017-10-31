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
 * Represent an abstract HTML node
 */
abstract class Node
{
    /**
     * @var array List of all attributes of the node
     */
    private $attributes = [];
    
    /**
     * Return a value of the attribute whoose name is given
     * @param string $name The name of the attribute
     * @return mixed
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name])?$this->attributes[$name]:null;
    }
    
    /**
     * Set a value of the atribute whoose name is given
     * @param string $name The name of the attribute
     * @param mixed $value The value of the attribute
     * @return Node
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    /**
     * Remove an attribute whoose name is given
     * @param string $name The name of the attribute
     * @return Node
     */
    public function removeAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
        return $this;
    }
    
    /**
     * Return an array with all attributes of the node element
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Return a string with a list of all attributes ot the node element
     * @return string
     */
    protected function getAttributeList()
    {
        $html = '';
        foreach ($this->getAttributes() as $name => $value) {
            $html .= ' '.$name.'=\''.$value.'\'';
        }
        return $html;
    }
    
    /**
     * Return a HTML string of the node element
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}

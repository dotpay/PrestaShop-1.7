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
namespace Dotpay\Loader\Xml;

/**
 * Param node in XML file with Dependency Injection rules.
 * It represents parameter, which is given during creating an object.
 */
class Param {
    /**
     * @var string A name of a class to which belongs an object which is a value of the parameter
     */
    private $className;
    
    /**
     * @var string A name of the parameter
     */
    private $name;
    
    /**
     * @var string An initial value of the parameter
     */
    private $value;
    
    /**
     * @var mixed A value which is stored in this parameter. 
     * It can be set after initialization of the object
     * and it can store for example an instance of other class.
     */
    private $storedValue;
    
    /**
     * Initialize the param object
     * @param string $className
     * @param string $name
     * @param mixed $value
     */
    public function __construct($className = '', $name = '', $value = '') {
        $this->className = (string)$className;
        $this->name = (string)$name;
        $this->value = (string)$value;
    }
    
    /**
     * Return a class name of an object which is a value of the param
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }
    
    /**
     * Return a name of the param
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Return a value which is set during initialization of the param object
     * @return string
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * Return a store value which is stored in the param object.
     * If a special value is set, then it's returned.
     * In other case can be returned a value which is set during initialization.
     * If none value is set, then will be returned a null value.
     * @return mixed
     */
    public function getStoredValue() {
        if(!empty($this->storedValue))
            return $this->storedValue;
        else if(!empty($this->getValue()))
            return $this->getValue();
        else
            return null;
    }
    
    /**
     * Set a value which can be stored in the parameter, after initialization the object
     * @param mixed $value A value which to store in the param object
     * @return Param
     */
    public function setStoredValue($value) {
        $this->storedValue = $value;
        return $this;
    }
    
    /**
     * Return a string which contains XML representation of the param object
     * @return string
     */
    public function getXml() {
        $element = '<param';
        if(!empty($this->getClassName()))
            $element .= ' class=\''.$this->getClassName().'\'';
        if(!empty($this->getName()))
            $element .= ' name=\''.$this->getName().'\'';
        if(!empty($this->getValue()))
            $element .= ' value=\''.$this->getValue().'\'';
        $element .= ' />';
        return $element;
    }
}
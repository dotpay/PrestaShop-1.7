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
namespace Dotpay\Html\Form;

use Dotpay\Html\Single;

/**
 * Represent a HTML input element
 */
class Input extends Single
{
    /**
     * Initialize the form element
     * @param string $type Type of the input element
     * @param string $name Name of the input element
     * @param mixed $value Value of the input element
     */
    public function __construct($type, $name = '', $value = null)
    {
        $this->setInputType($type);
        parent::__construct('input', $name);
        if ($value !== null) {
            $this->setValue($value);
        }
    }
    
    /**
     * Return a type of the input element
     * @return string
     */
    public function getInputType()
    {
        return $this->getAttribute('type');
    }
    
    /**
     * Return a value of the input element
     * @return mixed
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
    
    /**
     * Set a type of the input element
     * @param string $type A type of the input element
     * @return Input
     */
    public function setInputType($type)
    {
        return $this->setAttribute('type', $type);
    }
    
    /**
     * Set a type of the input element
     * @param mixed $value
     * @return Input
     */
    public function setValue($value)
    {
        return $this->setAttribute('value', $value);
    }
}

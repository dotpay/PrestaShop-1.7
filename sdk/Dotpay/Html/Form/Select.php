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

use Dotpay\Html\Container\Container;

/**
 * Represent a HTML select element
 */
class Select extends Container
{
    /**
     * @var Option An option element which is set as a selected option
     */
    private $selected;
    
    /**
     * Initialize the select element
     * @param string $name A name of the select element
     * @param array $options An array of options element which belong to the select element
     * @param Option $selected An option element which is set as a selected option
     */
    public function __construct($name = '', array $options = [], $selected = null)
    {
        parent::__construct('select', $options);
        if (!empty($name)) {
            $this->setName($name);
        }
        if (!empty($selected)) {
            $this->setSelected($selected);
        }
    }
    
    /**
     * Return an option element which is set as a selected option
     * @return Option
     */
    public function getSelected()
    {
        return $this->selected;
    }
    
    /**
     * Return an array of options element which belong to the select element
     * @return array
     */
    public function getOptions()
    {
        return $this->getChildren();
    }
    
    /**
     * Set an option element which contains the given value as the seleced option in this select element
     * @param mixed $value An value which can be also a text from an option element
     * @return Select
     */
    public function setSelected($value)
    {
        foreach ($this->getChildren() as $option) {
            if ($this->checkValue($option, $value)) {
                $this->selected = $option->setSelected();
            } else {
                $option->setSelected(false);
            }
        }
        return $this;
    }
    
    /**
     * Add a new option element to a list of options which belong to the select element
     * @param Option $option An option to add
     * @return Select
     */
    public function addOption(Option $option)
    {
        return $this->addChild($option);
    }
    
    /**
     * Remove an option element which contains the given value from the list of all options
     * @param mixed $value An value which can be also a text from an option element
     * @return Select
     */
    public function removeOption($value)
    {
        foreach ($this->getChildren() as $option) {
            if ($this->checkValue($option, $value)) {
                $this->removeChild($option);
                break;
            }
        }
        return $this;
    }
    
    /**
     * Check if the option element contains the given value or if the given value is a text of the option
     * @param Option $option An option element
     * @param mixed $value A given value
     * @return boolean
     */
    private function checkValue(Option $option, $value)
    {
        return $option->getValue() === $value ||
               ($option->getValue() === null && $option->getText() === $value);
    }
}

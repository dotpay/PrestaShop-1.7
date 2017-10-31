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
use Dotpay\Html\PlainText;

/**
 * Represent an option of an HTML select element
 */
class Option extends Container
{
    /**
     * Initialize the option element
     * @param string $text A text to displaying in the option element
     * @param mixed $value A value of the option element
     */
    public function __construct($text, $value = null)
    {
        parent::__construct('option');
        $this->setText($text);
        if (!empty($value)) {
            $this->setValue($value);
        }
    }
    
    /**
     * Return a value of the option element
     * @return mixed
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
    
    /**
     * Return a text to displaying in the option element
     * @return PlainText
     */
    public function getText()
    {
        $children = $this->getChildren();
        return $children[0];
    }
    
    /**
     * Set a value of the option element
     * @param mixed $value A value which is set
     * @return Option
     */
    public function setValue($value)
    {
        return $this->setAttribute('value', $value);
    }
    
    /**
     * Set a flag if the option element is selected
     * @param bool $mode A flad if the option element is selected
     * @return Option
     */
    public function setSelected($mode = true)
    {
        if ($mode) {
            return $this->setAttribute('selected', 'selected');
        } else {
            return $this->removeAttribute('selected');
        }
    }
    
    /**
     * Check if the option element is selected
     * @return boolean
     */
    public function isSelected()
    {
        return (bool)$this->getAttribute('selected');
    }
    
    /**
     * Set a text to displaying in the option element
     * @param string $text A text to displaying in the option element
     * @return Option
     */
    public function setText($text)
    {
        if (!empty($text)) {
            if (!($text instanceof PlainText)) {
                $text = new PlainText($text);
            }
            $this->setChildren([$text]);
        }
        return $this;
    }
}

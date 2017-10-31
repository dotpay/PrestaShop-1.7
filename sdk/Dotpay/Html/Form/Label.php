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

use Dotpay\Html\Node;

/**
 * Represent a HTML label element
 */
class Label extends Node
{
    /**
     *
     * @var Node An element which is inside the label
     */
    private $element;
    
    /**
     * @var string A text which is displayed on the left side of the inside element
     */
    private $llabel;
    
    /**
     *
     * @var string A text which is displayed on the right side of the inside element
     */
    private $rlabel;
    
    /**
     * Initialize the label element
     * @param Node $element An element which is inside the label
     * @param string $llabel A text which is displayed on the left side of the inside element
     * @param string $rlabel A text which is displayed on the right side of the inside element
     */
    public function __construct(Node $element, $llabel = '', $rlabel = '')
    {
        $this->element = $element;
        $this->setLLabel($llabel);
        $this->setRLabel($rlabel);
    }
    
    /**
     * Return an element which is inside the label
     * @return Node
     */
    public function getElement()
    {
        return $this->element;
    }
    
    /**
     * Return a text which is displayed on the left side of the inside element
     * @return string
     */
    public function getLLabel()
    {
        return $this->llabel;
    }
    
    /**
     * Return a text which is displayed on the right side of the inside element
     * @return string
     */
    public function getRLabel()
    {
        return $this->rlabel;
    }
    
    /**
     * Set a text which is displayed on the left side of the inside element
     * @param string $label A text to displaying on the left side
     * @return Label
     */
    public function setLLabel($label)
    {
        $this->llabel = $label;
        return $this;
    }
    
    /**
     * Set a text which is displayed on the right side of the inside element
     * @param string $label A text to displaying on the right side
     * @return Label
     */
    public function setRLabel($label)
    {
        $this->rlabel = $label;
        return $this;
    }
    
    /**
     * Return a HTML string of the label
     * @return string
     */
    public function __toString()
    {
        return '<label'.
                $this->getAttributeList().
                '>'.
                $this->getLLabel().
                (string)$this->getElement().
                $this->getRLabel().
                '</label>';
    }
}

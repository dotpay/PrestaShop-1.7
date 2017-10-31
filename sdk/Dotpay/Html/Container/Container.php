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
 * @copyright Dotpay sp. z o.o. sp. z o.o.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Dotpay\Html\Container;

use Dotpay\Html\Element;
use Dotpay\Html\Node;
use Dotpay\Html\PlainText;

/**
 * Represent an abstract container which can contain other HTML elements
 */
abstract class Container extends Element
{
    /**
     * @var array Elements which are children of the container
     */
    private $children = [];
    
    /**
     * Initialize the container
     * @param string $type Type of the container
     * @param array $children Children contained in the container
     */
    public function __construct($type, $children = [])
    {
        parent::__construct($type);
        if ($children instanceof Node) {
            $children = [$children];
        } elseif ($children === null ||
                is_scalar($children) ||
                is_callable([$children, '__toString'])) {
            $children = [new PlainText($children)];
        }
        $this->setChildren($children);
    }
    
    /**
     * Return children contained in the container
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * Add a new child to the container
     * @param Node $child A child of the container
     */
    public function addChild(Node $child)
    {
        $this->children[] = $child;
    }
    
    /**
     * Set a set of children for the container
     * @param array $children Children of the container
     */
    public function setChildren(array $children)
    {
        $this->children = [];
        foreach ($children as $child) {
            if ($child instanceof Node) {
                $this->addChild($child);
            }
        }
    }
    
    /**
     * Remove the given element from a set of children of the container
     * @param Node $child A HTML element which is a child of the container
     * @return Container
     */
    public function removeChild(Node $child)
    {
        foreach ($this->getChildren() as $index => $oneChild) {
            if ($oneChild === $child) {
                array_splice($this->children, $index, 1);
                break;
            }
        }
        return $this;
    }
    
    /**
     * Return a HTML string of the container
     * @return string
     */
    public function __toString()
    {
        $text = '<'.$this->getType().$this->getAttributeList().'>';
        foreach ($this->getChildren() as $child) {
            $text .= (string)$child;
        }
        $text .= '</'.$this->getType().'>';
        return $text;
    }
}

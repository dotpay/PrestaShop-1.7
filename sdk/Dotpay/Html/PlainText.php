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
 * Represent a plain text which is inserted into HTML without any tags
 */
class PlainText extends Node
{
    /**
     * @var string A content of the text element
     */
    private $text;
    
    /**
     * Initialize the plain text element
     * @param string $text A content of the text element
     */
    public function __construct($text = '')
    {
        $this->setText($text);
    }
    
    /**
     * Return a content of the text element
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * Set a content of the text element
     * @param string $text A content of the text element
     * @return PlainText
     */
    public function setText($text)
    {
        $this->text = (string)$text;
        return $this;
    }
    
    /**
     * Return a HTML string of the text element
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
    }
}

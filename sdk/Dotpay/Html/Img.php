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
 * Represent a HTML img element
 */
class Img extends Single
{
    /**
     * Initialize the img element
     * @param string $src An url of the image
     */
    public function __construct($src)
    {
        parent::__construct('img');
        $this->setSrc($src);
    }
    
    /**
     * Return the url address of the image
     * @return string
     */
    public function getSrc()
    {
        return $this->getAttribute('src');
    }
    
    /**
     * Return the alt text of the image
     * @return string
     */
    public function getAlt()
    {
        return $this->getAttribute('alt');
    }
    
    /**
     * Set the url address of the image
     * @param string $src The url address of the image
     * @return Img
     */
    public function setSrc($src)
    {
        return $this->setAttribute('src', $src);
    }
    
    /**
     * Set the alt text of the image
     * @param string $alt The alt text of the image
     * @return Img
     */
    public function setAlt($alt)
    {
        return $this->setAttribute('alt', $alt);
    }
}

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

/**
 * Represent HTML links
 */
class A extends Container
{
    /**
     * Initialize a link structure
     * @param string $href Url address
     * @param array $children Children of A element
     */
    public function __construct($href = null, $children = [])
    {
        parent::__construct('a', $children);
        $this->setHref($href);
    }
    
    /**
     * Return an url address
     * @return string
     */
    public function getHref()
    {
        return $this->getAttribute('href');
    }
    
    /**
     * Set an url address
     * @param type $href Url address
     * @return A
     */
    public function setHref($href)
    {
        return $this->setAttribute('href', $href);
    }
}

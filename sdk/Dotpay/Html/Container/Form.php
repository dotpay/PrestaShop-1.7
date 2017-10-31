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
namespace Dotpay\Html\Container;

/**
 * Represent HTML div block
 */
class Form extends Container
{
    /**
     * Initialize the block
     * @param array $children Children contained in the container
     */
    public function __construct($children = [])
    {
        parent::__construct('form', $children);
    }
    
    /**
     * Return a target URL
     * @return string
     */
    public function getAction() {
        return $this->getAttribute('action');
    }
    
    /**
     * Return an HTTP method
     * @return string
     */
    public function getMethod() {
        return $this->getAttribute('method');
    }
    
    /**
     * Set an action - target URL
     * @param string $action Target URL
     * @return mixed
     */
    public function setAction($action) {
        return $this->setAttribute('action', (string)$action);
    }
    /**
     * Set an HTTP method
     * @param string $method Name of used HTTP method
     * @return mixed
     */
    public function setMethod($method) {
        return $this->setAttribute('method', (string)$method);
    }
}

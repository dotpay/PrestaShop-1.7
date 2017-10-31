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
 * Represent HTML script block
 */
class Script extends Container
{
    /**
     * Initialize the block
     * @param array $children Children contained in the container
     * @param string|null $src Url to a file with a script
     */
    public function __construct($children = [], $src = null)
    {
        parent::__construct('script', $children);
        $this->setAttribute('type', 'text/javascript');
        if (!empty($src)) {
            $this->setSrc($src);
        }
    }
    
    /**
     * Set an url to a script file
     * @param string$src Url to a script file
     * @return type
     */
    public function setSrc($src)
    {
        return $this->setAttribute('src', $src);
    }
}

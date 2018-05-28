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

namespace Prestashop\Dotpay\Channel;

use \Context;
use \Tools;

/**
 * Overriden class of Dotpay main channel. It allows to adapt SDK features of channels for Prestashop
 */
class Dotpay extends \Dotpay\Channel\Dotpay
{
    /**
     * Return an URL of image which is a logo of the channel
     * @return string
     */
    public function getLogo()
    {
        if ($this->getChannelId() != null) {
            return parent::getLogo();
        } else {
            $baseUrl = Context::getContext()->link->getBaseLink();
            if (Tools::substr($baseUrl, -1, 1) !== '/') {
                $baseUrl .= '/';
            }
            return $baseUrl.'modules/'.$this->config->getPluginId().'/views/img/dotpay_logo_big.png';
        }
    }
}

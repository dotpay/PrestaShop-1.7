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

namespace Prestashop\Dotpay\Processor;

use \Tools;
use Dotpay\Tool\Checksum;

class Confirmation extends \Dotpay\Processor\Confirmation
{
    protected function completeInformations()
    {
        parent::completeInformations();
        $ext = Tools::getValue('ext')?explode(',', str_replace(' ', '', Tools::getValue('ext'))):array('php','tpl');
        $this->addOutputMessage('--- Dotpay Fee ---')
             ->addOutputMessage('Fee Enabled: '.(int)$this->config->getExtracharge())
             ->addOutputMessage('Fee Flat: '.$this->config->getExchargeAmount())
             ->addOutputMessage('Fee Percentage: '.$this->config->getExchargePercent(), true)
             ->addOutputMessage('--- Dotpay Discount ---')
             ->addOutputMessage('Discount Enabled: '.(int)$this->config->getReduction())
             ->addOutputMessage('Discount Flat: '.$this->config->getReductionAmount())
             ->addOutputMessage('Discount Percentage: '.$this->config->getReductionPercent(), true)
             ->addOutputMessage('--- Integrity ---')
             ->addOutputMessage('Checksum: '.Checksum::getForDir(mydirname(dirname(__FILE__), 4), $ext));
        if (Tools::getValue('files')) {
            $this->addOutputMessage("Files:<br>".Checksum::getFileList(mydirname(dirname(__FILE__), 4), "<br>", $ext));
        }
    }
}

<?php

namespace Prestashop\Dotpay\Processor;

use \Tools;
use Dotpay\Tool\Checksum;

class Confirmation extends \Dotpay\Processor\Confirmation 
{
    protected function completeInformations() 
	{
        parent::completeInformations();
        $ext = Tools::getValue('ext')?explode(',', str_replace(' ', '', Tools::getValue('ext'))):['php','tpl'];
        $this->addOutputMessage('--- Dotpay Fee ---')
             ->addOutputMessage('Fee Enabled: '.(int)$this->config->getExtracharge())
             ->addOutputMessage('Fee Flat: '.$this->config->getExchargeAmount())
             ->addOutputMessage('Fee Percentage: '.$this->config->getExchargePercent(), true)
             ->addOutputMessage('--- Dotpay Discount ---')
             ->addOutputMessage('Discount Enabled: '.(int)$this->config->getReduction())
             ->addOutputMessage('Discount Flat: '.$this->config->getReductionAmount())
             ->addOutputMessage('Discount Percentage: '.$this->config->getReductionPercent(), true)
             ->addOutputMessage('--- System Info ---')
             ->addOutputMessage('PrestaShop Version: '._PS_VERSION_)
             ->addOutputMessage('Module Version: '.$this->config->getPluginVersion(), true)
             ->addOutputMessage('--- Integrity ---')
             ->addOutputMessage('Checksum: '.Checksum::getForDir(mydirname(__DIR__, 4), $ext));
        if (Tools::getValue('files')) {
            $this->addOutputMessage("Files:<br>".Checksum::getFileList(mydirname(__DIR__, 4), "<br>", $ext));
        }
    }
}
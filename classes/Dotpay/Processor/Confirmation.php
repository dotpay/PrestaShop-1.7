<?php

namespace Prestashop\Dotpay\Processor;

class Confirmation extends \Dotpay\Processor\Confirmation {
    protected function completeInformations() {
        parent::completeInformations();
        $this->addOutputMessage('--- Dotpay Fee ---')
             ->addOutputMessage('Fee Enabled: '.(int)$this->config->getExtracharge())
             ->addOutputMessage('Fee Flat: '.$this->config->getExchargeAmount())
             ->addOutputMessage('Fee Percentage: '.$this->config->getExchargePercent(), true)
             ->addOutputMessage('--- Dotpay Discount ---')
             ->addOutputMessage('Discount Enabled: '.(int)$this->config->getReduction())
             ->addOutputMessage('Discount Flat: '.$this->config->getReductionAmount())
             ->addOutputMessage('Discount Percentage: '.$this->config->getReductionPercent());
    }
}
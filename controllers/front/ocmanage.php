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

use Dotpay\Loader\Loader;

require_once('dotpay.php');

class DotpayOcManageModuleFrontController extends DotpayController
{
    public function setMedia()
    {
        parent::setMedia();
        $this->registerJavascript(
            'dotpay-ocmanage',
            'modules/'.$this->module->name.'/views/js/ocmanage.js',
            array(
                'position' => 'bottom',
                'priority' => 10
            )
        );
        $this->registerStylesheet(
            'dotpay-ocmanage-style',
            'modules/'.$this->module->name.'/views/css/ocmanage.css',
            array(
              'media' => 'all',
              'priority' => 20,
            )
        );
        return true;
    }
    
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        $loader = Loader::load();
        $creditCard = $loader->get('CreditCard');
        $this->context->smarty->assign(array(
            'cards' => $creditCard::getAllCardsForCustomer($this->context->customer->id),
            'onRemoveMessage' => $this->module->l('Do you want to deregister a saved card'),
            'onDoneMessage' => $this->module->l('The card was deregistered'),
            'onFailureMessage' => $this->module->l('An error occurred while deregistering the card'),
            'removeUrl' => $this->context->link->getModuleLink($this->module->name, 'ocremove')
        ));
        
        return $this->setTemplate('module:dotpay/views/templates/front/ocmanage.tpl');
    }
}

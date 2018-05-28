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

include_once('dotpay.php');

/**
 * Controller for creating and displaying instructions of payments finishing for cash and transfer channels
 */
class DotpayInstructionModuleFrontController extends DotpayController
{
    /**
     * Add additional media (CSS and JS)
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet(
            'dotpay-instruction-style',
            'modules/'.$this->module->name.'/views/css/instruction.css',
            array(
              'media' => 'all',
              'priority' => 20,
            )
        );
    }
    
    /**
     * Display payment instruction for cash or transfer payments
     * @return mixed
     */
    public function initContent()
    {
        /**
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die($this->module->l('Dotpay module is inactive'));
        }
        
        parent::initContent();
        if (Tools::getValue('order') != false) {
            $this->setCart(Cart::getCartByOrderId(Tools::getValue('order')));
        }
        try {
            $this->initializeOrderData();
            $this->prepareChannel(new Order(Tools::getValue('order')));
        } catch (RuntimeException $ex) {
            die($ex->getMessage());
        }
        $loader = Loader::load();
        
        if (!$this->getChannel()->canHaveInstruction()) {
            die($this->module->l('Incorrect method for the given channel'));
        }
        
        try {
            $instruction = $loader->get('Instruction', array(Tools::getValue('order'), Tools::getValue('channel')));
            $initialized = false;
            if ($instruction->getId() == null) {
                $registerOrder = $loader->get('RegisterOrder');
                $instruction = $registerOrder->create($this->getChannel())->getInstruction();
                $instruction->save();
                $initialized = true;
            }
            if ($instruction->getIsCash()) {
                $buttonTitle = $this->module->l('Download form');
                $address = $instruction->getPdfUrl($this->getConfig());
            } else {
                $buttonTitle = $this->module->l('Make a money transfer');
                $address = $instruction->getBankPage($this->getConfig());
            }
            $this->context->smarty->assign(array(
                'instruction' => $instruction,
                'recipient_name' => $instruction::RECIPIENT_NAME,
                'recipient_street' => $instruction::RECIPIENT_STREET,
                'recipient_city' => $instruction::RECIPIENT_CITY,
                'order' =>  $this->getOrder(),
                'channel' =>  $this->getChannel(),
                'address' =>  $address,
                'buttonTitle' =>  $buttonTitle,
                'isOk' =>  true,
                'initialized' => $initialized
            ));
        } catch (RuntimeException $ex) {
            $this->errors[] = $this->module->l(
                'The error with initializing payment occured. Please try to refresh the page or conttact with seller.'
            );
            $this->context->smarty->assign(array(
                'isOk' =>  false
            ));
        }
        
        return $this->setTemplate('module:dotpay/views/templates/front/instruction.tpl');
    }
}

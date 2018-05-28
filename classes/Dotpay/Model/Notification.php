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

namespace Prestashop\Dotpay\Model;

use Dotpay\Loader\Loader;
use \Tools;
use \DateTime;

/**
 * Overriden class of payment instruction. It makes it possible to initialize the model by data from request (POST/GET)
 */
class Notification extends \Dotpay\Model\Notification
{
    /**
     * Initialize the model
     */
    public function __construct()
    {
        $loader = Loader::load();
        $loader->parameter('Operation:type', Tools::getValue('operation_type'));
        $loader->parameter('Operation:number', Tools::getValue('operation_number'));
        $operation = $loader->get('Operation');
        if (Tools::getValue('operation_datetime')) {
            $operation->setDateTime(new DateTime(Tools::getValue('operation_datetime')));
        }
        if (Tools::getValue('operation_status')) {
            $operation->setStatus(Tools::getValue('operation_status'));
        }
        if (Tools::getValue('operation_amount')) {
            $operation->setAmount(Tools::getValue('operation_amount'));
        }
        if (Tools::getValue('operation_currency')) {
            $operation->setCurrency(Tools::getValue('operation_currency'));
        }
        if (Tools::getValue('operation_original_amount')) {
            $operation->setOriginalAmount(Tools::getValue('operation_original_amount'));
        }
        if (Tools::getValue('operation_original_currency')) {
            $operation->setOriginalCurrency(Tools::getValue('operation_original_currency'));
        }
        if (Tools::getValue('is_completed')) {
            $operation->setCompleted(Tools::getValue('is_completed'));
        }
        if (Tools::getValue('operation_withdrawal_amount')) {
            $operation->setWithdrawalAmount(Tools::getValue('operation_withdrawal_amount'));
        }
        if (Tools::getValue('operation_commission_amount')) {
            $operation->setCommissionAmount(Tools::getValue('operation_commission_amount'));
        }
        if (Tools::getValue('id')) {
            $operation->setAccountId(Tools::getValue('id'));
        }
        if (Tools::getValue('description')) {
            $operation->setDescription(Tools::getValue('description'));
        }
        if (Tools::getValue('control')) {
            $operation->setControl(Tools::getValue('control'));
        }
        if (Tools::getValue('operation_related_number')) {
            $operation->setRelatedNumber(Tools::getValue('operation_related_number'));
        }
        if (Tools::getValue('email')) {
            $loader->parameter('Payer:email', Tools::getValue('email'));
            $operation->setPayer($loader->get('Payer'));
        }
        
        parent::__construct($operation, Tools::getValue('channel'));
        
        if (Tools::getValue('p_info')) {
            $this->setShopName(Tools::getValue('p_info'));
        }
        if (Tools::getValue('p_email')) {
            $this->setShopEmail(Tools::getValue('p_email'));
        }
        if (Tools::getValue('channel_country')) {
            $this->setChannelCountry(Tools::getValue('channel_country'));
        }
        if (Tools::getValue('geoip_country')) {
            $this->setIpCountry(Tools::getValue('geoip_country'));
        }
        if (Tools::getValue('signature')) {
            $this->setSignature(Tools::getValue('signature'));
        }
    }
}

<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Prestashop\Dotpay\Model\Instruction;
use Prestashop\Dotpay\Model\CreditCard;
use Prestashop\Dotpay\Model\CardBrand;
use Dotpay\Model\ChannelList;
use Dotpay\Model\Customer as DotpayCustomer;
use Dotpay\Resource\Channel\Agreement;
use Dotpay\Exception\IncompleteDataException;
use Dotpay\Channel\Channel;
use Dotpay\Exception\DotpayException;
use Dotpay\Exception\Resource\Account\NotFoundException as AccountNotFoundException;
use Prestashop\Dotpay\Model\Configuration as DotpayConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

include(dirname(__FILE__).'/sdk/dotpay.bootstrap.php');

/**
 * Load an overriden class
 * @param string $className Full name of class
 */
function dotpayOverrideApiLoader($className)
{
    $location = str_replace('Prestashop', 'classes', str_replace('\\', '/', $className));
    $path = dirname(__FILE__).'/'.$location.'.php';
    if (file_exists($path)) {
        include_once($path);
    }
}

spl_autoload_register('dotpayOverrideApiLoader');

/**
 * Dotpay plugin class
 */
class Dotpay extends PaymentModule
{
    protected $config_form = false;

    const REPOSITORY_NAME = 'PrestaShop-1.7';

    /**
     * @var Dotpay\Loader\Loader Instance of SDK Loader
     */
    protected $sdkLoader;

    /**
     * @var Prestashop\Dotpay\Model\Configuration Plugin configuration
     */
    protected $config;

    /**
     * Initialize the plugin
     */
    public function __construct()
    {
        $this->name = 'dotpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.4.1';
        $this->author = 'Dotpay';
        $this->need_instance = 1;
        $this->is_eu_compatible = 1;

        $this->sdkLoader = Dotpay\Loader\Loader::load(
            new Dotpay\Loader\Parser(dirname(__FILE__).'/sdk/Dotpay/di.xml'),
            new Dotpay\Loader\Parser(dirname(__FILE__).'/classes/Dotpay/di.xml')
        );

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Dotpay Payments');
        $this->description = $this->l('This module allows to pay via Dotpay');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Dotpay module?');

        $this->sdkLoader->parameter('Config:pluginId', $this->name);
        $this->config = $dpConfig = $this->sdkLoader->get('Config');
        $this->config->setPluginVersion($this->version);
        $this->limited_currencies = $dpConfig::$CURRENCIES;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Install the module
     * @return boolean
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        Module::updateTranslationsAfterInstall(false);
        try {
            return parent::install() &&
                $this->registerHook('displayHeader') &&
                $this->registerHook('displayBackOfficeHeader') &&
                $this->registerHook('displayCustomerAccount') &&
                $this->registerHook('displayAdminOrder') &&
                $this->registerHook('displayOrderDetail') &&
                $this->registerHook('paymentOptions') &&
                $this->addOrderWaitingStatus() &&
                $this->addTotalRefundStatus() &&
                $this->addPartialRefundStatus() &&
                $this->addWaitingRefundStatus() &&
                $this->addFailedRefundStatus() &&
                $this->addReturnTab() &&
                Instruction::install() &&
                CreditCard::install() &&
                CardBrand::install();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Uninstall the module
     * @return boolean
     */
    public function uninstall()
    {
        return parent::uninstall() &&
            Instruction::uninstall() &&
            CreditCard::uninstall() &&
            CardBrand::uninstall();
    }

    /**
     * Return an array with channels which are available
     * @param \Dotpay\Resource\Payment $paymentResource
     * @return array
     */
    private function getChannelList($paymentResource)
    {
        try {
            $config = $this->config;
            $seller = $this->sdkLoader->get('Seller', array($this->config->getId(), $this->config->getPin()));
            $customer = $this->sdkLoader->get('Customer', array('dotpay@dotpay.pl', 'Firstname', 'Lastname'));
            $customer->setLanguage($this->getLanguage());
            $order = $this->sdkLoader->get('Order', array(null, 301, 'PLN'));
            $payment = $this->sdkLoader->get('PaymentModel', array($customer, $order, ''));
            $payment->setSeller($seller);
            $info = $paymentResource->getChannelInfo($payment);
            $availableChannels = $info->getChannelList($config::$SPECIAL_CHANNELS);
            unset($info);
            $paymentResource->clearBuffer();
            return $availableChannels;
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitDotpayModule')) == true) {
            $this->saveConfiguration();
            $saved = true;
        } else {
            $saved = false;
        }

        try {
            $paymentResource = $this->sdkLoader->get('PaymentResource');
            $sellerResource = $this->sdkLoader->get('SellerResource');
            $testGoodApiData = $this->config->isGoodApiData();
            $testCorrectSellerForApi = true;
            $availableChannels = $this->getChannelList($paymentResource);
            try {
                $testSellerId = $paymentResource->checkSeller($this->config->getId(),'check');
                $testSellerIderror = $paymentResource->checkSeller($this->config->getId(),'error_code');
                $DotpayIDSellerName = $paymentResource->checkSeller($this->config->getId(),'receiver');
                $DotpayIDSeller = $this->config->getId();
                $testApiAccount = $sellerResource->isAccountRight();
                $testSellerPin = $sellerResource->checkPin();
            } catch (AccountNotFoundException $e) {
                $testSellerPin = true;
                $testCorrectSellerForApi = false;
            } catch (DotpayException $e) {
                $testSellerPin = false;
            }
            if (!isset($testSellerId)) {
                $testSellerId = false;
            }
            if (!isset($testSellerIderror)) {
                $testSellerIderror = false;
            }
            if (!isset($DotpayIDSellerName)) {
                $DotpayIDSellerName = false;
            }
            if (trim($this->config->getId()) == "") {
                $DotpayIDSeller = false;
            }
            
            if (!isset($testApiAccount)) {
                $testApiAccount = false;
            }
            try {
                $version = $this->sdkLoader->get('Github')->getLatestProjectVersion('dotpay', self::REPOSITORY_NAME);
                $number = $version->getNumber();
                $obsoletePlugin = version_compare($number, $this->version, '>');
                $canNotCheckPlugin = false;
            } catch (RuntimeException $e) {
                $obsoletePlugin = false;
                $canNotCheckPlugin = true;
                $number = $this->version;
            }

            $baseUrl2 = Context::getContext()->link->getBaseLink();
            if (Tools::substr($baseUrl2, -1, 1) !== '/') {
                $baseUrl2 .= '/';
            }

            $templateData = array(
                'repositoryName' => self::REPOSITORY_NAME,
                'moduleDir' => $this->_path,
                'regMessEn' => $this->config->getTestMode() || !$this->config->isGoodAccount(),
                'testMode' => $this->config->getTestMode(),
                'badIdMessage' => $this->l('Incorrect ID (required 6 digits)'),
                'badPinMessage' => $this->l('Incorrect PIN (minimum 16 and maximum 32 alphanumeric characters)'),
                'valueLowerThanZero' => $this->l('The value must be greater than zero.'),
                'targetForUrlc' => $this->context->link->getModuleLink(
                    'dotpay',
                    'confirm',
                    array('ajax' => '1'),
                    $this->isSSLEnabled()
                ),
                'oldVersion' => !version_compare(_PS_VERSION_, "1.7", ">="),
                'badPhpVersion' => !version_compare(PHP_VERSION, "5.6", ">="),
                'phpVersion' => PHP_VERSION,
                'minorPhpVersion' => '5.6',
                'confOK' => $this->config->isGoodAccount() && $this->config->getEnable(),
                'errorCodeID' => $testSellerIderror,
                'SellerIDName' => $DotpayIDSellerName,
                'SellerID' => $DotpayIDSeller,
                'moduleVersionGH' => $number,
                'moduleVersion' => $this->version,
                'testSellerId' => $testSellerId,
                'testApiAccount' => $testGoodApiData && !$testApiAccount,
                'testSellerPin' => $testGoodApiData && $testApiAccount && !$testSellerPin,
                'testCorrectSellerForApi' => !$testCorrectSellerForApi,
                'obsoletePlugin' => $obsoletePlugin,
                'canNotCheckPlugin' => $canNotCheckPlugin,
                'availableChannels' => $availableChannels,
            );
            if ($saved === false) {
                $templateData['universalErrorMessage'] = false;
            }
            $this->context->smarty->assign($templateData);
            $paymentResource->close();
            $sellerResource->close();
            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

            return $output.$this->renderForm();
        } catch (RuntimeException $e) {
            $this->context->smarty->assign(array(
                'class' => get_class($e),
                'message' => $e->getMessage()
            ));
            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/error.tpl');
        }
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $this->context->controller->addJS($this->_path.'views/js/chooseChannel.js');

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDotpayModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->config->getFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Module configuration'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable this payment module'),
                        'name' => 'DP_ENABLED',
                        'is_bool' => true,
                        'required' => true,
                        'desc' => $this->l('You can hide Dotpay payments without uninstalling the module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),array(
                        'type' => 'select',
                        'class' => 'fixed-width-xxl',
                        'label' => $this->l('Select used Dotpay API version'),
                        'name' => 'DP_API',
                        'desc' => '<b>'.$this->l('dev is only one version used in this plugin').
                                  '</b><br /><span id="message-for-old-version">'.
                                  $this->l(
                                      'if you use older version, please contact the Customer Care to change your '.
                                      'API version to dev'
                                  )
                                  .':&nbsp;<a href="'.$this->l('https://www.dotpay.pl/en/contact/').
                                  '" target="_blank"><b>'.$this->l('Contact').'</b></a></span>',
                        'required' => true,
                        'disabled' => true,
                        'class' => 'api-select',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option_api' => 'dev',
                                    'name_option_api' => $this->l('dev (ID has 6 digits)')
                                )
                            ),
                            'id' => 'id_option_api',
                            'name' => 'name_option_api'
                        ),
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_ID',
                        'prefix' => '<i style="font-weight: bold; color: #10279b; font-size: 1.4em;">&#35;</i>',
                        'label' => $this->l('ID'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-sm validate-gui',
                        'desc' => $this->l('Copy only number (without "#" char) from the Dotpay user panel.'),
                        'required' => true
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_PIN',
                        'prefix' => '<i class="icon-key" style="color: #10279b;"></i>',
                        'suffix' => '<i class="icon-eye-slash" id="eyelook" style="color: #2eacce; cursor : zoom-in;"></i>',
                        'label' => $this->l('PIN'),
                        'maxlength' => 32,
                        'class' => 'fixed-width-xxl validate-gui',
                        'desc' => $this->l('Copy from Dotpay user panel'),
                        'required' => true
                    ),array(
                        'type' => 'switch',
                        'label' => $this->l('Test mode'),
                        'name' => 'DP_TEST_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('I\'m using Dotpay test account (test ID)').
                                  '<br><b>'.$this->l('Required Dotpay test account:').
                                  ' <a href="https://ssl.dotpay.pl/test_seller/test/registration/?affilate_id='.
                                  'prestashop_module" target="_blank" title="'.
                                  $this->l('Dotpay test account registration').'">'.$this->l('registration').'</b></a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'switch',
                        'label' => $this->l('Enabling Dotpay widget'),
                        'name' => 'DP_WIDGET_EN',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Dotpay widget on shop site').'<br><b>'.
                                  $this->l('Disable this feature if you are using modules modifying checkout page').
                                  '</b>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'radio',
                        'label' => '<i class="icon-AdminTools" style="color: #10279b;"></i> <span class="dev-option advanced-mode-switch dotpayadvsett">'.$this->l('Advanced Mode').'</span>',
                        'name' => 'DP_ADV_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Show advanced plugin settings'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'radio',
                        'label' => $this->l('Enabling renew of payment'),
                        'is_bool' => true,
                        'class' => 'renew-enable-option',
                        'desc' => $this->l('Logged in clients can resume interrupted payments').'<br><b>'.
                                  $this->l(
                                      'Warning! Amount of renewed order will be the same as during '.
                                      'first payment attempt'
                                  )
                                  .'<br>'.$this->l('(changes in product prices will not be taken into account)').'</b>',
                        'name' => 'DP_RENEW',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_RENEW_DAYS',
                        'prefix' => '<i class="icon icon-calendar-o"></i>',
                        'label' => '<span class="renew-option">'.$this->l('Number of days after creating an order when is possible to renew payments').'</span>',
                        'size' => 3,
                        'maxlength' => 3,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->l('Enter for how many days customers will be able to renew their payments').
                                  '<br><b>'.$this->l('Leave blank if payment renew should not be restricted by time').
                                  '</b>',
                    ),array(
                        'type' => 'text',
                        'prefix' => '<i class="icon-money" style="color: #407786;"></i>',
                        'label' => '<span class="lastInSection">'.
                                   $this->l('Currencies for which main channel is disabled').'</span>',
                        'name' => 'DP_WIDGET_CURR',
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Enter currency codes separated by commas, for example: EUR,USD,GBP').
                                  '<br><b>'.
                                  $this->l('Leave this field blank to display the channel for all currencies').'</b>',
                    ),array(
                        'type' => 'switch',
                        'label' => $this->l('Enabling credit card channel'),
                        'name' => 'DP_CC',
                        'is_bool' => true,
                        'desc' => $this->l('Enable payment cards as separate channel'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'switch',
                        'label' => '<span class="lastInSection">'.$this->l('Enabling Blik channel').'</span>',
                        'name' => 'DP_BLIK',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Blik as separate channel').'<br><b>'.
                                  $this->l('Available only for PLN').'</b>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'switch',
                        'label' => $this->l('Enabling OneClick channel'),
                        'name' => 'DP_OC',
                        'is_bool' => true,
                        'desc' => $this->l('Enable payments with one click for credit card channel (248)').
                                  '<br><b>'.$this->l('Contact Dotpay customer service before using this option').
                                  ' <a href="http://www.dotpay.pl/kontakt/biuro-obslugi-klienta/" target="_blank" '.
                                  'title="'.$this->l('Dotpay customer service').'">'.$this->l('Contact').'</a><br>'.
                                  $this->l('Requires Dotpay API username and password (enter below).').'</b>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'switch',
                        'label' => $this->l('Enabling refunds of payment'),
                        'name' => 'DP_REFUND',
                        'is_bool' => true,
                        'desc' => $this->l('Enable sending payments refund requests directly from your shop').
                                  '<br><b>'.$this->l('Contact Dotpay customer service before using this option').
                                  ' <a href="http://www.dotpay.pl/kontakt/biuro-obslugi-klienta/" target="_blank" '.
                                  'title="'.$this->l('Dotpay customer service').'">'.$this->l('Contact').'</a><br>'.
                                  $this->l('Requires Dotpay API username and password (enter below).').'</b>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'switch',
                        'label' => $this->l('Payment instructions on shop site'),
                        'name' => 'DP_INSTR',
                        'is_bool' => true,
                        'desc' => $this->l('Display transfer payment instructions without redirecting to Dotpay site').
                                  '<br><b>'.$this->l('Contact Dotpay customer service before using this option').
                                  ' <a href="http://www.dotpay.pl/kontakt/biuro-obslugi-klienta/" target="_blank" '.
                                  'title="'.$this->l('Dotpay customer service').'">'.$this->l('Contact').'</a><br>'.
                                  $this->l('Requires Dotpay API username and password (enter below).').'</b>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_USERNAME',
                        'prefix' => '<i class="icon-male" style="color: #9b6610;"></i>',
                        'label' => $this->l('Dotpay panel username'),
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Your username for Dotpay user panel')
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_PASSWORD',
                        'prefix' => '<i class="icon-key" style="color: #9b6610;"></i>',
                        'label' => $this->l('Dotpay panel password'),
                        'class' => 'fixed-width-xxl password-field lastInSection',
                        'desc' => $this->l('Your password for Dotpay user panel'),
                    ),array(
                        'type' => 'radio',
                        'label' => $this->l('I have separate ID for foreign currencies'),
                        'name' => 'DP_FCC',
                        'is_bool' => true,
                        'class' => 'fcc-enable-option',
                        'desc' => $this->l('Enable separate payment channel for foreign currencies'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_FCC_ID',
                        'prefix' => '<i style="font-weight: bold; color: #407786; font-size: 1.4em;">&#35;</i>',
                        'label' => $this->l('ID for foreign currencies account'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-sm fcc-option validate-gui',
                        'desc' => $this->l('Copy only number (without "#" char) from the Dotpay user panel.').' <div id="infoID" /></div>',
                        'required' => true
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_FCC_PIN',
                        'prefix' => '<i class="icon-key" style="color: #407786;"></i>',
                        'maxlength' => 32,
                        'label' => $this->l('PIN for foreign currencies account'),
                        'class' => 'fixed-width-xxl fcc-option validate-gui',
                        'desc' => $this->l('Copy from Dotpay user panel').' <div id="infoPIN" /></div>',
                        'required' => true
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_FCC_CURR',
                        'prefix' => '<i class="icon-money" style="color: #407786;"></i>',
                        'label' => $this->l('Currencies used by foreign currencies account'),
                        'class' => 'fixed-width-xxl fcc-option lastInSection',
                        'desc' => $this->l('Enter currency codes separated by commas, for example: EUR,USD,GBP').
                                  '<br><b>'.$this->l('It is recommended to hide main channel for entered currencies').
                                  '</b>',
                    ),array(
                        'type' => 'radio',
                        'label' => $this->l('Information about surcharge'),
                        'name' => 'DP_SURCHARGE',
                        'is_bool' => true,
                        'class' => 'surcharge-enable-option',
                        'desc' => $this->l('Enable information about extra fee only on shop site').'<br />'.
                                  $this->l('Enabling this option needs to configure the seller account in Dotpay').
                                  '<br /><b>'.
                                  $this->l('Please contact with the Dotpay Customer Service before using this option').
                                  '&nbsp;'.'<a href="'.$this->l('https://www.dotpay.pl/en/contact/').'">'.
                                  $this->l('Contact').'</a></b><br />'.
                                  $this->l('Any value will not be add on shop site'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DP_SUR_AMOUNT',
                        'prefix' => '<i class="icon icon-money" style="color: #5498b0; font-weight: bold;"></i>',
                        'label' => $this->l('Show an information about increasing amount of order'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-lg surcharge-option validate-gui',
                        'desc' => $this->l('Value of additional fee for given currency (eg. 5.23)')
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_SUR_PERC',
                        'prefix' => '<i style="font-weight: bold; color: #5498b0; font-size: 1.4em;">&#37;</i>',
                        'label' => $this->l('Show an information about increasing amount of order (in %)'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-lg surcharge-option lastInSection validate-gui',
                        'desc' => $this->l('Value of additional fee for given currency in % (eg. 1.90)').'<br><b>'.
                                  $this->l('Bigger amount will be chosen').'</b>',
                    ),array(
                        'type' => 'radio',
                        'label' => $this->l('Extracharge option'),
                        'name' => 'DP_EXCHARGE',
                        'is_bool' => true,
                        'class' => 'excharge-enable-option',
                        'desc' => $this->l('Enable extra fee for Dotpay payment method').'<br><b>'.
                                  $this->l(
                                      'Enabling this option will add required "Online payment - DOTPAYFEE" '.
                                      'to your products'
                                  ).'</b>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_EX_AMOUNT',
                        'prefix' => '<i class="icon icon-money" style="color: #5498b0; font-weight: bold;"></i>',
                        'label' => $this->l('Increase amount of order'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-lg excharge-option validate-gui',
                        'desc' => $this->l('Value of additional fee for given currency (eg. 5.23)')
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_EX_PERC',
                        'prefix' => '<i style="font-weight: bold; color: #5498b0; font-size: 1.4em;">&#37;</i>',
                        'label' => $this->l('Increase amount of order (in %)'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-lg excharge-option lastInSection validate-gui',
                        'desc' => $this->l('Value of additional fee for given currency in % (eg. 1.90)').'<br><b>'.
                                  $this->l('Bigger amount will be chosen').'</b>',
                    ),array(
                        'type' => 'radio',
                        'label' => $this->l('Discount option of shipping costs'),
                        'name' => 'DP_REDUCT_SHIP',
                        'prefix' => '<i style="font-weight: bold; color: #5498b0; font-size: 1.4em;">&#37;</i>',
                        'is_bool' => true,
                        'class' => 'discount-enable-option',
                        'desc' => $this->l('Enable discount for Dotpay payment method'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable')
                            ),array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable')
                            )
                        )
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_RS_AMOUNT',
                        'prefix' => '<i class="icon icon-money" style="color: #5498b0; font-weight: bold;"></i>',
                        'label' => $this->l('Reduce amount of shipping costs'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-lg reduct-option validate-gui',
                        'desc' => $this->l('Value of discount amount (in current price)')
                    ),array(
                        'type' => 'text',
                        'name' => 'DP_RS_PERC',
                        'prefix' => '<i style="font-weight: bold; color: #5498b0; font-size: 1.4em;">&#37;</i>',
                        'label' => $this->l('Reduce amount of shipping costs (in %)'),
                        'size' => 6,
                        'maxlength' => 6,
                        'class' => 'fixed-width-lg reduct-option validate-gui lastInSection',
                        'desc' => $this->l('Value of discount for given currency in % (eg. 1.90)').'<br><b>'.
                                  $this->l('Bigger amount will be chosen').'</b>',
                    ),array(
                        'label' => $this->l('Isolated channels on the store page'),
                        'type' => 'text',
                        'name' => 'DP_CHANNELS',
                        'class' => 'chosen-channel-list',
                        'desc' => '<button id="add-new-channel" type="button"><i class="icon icon-plus"></i>&nbsp;'.
                                  $this->l('Add a new channel').'</button>'.
                                  $this->l(
                                      'Select which channels should be presented separately on the store page .The '.
                                      'same order will appear on your payment page.'
                                  ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-success center-block',
                ),
            ),
        );
    }

    /**
     * Returns language code for customer language
     * @return string
     */
    protected function getLanguage()
    {
        $lang = Tools::strtolower(LanguageCore::getIsoById($this->context->cookie->id_lang));
        if (in_array($lang, DotpayCustomer::$LANGUAGES)) {
            return $lang;
        } else {
            return "en";
        }
    }

    /**
     * Save form data.
     */
    protected function saveConfiguration()
    {
        $reductionFlagBefore = $this->config->getReduction();
        $extrachargeFlagBefore = $this->config->getExtracharge();

        $this->config->readFromForm()->persist();

        $reductionFlagAfter = $this->config->getReduction();
        $extrachargeFlagAfter = $this->config->getExtracharge();

        $universalErrorMessage = false;
        if ($extrachargeFlagBefore == false && $extrachargeFlagAfter == true) {
            $this->checkVirtualProduct();
            if ($this->config->getExtraChargeVirtualProductId() == 0) {
                $universalErrorMessage = $this->l(
                    'The error with switching extracharge option occured. Prease try to turn it on again.'
                );
            }
        }
        if ($reductionFlagBefore == false && $reductionFlagAfter == true) {
            $this->addShippingReduction();
            if ($this->config->getShippingReductionId() == 0) {
                $universalErrorMessage = $this->l(
                    'The error with switching shipping reduction occured. Prease try to turn it on again.'
                );
            }
        }
        $this->context->smarty->assign(array(
            'universalErrorMessage' => $universalErrorMessage
        ));
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the Back Office.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminOrders') {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/refunds.js');
        } elseif (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the Front Office.
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->registerJavascript(
            'jquery-transit',
            'modules/'.$this->name.'/views/js/jquery.transit.js',
            array(
                'position' => 'bottom',
                'priority' => 5
            )
        );
        $this->context->controller->registerJavascript(
            'dotpay-front',
            'modules/'.$this->name.'/views/js/front.js',
            array(
                'position' => 'bottom',
                'priority' => 150
            )
        );

        if ($this->context->controller instanceof OrderController) {
            $this->context->controller->registerJavascript(
                'dotpay-select-cards',
                'modules/'.$this->name.'/views/js/selectCards.js',
                array(
                    'position' => 'bottom',
                    'priority' => 10
                )
            );
            $this->context->controller->registerJavascript(
                'dotpay-animated-widget',
                'modules/'.$this->name.'/views/js/animatedWidget.js',
                array(
                    'position' => 'bottom',
                    'priority' => 10
                )
            );
        }
        $this->context->controller->addCSS('modules/'.$this->name.'/views/css/front.css');
    }

    /**
     * Add payment methods offered by the plugin
     * @param array $params Information about cart
     * @return PaymentOption
     */
    public function hookPaymentOptions($params)
    {
        if (!($this->active && $this->config->getEnable())) {
            return;
        }

        $currency = new Currency($params['cart']->id_currency);
        if (!$this->config->isGatewayEnabled($currency->iso_code)) {
            return;
        }

        $payment_options = array();
        try {
            $this->initializeChannelsDataFromCart($params);

            $cart = $this->context->cart;
            $var_id_address = $cart->id_address_invoice;
            $var_id_address_del = $cart->id_address_delivery;
            $address = new Address($var_id_address);
            $address_deliv = new Address($var_id_address_del);

            $channelsList = $this->getChannels();
            $order = $this->sdkLoader->get('Order');
            $exAmount = $order->getExtrachargeAmount($this->config);
            $surAmount = $order->getSurchargeAmount($this->config);
            $reductAmount = $order->getReductionAmount($this->config);
            if ($exAmount || $reductAmount || $surAmount) {
                $totalAmount = $order->getAmount() + $surAmount - $reductAmount;
                if (!$this->isVirtualProductInCart($this->config, $this->context->cart)) {
                    $totalAmount += $exAmount;
                }
            } else {
                $totalAmount = 0.0;
            }
            foreach ($channelsList as $channel) {
                $this->context->smarty->assign(array(
                    'channel' => $channel,
                    'modulePath' => $this->_path,
                    'dotpayUrl' => $this->config->getPaymentUrl(),
                    'script' => ($channel instanceof Dotpay\Channel\Dotpay)?$channelsList->getWidgetScript():'',
                    'exAmount' => $exAmount,
                    'surAmount' => $surAmount,
                    'reductAmount' => $reductAmount,
                    'currency' => $order->getCurrency(),
                    'surMessage' => $this->l('This payment will be increased by the additional surcharge'),
                    'exMessage' => $this->l('This payment will be increased by'),
                    'reductMessage' => $this->l('This payment will be reduced by'),
                    'agreementsMessage' => $this->l('Acceptance Dotpay regulation:'),
                    'totalMessage' => $this->l('Total amount for payment'),
                    'totalAmount' => number_format($totalAmount, 2, '.', ' '),
                    'isTestMode' => $this->config->getTestMode(),
                    'testModeMessage1' => $this->l('Attention!'),
                    'testModeMessage2' => $this->l(
                        'This store uses the Dotpay test payment mode. Payments are only simulated and your order '.
                        'will not be processed!'
                    ),
                    'jakisTest' => $address->address1.' deliv: '.$address_deliv->address1,
                ));
                $newOption = new PaymentOption();
                $newOption->setCallToActionText($channel->getTitle())
                          ->setAction($channel->get('target'))
                          ->setLogo($channel->getLogo())
                          ->setModuleName($this->name)
                          ->setForm($this->context->smarty->fetch('module:dotpay/views/templates/front/payment.tpl'));
                $payment_options[] = $newOption;
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
        $this->sdkLoader->get('PaymentResource')->close();
        $this->sdkLoader->get('SellerResource')->close();
        Dotpay\Loader\Loader::unload();
        return $payment_options;
    }

    /**
     * Return a list of channels which are available or not
     * @return ChannelList
     */
    private function getChannels()
    {
        $channelList = new ChannelList();

        $oneClick = $this->sdkLoader->get('Oc');
        $oneClick->setTitle($this->l("Credit Card - One Click"))
                 ->set(
                     'target',
                     $this->context->link->getModuleLink(
                         $this->name,
                         'preparing',
                         $this->getArgumentsForChannelTarget($oneClick),
                         true
                     )
                 );
        $agreement = $this->l(
            'I agree to repeated loading bill my credit card for the payment One-Click by way of purchase of goods '.
            'or services offered by the store.'
        );
        $oneClick->addAgreement(
            new Agreement(array(
                'type' => 'check',
                'name' => 'oc-store-card',
                'label' => 'One Click Agreement',
                'required' => true,
                'default' => true,
                'description_text' => $agreement,
                'description_html' => $agreement,
            ))
        );

        $cards = CreditCard::getAllCardsForCustomer(Context::getContext()->customer->id);
        foreach ($cards as $card) {
            $oneClick->addCard($card);
        }
        $oneClick->setRegisterCardDescription("&nbsp;".$this->l('Register a new card'));
        $oneClick->setSavedCardsDescription("&nbsp;".$this->l('Select saved credit card'));
        $oneClick->setManageCardsDescription($this->l('Manage saved credit cards'));
        $oneClick->setManageCardsUrl(htmlspecialchars(
            $this->context->link->getModuleLink(
                $this->name,
                'ocmanage',
                array(),
                true
            ),
            ENT_COMPAT
        ));
        if (Context::getContext()->customer) {
            $oneClick->setUserIsLogged(Context::getContext()->customer->isLogged());
        }
        $channelList->addChannel($oneClick);

        $fcc = $this->sdkLoader->get('Fcc');
        $fcc->setTitle($this->l("Credit Card"))
            ->set('target', $this->context->link->getModuleLink(
                $this->name,
                'preparing',
                $this->getArgumentsForChannelTarget($fcc),
                true
            ));
        $channelList->addChannel($fcc);

        $cc = $this->sdkLoader->get('Cc');
        $cc->setTitle($this->l("Credit Card"))
           ->set('target', $this->context->link->getModuleLink(
               $this->name,
               'preparing',
               $this->getArgumentsForChannelTarget($cc),
               true
           ));
        $channelList->addChannel($cc);

        $blik = $this->sdkLoader->get('Blik');
        $blik->set('target', $this->context->link->getModuleLink(
            $this->name,
            'preparing',
            $this->getArgumentsForChannelTarget($blik),
            true
        ))
             ->setFieldDescription($this->l('BLIK code').":&nbsp;");
        $channelList->addChannel($blik);

        foreach ($this->config->getVisibleChannelsArray() as $channelId) {
            $channel = $this->sdkLoader->get('Channel', array(
                $channelId,
                'channel',
                $this->config,
                $this->sdkLoader->get('Transaction'),
                $this->sdkLoader->get('PaymentResource'),
                $this->sdkLoader->get('SellerResource')
            ));
            $channel->setTitle($channel->getTitle().' '.$this->l('via Dotpay'));
            $channel->set(
                'target',
                $this->context->link->getModuleLink(
                    $this->name,
                    'preparing',
                    $this->getArgumentsForChannelTarget($channel),
                    true
                )
            );
            $channelList->addChannel($channel);

    }

        $dotpay = $this->sdkLoader->get('DotpayChannel');
        $dotpay->setTitle($this->l("Dotpay"))
               ->set(
                   'target',
                   $this->context->link->getModuleLink(
                       $this->name,
                       'preparing',
                       $this->getArgumentsForChannelTarget($dotpay),
                       true
                   )
               )
               ->setSelectChannelTitle($this->l('Selected payment channel'))
               ->setChangeChannel($this->l('change channel'))
               ->setAvailableChannelsTitle($this->l('Available channels'));
        $channelList->addChannel($dotpay);
        $this->sdkLoader->get('PaymentResource')->clearBuffer();
        return $channelList;
    }

    /**
     * Return an array with arguments for the target url for payment
     * @param Channel $channel ObjectNode of channel
     * @return array
     */
    private function getArgumentsForChannelTarget(Channel $channel)
    {
        $arguments = array();
        $arguments['method'] = $channel->getCode();
        if (is_object(Context::getContext()->cookie) && (int)Context::getContext()->cookie->dotpay_renew == 1) {
            if ($cardId = (int)Context::getContext()->cookie->id_cart) {
                $arguments['order'] = (int)Order::getOrderByCartId($cardId);
            }
        }
        return $arguments;
    }

    /**
     * Initialize data for channels
     * @param array $params Information about a current cart
     * @throws IncompleteDataException Thrown when given data is incompleted
     */
    private function initializeChannelsDataFromCart($params)
    {
        if (!empty($params['cart']->id_customer)) {
            $originalCustomer = new Customer($params['cart']->id_customer);
            $this->sdkLoader->parameter('Customer:email', $originalCustomer->email);
            $this->sdkLoader->parameter('Customer:firstName', $originalCustomer->firstname);
            $this->sdkLoader->parameter('Customer:lastName', $originalCustomer->lastname);
            unset($originalCustomer);
        } else if (Context::getContext()->customer->isLogged()) {
            throw new IncompleteDataException('Customer id');
        }
        $customer = $this->sdkLoader->get('Customer');
        $customer->setLanguage($this->getLanguage());
        $currency = new Currency($params['cart']->id_currency);
        $this->sdkLoader->parameter('Order:id', null);
        $this->sdkLoader->parameter('Order:amount', $params['cart']->getOrderTotal());
        $this->sdkLoader->parameter('Order:currency', $currency->iso_code);
        $this->sdkLoader->parameter('PaymentModel:description', '');
        $order = $this->sdkLoader->get('Order');
        $order->setShippingAmount($params['cart']->getOrderTotal(true, Cart::ONLY_SHIPPING));
        unset($customer);
        unset($currency);
    }

    /**
     * Hook for payment gateways list in checkout site
     * @return string
     */
    public function hookDisplayCustomerAccount()
    {
        $this->smarty->assign(array(
            'actionUrl' => $this->context->link->getModuleLink('dotpay', 'ocmanage'),
        ));
        return $this->display(__FILE__, 'ocbutton.tpl');
    }

    /**
     * Returns a rendered template with Dotpay area on order details
     * @param array $params Details of current order
     * @return string
     */
    public function hookDisplayOrderDetail($params)
    {
        $order = new Order(Tools::getValue('id_order'));
        $instruction = $this->sdkLoader->get('Instruction', array($order->id));
        $context =  Context::getContext();
        if ($order->module=='dotpay') {
            if ($instruction->getId() != null && $this->config->ifOrderCanBeRenewed(new DateTime($order->date_add))) {
                $this->smarty->assign(array(
                    'isInstruction' => (bool)($instruction->getId() != null),
                    'instructionUrl' => $context->link->getModuleLink('dotpay', 'instruction', array(
                        'method'=>'dotpay',
                        'order'=>$order->id,
                        'channel'=>$instruction->getChannel()
                    )),
                ));
            }

            $payment2 = OrderPayment::getByOrderId($order->id);
            if (count($payment2) > 0) {
                $Dotpay_transaction_id = $payment2[0]->transaction_id;
            } else {
                $Dotpay_transaction_id = null;
            }

            if ($this->config->ifOrderCanBeRenewed(new DateTime($order->date_add))) {
                $this->smarty->assign(array(
                      //  'isRenew' => $order->current_state == $this->config->getWaitingStatus(),
                    // renew payment for rejected and processing statuses:
                    'isRenew' => ($order->current_state == $this->config->getWaitingStatus() || $order->current_state == _PS_OS_ERROR_),
                    'paymentUrl' => $context->link->getModuleLink('dotpay', 'renew', array('order_id'=>$order->id)),
                    'moduleDir2' => $this->_path,
                    'DotpayTrId' => $Dotpay_transaction_id
                ));
            }

            return $this->display(__FILE__, 'renew.tpl');
        }
    }

    /**
     * Hook for displaying order by shop admin
     * @param array $params Details of displayed order
     * @return type
     */
    public function hookDisplayAdminOrder($params)
    {
        if (!$this->config->getRefundsEnable()) {
            return '';
        }
        if (Tools::getValue('dotpay_refund')!==false) {
            if (Tools::getValue('dotpay_refund')=='success') {
                $this->context->controller->confirmations[] = $this->l('Request of refund was sent');
            } elseif (Tools::getValue('dotpay_refund')=='error') {
                $this->context->controller->errors[] = $this->l('An error occurred during request of refund').
                '<br /><i>'.$this->context->cookie->dotpay_error.'</i>';
                unset($this->context->cookie->dotpay_error);
                $this->context->cookie->write();
            }
        }
        $order = new Order($params['id_order']);
        $payments = OrderPayment::getByOrderId($order->id);
        foreach ($payments as $key => $payment) {
            if ($payment->amount < 0) {
                unset($payments[$key]);
            }
        }
        $paidViaDotpay = false;
        foreach ($payments as $payment) {
            $currency = Currency::getCurrency($order->id_currency);
            if ($payment->payment_method === $this->displayName && $currency["iso_code"] === 'PLN') {
                $paidViaDotpay = true;
            }
            break;
        }
        if ($paidViaDotpay) {
            $this->smarty->assign(array(
                'orderId' => $order->id,
                'payments' => $payments,
                'returnUrl' => $this->context->link->getAdminLink('AdminDotpayRefund')
            ));
            return $this->display(__FILE__, 'orderDetails.tpl');
        }
        return '';
    }

    /**
     * Add a return tab
     * @return boolean
     */
    private function addReturnTab()
    {
        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminDotpayRefund';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Dotpay Refunds';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * Check, if SSL is enabled during current connection
     * @return boolean
     */
    public function isSSLEnabled()
    {
        if (isset($_SERVER['HTTPS'])) {
            if (Tools::strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1') {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')) {
            return true;
        }
        return false;
    }

    /**
     * Add Dotpay virtual product for extracharge option
     * @return boolean
     */
    public function checkVirtualProduct()
    {
        if (Validate::isInt($this->config->getExtraChargeVirtualProductId()) &&
           (Validate::isLoadedObject($product = new Product($this->config->getExtraChargeVirtualProductId()))) &&
           Validate::isInt($product->id)
        ) {
            if ($this->isVPIncomplete($product)) {
                $this->setVPFeatures($product);
                $product->save();
                StockAvailable::setQuantity($product->id, null, $product->quantity);
            }
            return true;
        }

        $product = new Product();
        $this->setVPFeatures($product);
        if (!$product->add()) {
            return false;
        }
        $product->addToCategories(array(1));
        StockAvailable::setQuantity($product->id, null, $product->quantity);
        $this->config->setExtraChargeVirtualProductId($product->id);

        return true;
    }

    /**
     * Check, if Virtual Product from Dotpay additional payment is in card
     * @param Configuration $config Dotpay configuration object
     * @param Cart $cart Prestashop cart
     * @return boolean
     */
    public function isVirtualProductInCart(DotpayConfiguration $config, Cart $cart)
    {
        $products = $cart->getProducts(true);
        foreach ($products as $product) {
            if ($product['id_product'] == $config->getExtraChargeVirtualProductId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if Dotpay virtual product is complete
     * @param Product $product Dotpay virtual product object
     * @return boolean
     */
    private function isVPIncomplete(Product $product)
    {
        return (
            empty($product->name) ||
            empty($product->link_rewrite) ||
            empty($product->visibility) ||
            empty($product->reference) ||
            empty($product->price) ||
            empty($product->is_virtual) ||
            empty($product->online_only) ||
            empty($product->redirect_type) ||
            empty($product->quantity) ||
            empty($product->id_tax_rules_group) ||
            empty($product->active) ||
            empty($product->meta_keywords) ||
            empty($product->id_category) ||
            empty($product->id_category_default)
        );
    }

    /**
     * Set values of Dotpay virtual product
     * @param Product $product Dotpay virtual product object
     */
    private function setVPFeatures(Product $product)
    {
        $product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Online payment');
        $product->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => 'online-payment');
        $product->visibility = 'none';
        $product->reference = 'DOTPAYFEE';
        $product->price = 0.0;
        $product->is_virtual = 1;
        $product->online_only = 1;
        $product->redirect_type = '404';
        $product->quantity = 9999999;
        $product->id_tax_rules_group = 0;
        $product->active = 1;
        $product->meta_keywords = 'payment';
        $product->id_category = 1;
        $product->id_category_default = 1;
    }

    /**
     * Set Dotpay discount for reducing shipping cost
     * @return boolean
     */
    private function addShippingReduction()
    {
        if (!Validate::isInt($this->config->getShippingReductionId()) || $this->config->getShippingReductionId() == 0) {
            $cartRule = new CartRule();
            $cartRule->name = array((int)Configuration::get('PS_LANG_DEFAULT') =>
                              $this->l('Discount for shipping thanks to Dotpay payment'));
            $cartRule->description = array((int)Configuration::get('PS_LANG_DEFAULT') =>
                                     $this->l('Customers can reduct the price of shipping by using Dotpay payments'));
            $cartRule->code = md5(date("d-m-Y H-i-s"));
            $cartRule->quantity = 9999999;
            $cartRule->quantity_per_user = 9999999;
            $cartRule->active = 1;
            $cartRule->reduction_amount = 1;
            $cartRule->reduction_tax = 1;
            $now = time();
            $cartRule->date_from = date('Y-m-d H:i:s', $now);
            $cartRule->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25)*50);
            if (!$cartRule->add()) {
                return false;
            }
            $this->config->setShippingReductionId($cartRule->id);
        }
        return true;
    }

    /**
     * Copies status image to Prestashop system dir
     * @param type $source
     * @param type $dest
     * @return boolean
     */
    private function copyStatusImage($source, $dest)
    {
        $target = mydirname($this->local_path, 3);
        return copy($this->local_path.'/views/img/'.$source.'.gif', $target.'/img/os/'.$dest.'.gif');
    }

    /**
     * Adds new Dotpay order status to shop's database. If successfully, returns id of new status.
     * @param string $plName Name of status in Polish
     * @param string $engName Name os status in English
     * @param string $color $Color of status in hexadecimal form
     * @return int|boolean
     */
    private function addDotpayOrderStatus($plName, $engName, $color)
    {
        $newOrderState = new OrderState();
        $newOrderState->name = array();
        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'pl') {
                $newOrderState->name[$language['id_lang']] = $plName;
            } else {
                $newOrderState->name[$language['id_lang']] = $engName;
            }
        }
        $newOrderState->module_name = $this->name;
        $newOrderState->send_email = false;
        $newOrderState->invoice = false;
        $newOrderState->unremovable = false;
        $newOrderState->color = $color;
        if (!$newOrderState->add()) {
            return false;
        }
        return $newOrderState->id;
    }

    /**
     * Adds Dotpay new payment status if not exist
     * @return boolean
     */
    private function addOrderWaitingStatus()
    {
        if (Validate::isInt($this->config->getWaitingStatus()) &&
           (Validate::isLoadedObject($order_state_new = new OrderState($this->config->getWaitingStatus()))) &&
           Validate::isInt($order_state_new->id)
        ) {
            return true;
        }
        $stateId = $this->addDotpayOrderStatus(
            'Oczekuje na potwierdzenie płatności z Dotpay',
            'Awaiting for Dotpay Payment confirmation',
            '#00abf4'
        );
        if ($stateId === false) {
            return false;
        }
        $this->config->setWaitingStatus($stateId);
        $this->copyStatusImage('wait', $stateId);
        return true;
    }

    /**
     * Adds Dotpay total refund status if not exist
     * @return boolean
     */
    private function addTotalRefundStatus()
    {
        if (Validate::isInt($this->config->getTotalRefundStatus()) &&
           (Validate::isLoadedObject($order_state_new = new OrderState($this->config->getTotalRefundStatus()))) &&
           Validate::isInt($order_state_new->id)
        ) {
            return true;
        }
        $stateId = $this->addDotpayOrderStatus('Całkowity zwrot płatności', 'Total refund of payment', '#f8d700');
        if ($stateId === false) {
            return false;
        }
        $this->config->setTotalRefundStatus($stateId);
        $this->copyStatusImage('refund', $stateId);
        return true;
    }

    /**
     * Adds Dotpay partial refund status if not exist
     * @return boolean
     */
    private function addPartialRefundStatus()
    {
        if (Validate::isInt($this->config->getPartialRefundStatus()) &&
           (Validate::isLoadedObject($order_state_new = new OrderState($this->config->getPartialRefundStatus()))) &&
           Validate::isInt($order_state_new->id)
        ) {
            return true;
        }
        $stateId = $this->addDotpayOrderStatus('Częściowy zwrot płatności', 'Partial refund of payment', '#f7ff59');
        if ($stateId === false) {
            return false;
        }
        $this->config->setPartialRefundStatus($stateId);
        $this->copyStatusImage('refund', $stateId);
        return true;
    }

    /**
     * Adds Dotpay waiting for refund status if not exist
     * @return boolean
     */
    private function addWaitingRefundStatus()
    {
        if (Validate::isInt($this->config->getWaitingRefundStatus()) &&
           (Validate::isLoadedObject($order_state_new = new OrderState($this->config->getWaitingRefundStatus()))) &&
           Validate::isInt($order_state_new->id)
        ) {
            return true;
        }
        $stateId = $this->addDotpayOrderStatus(
            'Zwrot oczekuje na potwierdzenie',
            'Refund is waiting for confirmation',
            '#ffe5d1'
        );
        if ($stateId === false) {
            return false;
        }
        $this->config->setWaitingRefundStatus($stateId);
        $this->copyStatusImage('waitrefund', $stateId);
        return true;
    }

    /**
     * Adds Dotpay status when refund has been refused
     * @return boolean
     */
    private function addFailedRefundStatus()
    {
        if (Validate::isInt($this->config->getFailedRefundStatus()) &&
           (Validate::isLoadedObject($order_state_new = new OrderState($this->config->getFailedRefundStatus()))) &&
           Validate::isInt($order_state_new->id)
        ) {
            return true;
        }
        $stateId = $this->addDotpayOrderStatus('Zwrot został odrzucony', 'Refund has rejected', '#ff6059');
        if ($stateId === false) {
            return false;
        }
        $this->config->setFailedRefundStatus($stateId);
        $this->copyStatusImage('failrefund', $stateId);
        return true;
    }
}

/**
 * Fix for PHP older than 7.0
 * @param string $dir
 * @param int $levels
 * @return string
 */
function mydirname($dir, $levels)
{
    while (--$levels) {
        $dir = dirname($dir);
    }
    return $dir;
}

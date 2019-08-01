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

use Dotpay\Validator\Amount;
use Dotpay\Exception\BadParameter\AmountException;

/**
 * Overriden class of module configuration. It extends SDK configuration for new features and makes it possible to save.
 */
class Configuration extends \Dotpay\Model\Configuration
{
    /**
     * @var boolean Flag if advanced mode is enabled
     */
    private $advancedMode = false;
    
    /**
     * @var boolean Flag if extracharge option is enabled
     */
    private $extracharge = false;
    
    /**
     * @var float Additional amount for extracharge
     */
    private $exchargeAmount = 0.0;
    
    /**
     * @var float Number of percents of order amount for extracharge
     */
    private $exchargePercent = 0.0;
    
    /**
     * @var boolean Flag if reduction of shipping cost is enabled
     */
    private $reduction = false;
    
    /**
     * @var float Amount of shipping reduction
     */
    private $reductionAmount = 0.0;
    
    /**
     * @var float Number of percents of shipping amount for reduction
     */
    private $reductionPercent = 0.0;
    
    /**
     * @var array List of Prestashop Configuration names which are bonded with setter/getter in SDK Configuration
     */
    private $modelMap = array(
        'DP_ENABLED' => 'Enable',
        'DP_API' => 'Api',
        'DP_ID' => 'Id',
        'DP_PIN'=> 'Pin',
        'DP_TEST_MODE' => 'TestMode',
        'DP_WIDGET_EN' => 'WidgetVisible',
        'DP_ADV_MODE' => 'AdvancedMode',
        'DP_RENEW' => 'Renew',
        'DP_RENEW_DAYS' => 'RenewDays',
        'DP_WIDGET_CURR' => 'WidgetCurrencies',
        'DP_CC' => 'CcVisible',
        'DP_BLIK' => 'BlikVisible',
        'DP_OC' => 'OcVisible',
        'DP_REFUND' => 'RefundsEnable',
        'DP_INSTR' => 'InstructionVisible',
        'DP_USERNAME' => 'Username',
        'DP_PASSWORD' => 'Password',
        'DP_FCC' => 'FccVisible',
        'DP_FCC_ID' => 'FccId',
        'DP_FCC_PIN' => 'FccPin',
        'DP_FCC_CURR' => 'FccCurrencies',
        'DP_SURCHARGE' => 'Surcharge',
        'DP_SUR_AMOUNT' => 'SurchargeAmount',
        'DP_SUR_PERC' => 'SurchargePercent',
        'DP_EXCHARGE' => 'Extracharge',
        'DP_EX_AMOUNT' => 'ExchargeAmount',
        'DP_EX_PERC' => 'ExchargePercent',
        'DP_REDUCT_SHIP' => 'Reduction',
        'DP_RS_AMOUNT' => 'ReductionAmount',
        'DP_RS_PERC' => 'ReductionPercent',
        'DP_CHANNELS' => 'VisibleChannels'
    );
    
    /**
     * Load saved data from Prestashop Configuration
     * @param string $pluginId Name of plugin which uses SDK
     */
    public function __construct($pluginId = '')
    {
        parent::__construct($pluginId);
        foreach ($this->modelMap as $key => $fname) {
            if (($readValue = $this->getFromExtendedSource($key)) !== null) {
                $fname = 'set'.$fname;
                $this->$fname($readValue);
            }
        }
    }

    /**
     * Return a flag if advanced mode is enabled
     * @return boolean
     */
    public function getAdvancedMode()
    {
        return $this->advancedMode;
    }
    
    /**
     * Return a flag if extracharge option is enabled
     * @return boolean
     */
    public function getExtracharge()
    {
        return $this->extracharge;
    }
    
    /**
     * Return an additional amount for extracharge
     * @return float
     */
    public function getExchargeAmount()
    {
        return $this->exchargeAmount;
    }
    
    /**
     * Return a number of percents of order amount for extracharge
     * @return float
     */
    public function getExchargePercent()
    {
        return $this->exchargePercent;
    }
    
    /**
     * Return a flag if reduction of shipping cost is enabled
     * @return boolean
     */
    public function getReduction()
    {
        return $this->reduction;
    }
    
    /**
     * Return an amount of shipping reduction
     * @return float
     */
    public function getReductionAmount()
    {
        return $this->reductionAmount;
    }
    
    /**
     * Return a number of percents of shipping amount for reduction
     * @return type
     */
    public function getReductionPercent()
    {
        return $this->reductionPercent;
    }
    
    /**
     * Return an id of payment waiting status
     * @return int
     */
    public function getWaitingStatus()
    {
        return (int)\Configuration::get('DP_WAITING_STATE');
    }
    
    /**
     * Return an id of partial refund status
     * @return int
     */
    public function getPartialRefundStatus()
    {
        return (int)\Configuration::get('DP_PARTREF_STATE');
    }

    /**
     * Return an id of total refund status
     * @return int
     */
    public function getTotalRefundStatus()
    {
        return (int)\Configuration::get('DP_TOTREF_STATE');
    }
    
    /**
     * Return an id of waiting refund status
     * @return int
     */
    public function getWaitingRefundStatus()
    {
        return (int)\Configuration::get('DP_WAITREF_STATE');
    }
    
    /**
     * Return an id of failed refund status
     * @return int
     */
    public function getFailedRefundStatus()
    {
        return (int)\Configuration::get('DP_FAILREF_STATE');
    }
    
    /**
     * Return an id of etracharge virtual product
     * @return int
     */
    public function getExtraChargeVirtualProductId()
    {
        return (int)\Configuration::get('DP_EXCHARGE_VP_ID');
    }
    
    /**
     * Return an id of shipping discount
     * @return int
     */
    public function getShippingReductionId()
    {
        return (int)\Configuration::get('DP_SHIP_REDUCT_ID');
    }

    /**
     * Set a flag if advanced mode is enabled
     * @param boolean $advancedMode Flag if advanced mode is enabled
     * @return Configuration
     */
    public function setAdvancedMode($advancedMode)
    {
        $this->advancedMode = (bool)$advancedMode;
        return $this;
    }
    
    /**
     * Set a flag if extracharge option is enabled
     * @param boolean $extracharge
     * @return Configuration
     */
    public function setExtracharge($extracharge)
    {
        $this->extracharge = (bool)$extracharge;
        return $this;
    }
    
    /**
     * Set an additional amount for extracharge
     * @param float $exchargeAmount Additional amount for extracharge
     * @return Configuration
     * @throws AmountException Thrown when the given amount is incorrect.
     */
    public function setExchargeAmount($exchargeAmount)
    {
        if ($this->getExtracharge()) {
            if (trim((float)$exchargeAmount) > 0 && !Amount::validate((float)$exchargeAmount)) {
                //throw new AmountException($exchargeAmount);
                $this->exchargeAmount = 0.0;
            }
            $this->exchargeAmount = $this->makeCorrectNumber($exchargeAmount);
        }
        return $this;
    }
    
    /**
     * Set a number of percents of order amount for extracharge
     * @param float $exchargePercent Number of percents of order amount for extracharge
     * @return Configuration
     */
    public function setExchargePercent($exchargePercent)
    {
        if ($this->getExtracharge()) {
            $this->exchargePercent = $this->makeCorrectNumber($exchargePercent);
        }
        return $this;
    }
    
    /**
     * Set a flag if reduction of shipping cost is enabled
     * @param boolean $reduction Flag if reduction of shipping cost is enabled
     * @return Configuration
     */
    public function setReduction($reduction)
    {
        $this->reduction = (bool)$reduction;
        return $this;
    }
    
    /**
     * Set an amount of shipping reduction
     * @param float $reductionAmount Amount of shipping reduction
     * @return Configuration
     * @throws AmountException Thrown when the given amount is incorrect.
     */
    public function setReductionAmount($reductionAmount)
    {
        if ($this->getReduction()) {
            if (trim((float)$reductionAmount) > 0 && !Amount::validate((float)$reductionAmount)) {
               // throw new AmountException($reductionAmount);
                $this->reductionAmount = 0.0;
            }
            $this->reductionAmount = $this->makeCorrectNumber($reductionAmount);
        }
        return $this;
    }
    
    /**
     * Set a number of percents of shipping amount for reduction
     * @param float $reductionPercent Number of percents of shipping amount for reduction
     * @return Configuration
     */
    public function setReductionPercent($reductionPercent)
    {
        if ($this->getReduction()) {
            $this->reductionPercent = $this->makeCorrectNumber($reductionPercent);
        }
        return $this;
    }
    
    /**
     * Set an id of payment waiting status
     * @param int $waitingStatus Id of payment waiting status
     * @return Configuration
     */
    public function setWaitingStatus($waitingStatus)
    {
        \Configuration::updateValue('DP_WAITING_STATE', (int)$waitingStatus);
        return $this;
    }
    
    /**
     * Set an id of partial refund status
     * @param int $partialRefundStatus Id of partial refund status
     * @return Configuration
     */
    public function setPartialRefundStatus($partialRefundStatus)
    {
        \Configuration::updateValue('DP_PARTREF_STATE', (int)$partialRefundStatus);
        return $this;
    }

    /**
     * Set an id of total refund status
     * @param int $totalRefundStatus Id of total refund status
     * @return Configuration
     */
    public function setTotalRefundStatus($totalRefundStatus)
    {
        \Configuration::updateValue('DP_TOTREF_STATE', (int)$totalRefundStatus);
        return $this;
    }
    
    /**
     * Set an id of waiting refund status
     * @param int $waitingRefundStatus Id of waiting refund status
     * @return Configuration
     */
    public function setWaitingRefundStatus($waitingRefundStatus)
    {
        \Configuration::updateValue('DP_WAITREF_STATE', (int)$waitingRefundStatus);
        return $this;
    }
    
    /**
     * Set an id of failed refund status
     * @param int $failedRefundStatus Id of failed refund status
     * @return Configuration
     */
    public function setFailedRefundStatus($failedRefundStatus)
    {
        \Configuration::updateValue('DP_FAILREF_STATE', (int)$failedRefundStatus);
        return $this;
    }
    
    /**
     * Set an id of etracharge virtual product
     * @param int $extraChargeVirtualProductId Id of etracharge virtual product
     * @return Configuration
     */
    public function setExtraChargeVirtualProductId($extraChargeVirtualProductId)
    {
        \Configuration::updateValue('DP_EXCHARGE_VP_ID', (int)$extraChargeVirtualProductId);
        return $this;
    }
    
    /**
     * Set an id of shipping discount
     * @param int $shippingReductionId Id of shipping discount
     * @return Configuration
     */
    public function setShippingReductionId($shippingReductionId)
    {
        \Configuration::updateValue('DP_SHIP_REDUCT_ID', (int)$shippingReductionId);
        return $this;
    }
    
    /**
     * Return an array with all information stored in the configuration
     * @return array
     */
    public function getFormValues()
    {
        $result = array();
        foreach ($this->modelMap as $key => $fname) {
            $fname = 'get'.$fname;
            $result[$key] = $this->$fname();
        }
        return $result;
    }
    
    /**
     * Initialize configuration data from request (POST/GET)
     * @return Configuration
     */
    public function readFromForm()
    {
        foreach ($this->modelMap as $key => $fname) {
            if (($readValue = \Tools::getValue($key, null)) !== null) {
                $fname = 'set'.$fname;
                $this->$fname($readValue);
            }
        }
        return $this;
    }
    
    /**
     * Save all configuration in shop's database
     */
    public function persist()
    {
        foreach ($this->getFormValues() as $key => $value) {
            \Configuration::updateValue($key, $value);
        }
    }
    
    /**
     * Return the model map
     * @return array
     */
    public function getModelMap()
    {
        return $this->modelMap;
    }
    
    /**
     * Makes the number values correct
     * @param float $input
     * @return float
     */
    private function makeCorrectNumber($input)
    {
        return preg_replace('/[^0-9\.]/', "", str_replace(',', '.', trim($input)));
    }
    
    /**
     * Return a configure value from shop registry
     * @param string $name Name of requested value
     * @return mixed
     */
    private function getFromExtendedSource($name)
    {
        $context = \Context::getContext();
        //Language id
        if (isset($context->id_lang)) {
            $langId = (int)$context->id_lang;
        } elseif (isset($context->language) && isset($context->language->id)) {
            $langId = (int)$context->language->id;
        } else {
            $langId = (int)\Configuration::get('PS_LANG_DEFAULT');
        }
        //Shop group id
        if (isset($context->id_shop_group)) {
            $shopGroupId = (int)$context->id_shop_group;
        } elseif (isset($context->shop) && isset($context->shop->id_shop_group)) {
            $shopGroupId = (int)$context->shop->id_shop_group;
        } else {
            $shopGroupId = null;
        }
        //Shop id
        if (isset($context->id_shop)) {
            $shopId = (int)$context->id_shop;
        } elseif (isset($context->shop) && isset($context->shop->id)) {
            $shopId = (int)$context->shop->id;
        } else {
            $shopId = null;
        }
        return \Configuration::get($name, $langId, $shopGroupId, $shopId, null);
    }
}

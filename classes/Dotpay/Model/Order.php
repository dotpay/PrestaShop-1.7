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

/**
 * Overriden class of order. It allows to add new features of SDK for Prestashop
 */
class Order extends \Dotpay\Model\Order
{
    /**
     * @var float Anount of shipping
     */
    private $shippingAmount;
    
    /**
     * Return an amount of shipping
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }
    
    /**
     * Return an amount of an additional surcharge for display it on shop site
     * @param Configuration $config Plugin configuration
     * @param array|Currency $currency Data of the order currency
     * @return float
     */
    public function getSurchargeAmount(Configuration $config, $currency = null)
    {
        if ($currency === null) {
            $price = $this->getSurcharge($config);
        } else {
            $price = \Tools::convertPrice($this->getSurcharge($config), $currency, false);
        }
        return $this->getFormatAmount($price, $currency);
    }
    
    /**
     * Return an amount of an additional extracharge for add it to an order
     * @param Configuration $config Plugin configuration
     * @param array|Currency $currency Data of the order currency
     * @return float
     */
    public function getExtrachargeAmount(Configuration $config, $currency = null)
    {
        if (!$config->getExtracharge()) {
            return 0.0;
        }
        $exPercentage = $this->getFormatAmount($this->getAmount() * $config->getExchargePercent()/100, $currency);
        $exAmount = $this->getFormatAmount($config->getExchargeAmount(), $currency);
        if ($currency === null) {
            $price = max($exPercentage, $exAmount);
        } else {
            $price = \Tools::convertPrice(max($exPercentage, $exAmount), $currency, false);
        }
        return $this->getFormatAmount($price, $currency);
    }
    
    /**
     * Returns amount after discount for Dotpay
     * @param Configuration $config Plugin configuration
     * @param array|Currency $currency Data of the order currency
     * @return float
     */
    public function getReductionAmount(Configuration $config, $currency = null)
    {
        if (!$config->getReduction()) {
            return 0.0;
        }
        $amount = $this->getShippingAmount();
        $discPercentage = $this->getFormatAmount($amount * $config->getReductionPercent()/100, $currency);
        $discAmount = $config->getReductionAmount();
        $tmpPrice = max($discPercentage, $discAmount);
        if ($currency === null) {
            $price =  min($tmpPrice, $amount);
        } else {
            $price = \Tools::convertPrice(min($tmpPrice, $amount), $currency, false);
        }
        return $price;
    }
    
    /**
     * Returns amount in correct format
     * @param float $amount Amount of the order
     * @param array|Currency $currency Data of the order currency
     * @return string
     */
    private function getFormatAmount($amount, $currency)
    {
        if (gettype($currency) == 'object' && isset($currency->decimals) && $currency->decimals ==0 ||
            gettype($currency) == 'array' && isset($currency['decimals']) && $currency['decimals'] ==0) {
            if (\Configuration::get('PS_PRICE_ROUND_MODE')!=null) {
                switch (\Configuration::get('PS_PRICE_ROUND_MODE')) {
                    case 0:
                        $amount = ceil($amount);
                        break;
                    case 1:
                        $amount = floor($amount);
                        break;
                    case 2:
                        $amount = round($amount);
                        break;
                }
            }
        }
        $amount = \Tools::displayPrice($amount);
        return preg_replace('/[^0-9.]/', '', str_replace(',', '.', $amount));
    }
    
    /**
     * Set the given amount as shipping cost of the order
     * @param float $shippingAmount
     * @return Order
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }
}

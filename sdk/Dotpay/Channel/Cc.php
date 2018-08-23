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
namespace Dotpay\Channel;

use Dotpay\Model\Configuration;
use Dotpay\Model\Transaction;
use Dotpay\Resource\Payment as PaymentResource;
use Dotpay\Resource\Seller as SellerResource;

/**
 * Class provides a special functionality for credit card payments
 */
class Cc extends Channel
{
    const CODE = 'cc';
    /**
     * Initialize a credit card channel
     * @param Configuration $config Dotpay configuration object
     * @param Transaction $transaction ObjectNode with transaction details
     * @param PaymentResource $paymentResource Payment resource which can be used for Payment API
     * @param SellerResource $sellerResource Seller resource which can be used for Seller API
     */
    public function __construct(Configuration $config, Transaction $transaction, PaymentResource $paymentResource, SellerResource $sellerResource)
    {
        parent::__construct(Configuration::OC_CHANNEL, self::CODE, $config, $transaction, $paymentResource, $sellerResource);
        if (!$this->available && $this->isVisible()) {
            $this->setChannelInfo(Configuration::CC_CHANNEL);
        }
    }
    
    /**
     * Check if the channel is visible
     * @return boolean
     */
    public function isVisible()
    {
        return parent::isVisible() &&
                $this->config->getCcVisible() &&
                !($this->config->isFccEnable() &&
                $this->config->isCurrencyForFcc(
                    $this->transaction->getPayment()->getOrder()->getCurrency()
                ));
    }
}

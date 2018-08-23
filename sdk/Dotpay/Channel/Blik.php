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

use Dotpay\Html\Form\Text;
use Dotpay\Html\Form\Label;
use Dotpay\Model\Configuration;
use Dotpay\Model\Transaction;
use Dotpay\Resource\Payment as PaymentResource;
use Dotpay\Resource\Seller as SellerResource;
use Dotpay\Validator\BlikCode;
use Dotpay\Exception\BadParameter\BlikCodeException;

/**
 * Class provides a special functionality for Blik payments
 */
class Blik extends Channel
{
    const CODE = 'blik';
    
    /**
     * @var int Blik code which can be used for making a payment
     */
    private $blikCode = '';
    
    /**
     * @var string Description of the field with BLIK code
     */
    private $fieldDescription = '';
    
    /**
     * Initialize a Blik channel
     * @param Configuration $config Dotpay configuration object
     * @param Transaction $transaction ObjectNode with transaction details
     * @param PaymentResource $paymentResource Payment resource which can be used for Payment API
     * @param SellerResource $sellerResource Seller resource which can be used for Seller API
     */
    public function __construct(Configuration $config, Transaction $transaction, PaymentResource $paymentResource, SellerResource $sellerResource)
    {
        parent::__construct(Configuration::BLIK_CHANNEL, self::CODE, $config, $transaction, $paymentResource, $sellerResource);
    }
    
    /**
     * Check if the channel is visible
     * @return boolean
     */
    public function isVisible()
    {
        return parent::isVisible() &&
               $this->config->getBlikVisible() &&
               ($this->transaction->getPayment()->getOrder()->getCurrency() === 'PLN');
    }
    
    /**
     * Return a Blik code which was set for a current payment
     * @return int
     */
    public function getBlikCode()
    {
        return $this->blikCode;
    }
    
    /**
     * Set a Blik code for a current payment
     * @param int $blikCode Blik code
     * @return Blik
     * @throws BlikCodeException Thrown if the Blik code is incorrect
     */
    public function setBlikCode($blikCode)
    {
        if (!BlikCode::validate($blikCode)) {
            throw new BlikCodeException($blikCode);
        }
        $this->blikCode = (int)$blikCode;
        return $this;
    }
    
    /**
     * Return an array of hidden fields for a form to redirecting to a Dotpay site with all needed information about a current payment
     * @return array
     */
    protected function prepareHiddenFields()
    {
        $data = parent::prepareHiddenFields();
        if (!$this->config->getTestMode()) {
            $data['blik_code'] = $this->blikCode;
        }
        return $data;
    }
    
    /**
     * Return an array of fields which can be displayed on a list of payment channels.
     * They can contain aditional fields with information which are needed before continue a payment process.
     * @return array
     */
    public function getViewFields()
    {
        $data = parent::getViewFields();
        $field = new Text('blik_code');
        $field->setClass('dotpay_blik_code');
        if(!empty($this->fieldDescription)) {
            $field = new Label($field, $this->fieldDescription);
        }
        $data[] = $field;
        return $data;
    }
    
    /**
     * Set the description of the BLIK field
     * @param string $description Description of the BLIK field
     * @return Blik
     */
    public function setFieldDescription($description) {
        $this->fieldDescription = (string)$description;
        return $this;
    }
}

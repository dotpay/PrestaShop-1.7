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

use Dotpay\Loader\Loader;
use Dotpay\Validator\ChannelId;
use Dotpay\Resource\Payment as PaymentResource;
use Dotpay\Resource\Seller as SellerResource;
use Dotpay\Model\Seller as SellerModel;
use Dotpay\Model\Transaction;
use Dotpay\Model\Configuration;
use Dotpay\Exception\BadParameter\ChannelIdException;
use Dotpay\Exception\Resource\Channel\NotFoundException;
use Dotpay\Resource\Channel\Agreement;
use Dotpay\Html\Form\Input;
use Dotpay\Html\Container\Script;
use Dotpay\Html\PlainText;
use Dotpay\Html\Container\Form;

/**
 * Class provides a special functionality for customization of payments channel
 */
class Channel
{
    const CODE = 'channel';

    /**
     * Name of cash channels group
     */
    const CASH_GROUP = 'cash';

    /**
     * Name of transfer channels group
     */
    const TRANSFER_GROUP = 'transfers';

    /**
     * @var int Code number of payment channel in Dotpay system
     */
    protected $code;

    /**
     * @var array Array of values which can be set for saving some additional informations
     */
    protected $reqistry = [];

    /**
     * @var Configuration Dotpay configuration object
     */
    protected $config;

    /**
     * @var \Dotpay\Resource\Channel\ChannelInfo A channel info struct, downloaded from Dotpay server
     */
    protected $channelInfo;

    /**
     * @var array An agreement struct, downloaded from Dotpay server
     */
    protected $agreements = [];

    /**
     * @var boolean Flag of an availability of the channel
     */
    protected $available = false;

    /**
     * @var Transaction ObjectNode with transaction details
     */
    protected $transaction;

    /**
     * @var PaymentResource Payment resource which can be used for Payment API
     */
    protected $paymentResource;

    /**
     * @var SellerResource Seller resource which can be used for Payment API
     */
    protected $sellerResource;

    /**
     * @var string Title which can be displayed on the channel list
     */
    protected $title = '';

    /**
     * @var Seller ObjectNode of used seller
     */
    protected $seller = null;

    /**
     * Initialize a separate channel
     * @param int $channelId Code number of payment channel in Dotpay system
     * @param string $code Short string code which can be used to identify
     * @param Configuration $config Dotpay configuration object
     * @param Transaction $transaction ObjectNode with transaction details
     * @param PaymentResource $paymentResource Payment resource which can be used for Payment API
     * @param SellerResource $sellerResource Seller resource which can be used for Seller API
     */
    public function __construct($channelId, $code, Configuration $config, Transaction $transaction, PaymentResource $paymentResource, SellerResource $sellerResource)
    {
        $this->code = $code;
        $this->config = $config;
        $this->transaction = $transaction;
        $this->paymentResource = $paymentResource;
        $this->sellerResource = $sellerResource;
        if (!$this->isVisible()) {
            return;
        }
        $this->chooseChannel();
        $this->transaction->getPayment()->setSeller($this->seller);
        $this->setChannelInfo($channelId);
    }

    /**
     * Save the given value for the name
     * @param string $name The name of the value
     * @param mixed $value The value to saving
     * @return Channel
     */
    public function set($name, $value)
    {
        $this->reqistry[$name] = $value;
        return $this;
    }

    /**
     * Get the saved value by the given name
     * @param string $name Name of the saved value
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->reqistry[$name])) {
            return $this->reqistry[$name];
        } else {
            return null;
        }
    }

    /**
     * Return a code number of payment channel in Dotpay system
     * @return int|null
     */
    public function getChannelId()
    {
        if ($this->channelInfo !== null) {
            return $this->channelInfo->getId();
        } else {
            return null;
        }
    }

    /**
     * Return a readable name of the channel
     * @return string|null
     */
    public function getName()
    {
        if ($this->channelInfo !== null) {
            return $this->channelInfo->getName();
        } else {
            return null;
        }
    }

    /**
     * Return a name of a group to which it belongs the channel
     * @return string|null
     */
    public function getGroup()
    {
        if ($this->channelInfo !== null) {
            return $this->channelInfo->getGroup();
        } else {
            return null;
        }
    }

    /**
     * Return an URL of a image with logo of the payment channel
     * @return string|null
     */
    public function getLogo()
    {
        if ($this->channelInfo !== null) {
            return $this->channelInfo->getLogo();
        } else {
            return null;
        }
    }

    /**
     * Return a short string code of the payment channel
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return a title which can be displayed on the channel list
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the given seller
     * @param SellerModel $seller Model of shop seller
     * @return Channel
     */
    public function setSeller(SellerModel $seller)
    {
        $this->seller = $seller;
        return $this;
    }

    /**
     * Check a visibility of the channel on a channels list
     * @return boolean
     */
    public function isVisible()
    {
        return $this->config->getEnable() &&
               $this->config->isGatewayEnabled(
                    $this->transaction->getPayment()->getOrder()->getCurrency()
               );
    }

    /**
     * Check an availability of the channel
     * @return boolean
     */
    final public function isAvailable()
    {
        return $this->available;
    }

    /**
     * Check if the channel is enabled to using
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isVisible() &&
               $this->isAvailable();
    }

    /**
     * Return an array of fields which can be displayed on a list of payment channels.
     * They can contain aditional fields with information which are needed before continue a payment process.
     * @return array
     */
    public function getViewFields()
    {
        $data = array();
        return $data;
    }

    /**
     * Return view fields enriched by an additional piece of HTML code
     * @return array
     */
    public function getViewFieldsHtml()
    {
        return $this->getViewFields();
    }

    /**
     * Return array of hidden fields for a form to redirecting to a Dotpay site with all needed information about a current payment
     * @return array
     */
    protected function prepareHiddenFields()
    {
        $data = [];
        $data['id'] = $this->seller->getId();
        $data['control'] = $this->transaction->getControl();
        $data['p_info'] = $this->transaction->getPayment()->getSeller()->getInfo();
        $sellerEmail = $this->transaction->getPayment()->getSeller()->getEmail();
        if (!empty($sellerEmail)) {
            $data['p_email'] = $sellerEmail;
        }
        $data['amount'] = $this->transaction->getPayment()->getOrder()->getAmount();
        $data['currency'] = $this->transaction->getPayment()->getOrder()->getCurrency();
        $data['description'] = $this->transaction->getPayment()->getDescription();
        $data['lang'] = $this->transaction->getPayment()->getCustomer()->getLanguage();
        $data['URL'] = $this->transaction->getBackUrl();
        $data['URLC'] = $this->transaction->getConfirmUrl();
        $data['api_version'] = $this->config->getApi();
        $data['type'] = 4;
        $data['ch_lock'] = 1;
        $data['firstname'] = $this->transaction->getPayment()->getCustomer()->getFirstName();
        $data['lastname'] = $this->transaction->getPayment()->getCustomer()->getLastName();
        $data['email'] = $this->transaction->getPayment()->getCustomer()->getEmail();
        $data['phone'] = $this->transaction->getPayment()->getCustomer()->getPhone();
        $data['street'] = $this->transaction->getPayment()->getCustomer()->getStreet();
        $data['street_n1'] = $this->transaction->getPayment()->getCustomer()->getBuildingNumber();
        $data['city'] = $this->transaction->getPayment()->getCustomer()->getCity();
        $data['postcode'] = $this->transaction->getPayment()->getCustomer()->getPostCode();
        $data['country'] = $this->transaction->getPayment()->getCustomer()->getCountry();
        $data['bylaw'] = 1;
        $data['personal_data'] = 1;
        $data['channel'] = $this->getChannelId();
        return $data;
    }

    /**
     * Return an array with all hidden fields including CHK
     * @return array
     */
    public function getAllHiddenFields() {
        $data = $this->prepareHiddenFields();
        $data['chk'] = $this->getCHK($data);
        return $data;
    }

    /**
     * Return a form with all hidden fields for payment
     * @return Form
     */
    public function getHiddenForm() {
        $fields = [];
        foreach ($this->getAllHiddenFields() as $name => $value) {
            $fields[] = new Input('hidden', $name, (string)$value);
        }
        $fields[] = new Script(new PlainText('setTimeout(function(){document.getElementsByClassName(\'dotpay-form\')[0].submit();}, 1);'));
        $form = new Form($fields);
        return $form->setClass('dotpay-form')
                    ->setMethod('post')
                    ->setAction($this->config->getPaymentUrl());
    }

    /**
     * Return an array with agreement structs, downloaded from Dotpay server
     * @return array
     */
    public function getAgreements()
    {
        return $this->agreements;
    }

    /**
     * Return object with transaction details
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Return a configuration object
     * @return Configuration
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Check if the channel can have an instruction
     * @return boolean
     */
    public function canHaveInstruction()
    {
        return $this->config->getInstructionVisible() &&
               $this->sellerResource->isAccountRight() &&
               ($this->getGroup() == self::CASH_GROUP ||
               $this->getGroup() == self::TRANSFER_GROUP);
    }

    /**
     * Add a new Agreement object for channel
     * @param Agreement $agreement The agreement to add to channel
     * @return Channel
     */
    public function addAgreement(Agreement $agreement)
    {
        $this->agreements[] = $agreement;
        return $this;
    }

    /**
     * Set a title which can be displayed on the channel list
     * @param string $title Title which can be displayed on the channel list
     * @return Channel
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;
        return $this;
    }

    /**
     * Return saved Seller model
     * @return SellerModel
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Retrieve informations about the channel from Dotpay server
     * @param int|null $channelId Code number of payment channel in Dotpay system
     * @throws ChannelIdException Thrown if the given channel id isn't correct
     */
    protected function setChannelInfo($channelId = null)
    {
        if ($channelId === null) {
            $this->available = false;
            return;
        }
        if ($channelId !== null && !ChannelId::validate($channelId)) {
            throw new ChannelIdException($channelId);
        }
        try {
            $channelsData = $this->paymentResource->getChannelInfo($this->transaction->getPayment());
            $this->channelInfo = $channelsData->getChannelInfo($channelId);
            $this->title = $this->channelInfo->getName();
            $this->agreements = $channelsData->getAgreements($channelId);
            $this->available = true;
        } catch (NotFoundException $e) {
            $this->available = false;
        }
    }

    /**
     * Set the seller model with the correct data from plugin Configuration
     */
    protected function chooseChannel()
    {
        $this->seller = Loader::load()->get('Seller', [
            $this->config->getId(),
            $this->config->getPin()
        ]);
    }

    /**
     * Calculate CHK for the given data
     * @param array $inputParameters Array with transaction parameters
     * @return string
     */
    protected function getCHK($inputParameters) {
        $CHkInputString =
            $this->seller->getPin().
            (isset($inputParameters['api_version']) ? $inputParameters['api_version'] : null).
            (isset($inputParameters['charset']) ? $inputParameters['charset'] : null).
            (isset($inputParameters['lang']) ? $inputParameters['lang'] : null).
            (isset($inputParameters['id']) ? $inputParameters['id'] : null).
            (isset($inputParameters['amount']) ? $inputParameters['amount'] : null).
            (isset($inputParameters['currency']) ? $inputParameters['currency'] : null).
            (isset($inputParameters['description']) ? $inputParameters['description'] : null).
            (isset($inputParameters['control']) ? $inputParameters['control'] : null).
            (isset($inputParameters['channel']) ? $inputParameters['channel'] : null).
            (isset($inputParameters['credit_card_brand']) ? $inputParameters['credit_card_brand'] : null).
            (isset($inputParameters['ch_lock']) ? $inputParameters['ch_lock'] : null).
            (isset($inputParameters['channel_groups']) ? $inputParameters['channel_groups'] : null).
            (isset($inputParameters['onlinetransfer']) ? $inputParameters['onlinetransfer'] : null).
            (isset($inputParameters['URL']) ? $inputParameters['URL'] : null).
            (isset($inputParameters['type']) ? $inputParameters['type'] : null).
            (isset($inputParameters['buttontext']) ? $inputParameters['buttontext'] : null).
            (isset($inputParameters['URLC']) ? $inputParameters['URLC'] : null).
            (isset($inputParameters['firstname']) ? $inputParameters['firstname'] : null).
            (isset($inputParameters['lastname']) ? $inputParameters['lastname'] : null).
            (isset($inputParameters['email']) ? $inputParameters['email'] : null).
            (isset($inputParameters['street']) ? $inputParameters['street'] : null).
            (isset($inputParameters['street_n1']) ? $inputParameters['street_n1'] : null).
            (isset($inputParameters['street_n2']) ? $inputParameters['street_n2'] : null).
            (isset($inputParameters['state']) ? $inputParameters['state'] : null).
            (isset($inputParameters['addr3']) ? $inputParameters['addr3'] : null).
            (isset($inputParameters['city']) ? $inputParameters['city'] : null).
            (isset($inputParameters['postcode']) ? $inputParameters['postcode'] : null).
            (isset($inputParameters['phone']) ? $inputParameters['phone'] : null).
            (isset($inputParameters['country']) ? $inputParameters['country'] : null).
            (isset($inputParameters['code']) ? $inputParameters['code'] : null).
            (isset($inputParameters['p_info']) ? $inputParameters['p_info'] : null).
            (isset($inputParameters['p_email']) ? $inputParameters['p_email'] : null).
            (isset($inputParameters['n_email']) ? $inputParameters['n_email'] : null).
            (isset($inputParameters['expiration_date']) ? $inputParameters['expiration_date'] : null).
            (isset($inputParameters['deladdr']) ? $inputParameters['deladdr'] : null).
            (isset($inputParameters['recipient_account_number']) ? $inputParameters['recipient_account_number'] : null).
            (isset($inputParameters['recipient_company']) ? $inputParameters['recipient_company'] : null).
            (isset($inputParameters['recipient_first_name']) ? $inputParameters['recipient_first_name'] : null).
            (isset($inputParameters['recipient_last_name']) ? $inputParameters['recipient_last_name'] : null).
            (isset($inputParameters['recipient_address_street']) ? $inputParameters['recipient_address_street'] : null).
            (isset($inputParameters['recipient_address_building']) ? $inputParameters['recipient_address_building'] : null).
            (isset($inputParameters['recipient_address_apartment']) ? $inputParameters['recipient_address_apartment'] : null).
            (isset($inputParameters['recipient_address_postcode']) ? $inputParameters['recipient_address_postcode'] : null).
            (isset($inputParameters['recipient_address_city']) ? $inputParameters['recipient_address_city'] : null).
            (isset($inputParameters['warranty']) ? $inputParameters['warranty'] : null).
            (isset($inputParameters['bylaw']) ? $inputParameters['bylaw'] : null).
            (isset($inputParameters['personal_data']) ? $inputParameters['personal_data'] : null).
            (isset($inputParameters['credit_card_number']) ? $inputParameters['credit_card_number'] : null).
            (isset($inputParameters['credit_card_expiration_date_year']) ? $inputParameters['credit_card_expiration_date_year'] : null).
            (isset($inputParameters['credit_card_expiration_date_month']) ? $inputParameters['credit_card_expiration_date_month'] : null).
            (isset($inputParameters['credit_card_security_code']) ? $inputParameters['credit_card_security_code'] : null).
            (isset($inputParameters['credit_card_store']) ? $inputParameters['credit_card_store'] : null).
            (isset($inputParameters['credit_card_store_security_code']) ? $inputParameters['credit_card_store_security_code'] : null).
            (isset($inputParameters['credit_card_customer_id']) ? $inputParameters['credit_card_customer_id'] : null).
            (isset($inputParameters['credit_card_id']) ? $inputParameters['credit_card_id'] : null).
            (isset($inputParameters['blik_code']) ? $inputParameters['blik_code'] : null).
            (isset($inputParameters['credit_card_registration']) ? $inputParameters['credit_card_registration'] : null).
            (isset($inputParameters['recurring_frequency']) ? $inputParameters['recurring_frequency'] : null).
            (isset($inputParameters['recurring_interval']) ? $inputParameters['recurring_interval'] : null).
            (isset($inputParameters['recurring_start']) ? $inputParameters['recurring_start'] : null).
            (isset($inputParameters['recurring_count']) ? $inputParameters['recurring_count'] : null);

        return hash('sha256',$CHkInputString);
    }
}

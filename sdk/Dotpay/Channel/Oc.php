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
use Dotpay\Model\CreditCard;
use Dotpay\Html\Form\Label;
use Dotpay\Html\Form\Radio;
use Dotpay\Html\Form\Select;
use Dotpay\Html\Form\Option;
use Dotpay\Html\Container\A;
use Dotpay\Html\Container\Div;
use Dotpay\Html\Img;
use Dotpay\Html\Element;

/**
 * Class provides a special functionality for credit card payments, realized as an one Click method
 */
class Oc extends Channel
{
    const CODE = 'oc';
    
    /**
     * @var CreditCard A credit card object which is assigned to this channel
     */
    private $card;
    
    /**
     * @var array An array of all available credit cards
     */
    private $cardList = [];
    
    /**
     * @var boolean A flag if user is logged on shop site
     */
    private $userIsLogged = true;
    
    /**
     * @var string An URL to a place in a shop where a customer can manage saved credit cards
     */
    private $manageCardsUrl;
    
    /**
     * @var string Description of saved cards option
     */
    private $savedCardsDescription = '';
    
    /**
     * @var string Description of register a new card option
     */
    private $registerCardDescription = '';
    
    /**
     * @var string Description of manage card URL
     */
    private $manageCardsDescription = '';


    /**
     * Initialize a credit card channel for the One Click method
     * @param Configuration $config Dotpay configuration object
     * @param Transaction $transaction ObjectNode with transaction details
     * @param PaymentResource $paymentResource Payment resource which can be used for Payment API
     * @param SellerResource $sellerResource Seller resource which can be used for Seller API
     */
    public function __construct(Configuration $config, Transaction $transaction, PaymentResource $paymentResource, SellerResource $sellerResource)
    {
        parent::__construct(Configuration::OC_CHANNEL, self::CODE, $config, $transaction, $paymentResource, $sellerResource);
        $this->setUserIsLogged(false);
    }
    
    /**
     * Check if the channel is visible
     * @return boolean
     */
    public function isVisible()
    {
        return parent::isVisible() &&
               $this->config->isOcEnable() &&
               $this->ifUserIsLogged();
    }
    
    /**
     * Return a credit card which is assigned
     * @return CreditCard
     */
    public function getCard()
    {
        return $this->card;
    }
    
    /**
     * Assign an credit card object to the channel
     * @param CreditCard $card An credit card object
     * @return Oc
     */
    public function setCard(CreditCard $card)
    {
        $this->card = $card;
        if($this->card->getUserId() == '') {
            $this->card->setUserId($this->transaction->getPayment()->getCustomer()->getId());
        }
        if($this->card->getOrderId() == null) {
            $this->card->setOrderId($this->transaction->getPayment()->getOrder()->getId());
        }
        return $this;
    }
    
    /**
     * Add a new credit card to a list of all available CCs
     * @param CreditCard $card An credit card object
     * @return Oc
     */
    public function addCard(CreditCard $card)
    {
        $this->cardList[] = $card;
        return $this;
    }
    
    /**
     * Return an array of all available credit cards
     * @return array
     */
    public function getCardList()
    {
        return $this->cardList;
    }
    
    /**
     * Return a flag if user is logged
     * @return boolean
     */
    public function ifUserIsLogged() {
        return $this->userIsLogged;
    }

    /**
     * Return an URL to a place in a shop where a customer can manage saved credit cards
     * @return string
     */
    public function getManageCardsUrl()
    {
		return $this->manageCardsUrl;
    }
    
    /**
     * Set an URL to a place in a shop where a customer can manage saved credit cards
     * @param string $url The given URL address
     * @return Oc
     */
    public function setManageCardsUrl($url)
    {
         $this->manageCardsUrl = (string)$url;
         return $this;
    }
    
    /**
     * Return array of hidden fields for a form to redirecting to a Dotpay site with all needed information about a current payment
     * @return array
     */
    protected function prepareHiddenFields()
    {
        $data = parent::prepareHiddenFields();
        $data['credit_card_customer_id'] = $this->getCard()->getCustomerHash();
        if ($this->getCard()->getCardId() == null) {
            $data['credit_card_store'] = 1;
        } else {
            $data['credit_card_id'] = $this->getCard()->getCardId();
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
        $numberOfcards = count($this->getCardList());
        if ($numberOfcards) {
            $data[] = new Radio('dotpay_oc_mode', 'select');
            $select = new Select('dotpay_card_list');
            foreach ($this->getCardList() as $card) {
                if ($card->isRegistered()) {
                    $select->addOption(new Option($card->getMask(), $card->getId()));
                }
            }
            if ($numberOfcards == 1) {
                $select->setSelected($this->cardList[0]->getId());
            }
            $data[] = $select;
        }
        $data[] = new Radio('dotpay_oc_mode', 'register');
        return $data;
    }
    
    /**
     * Return view fields enriched by an additional piece of HTML code
     * @return array
     */
    public function getViewFieldsHtml()
    {
        $data = $this->getViewFields();
        if(count($data) > 1) {
            return [
                $this->createSelectCardOption($data[0]),
                $this->createSelectCardList($data[1]),
                $this->createRegisterCardOption($data[2])
            ];
        } else if(count($data) == 1) {
            return [$this->createRegisterCardOption($data[0])];
        }
    }
    
    /**
     * Set a flag if user is logged
     * @param boolean $userIsLogged A flag if user is logged
     * @return Oc
     */
    public function setUserIsLogged($userIsLogged)
    {
        $this->userIsLogged = (boolean)$userIsLogged;
        return $this;
    }

    /**
     * Set a description of saved cards option
     * @param string $description Description of saved cards option
     * @return Oc
     */
    public function setSavedCardsDescription($description) {
        $this->savedCardsDescription = (string)$description;
        return $this;
    }
    
    /**
     * Set a description of register of a new card
     * @param string $description Description of register of a new card
     * @return Oc
     */
    public function setRegisterCardDescription($description) {
        $this->registerCardDescription = (string)$description;
        return $this;
    }
    
    /**
     * Set a description of manage cards URL
     * @param string $description Description of manage cards URL
     * @return Oc
     */
    public function setManageCardsDescription($description) {
        $this->manageCardsDescription = (string)$description;
        return $this;
    }

    /**
     * Create a HTML package for "select card option"
     * @param Element $element HTML element with "select card option"
     * @return Label
     */
    protected function createSelectCardOption(Element $element)
    {
        $a = new A($this->getManageCardsUrl(), $this->manageCardsDescription);
        $a->setAttribute('target', '_blank');
        $checkLabel = new Label($element, '', $this->savedCardsDescription.' ('.$a.')');
        $checkLabel->setAttribute('class', $element->getName());
        return $checkLabel;
    }
    
    /**
     * Create a HTML package for "select card list"
     * @param Element $element HTML element with credit card list
     * @return Div
     */
    protected function createSelectCardList(Element $element)
    {
        $img = new Img('');
        $img->setClass('dotpay-card-logo');
        foreach ($this->getCardList() as $card) {
            if ($card->isRegistered()) {
                $img->setData('card-'.$card->getId(), $card->getBrand()->getImage());
            }
        }
        $div = new Div([$element, $img]);
        $div->setAttribute('class', $element->getName());
        return $div;
    }
    
    /**
     * Create a HTML package for "register card option"
     * @param Element $element HTML element with "register card option"
     * @return Label
     */
    protected function createRegisterCardOption(Element $element)
    {
        $regLabel = new Label($element, '', $this->registerCardDescription);
        $regLabel->setAttribute('class', $element->getName());
        return $regLabel;
    }
}

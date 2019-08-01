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
namespace Dotpay\Model;

/**
 * Informations about a payment
 */
class Payment
{
    /**
     * @var Seller|null A Seller model for the payment
     */
    private $seller = null;

    /**
     * @var Customer A Customer model for the payment
     */
    private $customer;

    /**
     * @var Order An Order model for the payment
     */
    private $order;

    /**
     * @var string A description of the payment
     */
    private $description;

    /**
     * Initialize the model
     * @param Customer $customer Customer model for the payment
     * @param Order $order Order model for the payment
     * @param string $description Description of the payment
     */
    public function __construct(Customer $customer, Order $order, $description)
    {
        $this->setCustomer($customer);
        $this->setOrder($order);
        $this->setDescription($description);
    }

    /**
     * Return a Seller model for the payment
     * @return Seller|null
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Return a Customer model for the payment
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Return a Customer model for the payment
     * @return Customer
     */
    public function getCustomerDeliv()
    {
        return $this->customer;
    }


    /**
     * Return a Order model for the payment
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Return a description of the payment
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return an identifier of the payment, which depends on seller id, order amount, order currency and customer language
     * @return string
     */
    public function getIdentifier()
    {
        $sellerId = ($this->getSeller())?$this->getSeller()->getId():'';
        return $sellerId.$this->getOrder()->getAmount().$this->getOrder()->getCurrency().$this->getCustomer()->getLanguage();
    }

    /**
     * Set a Seller model for the payment
     * @param Seller $seller A Seller model for the payment
     * @return Payment
     */
    public function setSeller(Seller $seller)
    {
        $this->seller = $seller;
        return $this;
    }

    /**
     * Set a Customer model for the payment
     * @param Customer $customer A Customer model for the payment
     * @return Payment
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * Set a Order model for the payment
     * @param Order $order An Order model for the payment
     * @return Payment
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Set a description of the payment
     * @param string $description A description of the payment
     * @return Payment
     */
    public function setDescription($description)
    {
        $this->description = (string)$description;
        return $this;
    }
}

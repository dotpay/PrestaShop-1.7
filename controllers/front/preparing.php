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

include_once('dotpay.php');

/**
 * Controller for handling preparing form for Dotpay
 */
class DotpayPreparingModuleFrontController extends DotpayController
{
    /**
     * Preparing hidden form with payment data before sending it to Dotpay
     */
    public function initContent()
    {
        if ($this->module->active == false) {
            die;
        }
        
        if (is_object(Context::getContext()->cookie) && (int)Context::getContext()->cookie->dotpay_renew == 1) {
            unset(Context::getContext()->cookie->dotpay_renew);
            Context::getContext()->cookie->write();
        }
        
        parent::initContent();
        $this->display_column_left = false;
        $this->display_header = false;
        $this->display_footer = false;
        if (Tools::getValue('order') != false) {
            $this->setCart(Cart::getCartByOrderId(Tools::getValue('order')));
        }
        $this->initializeOrderData();
        $currency = Currency::getCurrency($this->getCart()->id_currency);
        $exAmount = $this->getOrder()->getExtrachargeAmount($this->getConfig(), $currency);
        if ($exAmount > 0 && !$this->module->isVirtualProductInCart($this->getConfig(), $this->getCart())) {
            $productId = $this->getConfig()->getExtraChargeVirtualProductId();
            $this->module->checkVirtualProduct();
            $product = new Product($productId, true);
            $product->price = $exAmount;
            $product->save();
            $product->flushPriceCache();

            $this->getCart()->updateQty(1, $product->id);
            $this->getCart()->update();
            $this->getCart()->getPackageList(true);
            $this->getOrder()->setAmount($this->getOrder()->getAmount()+$exAmount);
        }
        
        $discAmount = $this->getOrder()->getReductionAmount($this->getConfig(), $currency);
        if ($discAmount > 0) {
            $discount = new CartRule($this->getConfig()->getShippingReductionId());
            $discount->reduction_amount = $discAmount;
            $discount->reduction_currency = $this->getCart()->id_currency;
            $discount->reduction_tax = 1;
            $discount->update();
            $this->getCart()->addCartRule($discount->id);
            $this->getCart()->update();
            $this->getCart()->getPackageList(true);
            $this->getOrder()->setAmount($this->getOrder()->getAmount()-$discAmount);
        }
        
        if (Tools::getValue('order') == false) {
            $secureKey = (Context::getContext()->customer->secure_key!==null)?
                         Context::getContext()->customer->secure_key:md5(uniqid(rand(), true));
            $cartId = $this->getCart()->id;
            $this->module->validateOrder(
                $cartId,
                (int)$this->getConfig()->getWaitingStatus(),
                $this->getOrder()->getAmount(),
                $this->module->displayName,
                null,
                array(),
                null,
                false,
                $secureKey
            );
            $order = new Order(Order::getOrderByCartId($cartId));
        } else {
            $order = new Order(Tools::getValue('order'));
        }
        $this->prepareChannel($order);
        if ($this->getChannel()->canHaveInstruction()) {
            Tools::redirect(
                $this->context->link->getModuleLink($this->module->name, 'instruction', array(
                    'method' => Tools::getValue('method'),
                    'channel' => Tools::getValue('channel'),
                    'order' => $order->id
                ), true)
            );
        }
        $this->loader->get('PaymentResource')->close();
        $this->loader->get('SellerResource')->close();
        Dotpay\Loader\Loader::unload();
        die((string)$this->getChannel()->getHiddenForm());
    }
}

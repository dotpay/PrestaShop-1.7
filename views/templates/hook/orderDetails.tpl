{*
*
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
*  @author    Dotpay Team <tech@dotpay.pl>
*  @copyright Dotpay sp. z o.o.
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

<style>
    #dotpayDetailsPaymentPanel form {
        max-width: 600px;
    }
    .dotpay-margin {
        margin: 5px;
    }
    .dotpay-return-param {
        width: 500px !important;
    }
</style>
<script>{literal}
    window.refundConfig = {
        "orderId":{/literal}{$orderId|escape:'htmlall':'UTF-8'}{literal},
        "returnUrl":"{/literal}{$returnUrl}{literal}"
    }
{/literal}</script>
<div id="dotpayDetailsPaymentPanel" class="panel">
    <div class="panel-heading">
        <i class="icon-university"></i>
        Płatności Dotpay<span class="badge">$</span>
    </div>
    <h4>Zwrot płatności</h4>
    <form method="POST" action="{$returnUrl}">
        <input type="hidden" name="order_id" value="{$orderId|escape:'htmlall':'UTF-8'}" />
        <select id="dotpay-return-payment" name="payment" class="dotpay-margin dotpay-return-param">
            {foreach from=$payments key=count item=payment}
                <option value="{$payment->transaction_id|escape:'htmlall':'UTF-8'}">{$payment->transaction_id|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
        <div class="input-group dotpay-margin dotpay-return-param">
            <input type="number" name="amount" class="form-control dotpay-return-amount" aria-describedby="return-currency" step="0.01" value="" data-maxvalue="0" />
            <span class="input-group-addon" value="" id="return-currency"></span>
        </div>
        <input class="dotpay-margin dotpay-return-param" size="60" type="text" name="description" value="" />
        <input id="dotpay-refund-send" class="dotpay-margin" type="submit" value="Wykonaj zwrot" />
    </form>
</div>
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

{literal}
<style type="text/css">
    #dotpay-logo {
        height: 40px;
    }
</style>
{/literal}
<div class="box" style="overflow: auto;">
    <h2 class="page-subheading"><a href="http://www.dotpay.pl" target="_blank" title="www.dotpay.pl"><img id="dotpay-logo" src="{$moduleDir2}views/img/dotpay_logo_big.png" alt="Dotpay - {l s='Fast and secure Internet payments' mod='dotpay'}"/></a>&nbsp;{l s='Pay easy with Dotpay' mod='dotpay'}</h2>
    {if $isRenew}
    <p>{l s='Your payment was not yet confirmed by Dotpay. If you break payment, you can make it again.' mod='dotpay'}</p>
    <p class="cart_navigation">
        <a class="btn btn-info" href="{$paymentUrl}">
            <span id="proceedPaymentLink">
                {l s='Retry payment' mod='dotpay'}
            </span>
        </a>
    </p>
    {else}
        <p>{l s='Paments provide Dotpay' mod='dotpay'}</p>
    {/if}
	{if isset($DotpayTrId)}
		 {l s='This payment was processed in' mod='dotpay'} <a href="http://www.dotpay.pl" target="_blank" title="www.dotpay.pl">Dotpay</a> {l s='at the number:' mod='dotpay'}
		 <strong>{$DotpayTrId|escape:'htmlall':'UTF-8'}</strong>
	{/if}
	{if isset($isInstruction) && $isInstruction == true}
        <hr />
        <p>{l s='You can see instruction of payment completion.' mod='dotpay'}</p>
        <p class="cart_navigation">
            <a class="btn btn-info" href="{$instructionUrl}">
                <span id="proceedPaymentLink">
                    {l s='View instruction' mod='dotpay'}
                </span>
            </a>
        </p>
    {/if}
</div>
{*
* 2007-2015 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<?xml version="1.0" encoding="ISO-8859-1"?>
<form class="payment-form" method="POST" action="{$channel->get('target')}">
    <div class="dotpay-one-channel row">
        {if !empty($script)}
            <link href="{$dotpayUrl nofilter}widget/payment_widget.min.css" rel="stylesheet">
            <script src="{$modulePath nofilter}views/js/payment_widget.js"></script>
            {$script nofilter}
        {/if}
        <div class="channel-data">
            {if $surAmount > 0}
                <p class="alert alert-danger">{$surMessage|escape:'htmlall':'UTF-8'}: {$surAmount|escape:'htmlall':'UTF-8'}&nbsp;{$currency|escape:'htmlall':'UTF-8'}.</p>
            {/if}
            {if $exAmount > 0}
                <p class="alert alert-danger">{$exMessage|escape:'htmlall':'UTF-8'}: {$exAmount|escape:'htmlall':'UTF-8'}&nbsp;{$currency|escape:'htmlall':'UTF-8'}.</p>
            {/if}
            {if $reductAmount > 0}
                <p class="alert alert-success">{$reductMessage|escape:'htmlall':'UTF-8'}: {$reductAmount|escape:'htmlall':'UTF-8'}&nbsp;{$currency|escape:'htmlall':'UTF-8'}.</p>
            {/if}
            {foreach from=$channel->getViewFieldsHtml() item=field}
                {$field nofilter}
            {/foreach}
            {if $channel->getChannelId() != null}
            <input type="hidden" name="channel" value="{$channel->getChannelId()}" />
            {/if}
        </div>
		
        <div class="agreementsMessage">{$agreementsMessage|escape:'htmlall':'UTF-8'}</div><br>
        <div class="agreements">
            {foreach from=$channel->getAgreements() item=agreement}
                <label id="agreement_{$agreement->getName()}">
                    <input type="checkbox" value="1" name="{$agreement->getName()}" {if $agreement->getRequired()}required="true" checked="true" {/if}/>
                    {$agreement->getDescriptionHtml() nofilter}
                </label>
            {/foreach}
        </div>
    </div>
</form>
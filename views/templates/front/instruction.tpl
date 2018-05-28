{extends "$layout"}
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

{block name="content"}

<h2 class="page-heading">{l s='Payment instruction' mod='dotpay'}</h2>

{if $isOk}
<section id="instruction">
    {if $initialized}
        <p class="alert alert-success">{l s='Payment is now initialized' mod='dotpay'}</p>
    {/if}
    <div class="row">
        <div class="col-xs-12">
        {if $instruction->getIsCash() }
            <p class="alert alert-info" id="instruction-content">{l s='You can make the payment at post office or bank.' mod='dotpay'}<br />
            {l s='To pay with cash download and print the form below.' mod='dotpay'}<br />
            {l s='You can also use this data for online transfer or to fill your own form.' mod='dotpay'}</p>
        {else}
            <p class="alert alert-info" id="instruction-content">{l s='In order to pay login to your bank system and make a money transfer, using the following data:' mod='dotpay'}</p>
        {/if}
        </div>
        <div class="col-md-4 col-with-margin">
            {if $instruction->getBankAccount()!= null}
            <label>
                {l s='Account number' mod='dotpay'}
                <input type="text" class="important form-control" id="iban" value="{$instruction->getBankAccount()|escape:'htmlall':'UTF-8'}" readonly />
            </label>
            {/if}
            <label>
                {l s='Amount of payment' mod='dotpay'}
                <div class="input-group">
                    <input type="text" class="important form-control" id="amount" value="{$order->getAmount()|escape:'htmlall':'UTF-8'}" aria-describedby="transfer-currency" readonly >
                    <span class="input-group-addon" id="transfer-currency">{$order->getCurrency()|escape:'htmlall':'UTF-8'}</span>
                </div>
            </label>
            <label>
                {l s='Title of payment' mod='dotpay'}
                <input type="text" class="important form-control" id="payment-title" value="{$instruction->getNumber()|escape:'htmlall':'UTF-8'}" readonly />
            </label>
        </div>
        <div class="col-md-4">
            <label>
                {l s='Name of recipient' mod='dotpay'}
                <input type="text" class="important form-control" id="recipient" value="{$recipient_name|escape:'htmlall':'UTF-8'}" readonly />
            </label>
            <label>
                {l s='Street' mod='dotpay'}
                <input type="text" class="important form-control" id="street" value="{$recipient_street|escape:'htmlall':'UTF-8'}" readonly />
            </label>
            <label>
                {l s='Post code and city' mod='dotpay'}
                <input type="text" class="important form-control" id="post-code-city" value="{$recipient_city|escape:'htmlall':'UTF-8'}" readonly />
            </label>
        </div>
    </div>
    <div class="row">
        <section id="payment-form" class="col-xs-12">
            <div id="blankiet-download-form">
                <div id="channel_container_confirm">
                    <a href="{$address}" target="_blank" title="{$buttonTitle|escape:'htmlall':'UTF-8'}">
                        <div>
                            <img src="{$channel->getLogo()}" alt="{l s='Channel logo' mod='dotpay'}" />
                            <span><i class="material-icons">description</i>&nbsp;{$buttonTitle|escape:'htmlall':'UTF-8'}</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
        {if !$instruction->getIsCash() }
            <div class="col-xs-12">
                <p class="alert alert-info">{l s='Not following the above procedure (e.g. changing the amount or payment title) will make your payment not handled automatically, and therefore lengthen finalization time of the transaction.' mod='dotpay'}</p>
            </div>
        {/if}
    </div>
</section>
                            
{else}
    <p class="alert alert-danger">{l s='Payment is not found or registered' mod='dotpay'}</p>
{/if}
{/block}
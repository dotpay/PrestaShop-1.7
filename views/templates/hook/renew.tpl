{literal}
<style type="text/css">
    #dotpay-logo {
        height: 40px;
    }
</style>
{/literal}
<div class="box" style="overflow: auto;">
    <h2 class="page-subheading"><a href="http://dotpay.pl" target="_blank"><img id="dotpay-logo" src="/modules/{$this->_path nofilter}dotpay/views/img/dotpay_logo_big.png" alt="Dotpay - {l s='Fast and secure Internet payments' mod='dotpay'}"/></a>&nbsp;{l s='Pay easy with Dotpay' mod='dotpay'}</h2>
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
    {if $isInstruction == true}
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
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
*  @copyright Dotpay
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

{block name="content"}

<h2 class="page-heading">{l s='Status of payment of the order:' mod='dotpay'}&nbsp;{$orderReference|escape:'htmlall':'UTF-8'}</h2>

{literal}
    <style type="text/css">
        #statusMessageContainer {
            text-align: center;
        }
        
        #statusMessageContainer p {
            text-align: left;
        }
        
        /* Loader */
        .loading {
            position: relative;
            width: 100%;
            height: 70px;
        }

        .loading:after {
            font-family: Sans-Serif !important;
            box-sizing: border-box;
            content: '';
            position: absolute;
            z-index: 100;
            left: 50%;
            top: 50%;
            width: 40px;
            height: 40px;
            font-size: 40px;
            border-right: 3px solid #9e191d;
            border-bottom: 1px solid #ebebeb;
            border-top: 2px solid #9e191d;
            border-radius: 100px;
            margin: -30px 0 0 -20px; 
            animation: spin .75s infinite linear;
            -webkit-animation: spin .75s infinite linear;
            -moz-animation: spin .75s infinite linear;
            -o-animation: spin .75s infinite linear;
        }

        .spin {
            -webkit-animation: spin 1000ms infinite linear;
            animation: spin 1000ms infinite linear;
        }

        @keyframes spin {
            from { transform:rotate(0deg); }
            to { transform:rotate(360deg); }
        }

        @-webkit-keyframes spin {
            from { -webkit-transform: rotate(0deg); }
            to { -webkit-transform: rotate(360deg); }
        }
    </style>
{/literal}     
<div id="statusMessageContainer">
    {if $message != null}
        <p class="alert alert-danger">{$message|escape:'htmlall':'UTF-8'}</p>
        {literal}
            <script type="text/javascript">
                setTimeout(function(){location.href="{/literal}{$redirectUrl}{literal}";}, 4000);
            </script>
        {/literal}
    {/if}
</div>

{if $message == null}
    {literal}
        <script type="text/javascript">
            window.checkStatusConfig = {
                "url": "{/literal}{$checkStatusUrl nofilter}{literal}",
                "orderId": "{/literal}{$orderId}{literal}",
                "waitingMessage": "{/literal}{$waitingMessage nofilter}{literal}",
                "successMessage": "{/literal}{$successMessage nofilter}{literal}",
                "tooManyPaymentsMessage": "{/literal}{$tooManyPaymentsMessage nofilter}{literal}",
                "errorMessage": "{/literal}{$errorMessage nofilter}{literal}",
                "timeoutMessage": "{/literal}{$timeoutMessage nofilter}{literal}",
                "redirectUrl": "{/literal}{$redirectUrl nofilter}{literal}"
            };
        </script>
    {/literal}
{/if}
<a href="{$link->getPageLink('index', true, null)}" class="btn btn-warning dotpay-back-button">{l s='Back to main mage' mod='dotpay'}</a>
{/block}
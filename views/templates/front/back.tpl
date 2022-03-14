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
*  @copyright PayPro S.A.
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

{block name="content"}

<h2 class="page-heading">{l s='Status of payment of the order:' mod='dotpay'}&nbsp;{$orderReference|escape:'htmlall':'UTF-8'}</h2>

{literal}
    <style type="text/css">
        #statusMessageContainer {
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

        #hiddenHookData {
            display: none;
        }

		#statusMessageContainer > p.alert {
			font-size: 1.1em;
			min-height: 70px;
			padding: 30px;
		}

		#statusMessageContainer > p.alert-danger:before {
			font-family: "Material Icons"; content: "\E629";
			font-size: 2.5em;
			color: #d04f4f;
			display: block;
			width: 48px;
			height: 70px;
			float: left;
			padding-top: 5px;
			margin-right: 20px;
		}

		#statusMessageContainer > p.alert-success:before {
			font-family: "Material Icons"; content: "\E86C";
			font-size: 2.5em;
			color: #4cbb6c;
			display: block;
			width: 48px;
			height: 70px;
			float: left;
			padding-top: 5px;
			margin-right: 20px;
		}

		#statusMessageContainer > p.alert-info:before {
			font-family: "Material Icons"; content: "\E627";
			font-size: 2.5em;
			color: #2fb5d2;
			display: block;
			width: 48px;
			height: 70px;
			float: left;
			padding-top: 5px;
			margin-right: 20px;
		}

		#statusMessageContainer > p.alert-warning:before {
			font-family: "Material Icons"; content: "\e84f";
			font-size: 2.5em;
			color: #FF5722;
			display: block;
			width: 48px;
			height: 70px;
			float: left;
			padding-top: 5px;
			margin-right: 20px;
		}

        #dp_countdown{
            background-color: #e8f4f7;
            border-color: #99afb9;
            color: #2e2974;
            font-size: 0.9em;
            border-radius: 6px;
            box-sizing: inherit;
            min-width: 65px;
          }

    </style>
{/literal}
<div id="statusMessageContainer">
    {if $message != null}
        <p class="alert alert-danger">{$message|escape:'htmlall':'UTF-8'}</p>
        {literal}
            <script type="text/javascript">
                setTimeout(function(){location.href="{/literal}{$redirectUrl}{literal}";}, 6000);
            </script>
        {/literal}
    {/if}
</div>
<div id="hiddenHookData">{$hiddenHookData nofilter}</div>
{if $message == null}
    {literal}
        <script type="text/javascript">
            function ready(fn) {
                if (document.readyState !== 'loading'){
                    fn();
                } else if (document.addEventListener) {
                    document.addEventListener('DOMContentLoaded', fn);
                } else {
                    document.attachEvent('onreadystatechange', function() {
                        if (document.readyState !== 'loading')
                            fn();
                    });
                }
            }
            window.backConfig = {
                messages: {
                    notFound: "{/literal}{$notFoundMessage|escape:'htmlall':'UTF-8'}{literal}",
                    basic: "{/literal}{$basicMessage|escape:'htmlall':'UTF-8'}{literal}",
                    status: "{/literal}{$statusMessage|escape:'htmlall':'UTF-8'}{literal}",
                    timeout: "{/literal}{$timeoutMessage|unescape: 'html' nofilter}{literal}",
                    pending: "{/literal}{$waitingMessage nofilter}{literal}",
                    pendingend: "{/literal}{$waitingMessageEnd|unescape: 'html' nofilter}{literal}",
                    success: "{/literal}{$successMessage|unescape: 'html' nofilter}{literal}",
                    error: "{/literal}{$errorMessage|unescape: 'html' nofilter}{literal}",
                    tooMany: "{/literal}{$tooManyPaymentsMessage|unescape: 'html' nofilter}{literal}",
                    unknown: "{/literal}{$unknownMessage|escape:'htmlall':'UTF-8'}{literal}"
                },
                target: "{/literal}{$checkStatusUrl nofilter}{literal}",
                redirect: "{/literal}{$redirectUrl}{literal}",
                orderId: {/literal}{$orderId}{literal},
                interval: 9,
                timeout: 2*60
            };
            ready(function(){
                DotpayStatusChecker($('#statusMessageContainer'), window.backConfig);
            });
        </script>
    {/literal}
{/if}
<a href="{$link->getPageLink('index', true, null)}" class="btn btn-warning dotpay-back-button">{l s='Back to main page' mod='dotpay'}</a>
{/block}

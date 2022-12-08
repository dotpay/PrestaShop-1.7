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
* @author Dotpay Team <tech@dotpay.pl>
* @copyright PayPro S.A.
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
*
*}

    <style>
        #dotpayDetailsPaymentPanel form {
            max-width: 600px;
        }

        .dotpay-return-param {
            max-width: 300px !important;
        }
    </style>
		<script>
		{literal}
			window.refundConfig = {
				"orderId":{/literal}{$orderId|escape:'htmlall':'UTF-8'}{literal},
				"returnUrl":"{/literal}{$returnUrl}{literal}"
			}
		{/literal}
		</script>


    <div id="dotpayDetailsPaymentPanel" class="card mt-2 panel">
        <div class="card-header">
            <h3 class="card-header-title">
                <img src="data:image/png;base64,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" width="58" height="20" alt="Przelewy24" style="vertical-align: text-bottom">
                 Płatności Przelewy24
            </h3>
            <h4 style="margin-left: 20px; padding: 10px;"><i class="material-icons">rotate_left</i><i class="material-icons mi-payment">payment</i> {$RefundTransaction|escape:'htmlall':'UTF-8'}</h4>
        </div>
        <div class="card-body">
          <form method="POST" action="{$returnUrl}" id="dotpay_refund_form">
           <input type="hidden" name="order_id" value="{$orderId|escape:'htmlall':'UTF-8'}" />
            <table class="table">
                <thead>
                    <tr>
                        <th class="table-head-payment">{$TransactionNumber|escape:'htmlall':'UTF-8'}</th>
                        <th class="table-head-amount" style="min-width: 200px;">{$TransactionAmount|escape:'htmlall':'UTF-8'}</th>
                        <th class="table-head-invoice" >{$TransactionDescription|escape:'htmlall':'UTF-8'}</th>
                        <th >&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="d-print-none">

						 <td>
							<div class="input-group dotpay-return-param">
								<select id="dotpay-return-payment" name="payment" class="form-control dotpay-return-param" style="min-width: 140px;">
									{foreach from=$payments key=count item=payment}
										<option value="{$payment->transaction_id|escape:'htmlall':'UTF-8'}">{$payment->transaction_id|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
						 </td>
							<td >							
								<div class="input-group dotpay-return-param">
									<input type="number" name="amount" class="form-control dotpay-return-amount" aria-describedby="return-currency" step="0.01" value="" data-maxvalue="0" style="width:120px"/>
									<div class="input-group-append"><div class="input-group-text" value="" id="return-currency">&nbsp;</div></div>
								</div>
								
							</td>
							<td>
									<input class="dotpay-return-param form-control" type="text" name="description" value="" style="min-width: 250px;"/>
							</td>
							<td class="text-right">

									<button type="button" id="dotpay-refund-send-confirm" class="btn btn-warning btn-sm" style="width: 170px;">{$RefundTransaction|escape:'htmlall':'UTF-8'}</button>
							</td>
                              
                    </tr>
					<tr class="d-print-none">
					<td colspan="4">
					<div class="alert alert-warning" id="dp_refund_wait" style="display:none">
						<h4>{$RefundWait|escape:'htmlall':'UTF-8'}</h4>
						<p>{$RefundWaitDesc|escape:'htmlall':'UTF-8'} <strong><span id="dp_refund_status">Refund is waiting for confirmation</span></strong>. </p>
					</div>
					<div class="alert alert-warning" id="dp_refund_partial" style="display:none">
						<h4>{$RefundDone|escape:'htmlall':'UTF-8'}</h4>
						<p>{$RefundDonePartial|escape:'htmlall':'UTF-8'} <strong><span id="dp_refund_status">Partial refund of payment</span></strong>. </p>

					</div>
					<div class="alert alert-warning" id="dp_refund_total" style="display:none">
						<h4>{$RefundDone|escape:'htmlall':'UTF-8'}</h4>
						<p>{$RefundDoneTotal|escape:'htmlall':'UTF-8'} <strong><span id="dp_refund_status">Total refund of payment</span></strong>. </p>
					</div>
					<div class="alert alert-warning" id="dp_refund_rejected" style="display:none">
						<h4>{$RefundWait|escape:'htmlall':'UTF-8'}</h4>
						<p>{$RefundWaitDesc|escape:'htmlall':'UTF-8'} <strong><span id="dp_refund_status">Refund has rejected</span></strong>. </p>
					</div>
					<div class="alert alert-info">
						<h4>{$RefundAlertInfo1|escape:'htmlall':'UTF-8'}</h4>
						<p>{$RefundAlertInfo2|escape:'htmlall':'UTF-8'}</p>
						<p><em>{$RefundAlertInfo3|escape:'htmlall':'UTF-8'}</em></p>
					</div>

					
						<div id="dp_confirmRefund" style="display:none">
							<button type="button" id="dotpay-refund-send" class="btn btn-danger btn-sm" style="width: 180px;" >{$Refundbutton2|escape:'htmlall':'UTF-8'}</button>
						 		&nbsp;&nbsp;
							<button type="button" id="dotpay-refund-cancel" class="btn btn-secondary btn-sm" style="width: 170px;" >{$Refundbutton3|escape:'htmlall':'UTF-8'}</button>
							
						</div>
					</td>
					</tr>
                </tbody>
            </table>
			</form>  
            <p>&nbsp;</p>
        </div>
    </div>

    <script>
    {literal}
        $(document).ready(function () {
			
			var dp_is_refund_status_wait1 = $('div#historyTabContent span:contains("Zwrot płatności oczekuje na potwierdzenie")').text().trim();
			var dp_is_refund_status_wait2 = $('div#historyTabContent span:contains("Zwrot oczekuje na potwierdzenie")').text().trim();
			var dp_is_refund_status_wait3 = $('div#historyTabContent span:contains("Refund is waiting for confirmation")').text().trim();
			
			var dp_is_refund_status_total1 = $('div#historyTabContent span:contains("Całkowity zwrot płatności")').text().trim();
			var dp_is_refund_status_total2 = $('div#historyTabContent span:contains("Total refund of payment")').text().trim();
			
			var dp_is_refund_status_partial1 = $('div#historyTabContent span:contains("Częściowy zwrot płatności")').text().trim();
			var dp_is_refund_status_partial2 = $('div#historyTabContent span:contains("Partial refund of payment")').text().trim();
			
			var dp_is_refund_status_rejected1 = $('div#historyTabContent span:contains("Zwrot płatności został odrzucony")').text().trim();
			var dp_is_refund_status_rejected2 = $('div#historyTabContent span:contains("Zwrot został odrzucony")').text().trim();
			var dp_is_refund_status_rejected3 = $('div#historyTabContent span:contains("Refund has rejected")').text().trim();

			var dp_refund_wait_process = dp_is_refund_status_wait1.length + dp_is_refund_status_wait2.length + dp_is_refund_status_wait3.length;
			var dp_refund_completed_process = dp_is_refund_status_total1.length + dp_is_refund_status_total2.length + dp_is_refund_status_partial1.length + dp_is_refund_status_partial2.length + dp_is_refund_status_rejected1.length + dp_is_refund_status_rejected2.length + dp_is_refund_status_rejected3.length;
			
            setTimeout(function () {
                if ($('#view_order_payments_block').length == 1) {
                    $('#dotpayDetailsPaymentPanel').insertAfter($('#view_order_payments_block'));
                }
            }, 500);
		
			
			    $('#dotpay-refund-send-confirm').click(function() {			
					$('#dp_confirmRefund').show();
						if($('#dotpay-refund-send-confirm').attr('disabled') != "disabled"){
							$('#dotpay-refund-send-confirm').attr('disabled', true);
						}
			
						var refund_status_read = '';

						if(dp_is_refund_status_wait1.length >10 && dp_refund_completed_process < 10 ){						
							console.log('found refund status: '+ dp_is_refund_status_wait1);
							var refund_status_read = dp_is_refund_status_wait1;
							$('#dp_refund_wait').show();
						}else if(dp_is_refund_status_wait2.length >10 && dp_refund_completed_process < 10){
							console.log('found refund status: '+ dp_is_refund_status_wait2);
							var refund_status_read = dp_is_refund_status_wait2;
							$('#dp_refund_wait').show();
						}else if(dp_is_refund_status_wait3.length >10 && dp_refund_completed_process < 10){
							console.log('found refund status: '+ dp_is_refund_status_wait3);
							var refund_status_read = dp_is_refund_status_wait3;
							$('#dp_refund_wait').show();
						}else if(dp_is_refund_status_partial1.length >10 ){
							console.log('found refund status: '+ dp_is_refund_status_partial1);
							var refund_status_read = dp_is_refund_status_partial1;
							$('#dp_refund_partial').show();
						}else if(dp_is_refund_status_partial2.length >10){
							console.log('found refund status: '+ dp_is_refund_status_partial2);
							var refund_status_read = dp_is_refund_status_partial2;
							$('#dp_refund_partial').show();
						}else if(dp_is_refund_status_total1.length >10){
							console.log('found refund status: '+ dp_is_refund_status_total1);
							var refund_status_read = dp_is_refund_status_total1;
							$('#dp_refund_total').show();
							$('#dotpay-refund-send').attr('disabled', true);
						}else if(dp_is_refund_status_total2.length >10){
							console.log('found refund status: '+ dp_is_refund_status_total2);
							var refund_status_read = dp_is_refund_status_total2;
							$('#dp_refund_total').show();
						}else if(dp_is_refund_status_rejected1.length >10 && (dp_is_refund_status_total1.length + dp_is_refund_status_total2.length + dp_is_refund_status_partial1.length + dp_is_refund_status_partial2.length) < 10){
							console.log('found refund status: '+ dp_is_refund_status_rejected1);
							var refund_status_read = dp_is_refund_status_rejected1;
							$('#dp_refund_rejected').show();
						}else if(dp_is_refund_status_rejected2.length >10 && (dp_is_refund_status_total1.length + dp_is_refund_status_total2.length + dp_is_refund_status_partial1.length + dp_is_refund_status_partial2.length) < 10){
							console.log('found refund status: '+ dp_is_refund_status_rejected2);
							var refund_status_read = dp_is_refund_status_rejected2;
							$('#dp_refund_rejected').show();
						}else if(dp_is_refund_status_rejected3.length >10 && (dp_is_refund_status_total1.length + dp_is_refund_status_total2.length + dp_is_refund_status_partial1.length + dp_is_refund_status_partial2.length) < 10){
							console.log('found refund status: '+ dp_is_refund_status_rejected3);
							var refund_status_read = dp_is_refund_status_rejected3;
							$('#dp_refund_rejected').show();
						}
						
						//change of the displayed status according to the displayed language
						if(refund_status_read.length >10){
							$("span[id]").each(function(){
								if(this.id==='dp_refund_status')$(this).html(refund_status_read)
							})
						}

					return false;	
					});
					
					$('#dotpay-refund-send').click(function(){
						if(confirm("{/literal}{$RefundConfirmInfo|escape:'htmlall':'UTF-8'}{literal}")){
							 $("#dotpay_refund_form").submit();
							return true;
						}
						else{
							return false;
						}
					return false;
	
				});
				
				$('#dotpay-refund-cancel').click(function(){
					
					$('#dp_confirmRefund').hide();
					$('#dotpay-refund-send-confirm').attr('disabled', false);
				return false;					
				});
			
        });
    {/literal}
</script>

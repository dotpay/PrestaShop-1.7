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
                <img src="data:image/png;base64,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" width="65" height="19" alt="Dotpay" style="vertical-align: text-bottom">
                Płatności Dotpay
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

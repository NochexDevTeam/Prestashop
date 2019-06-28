
<!-- The <p> tags below are details of the button which users select to go to Nochex page.-->
<style>
a.nochex:after {
display: block;
content: "\f054";
position: absolute;
right: 15px;
margin-top: -11px;
top: 50%;
font-family: "FontAwesome";
font-size: 25px;
height: 22px;
width: 14px;
color: #777777;
}
</style>

<p class="payment_module">
	<a href="javascript:$('#nochex_form').submit();" title="{l s='Pay with Nochex APC' mod='NoChex'}" style="padding: 0;width: 49%;" class="nochex">
		
		<img src="https://www.nochex.com/logobase-secure-images/logobase-banners/clear-amex-mp.png" style="height:100px;">
	
	</a>
</p>

<!-- The form below is details from the nochex.php form which variables are attached to the relevant field. These will be sent to Nochex. -->
<form action="https://secure.nochex.com/default.aspx" method="post" id="nochex_form" class="hidden">
	<input type='hidden' name='amount' value="{$amount}" />
	<input type='hidden' name='description' value="{$description}" />
	<input type='hidden' name='xml_item_collection' value="{$xml_item_collection}" />
	<input type='hidden' name='postage' value="{$postage}" />
	<input type='hidden' name='billing_fullname' value="{$billing_fullname}" />
	<input type='hidden' name='billing_address' value="{$billing_address}" />
	<input type='hidden' name='billing_city' value="{$billing_city}" />
	<input type='hidden' name='billing_postcode' value="{$billing_postcode}" />
	<input type='hidden' name='delivery_fullname' value="{$delivery_fullname}" />
	<input type='hidden' name='delivery_address' value="{$delivery_address}" />
	<input type='hidden' name='delivery_city' value="{$delivery_city}" />
	<input type='hidden' name='delivery_postcode' value="{$delivery_postcode}" />
    <input type='hidden' name='customer_phone_number' value="{$customer_phone_number}" />
	<input type='hidden' name='email_address' value="{$email_address}" />
	<input type='hidden' name='order_id' value="{$order_id}" />
	<input type='hidden' name='optional_1' value="{$optional_1}" />
	<input type='hidden' name='optional_2' value="{$optional_2}" />
	<input type='hidden' name='merchant_id' value="{$merchant_id}" />
    <input type='hidden' name='success_url' value="{$successurl}"/>
	<input type='hidden' name='test_success_url' value="{$successurl}"/>
	<input type='hidden' name='cancel_url' value="{$cancelurl}"/>
	<input type='hidden' name='declined_url' value="{$cancelurl}"/>
	<input type='hidden' name='callback_url' value="{$responderurl}" />
	<input type='hidden' name='test_transaction' value="{$test_transaction}" />
</form>

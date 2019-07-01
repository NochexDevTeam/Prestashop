{*
* 2007-2019 PrestaShop
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
*  @author Nochex
*  @copyright  2007-2019 Nochex
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

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
	<a href="javascript:$('#nochex_form').submit();" title="{l s='Pay with Nochex APC' mod='nochex'}" style="padding: 0;width: 49%;" class="nochex">
		
		<img src="https://www.nochex.com/logobase-secure-images/logobase-banners/clear-amex-mp.png" style="height:100px;">
	
	</a>
</p>

<!-- The form below is details from the nochex.php form which variables are attached to the relevant field. These will be sent to Nochex. -->
<form action="https://secure.nochex.com/default.aspx" method="post" id="nochex_form" class="hidden">
	<input type='hidden' name='amount' value="{$amount|escape:'html'}" />
	<input type='hidden' name='description' value="{$description|escape:'html'}" />
	<input type='hidden' name='xml_item_collection' value="{$xml_item_collection|escape:'html'}" />
	<input type='hidden' name='postage' value="{$postage|escape:'html'}" />
	<input type='hidden' name='billing_fullname' value="{$billing_fullname|escape:'html'}" />
	<input type='hidden' name='billing_address' value="{$billing_address|escape:'html'}" />
	<input type='hidden' name='billing_city' value="{$billing_city|escape:'html'}" />
	<input type='hidden' name='billing_postcode' value="{$billing_postcode|escape:'html'}" />
	<input type='hidden' name='delivery_fullname' value="{$delivery_fullname|escape:'html'}" />
	<input type='hidden' name='delivery_address' value="{$delivery_address|escape:'html'}" />
	<input type='hidden' name='delivery_city' value="{$delivery_city|escape:'html'}" />
	<input type='hidden' name='delivery_postcode' value="{$delivery_postcode|escape:'html'}" />
    <input type='hidden' name='customer_phone_number' value="{$customer_phone_number|escape:'html'}" />
	<input type='hidden' name='email_address' value="{$email_address|escape:'html'}" />
	<input type='hidden' name='order_id' value="{$order_id|escape:'html'}" />
	<input type='hidden' name='optional_1' value="{$optional_1|escape:'html'}" />
	<input type='hidden' name='optional_2' value="{$optional_2|escape:'html'}" />
	<input type='hidden' name='merchant_id' value="{$merchant_id|escape:'html'}" />
    <input type='hidden' name='success_url' value="{$successurl|escape:'html'}"/>
	<input type='hidden' name='test_success_url' value="{$successurl|escape:'html'}"/>
	<input type='hidden' name='cancel_url' value="{$cancelurl|escape:'html'}"/>
	<input type='hidden' name='declined_url' value="{$cancelurl|escape:'html'}"/>
	<input type='hidden' name='callback_url' value="{$responderurl|escape:'html'}" />
	<input type='hidden' name='test_transaction' value="{$test_transaction|escape:'html'}" />
</form>

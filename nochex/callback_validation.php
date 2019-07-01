<?php
/**
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
*  Plugin Name: Nochex Payment Gateway for Prestashop
*  Description: Accept Nochex Payments, orders are updated using APC.
*  Version: 2.0
*  License: GPL2
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/nochex.php');
/** Creates a new instance of the nochexDebug class */
$nochexDebug = new nochex();
$values = "";
$customer = "";
// VARIABLES
foreach ($_POST as $key => $value) {
    $values[] = $key."=".urlencode($value);
}
$work_string = @implode("&", $values);
if (Tools::getValue("optional_2") == "Yes") {
    $url = "https://secure.nochex.com/callback/callback.aspx";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $work_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    $response = preg_replace("'Content-type: text/plain'si", "", $output);
    /** The response from APC is stored in this variable. */
    $responseMessage= "APC Response.... " . $response;
    /** The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt */
    $nochexDebug->writeDebug($responseMessage);
    $secure = "1";
    if (Tools::getValue("transaction_status") == "100") {
        $testStatus = "Test";
    } else {
        $testStatus = "Live";
    }
    $responses = "Payment Accepted - Callback Status: ". $response .". Transaction Type - ". $testStatus;
    $transaction_id = Tools::getValue("transaction_id");
    $customer = Tools::getValue("optional_1");
    $extras = array("transaction_id" => $transaction_id);
    $nochex = new nochex();
    /** If statement which checks the apc status of an order */
    if ($response == "AUTHORISED") {
        $nochex->validateOrder(Tools::getValue("order_id"), Configuration::get('PS_OS_PAYMENT'), Tools::getValue("amount"), $nochex->displayName, $responses, $extras, Tools::getValue("cIY"), false, $customer);
        /** The response from APC is stored in this variable. */
        $responseAuthorisedMessage= "APC Response.... Order ID: " . Tools::getValue("order_id") . "... PS_OS_Payment: ". _PS_OS_PAYMENT_ . ". Amount: ". Tools::getValue("amount"). ". Display name: ". $nochex->displayName. ". CurrencyID: ". Tools::getValue("cIY");
        /** The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt */
        $nochexDebug->writeDebug($responseAuthorisedMessage);
    } else if ($response == "DECLINED") {
        $nochex->validateOrder(Tools::getValue("order_id"), Configuration::get('PS_OS_ERROR'), Tools::getValue("amount"), $nochex->displayName, $responses, $extras, Tools::getValue("cIY"), $customer);
        /** The response from APC is stored in this variable. */
        $responseUnAuthorisedMessage= "APC Response.... Order ID: " . Tools::getValue("order_id") . "... PS_OS_Payment: ". _PS_OS_PAYMENT_ . "... Amount: ". Tools::getValue("amount"). "... Display name: ". $nochex->displayName. "... CurrencyID: ". Tools::getValue("cIY");
        /** The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt */
        $nochexDebug->writeDebug($responseUnAuthorisedMessage);
    } else {
        /** Error response if there is no response, APC is neither Autorised or Declined */
        $subject = "NOCHEX VALIDITY RESPONSE: INVALID RESPONSE";
        $msg = "RESPONSE FROM NOCHEX Was NEITHER AUTHORISED OR DECLINED?\n";
        $msg.= "This could be because cURL isn't supported on your webserver.\n\n";
        $msg.= "Response was \"{$response}\"\n\n";
        $nochex->validateOrder(Tools::getValue("order_id"), Configuration::get('PS_OS_ERROR'), Tools::getValue("amount"), $nochex->displayName, $responses, $extras, $secure, $customer);
    }
} else {
    $url = "https://www.nochex.com/apcnet/apc.aspx";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $work_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    $response = preg_replace("'Content-type: text/plain'si", "", $output);
    $responseMessage= "APC Response.... " . $response;
    $nochexDebug->writeDebug($responseMessage);
    $secure = "1";
    $responses = "Payment Accepted - APC ". $response . ". Transaction Status - ". Tools::getValue("status") ;
    $transaction_id = Tools::getValue("transaction_id");
    $nochex = new nochex();
    $extras = array("transaction_id" => $transaction_id);
    $custSecure = Tools::getValue("custom");
    if ($response == "AUTHORISED") {
        $nochex->validateOrder(Tools::getValue("order_id"), Configuration::get('PS_OS_PAYMENT'), Tools::getValue("amount"), $nochex->displayName, $responses, $extras, Tools::getValue("cIY"), false, $custSecure);
        $responseAuthorisedMessage= "APC Response.... Order ID: " . Tools::getValue("order_id") . "... PS_OS_Payment: ". _PS_OS_PAYMENT_ . ". Amount: ". Tools::getValue("amount"). ". Display name: ". $nochex->displayName. ". CurrencyID: ". Tools::getValue("cIY");
        $nochexDebug->writeDebug($responseAuthorisedMessage);
    } else {
        $nochex->validateOrder(Tools::getValue("order_id"), Configuration::get('PS_OS_ERROR'), Tools::getValue("amount"), $nochex->displayName, $responses, $extras, Tools::getValue("cIY"), $custSecure);
        $responseUnAuthorisedMessage= "APC Response.... Order ID: " . Tools::getValue("order_id") . "... PS_OS_Payment: ". _PS_OS_PAYMENT_ . "... Amount: ". Tools::getValue("amount"). "... Display name: ". $nochex->displayName. "... CurrencyID: ". Tools::getValue("cIY");
        $nochexDebug->writeDebug($responseUnAuthorisedMessage);
    }
}

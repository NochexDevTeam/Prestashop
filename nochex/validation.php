<?php
/*
Plugin Name: Nochex Payment Gateway for Prestashop
Description: Accept Nochex Payments, orders are updated using APC.
Version: 2.0
License: GPL2
*/
/* Includes information from two files, config.inc.php and nochex.php */
include(dirname(__FILE__).'/../../config/config.inc.php');
// Include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/nochex.php');
//--- This includes/gets a file which has a write function to nochex_debug.txt ---//
require(dirname(__FILE__).'/writeFunction.php');
//--- Creates a new instance of the nochexDebug class ---//
$nochexDebug = new nochexDebug();
		
// VARIABLES

if (!isset($_POST)) $_POST = &$HTTP_POST_VARS;
foreach ($_POST AS $key => $value) {
$values[] = $key."=".urlencode($value);
}
$work_string = @implode("&", $values);

$url = "https://www.nochex.com/apcnet/apc.aspx";
$ch = curl_init ();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_POST, true);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $work_string);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
$output = curl_exec ($ch);
curl_close ($ch);

$response = preg_replace ("'Content-type: text/plain'si","",$output);
//--- The response from APC is stored in this variable. ---//
$responseMessage= "APC Response.... " . $response;
//--- The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt ---//
$nochexDebug->writeDebug($responseMessage);

$secure = "1";

$responses = "Payment Accepted - APC ". $response .". Transaction Status - ".$_POST["status"] ;

$transaction_id = $_POST["transaction_id"];
/* If statement which checks the apc status of an order */
if ($response=="AUTHORISED") 
	{
	//--- Creates a new instances of the nochex class and gets the variable for the currency ---//
	$nochex = new nochex();
    $currency = new Currency($cookie->id_currency);
	//--- Sends the data to the database.  ---//
    //$nochex->validateOrder($_POST["order_id"], _PS_OS_PAYMENT_, $_POST["amount"], $nochex->displayName, NULL, NULL, $currency->id);

	$extras = array("transaction_id" => $transaction_id);
	$customer->secure_key = $_POST["custom"];

	$nochex->validateOrder($_POST["order_id"], Configuration::get('PS_OS_PAYMENT'), $_POST["amount"], $nochex->displayName,$responses,$extras, $_REQUEST["cIY"],false, $customer->secure_key);

	//--- The response from APC is stored in this variable. ---//
	$responseAuthorisedMessage= "APC Response.... Order ID: " . $_POST["order_id"] . "... PS_OS_Payment: ". _PS_OS_PAYMENT_ . ". Amount: ". $_POST["amount"]. ". Display name: ". $nochex->displayName. ". CurrencyID: ". $currency->id;
	//--- The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt ---//
    $nochexDebug->writeDebug($responseAuthorisedMessage);
	/** SOME SAMPLE OPTIONS OF WHAT YOU CAN DO HERE **/
	// mysql_query ("insert into received (invoice_id, amount, method, whenpaid) values ({$_POST["order_id"]}, {$_POST["amount"]}, 'nochex', now())");
} 
else {

//--- Creates a new instances of the nochex class and gets the variable for the currency ---//
$nochex = new nochex();
$currency = new Currency($cookie->id_currency);
//--- Sends the data to the database.  ---//
//$nochex->validateOrder($_POST["order_id"], _PS_OS_ERROR_, $_POST["amount"], $nochex->displayName, NULL, NULL, $currency->id);
$extras = array("transaction_id" => $transaction_id);
$custSecure = $_POST["custom"];

$nochex->validateOrder($_POST["order_id"], Configuration::get('PS_OS_ERROR'), $_POST["amount"], $nochex->displayName,$responses,$extras, $_REQUEST["cIY"], $custSecure);

//--- The response from APC is stored in this variable. ---//
$responseUnAuthorisedMessage= "APC Response.... Order ID: " . $_POST["order_id"] . "... PS_OS_Payment: ". _PS_OS_PAYMENT_ . "... Amount: ". $_POST["amount"]. "... Display name: ". $nochex->displayName. "... CurrencyID: ". $currency->id;
//--- The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt ---//
$nochexDebug->writeDebug($responseUnAuthorisedMessage);

} 

	
?>
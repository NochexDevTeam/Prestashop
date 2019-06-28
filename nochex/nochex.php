<?php
/*
Plugin Name: Nochex Payment Gateway for Prestashop
Description: Accept Nochex Payments, orders are updated using APC.
Version: 2.0
License: GPL2
*/
class nochex extends PaymentModule
{	
	private $_html = '';
	private $_postErrors = array();

	public  $details;
	public  $owner;
	public	$address;

	public function __construct()
	{
		$this->name = 'nochex';
		$this->tab = 'payments_gateways';
		$this->author = 'Nochex';
		$this->version = '2.1';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';		
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
		
		/*--- This array gets all of the configuration information from the Configuration file/table in the database. ---*/
		$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
		if (isset($config['NOCHEX_APC_EMAIL']))
			$this->email = $config['NOCHEX_APC_EMAIL'];
		if (isset($config['NOCHEX_APC_TESTMODE']))
			$this->test_mode = $config['NOCHEX_APC_TESTMODE'];
		if (isset($config['NOCHEX_APC_HIDEDETAILS']))
			$this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
		if (isset($config['NOCHEX_APC_DEBUG']))
			$this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
		if (isset($config['NOCHEX_APC_XMLCOLLECTION']))
			$this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
		if (isset($config['NOCHEX_APC_POSTAGE']))
			$this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];		
		if (isset($config['NOCHEX_APC_CALLBACK']))
			$this->nochex_callback = $config['NOCHEX_APC_CALLBACK'];		
		parent::__construct(); /* The parent construct is required for translations */

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Nochex APC Module');
		$this->description = $this->l('Accept payments by Nochex');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!isset($this->email))
			$this->warning = $this->l('Account APC Id and Email must be configured in order to use this module correctly');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}
	
	/*--- This function removes the module, and configuration information. ---*/
	public function uninstall()
	{
		if (!Configuration::deleteByName('NOCHEX_APC_EMAIL')
				OR !Configuration::deleteByName('NOCHEX_APC_TESTMODE')
				OR !Configuration::deleteByName('NOCHEX_APC_HIDEDETAILS')
				OR !Configuration::deleteByName('NOCHEX_APC_DEBUG')
				OR !Configuration::deleteByName('NOCHEX_APC_XMLCOLLECTION')
				OR !Configuration::deleteByName('NOCHEX_APC_POSTAGE')				
				OR !Configuration::deleteByName('NOCHEX_APC_CALLBACK')				
				OR !parent::uninstall())
			return false;
		return true;
	}

	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['email']))
				$this->_postErrors[] = $this->l('Account Email Id is required.');
		}
	}
/*--- Once the update settings button has been pressed on the admin/config file, information is posted and updates the database/configuration details. ---*/
	private function _postProcess()
	{	
	// Funtion and variable which writes to nochex_debug.txt
	
		
		if (isset($_POST['btnSubmit']))
		{
		
			$nochex_merchantID = preg_replace('/[^A-Za-z0-9\-]@_$/', '',filter_var($_POST['email'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
			
			if($nochex_merchantID != ""){
			Configuration::updateValue('NOCHEX_APC_EMAIL', $nochex_merchantID);
			Configuration::updateValue('NOCHEX_APC_TESTMODE', $_POST['test_mode']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_HIDEDETAILS', $_POST['hide_details']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_DEBUG', $_POST['nochex_debug']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_XMLCOLLECTION', $_POST['nochex_xmlcollection']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_POSTAGE', $_POST['nochex_postage']); /* value is checked or null, stores the state of the checkbox */			
			Configuration::updateValue('NOCHEX_APC_CALLBACK',$_POST['nochex_callback']); /* value is checked or null, stores the state of the checkbox */	
			}
			// Refreshes the page to show updated controls. 
			header('Location: ' . $_SERVER['HTTP_REFERER'] . '&ud=1');
		}else if(isset($_REQUEST['ud']) == 1){
		$this->_html .= '<div class="conf confirm" style="float: right;padding: 5px;background: lightgoldenrodyellow;font-weight: bold;"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Nochex Module updated').'</div>';
		}
	}
	private function _displayNoChex()
	{
		$this->_html .= '<img src="https://www.nochex.com/logobase-secure-images/logobase-banners/clear-amex-mp.png" height="100px" style="float:left; margin-right:15px;"><br style="clear:both;"/><br style="clear:both;"/><b>'.$this->l('This module allows you to accept payments by Nochex (APC Method).').'</b><br /><br />
		'.$this->l('If the client chooses this payment mode, the order will change its status once a positive confirmation is recieved from nochex server').'<br />
		<br /><br />';
	}

	/*---  Function returns the value to the form, which shows the state of the checkbox ---*/
	private function _validateTestCheckbox()
	{
			$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
			$this->test_mode = $config['NOCHEX_APC_TESTMODE'];

	return $this->test_mode;
	}
	/*---  Function returns the value to the form, which shows the state of the checkbox ---*/
	private function _validateBillCheckbox()
	{
	$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
	$this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
	return $this->hide_details;
	}
	/*---  Function returns the value to the form, which shows the state of the checkbox ---*/
	private function _validateDebugCheckbox()
	{
			$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));	
			$this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
		
	return $this->nochex_debug;
	}
	
	private function _validateXmlcollectionCheckbox()
	{	
	$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
	$this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
	return $this->nochex_xmlcollection;
	}
	private function _validatePostageCheckbox()
	{$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
			$this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
	return $this->nochex_postage;
	}
	private function _validateCallbackCheckbox()	{
	$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));			
	
	$this->nochex_callback = $config['NOCHEX_APC_CALLBACK'];	
	return $this->nochex_callback;	
	}			 	
	/*--- Function, write to a text file ---*/
	// Function that will be called when particular information needs to be written to a nochex_debug file.
	public function writeDebug($DebugData){
	// Calls the configuration information about a control in the module config. 
	$nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
	// If the control nochex_debug has been checked in the module config, then it will use data sent and received in this function which will write to the nochex_debug file
		if ($nochex_debug == "checked"){
		// Receives and stores the Date and Time
		$debug_TimeDate = date("m/d/Y h:i:s a", time());
		// Puts together, Date and Time, as well as information in regards to information that has been received.
		$stringData = "\n Time and Date: " . $debug_TimeDate . "... " . $DebugData ."... ";
		 // Try - Catch in case any errors occur when writing to nochex_debug file.
			try
			{
			// Variable with the name of the debug file.
				$debugging = "../modules/nochex/nochex_debug.txt";
			// variable which will open the nochex_debug file, or if it cannot open then an error message will be made.
				$f = fopen($debugging, 'a') or die("File can't open");
			// Open and write data to the nochex_debug file.
			$ret = fwrite($f, $stringData);
			// Incase there is no data being shown or written then an error will be produced.
			if ($ret === false)
			die("Fwrite failed");
			
				// Closes the open file.
				fclose($f)or die("File not close");
			} 
			//If a problem or something doesn't work, then the catch will produce an email which will send an error message.
			catch(Exception $e)
			{
			mail($this->email, "Debug Check Error Message", $e->getMessage());
			}
		}
	}/*---  Function shows the display form for the admin/config form. ---*/
	private function _displayForm()
	{
			/*--- Calls the function to return the value of the checkbox ---*/
		$validateTestCheck = $this->_validateTestCheckbox();
		$validateBillCheck = $this->_validateBillCheckbox();
		$validateDebugCheck = $this->_validateDebugCheckbox();
		$validateXmlcollectionCheck = $this->_validateXmlcollectionCheckbox();
		$validatePostageCheck = $this->_validatePostageCheckbox();		
		$validateCallbackCheck = $this->_validateCallbackCheckbox();
		
		/*--- Form parts that are added in the Configuration file of the nochex module. ---*/
		$this->_html .=
		'<form action="'.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Account details').'</legend>
				<table border="0" width="1250" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please specify your Nochex account details').'.<br /><br /></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Nochex Merchant ID / Email Address').'</td><td><input type="text" name="email" value="'.htmlentities(Tools::getValue('email', $this->email), ENT_COMPAT, 'UTF-8').'" style="width: 250px;" /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Nochex Merchant ID / Email Address, This is your Nochex Merchant ID, e.g. test@test.com or one that has been created: e.g. test</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Test Mode').'</td><td><input type="checkbox" name="test_mode" value="checked" '. htmlentities(Tools::getValue('test_mode', $validateTestCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Test Mode, If the Test mode option has been selected, the system will be in test mode. Note (leave unchecked for Live transactions.) </p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Hide Billing Details').'</td><td><input type="checkbox" name="hide_details" value="checked" '. htmlentities(Tools::getValue('hide_details', $validateBillCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Hide Billing Details, If the Hide Billing Details option has been checked then billing details will be hidden, Leave unchecked if you want customers to see billing details.</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Debug').'</td><td><input type="checkbox" name="nochex_debug" value="checked" '. htmlentities(Tools::getValue('nochex_debug', $validateDebugCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Debug, If the Debug option has been selected, details of the module will be saved to a file. nochex_debug.txt which can be found in the nochex module which can be found somewhere like: www.test.com/prestashop/modules/nochex/nochex_debug.txt, leave unchecked if you dont want to record data about the system.</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Detailed Product Information').'</td><td><input type="checkbox" name="nochex_xmlcollection" value="checked" '.  htmlentities(Tools::getValue('nochex_xmlcollection', $validateXmlcollectionCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">Enable this option to display ordered products in a table-structured format on your payment page</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Postage').'</td><td><input type="checkbox" name="nochex_postage" value="checked" '. htmlentities(Tools::getValue('nochex_postage', $validatePostageCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">Enable this option to view postage separately from the total amount on your Nochex payment page</p></td></tr>		
					<tr><td width="300" style="height: 35px;">'.$this->l('Callback System').'</td><td><input type="checkbox" name="nochex_callback" value="checked" '. htmlentities(Tools::getValue('nochex_callback', $validateCallbackCheck), ENT_COMPAT, 'UTF-8')  .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">Enable this option to use our callback system<br/><span style="color:red">Note: you will need to contact your Support Manager or raise a support ticket to enable this on your Nochex Account.</span></p></td></tr>				
					<tr><td></td><td><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayNoChex();
		$this->_displayForm();

		return $this->_html;
	}

	public function hookPayment($params)
	{
		global $smarty,$cart, $currency;
		
		$defaultCurrency = Configuration::get('PS_CURRENCY_DEFAULT');
		
		//Convert Currency to Pounds
		$currency = new Currency((int)$cart->id_currency);
		$currencyGBP = new Currency((int)$defaultCurrency);		
		
		$c_rate = (is_array($currency) ? $currency['conversion_rate'] : $currency->conversion_rate);
		
		$customer = new Customer(intval($params['cart']->id_customer));
		
		//--- get the delivery address
		$del_add = new Address(intval($params['cart']->id_address_delivery));
        $del_add_fields = $del_add->getFields();

		/*--- Gets the configuration details, which have been stored from the nochex module config form  ---*/
		$apc_email = Configuration::get('NOCHEX_APC_EMAIL');
		$test_mode = Configuration::get('NOCHEX_APC_TESTMODE');
		$hide_details = Configuration::get('NOCHEX_APC_HIDEDETAILS');
		$nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
		$nochex_xmlcollection = Configuration::get('NOCHEX_APC_XMLCOLLECTION');
		$nochex_postage = Configuration::get('NOCHEX_APC_POSTAGE');				
		$nochex_callback = Configuration::get('NOCHEX_APC_CALLBACK');
		
		if($nochex_postage == "checked"){
		
		$totalAmount = number_format(Tools::convertPriceFull($cart->getOrderTotal(true, 3),$currency , $currencyGBP), 2, '.', '');
		$totalShipping = number_format(Tools::convertPriceFull($cart->getOrderTotal(true, Cart::ONLY_SHIPPING),$currency , $currencyGBP), 2, '.', '');
		
		$totalAmount = number_format($totalAmount - $totalShipping, 2, '.', ''); 
		
		}else{
		
		$totalShipping = "";
		$totalAmount =  number_format(Tools::convertPriceFull($cart->getOrderTotal(true, 3),$currency , $currencyGBP), 2, '.', '');
		
		}
		
		if($nochex_xmlcollection == "checked"){
		
		//--- get the product details  
		$productDetails = $cart->getProducts();
		$item_collection = "<items>";
		

		//--- Loops through and stores each product that has been ordered in the $prodDet variable.
		foreach($productDetails as $details_product)
		{
		
		$item_collection .= "<item><id>". $details_product['id_product'] . "</id><name>" . $details_product['name'] . "</name><description>".$details_product['description_short']."</description><quantity>" . $details_product['quantity']  . "</quantity><price>" .  number_format(Tools::convertPriceFull($details_product['total_wt'],$currency , $currencyGBP), 2, '.', '')  . "</price></item>";
		
		}
		$item_collection .= "</items>";
		
		$prodDet = "Order created for: " . intval($params['cart']->id);
		}else{
		
		$item_collection = "";
		
		//--- get the product details  
		$productDetails = $cart->getProducts();
		$prodDet = "";
		
		//--- Loops through and stores each product that has been ordered in the $prodDet variable.
		foreach($productDetails as $details_product)
		{
		
		$prodDet .= "Product ID: ". $details_product['id_product'] . ", Product Name: " . $details_product['name'] . ", Quantity: " . $details_product['quantity']  . ", Amount: &#163 " .  number_format(Tools::convertPriceFull($details_product['total_wt'],$currency , $currencyGBP), 2, '.', '')  . ". ";
		
		}
		$prodDet .= " ";
		
		}
		
			// Funtion and variable which writes to nochex_debug.txt
		$submit_Config = 'Configuration Details... NOCHEX_APC_EMAIL: ' . $apc_email . '. NOCHEX_APC_TESTMODE: '. $test_mode .'. NOCHEX_APC_HIDEDETAILS: '.$hide_details.'. NOCHEX_APC_DEBUG: '.$nochex_debug;
		$this->writeDebug($submit_Config);	
	
		/*--- If the hide details variable has been saved as checked, then the billing details will be hidden. ---*/
		if ($hide_details == "checked"){
		
		$hideBilling = 1;
		
		}else{
		
		$hideBilling = 0;
		} 
		
		// get the billing address and details
		$bill_add = new Address(intval($params['cart']->id_address_invoice));
        $bill_add_fields = $bill_add->getFields();
	 		
		//// Funtion and variable which writes to nochex_debug.txt
		$submitOrder_Details = 'Order Details... Merchant_id: ' . $apc_email . '. amount: ' . number_format(round($totalAmount, 2), 2, '.', '') . '. order_id: ' . intval($params['cart']->id);
		$this->writeDebug($submitOrder_Details);
		//// Funtion and variable which writes to nochex_debug.txt
		$submitOrder_Contents = 'Order Contents... Description: ' . $prodDet;
		$this->writeDebug($submitOrder_Contents);
		//// Funtion and variable which writes to nochex_debug.txt
		$submitOrder_Billing = 'Billing Details... billing_fullname: ' . $bill_add_fields['firstname'] . ', ' . $bill_add_fields['lastname'] . '. billing_address: ' . $bill_add_fields['address1'] . '. billing_postcode: ' . $bill_add_fields['postcode'];
		$this->writeDebug($submitOrder_Billing);
		//// Funtion and variable which writes to nochex_debug.txt
		$submitOrder_Delivery = 'Delivery Details... delivery_fullname: ' . $del_add_fields['firstname'] . ', ' . $del_add_fields['lastname'] . '. delivery_address: ' . $del_add_fields['address1'] . '. delivery_postcode: ' . $del_add_fields['postcode'];
		$this->writeDebug($submitOrder_Delivery);
		//// Funtion and variable which writes to nochex_debug.txt
		$submitOrder_Contact = 'Contact Information... customer_phone_number: ' . $bill_add_fields['phone_mobile'] . '. email_address: ' . $customer->email;	
		$this->writeDebug($submitOrder_Contact);
		 
		if ($nochex_callback == "checked"){
			$enabledCB = "Yes"; 
		}else{
			$enabledCB = "No"; 
		}
		
		/*---  Gets the variables which will be retrieved from the order process form, and the variables will be sent to Nochex.tpl, as the customer gets to the final stage of the order and about to press pay with Nochex.  number_format(round($amo, 2), 2, '.', '')---*/
		 $smarty->assign(array(
			'merchant_id' => $apc_email,
			'amount' => $totalAmount,
			'order_id' => intval($params['cart']->id),
			'description' => $prodDet,
			'postage' => $totalShipping,
			'xml_item_collection' => $item_collection,
            'billing_fullname' => $bill_add_fields['firstname'].', '.$bill_add_fields['lastname'],
			'billing_address' => $bill_add_fields['address1'],
			'billing_city' => $bill_add_fields['city'],
			'billing_postcode' => $bill_add_fields['postcode'],
            'delivery_fullname' => $del_add_fields['firstname'] . ', '. $del_add_fields['lastname'],
			'delivery_address' => $del_add_fields['address1'],
			'delivery_city' => $del_add_fields['city'],
			'delivery_postcode' => $del_add_fields['postcode'],
			'customer_phone_number' => $bill_add_fields['phone_mobile'],
			'hide_billing_details' => $hideBilling,
			'optional_1' => $params['cart']->secure_key,
			'email_address' => $customer->email,
			'responderurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nochex/callback_validation.php?cIY='.(int)$defaultCurrency,
			'cancelurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php',
			'successurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nochex/success.php?id_cart='.$cart->id.'',			'optional_2' => $enabledCB,			
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		/*--- If the test_mode variable has been saved as checked, then test data will be attached to the form which will put their page in test mode  ---*/
		if($test_mode=="checked")
		{
		/*--- Attached variable which will send test information  ---*/
		 $smarty->assign(array(
				'teststatus' => true,
				'test_transaction' => '100',
				'test_success_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nochex/success.php?id_cart='.$cart->id.''));
				
		// Funtion and variable which writes to nochex_debug.txt
		$test_mode_Info = 'test_status = true';
		$this->writeDebug($test_mode_Info);
		
		}
		else
		{
		/*--- else test mode variable hasn't been checked then data will be sent to the form in live mode. ---*/
		 $smarty->assign(array('teststatus' => false));
		 
		 // Funtion and variable which writes to nochex_debug.txt
		 $test_mode_Info = 'test_status = false';
		 $this->writeDebug($test_mode_Info);
		}
		return $this->display(__FILE__, 'nochex.tpl');
	}

}

?>

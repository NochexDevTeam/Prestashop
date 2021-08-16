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
*  Version: 2.1.1
*  License: GPL2
*/

class Nochex extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();
    public function __construct()
    {
        $this->name = 'nochex';
        $this->tab = 'payments_gateways';
        $this->author = 'Nochex';
        $this->version = '2.1.1';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
        $config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
        if (isset($config['NOCHEX_APC_EMAIL'])) {
            $this->email = $config['NOCHEX_APC_EMAIL'];
        }
        if (isset($config['NOCHEX_APC_TESTMODE'])) {
            $this->test_mode = $config['NOCHEX_APC_TESTMODE'];
        }
        if (isset($config['NOCHEX_APC_HIDEDETAILS'])) {
            $this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
        }
        if (isset($config['NOCHEX_APC_DEBUG'])) {
            $this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
        }
        if (isset($config['NOCHEX_APC_XMLCOLLECTION'])) {
            $this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
        }
        if (isset($config['NOCHEX_APC_POSTAGE'])) {
            $this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
        }
        if (isset($config['NOCHEX_APC_CALLBACK'])) {
            $this->nochex_callback = $config['NOCHEX_APC_CALLBACK'];
        }
        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Nochex APC Module');
        $this->description = $this->l('Accept payments by Nochex');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        if (!isset($this->email)) {
            $this->warning = $this->l('Account APC Id and Email must be configured in order to use this module correctly');
        }
    }

    public function install()
    {
        if (!parent::install() or !$this->registerHook('payment') or !$this->registerHook('paymentReturn')) {
            return false;
        } else {
            return true;
        }
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('NOCHEX_APC_EMAIL')
        or !Configuration::deleteByName('NOCHEX_APC_TESTMODE')
        or !Configuration::deleteByName('NOCHEX_APC_HIDEDETAILS')
        or !Configuration::deleteByName('NOCHEX_APC_DEBUG')
        or !Configuration::deleteByName('NOCHEX_APC_XMLCOLLECTION')
        or !Configuration::deleteByName('NOCHEX_APC_POSTAGE')
        or !Configuration::deleteByName('NOCHEX_APC_CALLBACK')
        or !parent::uninstall() ) {
            return false;
        } else {
            return true;
        }
    }

    private function _postValidation()
    {
    }

    private function _postProcess()
    {
        if (Tools::getValue('btnSubmit')) {
            $nochex_merchantID = preg_replace('/[^A-Za-z0-9\-]@_$/', '', filter_var(Tools::getValue('email'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
            if ($nochex_merchantID != "") {
                Configuration::updateValue('NOCHEX_APC_EMAIL', $nochex_merchantID);
                Configuration::updateValue('NOCHEX_APC_TESTMODE', Tools::getValue('test_mode'));
                Configuration::updateValue('NOCHEX_APC_HIDEDETAILS', Tools::getValue('hide_details'));
                Configuration::updateValue('NOCHEX_APC_DEBUG', Tools::getValue('nochex_debug'));
                Configuration::updateValue('NOCHEX_APC_XMLCOLLECTION', Tools::getValue('nochex_xmlcollection'));
                Configuration::updateValue('NOCHEX_APC_POSTAGE', Tools::getValue('nochex_postage'));
                Configuration::updateValue('NOCHEX_APC_CALLBACK', Tools::getValue('nochex_callback'));
            }
        } else if (Tools::getValue('ud') == 1) {
                $this->_html .= '<div class="conf confirm" style="float: right;padding: 5px;background: lightgoldenrodyellow;font-weight: bold;"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Nochex Module updated').'</div>';
        }
    }
    private function _displayNoChex()
    {
        $this->_html .= '<img src="https://www.nochex.com/logobase-secure-images/logobase-banners/clear.png" height="100px" style="float:left; margin-right:15px;"><br style="clear:both;"/><br style="clear:both;"/><b>'.$this->l('This module allows you to accept payments by Nochex (APC Method).').'</b><br /><br />
'.$this->l('ifthe client chooses this payment mode, the order will change its status once a positive confirmation is recieved from nochex server').'<br /><br /><br />';
    }
    private function _validateTestCheckbox()
    {
        $config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
        $this->test_mode = $config['NOCHEX_APC_TESTMODE'];
        return $this->test_mode;
    }
    private function _validateBillCheckbox()
    {
        $config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
        $this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
        return $this->hide_details;
    }
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
    {
        $config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
        $this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
        return $this->nochex_postage;
    }
    private function _validateCallbackCheckbox()
    {
        $config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_APC_CALLBACK'));
        $this->nochex_callback = $config['NOCHEX_APC_CALLBACK'];
        return $this->nochex_callback;
    }
    public function writeDebug($DebugData)
    {
        $nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
        if ($nochex_debug == "checked") {
            $debug_TimeDate = date("m/d/Y h:i:s a", time());
            $stringData = "\n Time and Date: " . $debug_TimeDate . "... " . $DebugData ."... ";
            $debugging = "../modules/nochex/nochex_debug.txt";
            $f = fopen($debugging, 'a') or die("File can't open");
            $ret = fwrite($f, $stringData);
            if ($ret === false) {
                die("Fwrite failed");
            }
            fclose($f) or die("File not close");
        }
    }

    private function _displayForm()
    {
        $validateTestCheck = $this->_validateTestCheckbox();
        $validateBillCheck = $this->_validateBillCheckbox();
        $validateDebugCheck = $this->_validateDebugCheckbox();
        $validateXmlcollectionCheck = $this->_validateXmlcollectionCheckbox();
        $validatePostageCheck = $this->_validatePostageCheckbox();
        $validateCallbackCheck = $this->_validateCallbackCheckbox();
        $this->_html .= '<form action="'.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').$_SERVER['REQUEST_URI'].'" method="post">
        <fieldset>
        <legend><img src="../img/admin/contact.gif" />'.$this->l('Account details').'</legend>
        <table border="0" width="1250" cellpadding="0" cellspacing="0" id="form">
        <tr><td colspan="2">'.$this->l('Please specify your Nochex account details').'.<br /><br /></td></tr>
        <tr><td width="300" style="height: 35px;">'.$this->l('Nochex Merchant ID / Email Address').'</td><td><input type="text" name="email" value="'.htmlentities(Tools::getValue('email', $this->email), ENT_COMPAT, 'UTF-8').'" style="width: 250px;" /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Nochex Merchant ID / Email Address, This is your Nochex Merchant ID, e.g. test@test.com or one that has been created: e.g. test</p></td></tr>
        <tr><td width="300" style="height: 35px;">'.$this->l('Test Mode').'</td><td><input type="checkbox" name="test_mode" value="checked" '. htmlentities(Tools::getValue('test_mode', $validateTestCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Test Mode, ifthe Test mode option has been selected, the system will be in test mode. Note (leave unchecked for Live transactions.) </p></td></tr>
        <tr><td width="300" style="height: 35px;">'.$this->l('Hide Billing Details').'</td><td><input type="checkbox" name="hide_details" value="checked" '. htmlentities(Tools::getValue('hide_details', $validateBillCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Hide Billing Details, ifthe Hide Billing Details option has been checked then billing details will be hidden, Leave unchecked ifyou want customers to see billing details.</p></td></tr>
        <tr><td width="300" style="height: 35px;">'.$this->l('Debug').'</td><td><input type="checkbox" name="nochex_debug" value="checked" '. htmlentities(Tools::getValue('nochex_debug', $validateDebugCheck), ENT_COMPAT, 'UTF-8') .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Debug, ifthe Debug option has been selected, details of the module will be saved to a file. nochex_debug.txt which can be found in the nochex module which can be found somewhere like: www.test.com/prestashop/modules/nochex/nochex_debug.txt, leave unchecked ifyou dont want to record data about the system.</p></td></tr>
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
		
		if (isset($_POST["tab"]) == "AdminModules"){
        if (!empty($_POST["email"])) {
                $this->_postValidation();
            if (!sizeof($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= '<div class="alert error">'. $err .'</div>';
                }
            }
        } /*else {*/
            $this->_html .= '<br />';
            $this->_displayNoChex();
            $this->_displayForm();
            return $this->_html;
        /*}*/
		}
		
    }

    public function hookPayment($params)
    {
        $cart = $params['cart'];
        $defaultCurrency = Configuration::get('PS_CURRENCY_DEFAULT');
        $this->currency = new Currency((int)$cart->id_currency);
        $this->currencyGBP = new Currency((int)$defaultCurrency);
        $customer = new Customer($params['cart']->id_customer);
        $del_add = new Address($params['cart']->id_address_delivery);
        $del_add_fields = $del_add->getFields();
        $apc_email = Configuration::get('NOCHEX_APC_EMAIL');
        $test_mode = Configuration::get('NOCHEX_APC_TESTMODE');
        $hide_details = Configuration::get('NOCHEX_APC_HIDEDETAILS');
        $nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
        $nochex_xmlcollection = Configuration::get('NOCHEX_APC_XMLCOLLECTION');
        $nochex_postage = Configuration::get('NOCHEX_APC_POSTAGE');
        $nochex_callback = Configuration::get('NOCHEX_APC_CALLBACK');
        if ($nochex_postage == "checked") {
            $totalAmount = number_format(Tools::convertPriceFull($cart->getorderTotal(true, 3), $this->currency, $this->currencyGBP), 2, '.', '');
            $totalShipping = number_format(Tools::convertPriceFull($cart->getorderTotal(true, Cart::ONLY_SHIPPING), $this->currency, $this->currencyGBP), 2, '.', '');
            $totalAmount = number_format($totalAmount - $totalShipping, 2, '.', '');
        } else {
            $totalShipping = "";
            $totalAmount = number_format(Tools::convertPriceFull($cart->getorderTotal(true, 3), $this->currency, $this->currencyGBP), 2, '.', '');
        }
        if ($nochex_xmlcollection == "checked") {
            $productDetails = $cart->getProducts();
            $item_collection = "<items>";
            foreach ($productDetails as $details_product) {
                $item_collection .= "<item><id>". $details_product['id_product'] . "</id><name>" . $details_product['name'] . "</name><description>".$details_product['description_short']."</description><quantity>" . $details_product['quantity'] . "</quantity><price>" . number_format(Tools::convertPriceFull($details_product['total_wt'], $this->currency, $this->currencyGBP), 2, '.', '') . "</price></item>";
            }
            $item_collection .= "</items>";
            $prodDet = "order created for: " . $params['cart']->id;
        } else {
            $item_collection = "";
            $productDetails = $cart->getProducts();
            $prodDet = "";
            foreach ($productDetails as $details_product) {
                $prodDet .= "Product ID: ". $details_product['id_product'] . ", Product Name: " . $details_product['name'] . ", Quantity: " . $details_product['quantity']  . ", Amount: " . number_format(Tools::convertPriceFull($details_product['total_wt'], $this->currency, $this->currencyGBP), 2, '.', '') . " GBP. ";
            }
            $prodDet .= " ";
        }
        $submit_Config = 'Configuration Details... NOCHEX_APC_EMAIL: ' . $apc_email . '. NOCHEX_APC_TESTMODE: '. $test_mode .'. NOCHEX_APC_HIDEDETAILS: '.$hide_details.'. NOCHEX_APC_DEBUG: '.$nochex_debug;
        $this->writeDebug($submit_Config);
        if ($hide_details == "checked") {
            $hideBilling = 1;
        } else {
            $hideBilling = 0;
        }
        $bill_add = new Address($params['cart']->id_address_invoice);
        $bill_add_fields = $bill_add->getFields();
        $submitorder_Details = 'order Details... Merchant_id: ' . $apc_email . '. amount: ' . number_format(round($totalAmount, 2), 2, '.', '') . '. order_id: ' . $params['cart']->id;
        $this->writeDebug($submitorder_Details);
        $submitorder_Contents = 'order Contents... Description: ' . $prodDet;
        $this->writeDebug($submitorder_Contents);
        $submitorder_Billing = 'Billing Details... billing_fullname: ' . $bill_add_fields['firstname'] . ', ' . $bill_add_fields['lastname'] . '. billing_address: ' . $bill_add_fields['address1'] . '. billing_postcode: ' . $bill_add_fields['postcode'];
        $this->writeDebug($submitorder_Billing);
        $submitorder_Delivery = 'Delivery Details... delivery_fullname: ' . $del_add_fields['firstname'] . ', ' . $del_add_fields['lastname'] . '. delivery_address: ' . $del_add_fields['address1'] . '. delivery_postcode: ' . $del_add_fields['postcode'];
        $this->writeDebug($submitorder_Delivery);
		
		        
		if($bill_add_fields['phone_mobile'] == "") {
		$contact_number = $bill_add_fields['phone'];
		} else {
		$contact_number = $bill_add_fields['phone_mobile'];
		}
		
        $submitorder_Contact = 'Contact Information... customer_phone_number: ' . $contact . '. email_address: ' . $customer->email;
        $this->writeDebug($submitorder_Contact);

		
        $this->smarty->assign(array(
        'merchant_id' => $apc_email,
        'amount' => $totalAmount,
        'order_id' => $params['cart']->id,
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
        'customer_phone_number' => $contact_number,
        'hide_billing_details' => $hideBilling,
        'optional_1' => $params['cart']->secure_key,
        'email_address' => $customer->email,
        'responderurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nochex/callback_validation.php?cIY='.(int)$defaultCurrency,
        'cancelurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php',
        'successurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nochex/success.php?id_cart='.$cart->id.'',
        'optional_2' => "Enabled",
        'this_path' => $this->_path,
        'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        if ($test_mode=="checked") {
            $this->smarty->assign(array(
            'teststatus' => true,
            'test_transaction' => '100',
            'test_success_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/nochex/success.php?id_cart='.$cart->id.''));
            $test_mode_Info = 'test_status = true';
            $this->writeDebug($test_mode_Info);
        } else {
            $this->smarty->assign(array('teststatus' => false));
            $test_mode_Info = 'test_status = false';
            $this->writeDebug($test_mode_Info);
        }
        return $this->display(__FILE__, '/views/templates/front/nochex.tpl');
    }
}

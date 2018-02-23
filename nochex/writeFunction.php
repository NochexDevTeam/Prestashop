<?php
/*
Plugin Name: Nochex Payment Gateway for Prestashop
Description: Accept Nochex Payments, orders are updated using APC.
Version: 1.0
License: GPL2
*/
class nochexDebug{
// Function that will be called when particular information needs to be written to a nochex_debug file.
	public function writeDebug($DebugData){
	// Calls the configuration information about a control in the module config. 
	$nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
	$apc_email = Configuration::get('NOCHEX_APC_EMAIL');
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
				$debugging = "nochex_debug.txt";
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
			mail($apc_email, "Debug Check Error Message", $e->getMessage());
			}
		}
	}
	
}
?>
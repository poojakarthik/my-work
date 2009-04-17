<?php

// Flex Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

$objRPCSalesPortal	= new RPC_Client_SalesPortal('https://sp.telcoblue.yellowbilling.com.au/sales/format:json/portal/');

// Login
$objResponse	= $objRPCSalesPortal->call('jsonLogin', array('rich', 'rich'), 'login');
if ($objResponse !== true)
{
	throw new Exception("Login failed: \n\n".print_r($objResponse, true));
}

$objResponse	= $objRPCSalesPortal->call('loadData', array('Service_Mobile'), 'ProductTypeModule');
print_r($objResponse, true);

// Logout
$objResponse	= $objRPCSalesPortal->call('logout');

?>
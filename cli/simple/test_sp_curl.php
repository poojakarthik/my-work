<?php

require_once('../../lib/classes/Flex.php');
Flex::load();

$strSalesPortalBaseURL	= 'https://sp.telcoblue.yellowbilling.com.au/';

//----------------------------------------------------------------------------//
// Start a Session
//----------------------------------------------------------------------------//
$resSession	= curl_init();
curl_setopt($resSession, CURLOPT_RETURNTRANSFER	, true);
curl_setopt($resSession, CURLOPT_SSL_VERIFYPEER	, false);
curl_setopt($resSession, CURLOPT_COOKIESESSION	, true);
curl_setopt($resSession, CURLOPT_COOKIEFILE		, "/dev/null");	// Stores a cookie in memory, but doesn't retain it

//----------------------------------------------------------------------------//
// Log in

// This section is like calling the code:
//		$mixJSONResponse	= login::jsonLogin('rich', 'rich');
$arrFunctionParameters	= array
						(
							'rich',		// Username
							'rich'		// Password (plain text, not hashed)
						);

$strObject		= 'login';
$strFunction	= 'jsonLogin';
curl_setopt($resSession, CURLOPT_URL			, $strSalesPortalBaseURL."sales/format:json/portal/{$strObject}/{$strFunction}");
curl_setopt($resSession, CURLOPT_POST			, true);
curl_setopt($resSession, CURLOPT_POSTFIELDS		, array('json'=>json_encode($arrFunctionParameters)));

$strResponse		= curl_exec($resSession);
$mixJSONResponse	= json_decode($strResponse);
if ($mixJSONResponse !== true)
{
	throw new Exception("Login failed:\n>>>\n{$strResponse}\n<<<\n".curl_errno($resSession).": ".curl_error($resSession)."\n");
}
CliEcho("Logged In");
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Save Sale
// This section is like calling the code:
//		$mixJSONResponse	= Sale::submit($objSale);

$objSale				= new stdClass();
$objSale->sale_type_id	= 1;

$objSale->sale_account	= new stdClass();
$objSale->sale_account->vendor_id				= (int)$_POST['VendorId'];
$objSale->sale_account->abn						= (int)$_POST['ABN'];
$objSale->sale_account->acn						= (int)$_POST['ACN'];
$objSale->sale_account->address_line_1			= (int)$_POST['VendorId'];
$objSale->sale_account->address_line_2			= (int)$_POST['VendorId'];
$objSale->sale_account->bill_delivery_type_id	= (int)$_POST['VendorId'];
$objSale->sale_account->bill_payment_type_id	= (int)$_POST['VendorId'];
$objSale->sale_account->business_name			= (int)$_POST['VendorId'];
$objSale->sale_account->bill_payment_type_id	= (int)$_POST['VendorId'];
$objSale->sale_account->direct_debit_type_id	= (int)$_POST['VendorId'];
$objSale->sale_account->postcode				= (int)$_POST['VendorId'];
$objSale->sale_account->reference_id			= (int)$_POST['VendorId'];
$objSale->sale_account->state_id				= (int)$_POST['VendorId'];
$objSale->sale_account->suburb					= (int)$_POST['VendorId'];
$objSale->sale_account->trading_name			= (int)$_POST['VendorId'];

$arrFunctionParameters	= array
						(
							$objSale
						);

$strObject		= 'Sale';
$strFunction	= 'submit';
curl_setopt($resSession, CURLOPT_URL			, $strSalesPortalBaseURL."sales/format:json/portal/{$strObject}/{$strFunction}");
curl_setopt($resSession, CURLOPT_POST			, true);
curl_setopt($resSession, CURLOPT_POSTFIELDS		, array('json'=>json_encode($arrFunctionParameters)));

$strResponse		= curl_exec($resSession);
$mixJSONResponse	= json_decode($strResponse);
CliEcho("Request Data:");
CliEcho(print_r($mixJSONResponse, true));
CliEcho();
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Log out
curl_setopt($resSession, CURLOPT_URL, $strSalesPortalBaseURL."sales/portal/logout");
curl_setopt($resSession, CURLOPT_POST			, false);
curl_setopt($resSession, CURLOPT_POSTFIELDS		, null);
curl_exec($resSession);
CliEcho("Logged Out?");
//----------------------------------------------------------------------------//

?>
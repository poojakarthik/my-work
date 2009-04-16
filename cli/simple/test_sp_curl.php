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

//----------------------------------------------------------------------------//
// Log in
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
// Perform action
$arrFunctionParameters	= array
						(
							'Service_Mobile'
						);

$strObject		= 'ProductTypeModule';
$strFunction	= 'loadData';
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
<?php

// Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

// First parameter must be a file to upload
if (isset($argv[1]) && is_readable($argv[1]) && preg_match('/\w{1,8}\.\w{3}/i'))
{
	$sFilePath	= $argv[1];
}
else
{
	throw new Exception("First argument must be a readable file to upload to conforms to the 8.3 filename standard");
}

// Intialise cURL object
$oCURL	= new CURL();

$oCURL->RETURNTRANSFER	= true;	// Execute returns response data rather than TRUE on success
$oCURL->FOLLOWLOCATION	= true;	// Auto-redirects
$oCURL->AUTOREFERER		= true;	// Automatically set Referrer on redirect

// Step 1: Login
$oCURL->URL			= 'https://wholesalebbs.aapt.com.au/signon.asp';
$oCURL->POST		= true;
$oCURL->POSTFIELDS	= _buildPOSTFields(array('Username'=>'telcoblue', 'Password'=>'zbj6v04ls', 'Action'=>'submit', 'VTI-GROUP'=>0));

$sResponse	= $oCURL->execute();

if (stripos($oCURL->EFFECTIVE_URL, 'welcome.asp') === false)
{
	throw new Exception("Login Failed");
}

// Step 2: Upload
$oCURL->URL			= 'https://wholesalebbs.aapt.com.au/upload2.asp?filename='.urlencode(basename($sFilePath));
$oCURL->POST		= true;
$oCURL->POSTFIELDS	= _buildPOSTFields(array('data'=>"@{$sFilePath}"));

$sResponse	= $oCURL->execute();

if (stripos($sResponse, 'UPLOAD SUCCESSFUL') === false)
{
	throw new Exception("Login Failed");
}

// Step 3: Logout? (probably isn't necessary, but why not?)
$oCURL->URL			= 'https://wholesalebbs.aapt.com.au/logoff.asp';
$oCURL->POST		= false;

$oCURL->execute();	// We probably don't need to verify this response

//----------------------------------------------------------------------------//
// UTILITY FUNCTIONS
function _buildPOSTFields($aPostFields)
{
	$aTokens	= array();
	foreach ($aPostFields as $sField=>$mValue)
	{
		$aTokens[$sField]	= urlencode($sField).'='.urlencode($mValue);
	}
	
	return implode('&', $aTokens);
}

?>
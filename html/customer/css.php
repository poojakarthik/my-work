<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// css
//----------------------------------------------------------------------------//
/**
 * css
 *
 * Defines what css file to use for the client app website
 *
 * Defines what css file to use for the client app website
 *
 * @file		css.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// The CSS file URI contains a unique 'version number' (the md5 of the CSS content).
// If the browser is checking to see if has changed, the copy it has MUST be the latest version.
// No point sending it again, so send a 304 (not changed) header instead.
if (array_key_exists('v', $_GET) && array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
{
	header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
	exit;
}

header('Content-type: text/css');
header('Cache-Control: public'); // Set both to confuse browser (causes clash with PHP's own headers) forcing browser to decide
header('Pragma: public');		 // (see above)
header('Last-Modified: '.date('r', time()-10000)); // Some time in the past	
header('Expires: '.date('r', time()+(365*24*60*60))); // About a year from now


// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// ******** AUTHENTICATION ********
if (!Flex::continueSession(Flex::FLEX_ADMIN_SESSION))
{
	// Redirect the user to the login page
	#header("Location: " . Flex::getUrlBase() . "login.php");
	#exit;
}

// Load the Flex framework and application
Flex::load();

// Connect to database
$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);

// Load Style Configuration based on domain name 
$arrFetchCustomerStyleConfiguration = $dbConnection->fetchone("SELECT * FROM `CustomerGroup` WHERE flex_url LIKE \"%$_SERVER[HTTP_HOST]\" LIMIT 1");
DBO()->customer_style_configuration->Array = $arrFetchCustomerStyleConfiguration;

# I couldnt find the style for the URL you are using?
if($arrFetchCustomerStyleConfiguration == "")
{
	$ExternalName = DEFAULT_CUSTOMER_EXTERNAL_NAME;
	$customer_primary_color = DEFAULT_CUSTOMER_PRIMARY_COLOR;
	$customer_secondary_color = DEFAULT_CUSTOMER_SECONDARY_COLOR;
	$customer_logo = DEFAULT_CUSTOMER_LOGO;
	$customer_logo_type = DEFAULT_CUSTOMER_LOGO_TYPE;
	$handle = fopen("./$customer_logo", "rb");
	$customer_logo = stream_get_contents($handle);
	fclose($handle);
}
# I could find something?
if($arrFetchCustomerStyleConfiguration != "")
{
	$arrFetchCustomerStyleConfiguration = DBO()->customer_style_configuration->Array->Value;
	foreach($arrFetchCustomerStyleConfiguration as $mixKey=>$mixVal)
	{
		$$mixKey = $mixVal;
	}
}


$handle = fopen("default.css", "rb");
$customer_css = stream_get_contents($handle);
fclose($handle);

$customer_css = str_replace("#ffbf00","#$customer_primary_color",$customer_css);

echo $customer_css;

#require_once('default.css');

?>

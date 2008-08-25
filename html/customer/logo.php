<?php

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
	$handle = fopen("./img/template/$customer_logo", "rb");
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
header("Content-Type: $customer_logo_type");
echo $customer_logo;
?>
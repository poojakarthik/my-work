<?php

// Get the Flex class...
require_once '../../../../lib/classes/Flex.php';

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
	// Load Default Style...
	$arrFetchCustomerStyleConfiguration = $dbConnection->fetchone("SELECT * FROM `CustomerGroup` WHERE flex_url=\"https://telcoblue.yellowbilling.com.au\" LIMIT 1");
	DBO()->customer_style_configuration->Array = $arrFetchCustomerStyleConfiguration;
}
$arrFetchCustomerStyleConfiguration = DBO()->customer_style_configuration->Array->Value;
#print_r($arrFetchCustomerStyleConfiguration);
#exit;

foreach($arrFetchCustomerStyleConfiguration as $mixKey=>$mixVal)
{
	$$mixKey = $mixVal;
}

header("Content-Type: image/$customer_logo_type");
echo $customer_logo;

?>
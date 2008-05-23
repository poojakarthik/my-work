<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Email list of Accounts with Samples ready
//----------------------------------------------------------------------------//

// load application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// load remote copy
VixenRequire('lib/framework/remote_copy.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Get Command-line Params
switch (strtoupper(trim($argv[1])))
{
	case 'SILVER':
		$strMode	= "Silver";
		break;
	
	case 'BRONZE':
		$strMode	= "Bronze";
		break;
	
	case 'GOLD':
		$strMode	= "Gold";
		break;
	
	default:
		Debug("No mode specified! ('bronze', 'silver' or 'gold')");
		die;
}

Debug("[ GENERATING ".strtoupper($strMode)." SAMPLES LIST ]");

// Get list of Accounts
$arrAccounts		= Array();
$selSampleAccounts	= new StatementSelect("Account JOIN InvoiceTemp ON Account.Id = InvoiceTemp.Account", "Account.Id, Account.BusinessName", "Account.Sample != 0");
$selSampleAccounts->Execute();
while ($arrAccount = $selSampleAccounts->Fetch())
{
	$arrAccounts[]	= "<a href='https://telcoblue.yellowbilling.com.au/management/flex.php/Account/Overview/?Account.Id={$arrAccount['Id']}'>{$arrAccount['Id']}: {$arrAccount['BusinessName']}</a>";
}

$strTo		= "rich@voiptelsystems.com.au, msergeant@yellowbilling.com.au";
$strContent	= implode("<br/>\n", $arrAccounts);
SendEmail($strTo, date("F", strtotime("-2 days", time()))." $strMode Samples", date("F", strtotime("-2 days", time()))." $strMode Samples<br>\n<br>\n".$strContent);

?>
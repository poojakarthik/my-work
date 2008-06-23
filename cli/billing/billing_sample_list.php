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

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Get Command-line Params
$bolInternal	= FALSE;
switch (strtoupper(trim($argv[1])))
{
	case 'SILVER':
		$strMode		= "Silver";
		break;
	
	case 'BRONZE':
		$strMode		= "Bronze";
		break;
	
	case 'GOLD':
		$strMode		= "Gold";
		break;
		
	case 'INTERNALINITIAL':
		$bolInternal	= TRUE;
		$strMode		= "Initial Internal";
		break;
		
	case 'INTERNALFINAL':
		$bolInternal	= TRUE;
		$strMode		= "Final Internal";
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

$strTo		= "turdminator@hotmail.com, rich@voiptelsystems.com.au, msergeant@yellowbilling.com.au";
$strContent	= ($bolInternal) ? "NOTE: THIS IS AN INTERNAL SAMPLE RUN -- DO NOT FORWARD TO CUSTOMERS <br/>\n<br/>\n" : "";
$strContent	.= implode("<br/>\n", $arrAccounts);
SendEmail($strTo, date("F", strtotime("-2 days", time()))." $strMode Samples", date("F", strtotime("-2 days", time()))." $strMode Samples<br>\n<br>\n".$strContent);

?>

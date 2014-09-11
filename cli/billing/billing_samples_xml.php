<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
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

Debug("[ GENERATING ".strtoupper($strMode)." SAMPLES ]");

// Get list of Accounts
$arrAccounts		= Array();
$selSampleAccounts	= new StatementSelect("Account JOIN InvoiceTemp ON Account.Id = InvoiceTemp.Account", "Account.Id", "Account.Sample != 0");
$selSampleAccounts->Execute();
while ($arrAccount = $selSampleAccounts->Fetch())
{
	$arrAccounts[]	= $arrAccount['Id'];
}

// Get InvoiceRun from InvoiceTemp table
$selInvoiceRun	= new StatementSelect("InvoiceTemp", "InvoiceRun", "1", NULL, 1);
$selInvoiceRun->Execute();
if (!($arrInvoiceRun = $selInvoiceRun->Fetch()))
{
	// No Temporary InvoiceRun
	CliEcho("There is no temporary Invoice Run!");
	die;
}
$strInvoiceRun	= $arrInvoiceRun['InvoiceRun'];

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::{$strMode}Samples Started @ $strDateTime", "viXen Billing Started @ $strDateTime");

// Try to change to XML directory
$strWorkingDir		= getcwd();
$strFullDirectory	= INVOICE_XML_PATH.$strInvoiceRun.'/';
$strSampleDirectory	= $strFullDirectory."samples/";
@mkdir($strSampleDirectory, 0777, TRUE);
if (chdir("../../cli"))
{
	// Create Sample PDFs
	foreach ($arrAccounts as $intAccount)
	{
		CliEcho(" + Generating PDF for $intAccount...\t\t\t", FALSE);
		$strReturn	= shell_exec("php pdf.php -x{$strFullDirectory}$intAccount.xml -f$strSampleDirectory -oEMAIL");
		if (stripos($strReturn, 'Completed Successfully'))
		{
			CliEcho("[   OK   ]");
		}
		else
		{
			CliEcho("[ FAILED ]");
		}
	}
	
	// ZIP samples, copy to public location
	chdir($strSampleDirectory);
	$strBaseZipname = date("F", strtotime("-2 days", time()))." $strMode Samples";
	$strZipname		= $strBaseZipname.'.zip';
	$intVersion		= 1;
	while (file_exists($strZipname))
	{
		$intVersion++;
		$strZipname	= $strBaseZipname." MK$intVersion.zip";
	}
	echo shell_exec("zip -qj '$strZipname' *.pdf");
	
	$strCustomerName	= str_replace('working', '', $GLOBALS['**arrCustomerConfig']['Customer']);
	$strDir				= date("Y/m/", strtotime("-2 days", time()));
	
	$rcpRemoteCopySamples = new RemoteCopySSH("10.50.50.131", "rdavis", "password");
	if (is_string($mixResult = $rcpRemoteCopySamples->Connect("10.50.50.131", "rdavis", "password")))
	{
		echo "$mixResult \n";
	}
	$rcpRemoteCopySamples->Copy($strDownloadDir.$strZipname, "/data/www/samples.yellowbilling.com.au/html/$strCustomerName/$strDir", RCOPY_BACKUP);
	$rcpRemoteCopySamples->Disconnect();
}

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::ReprintSamples Ended @ $strDateTime", "viXen Billing Ended @ $strDateTime");

// finished
CliEcho("\n\n-- End of Billing --");
?>
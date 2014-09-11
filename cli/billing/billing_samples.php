<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
//----------------------------------------------------------------------------//

echo "<pre>";

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
$selSampleAccounts	= new StatementSelect("Account", "Id", "Sample != 0");
$selSampleAccounts->Execute();
while ($arrAccount = $selSampleAccounts->Fetch())
{
	$arrAccounts[]	= $arrAccount['Id'];
}

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::{$strMode}Samples Started @ $strDateTime", "viXen Billing Started @ $strDateTime");

// reprint
$bolResponse = $appBilling->PrintSampleAccounts($arrAccounts);

$appBilling->FinaliseReport();
ob_end_clean();

// Remote Copy
$strFilename	= "reprint".date("Y-m-d").".vbf";
$strLocalPath	= FILES_BASE_PATH."bill_output/";
echo "\nCopying '$strFilename' to BillPrint...\n";
ob_flush();
$rcpRemoteCopy = new RemoteCopyFTP("121.223.224.237", "vixen", "v1xen");
if (is_string($mixResult = $rcpRemoteCopy->Connect()))
{
	echo "$mixResult \n";
}
$rcpRemoteCopy->Copy($strLocalPath.$strFilename, "/Incoming/Samples/$strFilename", RCOPY_REMOVE);
$rcpRemoteCopy->Disconnect();

// Sleep for a bit, allowing the server to pick up the vbf file
Debug("Sleeping...");
usleep(60*1000000);

// Monitor FTP until all PDFs have appeared
echo "Downloading PDFs from BillPrint...\n\n";
ob_flush();
// Connect to FTP
$ptrConnection = ftp_connect("121.223.224.237");
ftp_login($ptrConnection, "vixen", "v1xen");
ftp_chdir($ptrConnection, "/Outgoing/Samples");

// Wait for our batch to appear
$intLatest = 0;
while (date("Y-m-d", $intLatest) != date("Y-m-d", time()))
{
	$arrListing = ftp_nlist($ptrConnection, "-p");
	foreach ($arrListing as $strFile)
	{
		if ($intTime = strtotime(rtrim($strFile, '/')));
		{
			$intLatest = ($intTime > $intLatest) ? $intTime : $intLatest;
		}
	}
}

$strDirectory = "/Outgoing/Samples/".date("YmdHis/", $intLatest);
Debug("Using directory: '$strDirectory'");

// Parse all directories (except 'Processed'), counting the number of completed PDFs (monitor file size fluctuation)
$arrDownloaded		= Array();
$arrDirList			= Array();
$arrDirList[]		= "001/";
$arrDirList[]		= "002/";
$arrDirList[]		= "003/";
$intLastDownload	= time();
$strDownloadDir		= FILES_BASE_PATH."bill_output/sample_pdf_temp/";
if (!file_exists($strDownloadDir))
{
	mkdir($strDownloadDir, 0777);
}
chdir($strDownloadDir);
exec("rm *.pdf");
while (($intLastDownload + 60) > time())
{
	$intPDFCount = 0;
	foreach ($arrDirList as $strDir)
	{
		if (@ftp_chdir($ptrConnection, $strDirectory.$strDir))
		{
			$arrFiles = ftp_nlist($ptrConnection, "*");
			if ($arrFiles)
			{
				foreach ($arrFiles as $strFile)
				{
					if (!in_array($strFile, $arrDownloaded))
					{
						$intLastSize	= ftp_size($ptrConnection, $strFile);
						$fltTime		= 0;
						while ($fltTime < microtime(TRUE))
						{
							if ($intLastSize && $intLastSize === ftp_size($ptrConnection, $strFile))
							{
								break;
							}
							$fltTime = microtime(TRUE) + 0.25;
						}
						echo "\t+ Downloading '$strFile'\t(".ceil($intLastSize/1024)."KB)...\t\t";
						if (ftp_get($ptrConnection, $strDownloadDir.$strFile, $strDirectory.$strDir.$strFile, FTP_BINARY))
						{
							echo "[   OK   ]\n";
						}
						else
						{
							echo "[ FAILED ]\n";
						}
						$intLastDownload = time();
						ob_flush();
						$arrDownloaded[] = $strFile;
					}
				}
			}
		}
	}
}

// ZIP samples, copy to public location
chdir($strDownloadDir);
$strBaseZipname = date("F")." $strMode Samples";
$strZipname		= $strBaseZipname.'.zip';
$intVersion		= 1;
while (file_exists($strZipname))
{
	$intVersion++;
	$strZipname	= $strBaseZipname." MK$intVersion.zip";
}
echo shell_exec("zip -qj '$strZipname' *.pdf");

$strCustomerName	= $GLOBALS['**arrCustomerConfig']['Customer'];
$strDir				= date("Y/m/", strtotime("-1 day", time()));

$rcpRemoteCopySamples = new RemoteCopySSH("192.168.2.224", "flame", "zeemu");
if (is_string($mixResult = $rcpRemoteCopySamples->Connect()))
{
	echo "$mixResult \n";
}
$rcpRemoteCopySamples->Copy($strDownloadDir.$strZipname, "/data/www/samples.yellowbilling.com.au/html/$strCustomerName/$strDir", RCOPY_BACKUP);
$rcpRemoteCopySamples->Disconnect();

$strURL = "http://samples.yellowbilling.com.au/$strCustomerName/$strDir/$strZipname";
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', trim($strZipname, '.zip'), trim($strZipname, '.zip')." are available at $strURL");

/*
// Upload Sample Zip to a Web location
echo "\nCopying '$strZipname' to Public Directory...\n";
ob_flush();
$rcpRemoteCopy = new RemoteCopySSH("10.50.50.15", "flame", "zeemu");
if (is_string($mixResult = $rcpRemoteCopy->Connect()))
{
	echo "$mixResult \n";
}
$strCustomer	= $GLOBALS['**arrCustomerConfig']['Customer'];
$strYearMonth	= date("Y/n");
$rcpRemoteCopy->Copy($strDownloadDir.$strZipname, "/home/vixen/$strCustomer/samples/$strYearMonth/$strFilename", RCOPY_REMOVE);
$rcpRemoteCopy->Disconnect();



// Email link to samples
$strSubject	= "$strMode Samples for $strMonth";
//$strEmail	= "paula@staralliance.com.au, jade@wxc.com.au, adele.k@telcoblue.com.au, mshield@telcoblue.com.au, jared@telcoblue.com.au, rdavis@ybs.net.au, turdminator@hotmail.com, aphplix@gmail.com";
$strEmail	= "rdavis@ybs.net.au, turdminator@hotmail.com, mark.s@yellowbilling.com.au";
$strContent	= "Below is a link to the $strMode Samples for $strMonth Billing.\n\n";
SendEmail($strEmail, $strSubject, $strContent);
*/

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::ReprintSamples Ended @ $strDateTime", "viXen Billing Ended @ $strDateTime");

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
?>
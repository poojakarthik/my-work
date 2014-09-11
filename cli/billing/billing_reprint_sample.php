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
VixenRequire('framework/remote_copy.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Add in list of accounts
//---------------------------

// Voicetalk customers
$arrAccounts[]	= 1000162906;
$arrAccounts[]	= 1000162059;
$arrAccounts[]	= 1000159470;
$arrAccounts[]	= 1000162528;
$arrAccounts[]	= 1000007031;
$arrAccounts[]	= 1000162445;
$arrAccounts[]	= 1000161645;
$arrAccounts[]	= 1000162437;
$arrAccounts[]	= 1000162825;
$arrAccounts[]	= 1000159466;
$arrAccounts[]	= 1000159893;
$arrAccounts[]	= 1000010134;
$arrAccounts[]	= 1000161816;
$arrAccounts[]	= 1000163105;
//$arrAccounts[]	= 1000161744;

// w/ Inbound Services
$arrAccounts[]	= 1000157275;
$arrAccounts[]	= 1000157088;
$arrAccounts[]	= 1000162420;
$arrAccounts[]	= 1000155448;
$arrAccounts[]	= 1000157889;
$arrAccounts[]	= 1000155226;
$arrAccounts[]	= 1000162199;
$arrAccounts[]	= 1000154974;
$arrAccounts[]	= 1000154909;
//$arrAccounts[]	= 1000157423;
$arrAccounts[]	= 1000156613;
$arrAccounts[]	= 1000154838;
$arrAccounts[]	= 1000157129;
$arrAccounts[]	= 1000160069;
$arrAccounts[]	= 1000160496;

// w/ Mobiles
$arrAccounts[]	= 1000157278;
$arrAccounts[]	= 1000163258;
$arrAccounts[]	= 1000158462;
$arrAccounts[]	= 1000160638;
$arrAccounts[]	= 1000157524;
$arrAccounts[]	= 1000159582;
$arrAccounts[]	= 1000162277;
$arrAccounts[]	= 1000162126;
$arrAccounts[]	= 1000157548;
$arrAccounts[]	= 1000158255;
$arrAccounts[]	= 1000158558;
$arrAccounts[]	= 1000159107;
$arrAccounts[]	= 1000157175;
$arrAccounts[]	= 1000158156;
$arrAccounts[]	= 1000159979;

// w/ Cost Centres
$arrAccounts[]	= 1000155448;
$arrAccounts[]	= 1000157203;
$arrAccounts[]	= 1000155054;
//$arrAccounts[]	= 1000155021;
$arrAccounts[]	= 1000156265;
$arrAccounts[]	= 1000155104;
$arrAccounts[]	= 1000158291;
$arrAccounts[]	= 1000157570;
$arrAccounts[]	= 1000155313;

// other
$arrAccounts[]	= 1000160299;
$arrAccounts[]  = 1000007119;
$arrAccounts[]	= 1000162429;
$arrAccounts[]	= 1000163167;

// ELB Accounts
$arrAccounts[]	= 1000156611;
$arrAccounts[]	= 1000160843;
$arrAccounts[]	= 1000157789;

// Indial 100 without ELB (Itemisation was missing)
$arrAccounts[]	= 1000158008;
$arrAccounts[]	= 1000160250;

// Telco Blue
$arrAccounts[]	= 1000154811;

// New Mobile Plan Accounts
$arrAccounts[]	= 1000162273;
$arrAccounts[]	= 1000164826;
$arrAccounts[]	= 1000164618;
$arrAccounts[]	= 1000164236;
$arrAccounts[]	= 1000162139;

// Recurring Charges
$arrAccounts[]	= 1000158996;
$arrAccounts[]	= 1000164686;


// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com', "viXen Billing::ReprintSample Started @ $strDateTime", "viXen Billing Started @ $strDateTime");

// reprint
$bolResponse = $appBilling->PrintSampleAccounts($arrAccounts);

$appBilling->FinaliseReport();
ob_end_clean();

// Remote Copy
$strFilename	= "reprint".date("Y-m-d").".vbf";
//$strFilename	= "reprint2007-06-30.vbf";
$strLocalPath	= FILES_BASE_PATH."bill_output/";
echo "\nCopying '$strFilename' to BillPrint...\n";
ob_flush();
$rcpRemoteCopy = new RemoteCopyFTP("203.201.137.55", "vixen", "v1xen");
if (is_string($mixResult = $rcpRemoteCopy->Connect()))
{
	echo "$mixResult \n";
}
$rcpRemoteCopy->Copy($strLocalPath.$strFilename, "/Incoming/Samples/$strFilename", RCOPY_REMOVE);
$rcpRemoteCopy->Disconnect();

// Sleep for a bit, allowing the server to pick up the vbf file
Debug("Sleeping...");
sleep(60);

// Monitor FTP until all PDFs have appeared
echo "Downloading PDFs from BillPrint...\n\n";
ob_flush();
// Connect to FTP
$ptrConnection = ftp_connect("203.201.137.55");
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
exec("rm *.*");
while (($intLastDownload + 60) > time())
{
	$intPDFCount = 0;
	foreach ($arrDirList as $strDir)
	{
		if (@ftp_chdir($ptrConnection, $strDirectory.$strDir))
		{
			$arrFiles = ftp_nlist($ptrConnection, "*");
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

// ZIP (1 file for most people, 2 split files for Paula)
chdir($strDownloadDir);
$strZipname = date("F", strtotime("-1 day", time()))." Signoff Samples"; 
echo shell_exec("zip -qj '$strZipname' *.pdf");
echo shell_exec("zipsplit -n 5242880 \"$strZipname\"");


// Email
$arrHeaders = Array	(
						'From'		=> "billing@telcoblue.com.au",
						'Subject'	=> $strZipname
					);
$mimMime = new Mail_mime("\n");
$mimMime->setTXTBody("Here are the final sign-off PDFs for the ".date("F", strtotime("-1 day", time()))." billing period.");
$mimMime->addAttachment("$strDownloadDir{$strZipname}.zip", 'application/zip');
$strBody = $mimMime->get();
$strHeaders = $mimMime->headers($arrHeaders);
$emlMail =& Mail::factory('mail');

// Send the email
$strEmail = "jade@wxc.com.au, adele.k@telcoblue.com.au, andrew.p@telcoblue.com.au, mshield@telcoblue.com.au, jared@telcoblue.com.au, rdavis@ybs.net.au, turdminator@hotmail.com, aphplix@gmail.com";
$emlMail->send($strEmail, $strHeaders, $strBody);



// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com', "viXen Billing::ReprintSamples Ended @ $strDateTime", "viXen Billing Ended @ $strDateTime");

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";

?>

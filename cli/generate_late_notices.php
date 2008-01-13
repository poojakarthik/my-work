<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// generate_late_notices
//----------------------------------------------------------------------------//
/**
 * generate_late_notices
 *
 * generates the late notices
 *
 * generates the late notices
 *
 * @file		generate_late_notices.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// It is assumed that this script is in one of the sub-directories of vixen
require_once("../framework/require.php");

// Declare constants
Define(DEFAULT_BASE_PATH, "./late_notices");

// Get the command line arguments
$arrArgs = $_SERVER['argv'];
$intArgs = $_SERVER['argc'];

$strUsage = "usage: generate_late_notices NoticeType [Dest]\n".
			"where:\n".
			"\tNoticeType :\toverdue | suspended | final\n".
			"\tDest       :\tDestination directory (defaults to '". DEFAULT_BASE_PATH ."')\n\n";

$strProgramName = "Late Notice Generator\n";

if (($intArgs != 2) && ($intArgs != 3))
{
	// No command line arguments were provided, exit gracefully
	echo $strProgramName. $strUsage;
	exit(1);
}

switch (strtolower($arrArgs[1]))
{
	case "overdue":
		$intNoticeType = ACCOUNT_NOTICE_OVERDUE;
		break;
	
	case "suspended":
		$intNoticeType = ACCOUNT_NOTICE_SUSPENSION;
		break;
	
	case "final":
		$intNoticeType = ACCOUNT_NOTICE_FINAL_DEMAND;
		break;
	
	default:
		echo "{$strProgramName}ERROR: NoticeType is invalid\n\n";
		exit(1);
		break;
}

$strBasePath = ($intArgs == 3)? $arrArgs[2] : DEFAULT_BASE_PATH;

if (!is_dir($strBasePath))
{
	echo "{$strProgramName}ERROR: Dest does not exist\n\n";
	exit(1);
}

echo "Generating ". GetConstantDescription($intNoticeType, "AccountNotice"). "s...";

// If I don't flush and end the current buffer, then none of the above text is
// outputted until the GenerateLatePaymentNotices function returns, although
// I'm not sure if I'm using this the right way, because I haven't explicitly started
// output buffering (ob_start()).  If I do start it, it doesn't work
ob_end_flush();

$mixResult = GenerateLatePaymentNotices($intNoticeType, $strBasePath);

if ($mixResult === FALSE)
{
	echo "\nERROR: Generating late notices failed, unexpectedly\n\n";
	exit(2);
}
else
{
	echo "Ok\n";
	echo "Notices successfully generated  : {$mixResult['Successful']}\n";
	echo "Notices that failed to generate : {$mixResult['Failed']}\n\n";
}

exit(0);

?>

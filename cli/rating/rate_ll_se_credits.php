<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating a single CDR
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

// load rating class
$appRating = new ApplicationRating($arrConfig);


// get all of the CDRs we need
$selSECCDRs = new StatementSelect(	"CDR",
									"*",
									"Credit = 1 AND " .
									"RecordType = 21 AND " .
									"Status IN (".CDR_CREDIT_MATCH_NOT_FOUND.")");

$arrCols = Array();
$arrCols['Rate']	= NULL;
$arrCols['Charge']	= NULL;
$arrCols['Status']	= CDR_RATED;
$arrCols['RatedOn']	= new MySQLFunction('NOW()');
$ubiCDR		= new StatementUpdateById("CDR", $arrCols);
$intTotal = $selSECCDRs->Execute();
$intPassed = 0;

ob_start();
echo "\n\n[ RATING LL S&E CREDIT CDRS ]\n\n";
ob_flush();

// Rate the CDRs
while ($arrCDR = $selSECCDRs->Fetch())
{
	echo "Rating CDR {$arrCDR['Id']}...";
	ob_flush();
	// Rate and Save
	if (($arrCDR = $appRating->RateCDR($arrCDR, TRUE)) === FALSE)
	{
		// Error, don't change status
		echo "\t\t\t\t[ FAILED ]\n";
		continue;
		
	}
	
	// Rated fine, update CDR
	$arrCols['Id']		= $arrCDR['Id'];
	$arrCols['Rate']	= $arrCDR['Rate'];
	$arrCols['Charge']	= $arrCDR['Charge'];
	$ubiCDR->Execute($arrCols);
	echo "\t\t\t\t[   OK   ]\n";
	$intPassed++;
}

echo "\n * Rated $intPassed of $intTotal LL S&E Credits.\n\n";

?>
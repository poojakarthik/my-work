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

$intCDR = (int)$_REQUEST['id'];
if (!$intCDR)
{
	if (!($intCDR = (int)$argv[1]))
	{
		echo "No CDR record requested.\n";
		die;
	}
}

// load rating class
$appRating = new ApplicationRating($arrConfig);



// Get the CDR
/*$selCDR = new StatementSelect("CDR", "*", "CDR.Id = <Id>");
if (!$selCDR->Execute(Array('Id' => $intCDR)))
{
	echo "Invalid CDR record requested.  Please double-check the Id ($intCDR).\n";
	die;
}
$arrCDR = $selCDR->Fetch();
*/

define('RATING_DEBUG', TRUE);

// Rate the CDR
$arrCDR = $appRating->RateCDR($intCDR, TRUE);

// Print Output
CliEcho("CDR:");
Debug($arrCDR);

CliEcho("Rate:");
Debug($appRating->_arrCurrentRate);

die();
?>

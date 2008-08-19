<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Finds the Rate for a Single CDR
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig	= LoadApplication();

define('RATING_DEBUG', TRUE);

$intCDR		= (int)$_REQUEST['id'];
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
$selCDR = new StatementSelect("CDR", "*", "CDR.Id = <Id>");
if (!$selCDR->Execute(Array('Id' => $intCDR)))
{
	$selCDRInvoiced	= new StatementSelect("CDRInvoiced", "*", "Id = <Id>", NULL, NULL, NULL, FLEX_DATABASE_CONNECTION_CDR);
	
	if (!$selCDRInvoiced->Execute(Array('Id' => $intCDR)))
	{
		CliEcho("\nInvalid CDR record requested.  Please double-check the Id ($intCDR).\n");
		die;
	}
	else
	{
		$arrCDR	= $selCDRInvoiced->Fetch();
	}
}
else
{
	$arrCDR	= $selCDR->Fetch();
}

// Rate the CDR
$arrRate	= $appRating->CDRFindRate($arrCDR);

// Print Output
CliEcho("CDR:");
Debug($arrCDR);

CliEcho("Rate:");
Debug($arrRate);

die();
?>

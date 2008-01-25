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
	echo "No CDR record requested.\n";
	die;
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

// Rate the CDR
$fltCharge = $appRating->RateCDR($intCDR);

// Print Output
if ($fltCharge !==FALSE)
{
	echo 'CDR Rated at : $'.money_format('%i',$fltCharge);
}
else
{
	echo "Could Not Rate CDR";
}

die();
?>

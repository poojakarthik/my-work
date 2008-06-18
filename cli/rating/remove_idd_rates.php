<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// remove_idd_rates
//----------------------------------------------------------------------------//
/**
 * remove_idd_rates
 *
 * Removes all internationl rates, rate groups and linkages from the database
 *
 * Removes all internationl rates, rate groups and linkages from the database
 *
 * @file		remove_idd_rates.php
 * @language	PHP
 * @package		rating_application
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// DONT LET THIS SCRIPT RUN !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
echo "This script is disabled ! make sure you know what you are doing before you try and run this";
Die();


// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");


$sqlQuery = new Query();

// get list of rate groups
$strQuery = "SELECT Id FROM RateGroup WHERE RecordType = 27 OR RecordType = 28";
$sqlResult = $sqlQuery->Execute($strQuery);
While ($arrRow = $sqlResult->fetch_assoc())
{
	$intRateGroup = $arrRow['Id'];
	
	if ($intRateGroup)
	{
		// remove from RatePlanRateGroup
		$strQuery = "DELETE FROM RatePlanRateGroup WHERE RateGroup = $intRateGroup";
		$sqlQuery->Execute($strQuery);
		
		// remove from RateGroupRate
		$strQuery = "DELETE FROM RateGroupRate WHERE RateGroup = $intRateGroup";
		$sqlQuery->Execute($strQuery);
	}
}

// remove RateGroups
$strQuery = "DELETE FROM RateGroup WHERE RecordType = 27 OR RecordType = 28";
$sqlQuery->Execute($strQuery);

// remove Rates
$strQuery = "DELETE FROM Rate WHERE RecordType = 27 OR RecordType = 28";
$sqlQuery->Execute($strQuery);

// done
echo "[  DONE  ]\n";

?>

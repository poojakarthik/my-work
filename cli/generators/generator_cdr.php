<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// generator_customer
//----------------------------------------------------------------------------//
/**
 * generator_customer
 *
 * Generates customer data
 *
 * Generates customer data for use in demo system
 * 
 * @file		generator_customer.php
 * @language	PHP
 * @package		setup_scripts
 * @author		Rich 'Waste' Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once("../../flex.require.php");

$arrRecordTypes = Array();
				// ServiceType			RecordType	% liklihood
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[2]			= 20;		// Mob -> Mob
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[6]			= 20;		// Mob -> Nat
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[7]			= 2;		// Mob Freecall
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[8]			= 5;		// Voicemail Ret
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[9]			= 5;		// Voicemail Dep
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[10]		= 30;		// SMS
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[11]		= 2;		// Roaming
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[12]		= 2;		// GPRS
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[14]		= 2;		// OS Air Time
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[15]		= 5;		// MMS
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[16]		= 2;		// Mob Other
$arrRecordTypes[SERVICE_TYPE_MOBILE]	[27]		= 5;		// Mob -> IDD

$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[17]		= 25;		// Local
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[18]		= 5;		// Program Local
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[19]		= 25;		// LL > Nat
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[20]		= 15;		// LL > Mob
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[21]		= 5;		// S&E
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[23]		= 5;		// LL > 1900
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[24]		= 5;		// LL > 13
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[25]		= 5;		// LL > 019
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[26]		= 5;		// LL Other
$arrRecordTypes[SERVICE_TYPE_LAND_LINE]	[28]		= 5;		// LL > IDD


// Statements
$selServices = new StatementSelect("Service", "*", "ClosedOn IS NULL");

$intServices = $selServices->Execute();
$arrServices = $selServices->FetchAll();

$intFile = time();
for ($i = 0; $i < rand(10*$intServices, $intServices*100); $i++)
{
	$arrCDR	= DataAccess::getDataAccess()->FetchClean('CDR');
	
	// Randomly select a service
	$arrService = $arrServices[rand(0, count($arrServices)-1)];
	$arrCDR['Service']		= $arrService['Id'];
	$arrCDR['Account']		= $arrService['Account'];
	$arrCDR['AccountGroup']	= $arrService['AccountGroup'];
	$arrCDR['ServiceType']	= $arrService['ServiceType'];
	if ($arrService['Indial100'])
	{
		// Randomise an FNN in this range
		$strExtension	= str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
		$arrCDR['FNN']	= substr($arrService['FNN'], 0, -2) . $strExtension;
	}
	else
	{
		$arrCDR['FNN']	= $arrService['FNN'];
	}
	
	// Randomly select a record type
	$intType	= rand(0, 100);
	$intCount	= 0;
	foreach ($arrRecordTypes[$arrService['ServiceType']] as $intRecordType=>$intPercent)
	{
		$intCount += $intPercent;
		if ($intType < $intCount)
		{
			$arrCDR['RecordType'] = $intRecordType;
			break;
		}
	}
	
	// Source/Destination
	$intDestination = $arrCDR['FNN'];
	$intSource		= '0'.rand(300000000, 999999999);
	switch ($arrCDR['RecordType'])
	{
		case 10:
		case 15:
			// SMS
			$arrCDR['Units']			= 1;
			$arrCDR['StartDatetime']	= rand(strtotime("-1 day", time()), time());
			break;
			
		case 27:
		case 28:
			$intSource = '+'.rand(10000000000, 999999999999);
			break;
		
		default:
			// Time based
			$arrCDR['Units']			= rand(1, 3600*5);
			$arrCDR['StartDatetime']	= date("Y-m-d H:i:s", rand(strtotime("-1 day", time()), time()));
			$arrCDR['EndDatetime']		= date("Y-m-d H:i:s", strtotime($arrCDR['StartDatetime']) + $arrCDR['Units']);
	}
	if ($arrRecordTypes[$arrService['ServiceType']] == SERVICE_TYPE_INBOUND)
	{
		$arrCDR['Destination']	= $intSource;
		$arrCDR['Source']		= $intDestination;
	}
	else
	{
		$arrCDR['Destination']	= $intDestination;
		$arrCDR['Source']		= $intSource;
	}
	
	// Other fields
	$arrCDR['Carrier']		= rand(1, 4);
	$arrCDR['NormalisedOn']	= date("Y-m-d H:i:s");
	$arrCDR['SequenceNo']	= $i+1;
	$arrCDR['File']			= $intFile;
	$arrCDR['CarrierRef']	= "Generated ".date("Y-m-d H:i:s");
	
	Debug($arrCDR);
	die;
}

?>
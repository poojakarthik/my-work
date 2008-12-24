<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

// Normalisation Modules
require_once("modules/base_module.php");
require_once("modules/module_aapt.php");
require_once("modules/module_commander.php");
require_once("modules/module_iseek.php");
require_once("modules/module_m2.php");
require_once("modules/module_optus.php");
require_once("modules/module_rslcom.php");

// Create an instance of each Normalisation module
CliEcho(" * NORMALISATION MODULES");
$selCarrierModules	= new StatementSelect("CarrierModule", "*", "Type = <Type> AND Active = 1");
$selCarrierModules->Execute(Array('Type' => MODULE_TYPE_NORMALISATION_CDR));
while ($arrModule = $selCarrierModules->Fetch())
{
	$arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
	CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'Carrier')." : ".$arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
}

$strLogPath	= FILES_BASE_PATH."/logs/normalisation/cdrerrorlogger/";
@mkdir($strLogPath, 0777, true);
$strRunDate	= date("YmdHis");

$arrStatuses	= array(CDR_BAD_RECORD_TYPE, CDR_BAD_DESTINATION, CDR_CANT_NORMALISE_INVALID);
$strStatuses	= implode(', ', $arrStatuses);

$intStartTime	= time();
$intCurrentTime	= 0;

$intRefreshRate	= 2;

$qryQuery	= new Query();
$resResult	= $qryQuery->Execute("SELECT CDR.*, FileImport.FileType FROM CDR JOIN FileImport ON CDR.File = FileImport.Id WHERE CDR.Status IN ({$strStatuses})");
if ($resResult === false)
{
	throw new Exception($qryQuery->Error());
}
else
{
	$resRecordTypeLog	= fopen($strLogPath."{$strRunDate}_recordtype.log", 'w');
	$resDestinationLog	= fopen($strLogPath."{$strRunDate}_destination.log", 'w');
	$resInvalidLog		= fopen($strLogPath."{$strRunDate}_invalid.log", 'w');
	
	if (!$resRecordTypeLog || !$resDestinationLog || !$resInvalidLog)
	{
		CliEcho();
		throw new Exception("One or more files could not be opened for writing");
	}
	
	$arrRecordTypeSummary	= array();
	$arrDestinationSummary	= array();
	$arrInvalidSummary		= array();
	
	$intTotal	= $resResult->num_rows;
	$intCount	= 0;
	while ($arrCDR = $resResult->fetch_assoc())
	{
		$intCount++;
		
		switch ($arrCDR['Status'])
		{
			case CDR_BAD_RECORD_TYPE:
				// Get the Raw Record Type equivalent
				$arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->RawCDR($arrCDR['CDR']);
				$mixRawRecordType	= $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->RawRecordType();
				
				// Add to Summary
				$arrRecordTypeSummary[$arrCDR['Carrier']][$arrCDR['Carrier']][$mixRawRecordType]++;
				
				// Add to Itemisation
				fwrite($resRecordTypeLog, "CDR #{$arrCDR['Id']} from Carrier #{$arrCDR['Carrier']} has a raw Record Type of '{$mixRawRecordType}'\n");
				break;
				
			case CDR_BAD_DESTINATION:
				// Get the Raw Destination Code equivalent
				$arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->RawCDR($arrCDR['CDR']);
				$mixRawDestination	= $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->RawDestinationCode();
				$mixRawDescription	= $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->RawDescription();
				
				// Add to Summary
				$arrDestinationSummary[$arrCDR['Carrier']][$mixRawDestination]++;
				
				// Add to Itemisation
				fwrite($resDestinationLog, "CDR #{$arrCDR['Id']} from Carrier #{$arrCDR['Carrier']} has a raw Destination Code of '{$mixRawDestination}' (Description: '{$mixRawDescription}')\n");
				break;
			
			case CDR_CANT_NORMALISE_INVALID:
				// Renormalise the CDR
				$arrNormalised	= $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->Normalise($arrCDR);
				$arrValid		= $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->Validate(true);
				
				$strInvalidFields	= '';
				foreach ($arrValid as $strField=>$bolValid)
				{
					if (!$bolValid)
					{
						// Add to Summary
						$arrInvalidSummary[$arrCDR['Carrier']][$strField]++;
						
						$strInvalidFields	.= "{$strField}: {$arrNormalised[$strField]}; ";
					}
				}
				
				// Add to Itemisation
				fwrite($resInvalidLog, "CDR #{$arrCDR['Id']} from Carrier #{$arrCDR['Carrier']} has the following invalid fields: {$strInvalidFields}\n");
				break;
		}
		
		// Update the on-screen summary
		$inLastTime		= $intCurrentTime;
		$intCurrentTime	= time();
		if ($intCurrentTime-$inLastTime > $intRefreshRate)
		{
			// Clear the screen, reposition at 0,0
			CliEcho("\033[2J");
			
			CliEcho("[ RECORD TYPES ]");
			foreach ($arrRecordTypeSummary as $intCarrier=>$arrRecordTypes)
			{
				CliEcho("\t".GetConstantDescription($intCarrier, 'Carrier').":");
				foreach ($arrRecordTypes as $mixRecordType=>$intCount)
				{
					CliEcho("\t\t{$mixRecordType}\t: {$intCount}");
				}
			}
			
			CliEcho("[ DESTINATIONS ]");
			foreach ($arrDestinationSummary as $intCarrier=>$arrDestinations)
			{
				CliEcho("\t".GetConstantDescription($intCarrier, 'Carrier').":");
				foreach ($arrDestinations as $mixDestination=>$intCount)
				{
					CliEcho("\t\t{$mixDestination}\t: {$intCount}");
				}
			}
			
			CliEcho("[ INVALID ]");
			foreach ($arrInvalidSummary as $intCarrier=>$arrFields)
			{
				CliEcho("\t".GetConstantDescription($intCarrier, 'Carrier').":");
				foreach ($arrFields as $strField=>$intCount)
				{
					CliEcho("\t\t{$strField}\t: {$intCount}");
				}
			}
		}
	}
	
	fclose($resRecordTypeLog);
	fclose($resDestinationLog);
	fclose($resInvalidLog);
}
?>
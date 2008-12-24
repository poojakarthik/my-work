<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

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
mkdir($strLogPath);
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
	$resRecordTypeLog	= fopen($strLogPath."{$strRunDate}_recordtype.log");
	$resDestinationLog	= fopen($strLogPath."{$strRunDate}_destination.log");
	$resInvalidLog		= fopen($strLogPath."{$strRunDate}_invalid.log");
	
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
				fwrite($resDestinationLog, "CDR #{$arrCDR['Id']} from Carrier #{$arrCDR['Carrier']} has a raw Destination Code of '{$mixRawRecordType}' (Description: '{$mixRawDescription}')\n");
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
			CliEcho("\033[2J", false);
			
			CliEcho("[ RECORD TYPES ]");
			foreach ($arrRecordTypeSummary as $intCarrier=>$arrRecordTypes)
			{
				CliEcho("\t".GetConstantName($intCarrier, 'Carrier').":");
				foreach ($arrRecordTypes as $mixRecordType=>$intCount)
				{
					CliEcho("\t\t{$mixRecordType}\t: {$intCount}");
				}
			}
			
			CliEcho("[ DESTINATIONS ]");
			foreach ($arrDestinationSummary as $intCarrier=>$arrDestinations)
			{
				CliEcho("\t".GetConstantName($intCarrier, 'Carrier').":");
				foreach ($arrDestinations as $mixDestination=>$intCount)
				{
					CliEcho("\t\t{$mixDestination}\t: {$intCount}");
				}
			}
			
			CliEcho("[ INVALID ]");
			foreach ($arrDestinationSummary as $intCarrier=>$arrFields)
			{
				CliEcho("\t".GetConstantName($intCarrier, 'Carrier').":");
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
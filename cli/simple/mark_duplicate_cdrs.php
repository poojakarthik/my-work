<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selCDR		= new StatementSelect("CDR", "*", "Status IN (101, 107, 150, 151) AND StartDatetime >= '2008-07-01 00:00:00'");
$ubiCDR		= new StatementUpdateById("CDR", Array('Status'=>NULL));
$qryQuery	= new Query();

// Run Processor
CliEcho("\n[ MARK DUPLICATE CDRS ]\n");

$intCount		= 0;
$intStartTime	= time();
$intCurrentTime	= $intStartTime;

DataAccess::getDataAccess()->TransactionStart();
try
{
	if (($intTotal = $selCDR->Execute()) === FALSE)
	{
		Debug($selCDR->Error());
	}
	else
	{		
		// Foreach CDR
		while ($arrCDR = $selCDR->Fetch())
		{
			$intLastTime	= $intCurrentTime;
			
			$intCount++;
			CliEcho(" \t + CDR $intCount/$intTotal...\t\t", FALSE);
			
			$strCarrierRef			= ($arrCDR['CarrierRef'] === NULL)		? 'NULL'	: "'{$arrCDR['CarrierRef']}'";
			$strSource				= ($arrCDR['Source'] === NULL)			? 'NULL'	: "'{$arrCDR['Source']}'";
			$strDestination			= ($arrCDR['Destination'] === NULL)		? 'NULL'	: "'{$arrCDR['Destination']}'";
			$strStartDatetime		= ($arrCDR['StartDatetime'] === NULL)	? 'NULL'	: "'{$arrCDR['StartDatetime']}'";
			$strEndDatetime			= ($arrCDR['EndDatetime'] === NULL)		? 'NULL'	: "'{$arrCDR['EndDatetime']}'";
			$strDescription			= ($arrCDR['Description'] === NULL)		? 'NULL'	: "'{$arrCDR['Description']}'";
			$strFindDuplicateSQL	= "SELECT Id, CASE WHEN CarrierRef <=> {$strCarrierRef} THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status 
										FROM CDR 
										WHERE Id != {$arrCDR['Id']} AND 
										FNN = '{$arrCDR['FNN']}' AND 
										Source <=> {$strSource} AND 
										Destination <=> {$strDestination} AND 
										StartDatetime <=> {$strStartDatetime} AND 
										EndDatetime <=> {$strEndDatetime} AND 
										Units = {$arrCDR['Units']} AND 
										Cost = {$arrCDR['Cost']} AND 
										RecordType = {$arrCDR['RecordType']} AND 
										RecordType NOT IN (10, 15, 33, 21) AND 
										Credit = {$arrCDR['Credit']} AND 
										Description <=> {$strDescription} AND 
										Status NOT IN (".CDR_DUPLICATE.", ".CDR_RECHARGE.")
										ORDER BY Id DESC
										LIMIT 1";
			
			// Does this already exist?
			$mixResult	= $qryQuery->Execute($strFindDuplicateSQL);
			if ($arrDuplicateCDR = $mixResult->fetch_assoc())
			{
				$strMatchString			= GetConstantDescription($arrDuplicateCDR['Status'], 'CDR');
				$arrCDR['Status']		= $arrDuplicateCDR['Status'];
				
				// Set this CDR's status
				if ($ubiCDR->Execute($arrCDR) === FALSE)
				{
					CliEcho("[ FAILED ]");
					CliEcho("\t\t -- ".$ubiCDR->Error(), FALSE);
				}
				else
				{
					CliEcho("[   OK   ]");
					CliEcho("\t\t !!! CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}", FALSE);
				}
			}
			else
			{
				CliEcho("[  SKIP  ]", FALSE);
			}
			
			$intCurrentTime	= time();
			$intProcessTime	= $intCurrentTime - $intLastTime;
			$intRunningTime	= $intCurrentTime - $intStartTime;
			CliEcho(" ({$intProcessTime}s/{$intRunningTime}s)");
		}
	}
	
	DataAccess::getDataAccess()->TransactionCommit();
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
}

$intTotalTime	= time() - $intStartTime;
CliEcho("Total Time Taken: $intTotalTime seconds\n");

?>
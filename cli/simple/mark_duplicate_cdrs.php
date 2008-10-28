<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selCDR		= new StatementSelect("CDR", "*", "Status IN (101, 107, 150, 151)");
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
	CliEcho("Fetching CDRs...");
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
			
			$arrMatchCDR	= Array();
			foreach ($arrCDR as $strField=>$mixValue)
			{
				if ($mixValue === NULL)
				{
					$mixValue	= 'NULL';
				}
				elseif (is_string($mixValue))
				{
					$mixValue	= "'".str_replace("'", '\\\'', $mixValue)."'";
				}
				$arrMatchCDR[$strField]	= $mixValue;
			}
			$strFindDuplicateSQL	= "SELECT Id, CASE WHEN CarrierRef <=> {$arrMatchCDR['CarrierRef']} THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status 
										FROM CDR 
										WHERE Id != {$arrMatchCDR['Id']} AND 
										FNN = {$arrMatchCDR['FNN']} AND 
										Source <=> {$arrMatchCDR['Source']} AND 
										Destination <=> {$arrMatchCDR['Destination']} AND 
										StartDatetime <=> {$arrMatchCDR['StartDatetime']} AND 
										EndDatetime <=> {$arrMatchCDR['EndDatetime']} AND 
										Units = {$arrMatchCDR['Units']} AND 
										Cost = {$arrMatchCDR['Cost']} AND 
										RecordType = {$arrMatchCDR['RecordType']} AND 
										RecordType NOT IN (10, 15, 33, 21) AND 
										Credit = {$arrMatchCDR['Credit']} AND 
										Description <=> {$arrMatchCDR['Description']} AND 
										Status NOT IN (".CDR_DUPLICATE.", ".CDR_RECHARGE.")
										ORDER BY Id DESC
										LIMIT 1";
			
			// Does this already exist?
			$mixResult	= $qryQuery->Execute($strFindDuplicateSQL);
			if (!$mixResult)
			{
				throw new Exception($qryQuery->Error());
			}
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
	Debug($eException->__toString());
}

$intTotalTime	= time() - $intStartTime;
CliEcho("Total Time Taken: $intTotalTime seconds\n");

?>
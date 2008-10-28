<?php

// Framework
require_once("../../flex.require.php");

$intCDR	= (int)$argv[1];
if (!$intCDR)
{
	CliEcho("\nPlease provide a valid CDR Id as the sole parameter.\n");
	exit(1);
}

$selCDR	= new StatementSelect("CDR", "*", "Id = {$intCDR}");
$selCDR->Execute();
$arrCDR	= $selCDR->Fetch();

$strFindDuplicateSQL	= "SELECT Id, CASE WHEN CarrierRef <=> '{$arrCDR['CarrierRef']}' THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status 
											FROM CDR 
											WHERE Id != {$arrCDR['Id']} AND 
											FNN = '{$arrCDR['FNN']}' AND 
											Source <=> '{$arrCDR['Source']}' AND 
											Destination <=> '{$arrCDR['Destination']}' AND 
											StartDatetime <=> '{$arrCDR['StartDatetime']}' AND 
											EndDatetime <=> '{$arrCDR['EndDatetime']}' AND 
											Units = {$arrCDR['Units']} AND 
											Cost = {$arrCDR['Cost']} AND 
											RecordType = {$arrCDR['RecordType']} AND 
											RecordType NOT IN (10, 15, 33, 21) AND 
											Credit = {$arrCDR['Credit']} AND 
											Description <=> '{$arrCDR['Description']}' AND 
											Status NOT IN (".CDR_DUPLICATE.", ".CDR_RECHARGE.")
											ORDER BY Id DESC
											LIMIT 1";

$qryQuery	= new Query();
$mixResult	= $qryQuery->Execute($strFindDuplicateSQL);
if ($arrDuplicateCDR = $mixResult->fetch_assoc())
{
	$strMatchString			= ($arrDuplicateCDR['Status'] === CDR_DUPLICATE) ? 'duplicate' : 'recharge';
	CliEcho("!!! CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}");
	$arrCDR['Status']		= $arrDuplicateCDR['Status'];
}
else
{
	CliEcho("No Duplicate Found!");
}


?>
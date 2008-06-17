<?php

$arrInvoiceRun	= Array();
$arrInvoiceRun['InvoiceRun']	= "47c954b52e38f";


require_once("../../flex.require.php");


// Update ServiceRatePlan.LastChargedOn field
$qryUpdateLastChargedOn	= new Query();
$selBillServices		= new StatementSelect("ServiceTotal", "Service", "InvoiceRun = <InvoiceRun>");
$intTotal	= $selBillServices->Execute($arrInvoiceRun);
while ($arrService = $selBillServices->Fetch())
{
	// Update ServiceRatePlan
	$strQuery	= "UPDATE ServiceRatePlan SET LastChargedOn = CURDATE() WHERE Service = {$arrService['Service']} AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER By CreatedOn DESC LIMIT 1";
	if ($qryUpdateLastChargedOn->Execute($strQuery) === FALSE)
	{
		Debug($qryUpdateLastChargedOn->Error());
	}
}

CliEcho($intTotal);
?>
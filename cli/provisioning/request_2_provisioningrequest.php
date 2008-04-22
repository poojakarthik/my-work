<?php


require_once("../../flex.require.php");

// Statements
$intProvisioningRequest	= new StatementInsert("ProvisioningRequest");
$selRequest				= new StatementSelect(	"(Request JOIN Service ON Service.Id = Request.Service) LEFT JOIN ProvisioningLog ON ProvisioningLog.Request = Request.Id",
												"Request.*, Account, AccountGroup, FNN, ProvisioningLog.Description",
												"ProvisioningLog.Id = (SELECT MAX(Id) FROM ProvisioningLog PL2 WHERE Request = Request.Id)");

// Get Requests
$selRequest->Execute();
while ($arrRequest = $selRequest->Fetch())
{
	$arrProvisioningRequest	= Array();
	$arrProvisioningRequest['AccountGroup']			= $arrRequest['AccountGroup'];
	$arrProvisioningRequest['Account']				= $arrRequest['Account'];
	$arrProvisioningRequest['Service']				= $arrRequest['Service'];
	$arrProvisioningRequest['FNN']					= $arrRequest['FNN'];
	$arrProvisioningRequest['Employee']				= $arrRequest['Employee'];
	$arrProvisioningRequest['Carrier']				= $arrRequest['Carrier'];
	$arrProvisioningRequest['Type']					= $arrRequest['RequestType'];
	$arrProvisioningRequest['CarrierRef']			= $arrRequest['Sequence'];
	$arrProvisioningRequest['FileExport']			= NULL;
	$arrProvisioningRequest['Response']				= NULL;
	$arrProvisioningRequest['Description']			= $arrRequest['Description'];
	$arrProvisioningRequest['RequestedOn']			= $arrRequest['RequestDatetime'];
	$arrProvisioningRequest['AuthorisationDate']	= $arrRequest['RequestDatetime'];
	$arrProvisioningRequest['SentOn']				= $arrRequest['RequestDatetime'];
	$arrProvisioningRequest['LastUpdated']			= NULL;
	$arrProvisioningRequest['Status']				= $arrRequest['Status'];
	
	// Insert ProvisioningRequest
}




?>
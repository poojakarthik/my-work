<?php


require_once("../../flex.require.php");

// Statements
$insProvisioningRequest	= new StatementInsert("ProvisioningRequest");
$selRequest				= new StatementSelect(	"Request JOIN Service ON Service.Id = Request.Service",
												"Request.*, Account, AccountGroup, FNN",
												"1");

$selDescription			= new StatementSelect(	"ProvisioningLog",
												"Description",
												"Request = <Request>",
												"Id DESC",
												"1");

CliEcho("\n");

// Get Requests
$selRequest->Execute();
while ($arrRequest = $selRequest->Fetch())
{
	CliEcho(" + Request #".$arrRequest['Id']);
	
	if ($selDescription->Execute($arrRequest))
	{
		$arrDescription	= $selDescription->Fetch();
	}
	
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
	$arrProvisioningRequest['Description']			= $arrDescription['Description'];
	$arrProvisioningRequest['RequestedOn']			= $arrRequest['RequestDateTime'];
	$arrProvisioningRequest['AuthorisationDate']	= $arrRequest['RequestDateTime'];
	$arrProvisioningRequest['SentOn']				= $arrRequest['RequestDateTime'];
	$arrProvisioningRequest['LastUpdated']			= NULL;
	$arrProvisioningRequest['Status']				= $arrRequest['Status'];
	
	// Insert ProvisioningRequest
	if ($insProvisioningRequest->Execute($arrProvisioningRequest) === FALSE)
	{
		Debug($insProvisioningRequest->Error());
		die;
	}
}




?>
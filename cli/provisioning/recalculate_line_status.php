<?php

// Framework
require_once("../../flex.require.php");
$arrConfig			= LoadApplication();
$appProvisioning	= new ApplicationProvisioning();

$selServices	= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
$selResponses	= new StatementSelect("ProvisioningResponse JOIN provisioning_type ON provisioning_type.id = ProvisioningResponse.Type", "ProvisioningResponse.*", "provisioning_type.provisioning_type_nature = <Nature> AND ProvisioningResponse.Service = <Service> AND ProvisioningResponse.Status = ".RESPONSE_STATUS_IMPORTED, "ProvisioningResponse.EffectiveDate DESC, ProvisioningResponse.ImportedOn ASC, ProvisioningResponse.Id ASC");

CliEcho("\n[ RECALCULATING LINE STATUS ]\n");

// Select all non-Archived Landline Services
if ($selServices->Execute())
{
	while ($arrService = $selServices->Fetch())
	{
		CliEcho(" * {$arrService['Account']}::{$arrService['FNN']}...", FALSE);
		
		// DETERMINE CURRENT SERVICE LINE STATUS
		if ($selResponses->Execute(Array('Service' => $arrService['Id'], 'Nature' => REQUEST_TYPE_NATURE_FULL_SERVICE)) !== FALSE)
		{
			// Get all Responses
			$intEffectiveDate		= 0;
			$arrCurrentResponses	= Array();
			while ($arrResponse = $selResponses->Fetch())
			{
				if ($arrResponse)
				{
					// Is this Response on the last EffectiveDate?
					if ($intEffectiveDate <= strtotime($arrResponse['EffectiveDate']))
					{
						$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
						$arrCurrentResponses[]	= $arrResponse;
					}
				}
			}
			
			// Which of these Responses is current?  Apply to Service in the order they would have come in
			foreach ($arrCurrentResponses as $arrResponse)
			{
				$mixResponse	= ImportBase::UpdateLineStatus($arrResponse);
				if (is_string($mixResponse))
				{
					CliEcho($mixResponse);
				}
			}
			CliEcho("FS...", FALSE);
		}
		else
		{
			CliEcho("ERROR: There was an error with Service selResponses: ".$selResponses->Error());
			exit(2);
		}
		
		// DETERMINE CURRENT PROVISIONING LINE STATUS
		if ($selResponses->Execute(Array('Service' => $arrService['Id'], 'Nature' => REQUEST_TYPE_NATURE_PRESELECTION)) !== FALSE)
		{
			// Get all Responses
			$intEffectiveDate		= 0;
			$arrCurrentResponses	= Array();
			while ($arrResponse = $selResponses->Fetch())
			{
				if ($arrResponse)
				{
					// Is this Response on the last EffectiveDate?
					if ($intEffectiveDate <= strtotime($arrResponse['EffectiveDate']))
					{
						$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
						$arrCurrentResponses[]	= $arrResponse;
					}
				}
			}
			
			// Which of these Responses is current?  Apply to Service in the order they would have come in
			foreach ($arrCurrentResponses as $arrResponse)
			{
				$mixResponse	= ImportBase::UpdateLineStatus($arrResponse);
				if (is_string($mixResponse))
				{
					CliEcho($mixResponse);
				}
			}
			CliEcho("PS...");
		}
		else
		{
			CliEcho("ERROR: There was an error with Provisioning selResponses: ".$selResponses->Error());
			exit(2);
		}
	}
}
else
{
	CliEcho("ERROR: There was an error with selServices: ".$selServices->Error());
	exit(1);
}

?>
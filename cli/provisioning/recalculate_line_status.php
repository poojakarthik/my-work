<?php

// Framework
require_once("../../flex.require.php");
$arrConfig			= LoadApplication();
$appProvisioning	= new ApplicationProvisioning();

$selServices	= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
$selResponses	= new StatementSelect("ProvisioningResponse JOIN provisioning_type ON provisioning_type.id = ProvisioningResponse.Type", "ProvisioningResponse.*", "provisioning_type.provisioning_type_nature = <Nature> AND ProvisioningResponse.Service = <Service> AND ProvisioningResponse.Status = ".RESPONSE_STATUS_IMPORTED);

CliEcho("\n[ RECALCULATING LINE STATUS ]\n");

// Select all non-Archived Landline Services
$intCount	= 0;
if ($intServiceCount = $selServices->Execute())
{
	while ($arrService = $selServices->Fetch())
	{
		$intCount++;
		CliEcho(" * ($intCount/$intServiceCount){$arrService['Account']}::{$arrService['FNN']}...", FALSE);
		
		// DETERMINE CURRENT SERVICE LINE STATUS
		CliEcho("FS...", FALSE);
		if ($selResponses->Execute(Array('Service' => $arrService['Id'], 'Nature' => REQUEST_TYPE_NATURE_FULL_SERVICE)) !== FALSE)
		{
			WaitingIcon(TRUE);
			
			// Get all Responses
			$intEffectiveDate		= 0;
			$arrCurrentResponses	= Array();
			while ($arrResponse = $selResponses->Fetch())
			{
				WaitingIcon();
				if ($arrResponse)
				{
					// Is this Response on the last EffectiveDate?
					if ($intEffectiveDate < strtotime($arrResponse['EffectiveDate']))
					{
						$arrCurrentResponses	= Array();
					}
					if ($intEffectiveDate === strtotime($arrResponse['EffectiveDate']))
					{
						$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
						$arrCurrentResponses[]	= $arrResponse;
					}
				}
			}
			
			// Which of these Responses is current?  Apply to Service in the order they would have come in
			foreach ($arrCurrentResponses as $arrResponse)
			{
				WaitingIcon();
				$mixResponse	= ImportBase::UpdateLineStatus($arrResponse);
				if (is_string($mixResponse))
				{
					CliEcho($mixResponse);
				}
			}
		}
		else
		{
			CliEcho("ERROR: There was an error with Service selResponses: ".$selResponses->Error());
			exit(2);
		}
		
		// DETERMINE CURRENT PROVISIONING LINE STATUS
		CliEcho("PS...", FALSE);
		if ($selResponses->Execute(Array('Service' => $arrService['Id'], 'Nature' => REQUEST_TYPE_NATURE_PRESELECTION)) !== FALSE)
		{
			WaitingIcon(TRUE);
			
			// Get all Responses
			$intEffectiveDate		= 0;
			$arrCurrentResponses	= Array();
			while ($arrResponse = $selResponses->Fetch())
			{
				WaitingIcon();
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
				WaitingIcon();
				$mixResponse	= ImportBase::UpdateLineStatus($arrResponse);
				if (is_string($mixResponse))
				{
					CliEcho($mixResponse);
				}
			}
		}
		else
		{
			CliEcho("ERROR: There was an error with Provisioning selResponses: ".$selResponses->Error());
			exit(2);
		}
		CliEcho();
	}
}
else
{
	CliEcho("ERROR: There was an error with selServices: ".$selServices->Error());
	exit(1);
}
exit(0);





// WaitingIcon
function WaitingIcon($bolRestart = FALSE)
{
	static	$arrIcon	= Array('-', '\\', '|', '/');
	static	$intIndex	= 0;
	
	// Are we overwriting the last Icon?
	$strOutput	= "";
	if (!$bolRestart)
	{
		reset($arrIcon);
		$strOutput	= "\033[1D";
	}
	
	// Get the next Icon
	if (!$strIcon = next($arrIcon))
	{
		$strIcon	= reset($arrIcon);
	}
	CliEcho($strOutput.$strIcon, FALSE);
}

?>
<?php

// Framework
require_once("../../flex.require.php");
$arrConfig			= LoadApplication();
$appProvisioning	= new ApplicationProvisioning();

/*DEBUG QUERY*/$selServices	= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "Account = 1000154811 AND ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
//$selServices	= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
$selResponses	= new StatementSelect("(ProvisioningResponse JOIN provisioning_type ON provisioning_type.id = ProvisioningResponse.Type) JOIN FileImport ON FileImport.Id = ProvisioningResponse.FileImport", "ProvisioningResponse.*, FileImport.FileType", "provisioning_type.provisioning_type_nature = <Nature> AND ProvisioningResponse.Service = <Service> AND ProvisioningResponse.Status = ".RESPONSE_STATUS_IMPORTED);

// File Type Conversion Array (Key: Old Type; Value: New Type)
$arrFileTypeConvert	= Array();
$arrFileTypeConvert[PRV_UNITEL_DAILY_ORDER_RPT]		= FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER;
$arrFileTypeConvert[PRV_UNITEL_DAILY_STATUS_RPT]	= FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS;
$arrFileTypeConvert[PRV_UNITEL_BASKETS_RPT]			= FILE_IMPORT_PROVISIONING_UNITEL_BASKETS;
$arrFileTypeConvert[PRV_AAPT_ALL]					= FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER;
$arrFileTypeConvert[PRV_UNITEL_PRESELECTION_RPT]	= FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION;
$arrFileTypeConvert[PRV_AAPT_EOE_RETURN]			= FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN;
$arrFileTypeConvert[PRV_AAPT_LSD]					= FILE_IMPORT_PROVISIONING_AAPT_LSD;
$arrFileTypeConvert[PRV_AAPT_REJECT]				= FILE_IMPORT_PROVISIONING_AAPT_REJECT;
$arrFileTypeConvert[PRV_AAPT_LOSS]					= FILE_IMPORT_PROVISIONING_AAPT_LOSS;

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
				//Debug($arrResponse);
				$intFileType	= ($arrFileTypeConvert[$arrResponse['FileType']]) ? $arrFileTypeConvert[$arrResponse['FileType']] : $arrResponse['FileType'];
				$arrResponse	= $appProvisioning->_arrImportFiles[$arrResponse['Carrier']][$intFileType]->Normalise($arrResponse['Raw'], DONKEY);
				//Debug($arrResponse);
				
				// Is this Response on the last EffectiveDate?
				if ($intEffectiveDate < strtotime($arrResponse['EffectiveDate']))
				{
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate < ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$arrCurrentResponses	= Array();
					$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
				}
				if ($intEffectiveDate === strtotime($arrResponse['EffectiveDate']))
				{
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate === ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
					$arrCurrentResponses[]	= $arrResponse;
				}
			}
			
			WaitingIcon(TRUE);
			
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
				$intFileType	= ($arrFileTypeConvert[$arrResponse['FileType']]) ? $arrFileTypeConvert[$arrResponse['FileType']] : $arrResponse['FileType'];
				$arrResponse	= $appProvisioning->_arrImportFiles[$arrResponse['Carrier']][$intFileType]->Normalise($arrResponse['Raw'], DONKEY);
				
				// Is this Response on the last EffectiveDate?
				if ($intEffectiveDate < strtotime($arrResponse['EffectiveDate']))
				{
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate < ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$arrCurrentResponses	= Array();
					$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
				}
				if ($intEffectiveDate === strtotime($arrResponse['EffectiveDate']))
				{
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate === ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$intEffectiveDate		= strtotime($arrResponse['EffectiveDate']);
					$arrCurrentResponses[]	= $arrResponse;
				}
			}
			
			WaitingIcon(TRUE);
			
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
	static	$arrIcon	= Array(1, 2, 3, 4, 5, 6, 7, 8, 9);
	static	$intIndex	= 0;
	
	// Are we overwriting the last Icon?
	$strOutput	= "";
	if (!$bolRestart)
	{
		$strOutput	= "\033[1D";
	}
	else
	{
		reset($arrIcon);
	}
	
	// Get the next Icon
	if (!($strIcon = next($arrIcon)))
	{
		$strIcon	= reset($arrIcon);
	}
	CliEcho($strOutput.$strIcon, FALSE);
}

?>
<?php

// Framework
require_once("../../flex.require.php");
$arrConfig			= LoadApplication();
$appProvisioning	= new ApplicationProvisioning();

$bolUpdateAllFNNInstances	= false;
$bolMustHaveExistingStatus	= false;

///*DEBUG QUERY*/$selServices		= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "Account = 1000154811 AND ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
$selResponses		= new StatementSelect("(ProvisioningResponse JOIN provisioning_type ON provisioning_type.id = ProvisioningResponse.Type) JOIN FileImport ON FileImport.Id = ProvisioningResponse.FileImport", "ProvisioningResponse.*, FileImport.FileType", "provisioning_type.provisioning_type_nature = <Nature> AND ProvisioningResponse.Service = <Service> AND ProvisioningResponse.Status = ".RESPONSE_STATUS_IMPORTED);
$selLineStatus		= new StatementSelect("Service", "*", "Id = <Id>");
$updFNNLineStatus	= new StatementUpdate("Service", "FNN = <FNN> AND (LineStatusDate < <LineStatusDate> OR LineStatusDate IS NULL)", Array('LineStatus'=>NULL, 'LineStatusDate'=>NULL));

if ($bolMustHaveExistingStatus)
{
	$selServices		= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1 AND Service.LineStatus IS NOT NULL", "Account.Id, Service.FNN, Service.Id");
}
else
{
	$selServices		= new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
}


// File Type Conversion Array (Key: Old Type; Value: New Type)
$arrFileTypeConvert	= Array();
$arrFileTypeConvert[PRV_UNITEL_DAILY_ORDER_RPT]		= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER;
$arrFileTypeConvert[PRV_UNITEL_DAILY_STATUS_RPT]	= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS;
$arrFileTypeConvert[PRV_UNITEL_BASKETS_RPT]			= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS;
$arrFileTypeConvert[PRV_AAPT_ALL]					= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER;
$arrFileTypeConvert[PRV_UNITEL_PRESELECTION_RPT]	= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION;
$arrFileTypeConvert[PRV_AAPT_EOE_RETURN]			= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN;
$arrFileTypeConvert[PRV_AAPT_LSD]					= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD;
$arrFileTypeConvert[PRV_AAPT_REJECT]				= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT;
$arrFileTypeConvert[PRV_AAPT_LOSS]					= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS;

CliEcho("\n[ RECALCULATING LINE STATUS ]\n");

// Select all non-Archived Landline Services
$intCount		= 0;
$intTimeStart	= time();
if ($intServiceCount = $selServices->Execute())
{
	while ($arrService = $selServices->Fetch())
	{
		$intCount++;
		$intSplit	= time() - $intTimeStart;
		$fltPercent	= round(($intCount / $intServiceCount) * 100, 1);
		CliEcho(" * ($intCount/$intServiceCount {$fltPercent}% @ {$intSplit}s){$arrService['Account']}::{$arrService['FNN']}...", FALSE);
		
		// DETERMINE CURRENT SERVICE LINE STATUS
		CliEcho("FS Current: {$arrService['LineStatus']}::{$arrService['LineStatusDate']}", FALSE);
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
				if (!array_key_exists($appProvisioning->_arrImportFiles[$arrResponse['Carrier']][$intFileType]))
				{
					$arrDebug	= array(print_r($arrResponse, true), print_r($appProvisioning->_arrImportFiles, true));
					throw new Exception(implode("\n\n", $arrDebug));
				}
				
				$arrResponse	= array_merge($arrResponse, $appProvisioning->_arrImportFiles[$arrResponse['Carrier']][$intFileType]->Normalise($arrResponse['Raw'], DONKEY));
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
					throw new Exception($mixResponse);
				}
			}
			
			if ($selLineStatus->Execute($arrService) === FALSE)
			{
				throw new Exception($selLineStatus->Error());
			}
			$arrNewStatus	= $selLineStatus->Fetch();
			CliEcho("; New: {$arrNewStatus['LineStatus']}::{$arrNewStatus['LineStatusDate']}");
			
			// Update all Services with this FNN with this Status
			if ($bolUpdateAllFNNInstances && $arrNewStatus['LineStatus'])
			{
				if ($updFNNLineStatus->Execute($arrNewStatus, $arrNewStatus) === FALSE)
				{
					throw new Exception($updFNNLineStatus->Error());
				}
			}
		}
		else
		{
			throw new Exception($selResponses->Error());
		}
		/*
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
				$arrResponse	= array_merge($arrResponse, $appProvisioning->_arrImportFiles[$arrResponse['Carrier']][$intFileType]->Normalise($arrResponse['Raw'], DONKEY));
				
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
		*/
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
	static	$arrIcon	= Array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
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
	if (!($arrCurrentIcon = each($arrIcon)))
	{
		$strIcon	= reset($arrIcon);
	}
	else
	{
		$strIcon	= $arrCurrentIcon['value'];
	}
	CliEcho($strOutput.$strIcon, FALSE);
}

?>
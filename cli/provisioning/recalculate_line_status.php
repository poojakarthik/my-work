<?php

// Framework
//require_once("../../flex.require.php");
require_once('../../lib/classes/Flex.php');
Flex::load();

$arrConfig = LoadApplication();
$appProvisioning = new ApplicationProvisioning();

$bolUpdateAllFNNInstances = false;
$bolMustHaveExistingStatus = false;

///*DEBUG QUERY*/$selServices = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "Account = 1000154811 AND ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
$selResponses = new StatementSelect("(ProvisioningResponse JOIN provisioning_type ON provisioning_type.id = ProvisioningResponse.Type) JOIN FileImport ON FileImport.Id = ProvisioningResponse.FileImport", "ProvisioningResponse.*, FileImport.FileType", "provisioning_type.provisioning_type_nature = <Nature> AND ProvisioningResponse.Service = <Service> AND ProvisioningResponse.Status = ".RESPONSE_STATUS_IMPORTED);
$selLineStatus = new StatementSelect("Service", "*", "Id = <Id>");
$updFNNLineStatus = new StatementUpdate("Service", "FNN = <FNN> AND (LineStatusDate < <LineStatusDate> OR LineStatusDate IS NULL)", array('LineStatus'=>null, 'LineStatusDate'=>null));

if ($bolMustHaveExistingStatus) {
	$selServices = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1 AND Service.LineStatus IS NOT NULL", "Account.Id, Service.FNN, Service.Id");
} else {
	$selServices = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "ServiceType = 102 AND Service.Status != 403 AND Account.Archived != 1", "Account.Id, Service.FNN, Service.Id");
}

CliEcho("\n[ RECALCULATING LINE STATUS ]\n");

// Select all non-Archived Landline Services
$intCount = 0;
$intTimeStart = time();
$arrFileTypeConvert = array();
if ($intServiceCount = $selServices->Execute()) {
	while ($arrService = $selServices->Fetch()) {
		$intCount++;
		$intSplit = time() - $intTimeStart;
		$fltPercent = round(($intCount / $intServiceCount) * 100, 1);
		CliEcho(" * ($intCount/$intServiceCount {$fltPercent}% @ {$intSplit}s){$arrService['Account']}::{$arrService['FNN']}...", false);
		
		// DETERMINE CURRENT FULL SERVICE LINE STATUS
		CliEcho("FS Current: {$arrService['LineStatus']}::{$arrService['LineStatusDate']}", false);
		if ($selResponses->Execute(array('Service' => $arrService['Id'], 'Nature' => REQUEST_TYPE_NATURE_FULL_SERVICE)) !== false) {
			WaitingIcon(true);
			
			// Get all Responses
			$intEffectiveDate = 0;
			$arrCurrentResponses = array();
			while ($arrResponse = $selResponses->Fetch()) {
				WaitingIcon();
				
				//Debug($arrResponse);
				$intFileType = (isset($arrFileTypeConvert[$arrResponse['FileType']]) ? $arrFileTypeConvert[$arrResponse['FileType']] : $arrResponse['FileType']);
				if (!array_key_exists($intFileType, $appProvisioning->_arrImportFiles[$arrResponse['Carrier']])) {
					// Old file type -- no longer supported
					CliEcho("OLD FILE TYPE -- SKIPPED");
					continue;
				}
				
				// Is this Response on the last EffectiveDate?
				if ($intEffectiveDate < strtotime($arrResponse['EffectiveDate'])) {
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate < ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$arrCurrentResponses = array();
					$intEffectiveDate = strtotime($arrResponse['EffectiveDate']);
				}
				if ($intEffectiveDate === strtotime($arrResponse['EffectiveDate'])) {
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate === ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$intEffectiveDate = strtotime($arrResponse['EffectiveDate']);
					$arrCurrentResponses[] = $arrResponse;
				}
			}
			
			WaitingIcon(true);
			
			// Which of these Responses is current?  Apply to Service in the order they would have come in
			foreach ($arrCurrentResponses as $arrResponse) {
				WaitingIcon();
				
				$mixResponse = ImportBase::UpdateLineStatus($arrResponse);
				if (is_string($mixResponse)) {
					throw new Exception($mixResponse);
				}
			}
			
			if ($selLineStatus->Execute($arrService) === false) {
				throw new Exception_Database($selLineStatus->Error());
			}
			$arrNewStatus = $selLineStatus->Fetch();
			CliEcho("; New: {$arrNewStatus['LineStatus']}::{$arrNewStatus['LineStatusDate']}");
			
			// Update all Services with this FNN with this Status
			if ($bolUpdateAllFNNInstances && $arrNewStatus['LineStatus']) {
				if ($updFNNLineStatus->Execute($arrNewStatus, $arrNewStatus) === false) {
					throw new Exception_Database($updFNNLineStatus->Error());
				} else {
					CliEcho(" * Updated successfully");
				}
			}
		} else {
			throw new Exception_Database($selResponses->Error());
		}
		/*
		// DETERMINE CURRENT PRESELECTION LINE STATUS
		CliEcho("PS...", false);
		if ($selResponses->Execute(array('Service' => $arrService['Id'], 'Nature' => REQUEST_TYPE_NATURE_PRESELECTION)) !== false) {
			WaitingIcon(true);
			
			// Get all Responses
			$intEffectiveDate = 0;
			$arrCurrentResponses = array();
			while ($arrResponse = $selResponses->Fetch()) {
				WaitingIcon();
				$intFileType = ($arrFileTypeConvert[$arrResponse['FileType']]) ? $arrFileTypeConvert[$arrResponse['FileType']] : $arrResponse['FileType'];
				$arrResponse = array_merge($arrResponse, $appProvisioning->_arrImportFiles[$arrResponse['Carrier']][$intFileType]->Normalise($arrResponse['Raw'], DONKEY));
				
				// Is this Response on the last EffectiveDate?
				if ($intEffectiveDate < strtotime($arrResponse['EffectiveDate'])) {
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate < ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$arrCurrentResponses = array();
					$intEffectiveDate = strtotime($arrResponse['EffectiveDate']);
				}
				if ($intEffectiveDate === strtotime($arrResponse['EffectiveDate'])) {
					//CliEcho("(".date("Y-m-d H:i:s", $intEffectiveDate).") $intEffectiveDate === ".strtotime($arrResponse['EffectiveDate'])." ({$arrResponse['EffectiveDate']})");
					$intEffectiveDate = strtotime($arrResponse['EffectiveDate']);
					$arrCurrentResponses[] = $arrResponse;
				}
			}
			
			WaitingIcon(true);
			
			// Which of these Responses is current?  Apply to Service in the order they would have come in
			foreach ($arrCurrentResponses as $arrResponse) {
				WaitingIcon();
				
				$mixResponse = ImportBase::UpdateLineStatus($arrResponse);
				if (is_string($mixResponse)) {
					CliEcho($mixResponse);
				}
			}
		} else {
			CliEcho("ERROR: There was an error with Provisioning selResponses: ".$selResponses->Error());
			exit(2);
		}
		*/
		CliEcho();
	}
} else {
	CliEcho("ERROR: There was an error with selServices: ".$selServices->Error());
	exit(1);
}
exit(0);





// WaitingIcon
function WaitingIcon($bolRestart = false) {
	static $arrIcon = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
	static $intIndex = 0;
	
	// Are we overwriting the last Icon?
	$strOutput = "";
	if (!$bolRestart) {
		$strOutput = "\033[1D";
	} else {
		reset($arrIcon);
	}
	
	// Get the next Icon
	if (!($arrCurrentIcon = each($arrIcon))) {
		$strIcon = reset($arrIcon);
	} else {
		$strIcon = $arrCurrentIcon['value'];
	}
	CliEcho($strOutput.$strIcon, false);
}

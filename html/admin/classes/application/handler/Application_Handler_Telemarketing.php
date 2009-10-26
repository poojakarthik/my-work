<?php

class Application_Handler_Telemarketing extends Application_Handler
{
	static protected	$_aReconciliationColumns	=	array
														(
															'FNN'				=> 'FNN',
															'PROPOSED_FILENAME'	=> 'Proposed List File',
															'DATE_WASHED'		=> 'Date Washed',
															'WASH_OUTCOME'		=> 'Washing Outcome',
															'PERMITTED_START'	=> 'Permitted Period Start',
															'PERMITTED_END'		=> 'Permitted Period End',
															'DATE_DIALLED'		=> 'Date Dialled',
															'CALL_OUTCOME'		=> 'Call Outcome'
														);
	
	// Shows a history of Proposed Dialling Lists and their associated data
	public function History($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		
		// Build List of Breached Contracts and their recommended actions
		try
		{
			// Get list of Imported Proposed Dialler files
			// TODO
			
			$this->LoadPage('telemarketing_file_history', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	// Uploads a Proposed Dialling List file
	public function UploadProposedDiallingList($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception("Flex was unable to start a Transaction.  The Upload has been aborted.  Please try again shortly.");
			}
			
			$qryQuery	= new Query();
			
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to upload a Proposed Dialling List!" . (($bolVerboseErrors) ? ' But you do have GOD mode... wtf' : ''));
			}
			
			// Load the Dealer object
			$objDealer	= Dealer::getForId((int)$_POST['Telemarketing_ProposedUpload_Dealer']);
			
			// Get File Format Details
			$strSQL		= "SELECT * FROM CarrierModule WHERE Carrier = {$objDealer->carrierId} AND Type = ".MODULE_TYPE_TELEMARKETING_PROPOSED_IMPORT." AND Active = 1";
			$resResult	= $qryQuery->Execute($strSQL);
			if ($resResult === false)
			{
				throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error()."\n\n{$strSQL}" : '');
			}
			if (!($arrCarrierModule = $resResult->fetch_assoc()))
			{
				$strDealerName	= $objDealer->firstName . (($objDealer->lastName) ? ' '.$objDealer->lastName : '');
				throw new Exception("Flex does not support Proposed Dialling Lists from {$strDealerName}." . (($bolVerboseErrors) ? "\n\n".$qryQuery->Error() : ''));
			}
			
			// Check the File Name format
			if (!Resource_Type::validateFileName($arrCarrierModule['FileType'], $_POST['Telemarketing_ProposedUpload_File']['name']))
			{
				throw new Exception("'{$_POST['Telemarketing_ProposedUpload_File']['name']}' is not a valid file name.  Ensure that you are trying to upload the correct file, and try again.");
			}
			
			// Import the File (into FileImport)
			$strFriendlyFileName	= dirname($_FILES['Telemarketing_ProposedUpload_File']['tmp_name']).'/'.$_FILES['Telemarketing_ProposedUpload_File']['name'];
			move_uploaded_file($_FILES['Telemarketing_ProposedUpload_File']['tmp_name'], $strFriendlyFileName);
			try
			{
				$objFileImport	= File_Import::import($strFriendlyFileName, $arrCarrierModule['FileType'], $objDealer->carrierId, "FileName = <FileName>");
			}
			catch (Exception $eException)
			{
				throw new Exception("There was an internal error when importing the File.  If this problem occurs more than once, please notify YBS at support@ybs.net.au" . (($bolVerboseErrors) ? "\n".$eException->getMessage() : ''));
			}
			//unlink($strFriendlyFileName);
			
			// If the File was imported OK, then Normalise
			if ($objFileImport->Status === FILE_IMPORTED || $objFileImport->Status === FILE_COLLECTED)
			{
				// Import the Proposed FNNs into the telemarketing_fnn table
				$objNormaliser	= new $arrCarrierModule['Module']($objFileImport, (int)$_POST['Telemarketing_ProposedUpload_Vendor'], $objDealer->id);
				$arrErrors		= $objNormaliser->normalise();
				if ($arrErrors)
				{
					// Create a log dump
					$strLogFileName	= FILES_BASE_PATH.'logs/telemarketing/proposed/'.date('YmdHis').'_'.AuthenticatedUser()->GetUserId().'.log';
					@mkdir(dirname($strLogFileName), 0777, true);
					@file_put_contents($strLogFileName, implode("\n", $arrErrors));
					
					//throw new Exception("The uploaded file is invalid.  The were ".count($arrErrors)." errors encountered while importing.\nPlease ensure that you have selected the correct file, and try again.\nIf this message appears more than once, please contact YBS.");
				}
				
				// Update the FileImport Status to Imported
				$objFileImport->Status	= FILE_NORMALISED;
				$objFileImport->save();
				
				$arrDetailsToRender['Success']			= true;
				$arrDetailsToRender['Message']			= "The Proposed Dialling File '".basename($_FILES['Telemarketing_ProposedUpload_File']['name'])."' has been imported.  Your File Reference Id is '{$objFileImport->Id}'." . (($bolVerboseErrors && $arrErrors) ? "\nFlex encountered ".count($arrErrors)." non-fatal errors while processing the file.  For more information on these errors, please contact YBS." : '');
			}
			else
			{
				$arrDetailsToRender['Message']			= "The File could not be Imported";
				if ($objFileImport->Status === FILE_NOT_UNIQUE)
				{
					$arrDetailsToRender['Message']		.= " because a file with this Name already exists in Flex";
				}
				else
				{
					$arrDetailsToRender['Message']		.= ".  If you receive this error more than once, please notify YBS." . (($bolVerboseErrors) ? "(".GetConstantDescription($objFileImport->Status, 'FileStatus').")" : '');
				}
				$arrDetailsToRender['Success']			= false;
			}
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['file_import_id']	= $objFileImport->Id;
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
	
	// Uploads a Proposed Dialling List file
	public function DownloadDNCRWashList($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			$qryQuery				= new Query();
			
			$intFileImportId	= (int)$_REQUEST['Telemarketing_DNCRDownload_File'];
			
			
			// Get list of washed FNNs for this File
			$arrFNNs	= self::_washFNNsByImportFile($intFileImportId);
			
			// HACKHACKHACK: Assume we are dealing with the ACMA, and using their File Format			
			// Create DNCR Export File
			$objDNCRExport	= new Resource_Type_File_Export_Telemarketing_ACMA_DNCRExport(CARRIER_ACMA);
			$arrErrors		= $objDNCRExport->export($arrFNNs);
			
			$objFileExport	= $objDNCRExport->getFileExport();
			
			// Update each of the telemarketing_fnn_proposed records that are being exported
			foreach ($arrFNNs as $mixIndex=>$arrFNN)
			{
				$objFNN	= new Telemarketing_FNN_Proposed($arrFNN);
				$objFNN->do_not_call_file_export_id	= $objFileExport->Id;
				$objFNN->save();
			}
			
			// Send the File to be downloaded
			header('content-type: text/csv');
			header('content-disposition: attachment; filename="'.$objFileExport->FileName.'"');
			echo file_get_contents($objFileExport->Location);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		die;
	}
	
	// Uploads a Proposed Dialling List file
	public function UploadDNCRWashList($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception("Flex was unable to start a Transaction.  The Upload has been aborted.  Please try again shortly.");
			}
			
			$qryQuery	= new Query();
			
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to upload a Proposed Dialling List!" . (($bolVerboseErrors) ? ' But you do have GOD mode... wtf' : ''));
			}
			
			$intFileExportId	= (int)$_POST['Telemarketing_DNCRDownload_File'];
			
			// HACKHACKHACK: Assume we are dealing with the ACMA, and using their File Format
			$intCarrier		= CARRIER_ACMA;
			$intFileType	= RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_ACMA_DNCR_RESPONSE;
			
			// Check the File Name format
			if (!Resource_Type::validateFileName($intFileType, $_POST['Telemarketing_DNCRUpload_File']['name']))
			{
				throw new Exception("'{$_POST['Telemarketing_ProposedUpload_File']['name']}' is not a valid file name.  Ensure that you are trying to upload the correct file, and try again.");
			}
			
			// Import the File (into FileImport)
			$strFriendlyFileName	= dirname($_FILES['Telemarketing_DNCRUpload_File']['tmp_name']).'/'.$_FILES['Telemarketing_DNCRUpload_File']['name'];
			try
			{
				if (!move_uploaded_file($_FILES['Telemarketing_DNCRUpload_File']['tmp_name'], $strFriendlyFileName))
				{
					throw new Exception("Unable to move temporary file");
				}
				
				$objFileImport	= File_Import::import($strFriendlyFileName, $intFileType, $intCarrier, "FileName = <FileName>");
			}
			catch (Exception $eException)
			{
				throw new Exception("There was an internal error when importing the File.  If this problem occurs more than once, please notify YBS at support@ybs.net.au" . (($bolVerboseErrors) ? "\n".$eException->getMessage() : ''));
			}
			unlink($strFriendlyFileName);
			
			// If the File was imported OK, then Normalise
			if ($objFileImport->Status === FILE_IMPORTED || $objFileImport->Status === FILE_COLLECTED)
			{
				// Import the Blacklisted FNNs into the telemarketing_fnn_blacklist table
				$objNormaliser	= new Resource_Type_File_Import_Telemarketing_ACMA_DNCRResponse($objFileImport);
				$arrErrors		= $objNormaliser->normalise();
				if ($arrErrors)
				{
					// Create a log dump
					$strLogFileName	= FILES_BASE_PATH.'logs/telemarketing/dncrupload/'.date('YmdHis').'_'.AuthenticatedUser()->GetUserId().'.log';
					@mkdir(dirname($strLogFileName), 0777, true);
					file_put_contents($strLogFileName, implode("\n", $arrErrors));
					
					throw new Exception("The uploaded file is invalid.  The were ".count($arrErrors)." errors encountered while importing.\nPlease ensure that you have selected the correct file, and try again.\nIf this message appears more than once, please contact YBS.");
				}
				
				// Update the FileImport Status to Imported
				$objFileImport->Status	= FILE_NORMALISED;
				$objFileImport->save();
				
				// Update all telemarketing_fnn_proposed records with this file Id
				$resResult	= $qryQuery->Execute("UPDATE telemarketing_fnn_proposed SET do_not_call_file_import_id = {$objFileImport->Id} WHERE do_not_call_file_export_id = ".$intFileExportId);
				if ($resResult === false)
				{
					throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error() : '');
				}
				
				$arrDetailsToRender['Success']			= true;
				$arrDetailsToRender['Message']			= "The DNCR Wash File '".basename($_FILES['Telemarketing_DNCRUpload_File']['name'])."' has been imported.  Your File Reference Id is '{$objFileImport->Id}'." . (($bolVerboseErrors && $arrErrors) ? "\nThe following ".count($arrErrors)." non-fatal errors occurred:\n\n".implode("\n", $arrErrors) : '');
			}
			else
			{
				$arrDetailsToRender['Message']			= "The File could not be Imported";
				if ($objFileImport->Status === FILE_NOT_UNIQUE)
				{
					$arrDetailsToRender['Message']		.= " because a file with this Name already exists in Flex";
				}
				else
				{
					$arrDetailsToRender['Message']		.= ".  If you receive this error more than once, please notify YBS." . (($bolVerboseErrors) ? "(".GetConstantDescription($objFileImport->Status, 'FileStatus').")" : '');
				}
				$arrDetailsToRender['Success']			= false;
			}
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['file_import_id']	= $objFileImport->Id;
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
	
	// Uploads a Proposed Dialling List file
	public function DownloadPermittedDiallingList($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			$qryQuery				= new Query();
			
			$intFileImportId	= (int)$_REQUEST['Telemarketing_PermittedDownload_File'];
			$objFileImport		= new File_Import(array('Id'=>$intFileImportId), true);
			
			// Get list of washed FNNs for this File
			$arrFNNs	= self::_washFNNsByImportFile($intFileImportId);
			
			// Get File Format Details
			$strSQL		= "SELECT * FROM CarrierModule WHERE Carrier = {$objFileImport->Carrier} AND Type = ".MODULE_TYPE_TELEMARKETING_PERMITTED_EXPORT." AND Active = 1";
			$resResult	= $qryQuery->Execute($strSQL);
			if ($resResult === false)
			{
				throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error()."\n\n{$strSQL}" : '');
			}
			if (!($arrCarrierModule = $resResult->fetch_assoc()))
			{
				$strDealerName	= $objDealer->firstName . (($objDealer->lastName) ? ' '.$objDealer->lastName : '');
				throw new Exception("Flex does not support Permitted Dialling Lists for this Dealer." . (($bolVerboseErrors) ? "\n\n".$qryQuery->Error() : ''));
			}			
			
			// Create Permitted Dialling List
			$objDNCRExport	= new $arrCarrierModule['Module']($objFileImport->Carrier);
			$arrErrors		= $objDNCRExport->export($arrFNNs);
			
			$objFileExport	= $objDNCRExport->getFileExport();
			
			// Update each of the telemarketing_fnn_proposed records that are being exported
			foreach ($arrFNNs as $mixIndex=>$arrFNN)
			{
				$objFNN	= new Telemarketing_FNN_Proposed($arrFNN);
				$objFNN->permitted_list_file_export_id			= $objFileExport->Id;
				$objFNN->telemarketing_fnn_proposed_status_id	= TELEMARKETING_FNN_PROPOSED_STATUS_EXPORT;
				$objFNN->save();
			}
			
			// Send the File to be downloaded
			header('content-type: text/csv');
			header('content-disposition: attachment; filename="'.$objFileExport->FileName.'"');
			echo file_get_contents($objFileExport->Location);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		die;
	}
	
	private static function _washFNNsByImportFile($intFileImportId)
	{
		$strPath	= FILES_BASE_PATH."logs/telemarketing/wash_cache_".date("YmdHis").".log";
		@mkdir(dirname($strPath), 0777, true);
		$resLogFile	= fopen($strPath, 'w');
		
		$fltStartTime	= microtime(true);
		$fltSplit		= $fltStartTime;
		fwrite($resLogFile, "({$fltStartTime}) Started!\n");
		
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$qryQuery				= new Query();
		
		// Build a Cache of Internal Opt-Out and DNCR FNNs
		$resResult	= $qryQuery->Execute("SELECT fnn, telemarketing_fnn_blacklist_nature_id, cached_on, expired_on FROM telemarketing_fnn_blacklist WHERE expired_on > NOW()");
		$arrOptOut	= array();
		$arrDNCR	= array();
		if ($resResult === false)
		{
			throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error()."\n\n{$strSQL}" : '');
		}
		while ($arrBlacklist = $resResult->fetch_assoc())
		{
			switch ($arrBlacklist['telemarketing_fnn_blacklist_nature_id'])
			{
				case TELEMARKETING_FNN_BLACKLIST_NATURE_DNCR:
					$arrDNCR[$arrBlacklist['fnn']]		= array('cached_on'=>$arrBlacklist['cached_on'], 'expired_on'=>$arrBlacklist['expired_on']);
					break;
					
				case TELEMARKETING_FNN_BLACKLIST_NATURE_OPTOUT:
					$arrOptOut[$arrBlacklist['fnn']]	= true;
				default:
					break;
			}
		}
		
		$fltOldSplit	= $fltSplit;
		$fltSplit		= microtime(true);
		fwrite($resLogFile, "({$fltSplit}) Blacklist built! (".($fltSplit-$fltOldSplit)." seconds)\n");
		
		// Build a Cache of Active Service FNNs
		$resResult	= $qryQuery->Execute("SELECT FNN, Indial100 FROM Service WHERE Status = ".SERVICE_ACTIVE);
		$arrServiceCache	= array();
		if ($resResult === false)
		{
			throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error()."\n\n{$strSQL}" : '');
		}
		while ($arrService = $resResult->fetch_assoc())
		{
			$arrServiceCache[$arrService['FNN']]	= true;
			if ($arrService['Indial100'])
			{
				$strPrefix	= substr($arrService['FNN'], 0, -2);
				for ($intExtension = 0; $intExtension < 100; $intExtension++)
				{
					$arrServiceCache[$strPrefix.str_pad($intExtension, 2, '0', STR_PAD_LEFT)]	= true;
				}
			}
		}
		
		$fltOldSplit	= $fltSplit;
		$fltSplit		= microtime(true);
		fwrite($resLogFile, "({$fltSplit}) Active Service Cache built! (".($fltSplit-$fltOldSplit)." seconds)\n");
		
		// Build a Cache of Active Contacts
		$resResult	= $qryQuery->Execute("SELECT Phone, Fax, Mobile FROM Contact JOIN Account ON Account.PrimaryContact = Contact.Id WHERE Account.Archived = 0 AND Contact.Archived = 0");
		$arrContactCache	= array();
		if ($resResult === false)
		{
			throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error()."\n\n{$strSQL}" : '');
		}
		while ($arrContact = $resResult->fetch_assoc())
		{
			if ($arrContact['Phone'])	$arrContactCache[$arrContact['Phone']]	= true;
			if ($arrContact['Fax'])		$arrContactCache[$arrContact['Fax']]	= true;
			if ($arrContact['Mobile'])	$arrContactCache[$arrContact['Mobile']]	= true;
		}
		
		$fltOldSplit	= $fltSplit;
		$fltSplit		= microtime(true);
		fwrite($resLogFile, "({$fltSplit}) Active Contact Cache built! (".($fltSplit-$fltOldSplit)." seconds)\n");
		
		// Get list of FNNs for this File
		$arrFNNs	= Telemarketing_FNN_Proposed::getFor("proposed_list_file_import_id = {$intFileImportId} AND telemarketing_fnn_proposed_status_id = ".TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED, true);
		$intTotal	= count($arrFNNs);
		$intCount	= 0;
		$arrResult	= array();
		foreach ($arrFNNs as $mixIndex=>$arrFNN)
		{
			$intCount++;
			$fltLap	= microtime(true);
			
			// Wash against the Internal Opt-Out
			if ($arrOptOut[$arrFNN['fnn']])
			{
				// Blacklisted (Opt-Out)
				$objFNN	= new Telemarketing_FNN_Proposed($arrFNN);
				$objFNN->telemarketing_fnn_withheld_reason_id	= TELEMARKETING_FNN_WITHHELD_REASON_OPTOUT;
				$objFNN->telemarketing_fnn_proposed_status_id	= TELEMARKETING_FNN_PROPOSED_STATUS_WITHHELD;
				$objFNN->save();
				unset($arrFNNs[$mixIndex]);
				$strDescription	= "--OPTOUT";
				$arrResult['OPTOUT']++;
			}
			
			// Wash against the Internal DNCR Cache
			elseif ($arrDNCR[$arrFNN['fnn']])
			{
				// ACMA DNCR
				$objFNN	= new Telemarketing_FNN_Proposed($arrFNN);
				$objFNN->telemarketing_fnn_withheld_reason_id	= TELEMARKETING_FNN_WITHHELD_REASON_DNCR;
				$objFNN->telemarketing_fnn_proposed_status_id	= TELEMARKETING_FNN_PROPOSED_STATUS_WITHHELD;
				$objFNN->save();
				unset($arrFNNs[$mixIndex]);
				$strDescription	= "--DNCR {$arrDNCR[$arrFNN['fnn']]['cached_on']} >> {$arrDNCR[$arrFNN['fnn']]['expired_on']}";
				$arrResult['DNCR']++;
			}
			
			// Wash against Active Services in Flex
			elseif ($arrServiceCache[$arrFNN['fnn']])
			{
				// Currently in Flex
				$objFNN	= new Telemarketing_FNN_Proposed($arrFNN);
				$objFNN->telemarketing_fnn_withheld_reason_id	= TELEMARKETING_FNN_WITHHELD_REASON_FLEX_SERVICE;
				$objFNN->telemarketing_fnn_proposed_status_id	= TELEMARKETING_FNN_PROPOSED_STATUS_WITHHELD;
				$objFNN->save();
				unset($arrFNNs[$mixIndex]);
				$strDescription	= "--FLEX SERVICE";
				$arrResult['FLEX_SERVICE']++;
			}
			
			// Wash against Active Contacts in Flex
			elseif ($arrContactCache[$arrFNN['fnn']])
			{
				// Active Contact in Flex
				$objFNN	= new Telemarketing_FNN_Proposed($arrFNN);
				$objFNN->telemarketing_fnn_withheld_reason_id	= TELEMARKETING_FNN_WITHHELD_REASON_FLEX_CONTACT;
				$objFNN->telemarketing_fnn_proposed_status_id	= TELEMARKETING_FNN_PROPOSED_STATUS_WITHHELD;
				$objFNN->save();
				unset($arrFNNs[$mixIndex]);
				$strDescription	= "--FLEX CONTACT";
				$arrResult['FLEX_CONTACT']++;
			}
			
			else
			{
				// Ok to Call
				$strDescription	= "++ALLOWED";
				$arrResult['ALLOWED']++;
			}
			
			$fltSplit	= microtime(true);
			fwrite($resLogFile, "({$fltSplit}) FNN {$arrFNN['fnn']} ({$intCount}/{$intTotal}) completed in ".($fltSplit-$fltLap)." seconds ({$strDescription})\n");
		}
		
		// Totals
		fwrite($resLogFile, "\n".print_r($arrResult, true)."\n");
		
		fclose($resLogFile);
		
		// Return the washed list of FNNs
		return $arrFNNs;
	}
	
	public function UploadDiallerReport()
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		if (!DataAccess::getDataAccess()->TransactionStart())
		{
			throw new Exception("Flex was unable to start a Transaction.  The Upload has been aborted.  Please try again shortly.");
		}
		try
		{
			$qryQuery	= new Query();
			
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to upload a Dialler Report!" . (($bolVerboseErrors) ? ' But you do have GOD mode... wtf' : ''));
			}
			
			// Load the Dealer object
			$objDealer	= Dealer::getForId((int)$_POST['Telemarketing_DiallerReportUpload_Dealer']);
			
			// Get File Format Details
			$strSQL		= "SELECT * FROM CarrierModule WHERE Carrier = {$objDealer->carrierId} AND Type = ".MODULE_TYPE_TELEMARKETING_DIALLER_IMPORT." AND Active = 1";
			$resResult	= $qryQuery->Execute($strSQL);
			if ($resResult === false)
			{
				throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error()."\n\n{$strSQL}" : '');
			}
			if (!($arrCarrierModule = $resResult->fetch_assoc()))
			{
				$strDealerName	= $objDealer->firstName . (($objDealer->lastName) ? ' '.$objDealer->lastName : '');
				throw new Exception("Flex does not support Dialler Reports from {$strDealerName}." . (($bolVerboseErrors) ? "\n\n".$qryQuery->Error() : ''));
			}
			
			// Check the File Name format
			if (!Resource_Type::validateFileName($arrCarrierModule['FileType'], $_POST['Telemarketing_DiallerReportUpload_File']['name']))
			{
				throw new Exception("'{$_POST['Telemarketing_ProposedUpload_File']['name']}' is not a valid file name.  Ensure that you are trying to upload the correct file, and try again.");
			}
			
			// Import the File (into FileImport)
			$strFriendlyFileName	= dirname($_FILES['Telemarketing_DiallerReportUpload_File']['tmp_name']).'/'.$_FILES['Telemarketing_DiallerReportUpload_File']['name'];
			move_uploaded_file($_FILES['Telemarketing_DiallerReportUpload_File']['tmp_name'], $strFriendlyFileName);
			try
			{
				$objFileImport	= File_Import::import($strFriendlyFileName, $arrCarrierModule['FileType'], $objDealer->carrierId, "FileName = <FileName>");
			}
			catch (Exception $eException)
			{
				throw new Exception("There was an internal error when importing the File.  If this problem occurs more than once, please notify YBS at support@ybs.net.au" . (($bolVerboseErrors) ? "\n".$eException->getMessage() : ''));
			}
			//unlink($strFriendlyFileName);
			
			// If the File was imported OK, then Normalise
			if ($objFileImport->Status === FILE_IMPORTED || $objFileImport->Status === FILE_COLLECTED)
			{
				// Import the Dialled FNNs into the telemarketing_fnn_dialled table
				$objNormaliser	= new $arrCarrierModule['Module']($objFileImport, (int)$_POST['Telemarketing_DiallerReportUpload_Vendor'], $objDealer->id);
				$arrErrors		= $objNormaliser->normalise();
				if ($arrErrors)
				{
					// Create a log dump
					$strLogFileName	= FILES_BASE_PATH.'logs/telemarketing/dialler/'.date('YmdHis').'_'.AuthenticatedUser()->GetUserId().'.log';
					@mkdir(dirname($strLogFileName), 0777, true);
					@file_put_contents($strLogFileName, implode("\n", $arrErrors));
					
					//throw new Exception("The uploaded file is invalid.  The were ".count($arrErrors)." errors encountered while importing.\nPlease ensure that you have selected the correct file, and try again.\nIf this message appears more than once, please contact YBS.");
				}
				
				// Update the FileImport Status to Imported
				$objFileImport->Status	= FILE_NORMALISED;
				$objFileImport->save();
				
				$arrDetailsToRender['Success']			= true;
				$arrDetailsToRender['Message']			= "The Dialler Report '".basename($_FILES['Telemarketing_DiallerReportUpload_File']['name'])."' has been imported.  Your File Reference Id is '{$objFileImport->Id}'." . (($bolVerboseErrors && $arrErrors) ? "\nFlex encountered ".count($arrErrors)." non-fatal errors while processing the file.  For more information on these errors, please contact YBS." : '');
			}
			else
			{
				$arrDetailsToRender['Message']			= "The File could not be Imported";
				if ($objFileImport->Status === FILE_NOT_UNIQUE)
				{
					$arrDetailsToRender['Message']		.= " because a file with this Name already exists in Flex";
				}
				else
				{
					$arrDetailsToRender['Message']		.= ".  If you receive this error more than once, please notify YBS." . (($bolVerboseErrors) ? "(".GetConstantDescription($objFileImport->Status, 'FileStatus').")" : '');
				}
				$arrDetailsToRender['Success']			= false;
			}
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['file_import_id']	= $objFileImport->Id;
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
	
	// Downloads a Call Reconciliation Report
	public function DownloadCallReconciliationReport($subPath)
	{
		$bVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$aDetailsToRender	= array();
		try
		{
			$oQuery				= new Query();
			
			$iFileImportId	= (int)$_REQUEST['Telemarketing_CallReconciliationDownload_File'];
			$oFileImport	= new File_Import(array('Id'=>$iFileImportId), true);
			
			$oCSVFile	= new File_CSV();
			$oCSVFile->setColumns(array_values(self::$_aColumns));
			
			// Get list of FNNs in the Dialler Report, and try to match them up to FNNs we've previously permitted
			$oFNNsResult	= $oQuery->Execute("	SELECT		tfd.id			AS telemarketing_fnn_dialled_id,
																tfp.id			AS telemarketing_fnn_proposed_id
													
													FROM		telemarketing_fnn_dialled tfd
																LEFT JOIN telemarketing_fnn_proposed tfp ON (tfd.fnn = tfp.fnn AND CAST(tfp.call_period_start AS DATE) <= CAST(tfd.dialled_on AS DATE) AND tfd.dealer_id = tfp.dealer_id)
													
													WHERE		tfd.file_import_id = {$oFileImport->Id}
																AND tfp.id =	(
																				SELECT		MAX(id)
																				FROM		telemarketing_fnn_proposed
																							JOIN telemarketing_fnn_proposed_status tfps ON (tfps.id = telemarketing_fnn_proposed.telemarketing_fnn_proposed_status_id)
																				WHERE		tfd.fnn = fnn
																							AND CAST(call_period_start AS DATE) <= CAST(tfd.dialled_on AS DATE)
																							AND tfd.dealer_id = tfp.dealer_id
																				ORDER BY	(CAST(call_period_end AS DATE) >= CAST(tfd.dialled_on AS DATE) AND tfps.const_name = 'TELEMARKETING_FNN_PROPOSED_STATUS_EXPORT') DESC,
																							CAST(call_period_end AS DATE) >= CAST(tfd.dialled_on AS DATE) DESC,
																							tfps.const_name = 'TELEMARKETING_FNN_PROPOSED_STATUS_EXPORT' DESC,
																							call_period_start DESC
																				LIMIT		1
																			)");
			if ($oFNNsResult === false)
			{
				throw new Exception($oQuery->Error());
			}
			$aFNNs	= array();
			while ($aFNN = $oFNNsResult->fetch_assoc())
			{
				$aRendered	= array();
				
				$oTelemarketingDialledFNN	= Telemarketing_FNN_Dialled::getForId($aFNN['telemarketing_fnn_dialled_id']);
				
				$aRendered[self::$_aColumns['FNN']]					= $aFNN['oTelemarketingDialledFNN']->fnn;
				$aRendered[self::$_aColumns['DATE_DIALLED']]		= $oTelemarketingDialledFNN->dialled_on;
				$aRendered[self::$_aColumns['CALL_OUTCOME']]		= Telemarketing_FNN_Dialled_Result::getForId($oTelemarketingDialledFNN->telemarketing_fnn_dialled_result_id)->description;
				
				// Some FNNs will not have been permitted
				if ($aFNN['telemarketing_fnn_proposed_id'])
				{
					$oTelemarketingProposedFNN	= Telemarketing_FNN_Proposed::getForId($aFNN['telemarketing_fnn_proposed_id']);
					
					$aRendered[self::$_aColumns['PROPOSED_FILENAME']]	= File_Import::getForId($oTelemarketingProposedFNN->proposed_list_file_import_id)->FileName;
					$aRendered[self::$_aColumns['DATE_WASHED']]			= File_Export::getForId($oTelemarketingProposedFNN->permitted_list_file_export_id)->ExportedOn;
					$aRendered[self::$_aColumns['WASH_OUTCOME']]		= ($oTelemarketingProposedFNN->telemarketing_fnn_withheld_reason_id) ? 'Withheld: ' . Telemarketing_FNN_Withheld_Reason::getForId($oTelemarketingProposedFNN->telemarketing_fnn_withheld_reason_id)->description : 'Permitted';
					$aRendered[self::$_aColumns['PERMITTED_START']]		= $oTelemarketingProposedFNN->call_period_start;
					$aRendered[self::$_aColumns['PERMITTED_END']]		= $oTelemarketingProposedFNN->call_period_end;
				}
				
				$oCSVFile->addRow($aRendered); 
			}
			
			// Dump the data to an export string
			$sCSVContents	= $oCSVFile->save();
			
			// Send the File to be downloaded
			$sFileName	= "reconciled_{$oFileImport->FileName}.csv";
			header('content-type: text/csv');
			header('content-disposition: attachment; filename="'.$sFileName.'"');
			echo file_get_contents($sCSVContents);
		}
		catch (Exception $oException)
		{
			$aDetailsToRender['Success']	= false;
			$aDetailsToRender['Message']	= $oException->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		die;
	}
}
?>
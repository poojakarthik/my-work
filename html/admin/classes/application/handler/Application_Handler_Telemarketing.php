<?php

class Application_Handler_Telemarketing extends Application_Handler
{
	// Shows a history of Proposed Dialling Lists and their associated data
	public function History($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
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
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
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
			unlink($strFriendlyFileName);
			
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
				$arrDetailsToRender['Message']			= "The Proposed Dialling File '".basename($_FILES['Telemarketing_ProposedUpload_File']['name'])."' has been imported.  Your File Reference Id is '{$objFileImport->Id}'." . (($bolVerboseErrors && $arrErrors) ? "\nThe following ".count($arrErrors)." non-fatal errors occurred:\n\n".implode("\n", $arrErrors) : '');
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
			$qryQuery	= new Query();
			
			throw new Exception("DNCR Downloading is not supported!");
			
			// HACKHACKHACK: Assume we are dealing with the ACMA, and using their File Format
			
			// Create DNCR Export File
			$objDNCRExport	= new Resource_Type_File_Export_Telemarketing_ACMA_DNCRExport();
			$objFileExport	= $objDNCRExport->getFileExport();
			
			// Send the File to be downloaded
			flush();
			header('content-type: text/csv');
			echo file_get_contents($objFileExport->Location);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}
?>
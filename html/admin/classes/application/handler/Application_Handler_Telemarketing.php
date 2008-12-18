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
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to upload a Proposed Dialling List");
			}
			
			throw new Exception("Uploading not supported yet! ".print_r($_FILES, true).print_r($_REQUEST, true));
			
			// Load the Dealer object
			$objDealer	= Dealer::getForId((int)$_POST['Telemarketing_ProposedUpload_Dealer']);
			
			// Get File Format Details
			$strSQL		= "SELECT FileType FROM CarrierModule WHERE Carrier = {$objDealer->carrier_id} AND Type = ".MODULE_TYPE_TELEMARKETING_INPUT." AND Active = 1";
			$resResult	= $qryQuery->Execute($strSQL);
			if ($resResult === false)
			{
				throw new Exception("There was an internal database error.  Please notify YBS of this error." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error() : '');
			}
			if (!($arrCarrierModule = $resResult->fetch_assoc()))
			{
				$strDealerName	= $objDealer->FirstName . ($objDealer->LastName) ? ' '.$objDealer->LastName : '';
				throw new Exception("Flex does not support Proposed Dialling Lists from {$strDealerName}." . ($bolVerboseErrors) ? "\n\n".$qryQuery->Error() : '');
			}
			
			// Check the File Name format
			if (!Resource_Type::validateFileName($arrCarrierModule['FileType'], $_POST['Telemarketing_ProposedUpload_File']['name']))
			{
				throw new Exception("'$_POST['Telemarketing_ProposedUpload_File']['name']' is not a valid file name.  Ensure that you are trying to upload the correct file, and try again.");
			}
			
			// Import the File (into FileImport)
			try
			{
				$objFileImport	= File_Import::import($_FILES['Telemarketing_ProposedUpload_File']['tmp_name'], $arrCarrierModule['FileType'], $objDealer->carrierId, "FileName = <FileName>");
			}
			catch (Exception $eException)
			{
				throw new Exception("There was an internal error when importing the File.  If this problem occurs more than once, please notify YBS at support@ybs.net.au");
			}
			
			// Get the CarrierModule Record for this Carrier/FileType
			$resResult	= $qryQuery->Execute("SELECT * FROM CarrierModule WHERE Carrier = {$objFileImport->Carrier} AND Type = ".MODULE_TYPE_TELEMARKETING_PROPOSED." AND FileType = {$objFileImport->FileType}");
			if ($resResult === false)
			{
				throw new Exception($qryQuery->Error());
			}
			$arrCarrierModule	= $resResult->fetch_assoc();
			
			// Import the Proposed FNNs into the telemarketing_fnn table
			$objNormaliser	= new {$arrCarrierModule['Module']}($objFileImport, (int)$_POST['Telemarketing_ProposedUpload_Vendor'], $objDealer->id);
			$arrErrors		= $objNormaliser->normalise();
			
			// Update the FileImport Status to Imported
			$objFileImport->Status;
			$objFileImport->save();
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['Success']			= true;
			$arrDetailsToRender['Message']			= "The Proposed Dialling File '".basename($_FILES['Telemarketing_ProposedUpload_File']['name'])."' has been imported.  Your File Reference Id is '{$objFileImport->Id}'." . ($bolVerboseErrors && $arrErrors) ? "<br />\nThe following non-fatal errors occurred:<br />\n".implode("<br />\n", $arrErrors) : '';
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
}
?>
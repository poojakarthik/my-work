<?php

class JSON_Handler_Telemarketing_Wash extends JSON_Handler
{
	
	public function getCallCentrePermissions()
	{
		return AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			// Get list of Call Centres
			$arrCallCentres				= Dealer::getCallCentres();
			$arrCallCentrePermissions	= array();
			foreach ($arrCallCentres as $objDealer)
			{
				$arrCallCentrePermissions[$objDealer->id]	= $objDealer->toArray();
			}
			
			// Get list of Vendors
			$arrCustomerGroups	= Customer_Group::getAll();
			$arrVendors			= array();
			foreach ($arrCustomerGroups as $objCustomerGroup)
			{
				$arrVendors[$objCustomerGroup->id]	= array('id' => $objCustomerGroup->id, 'externalName' => $objCustomerGroup->externalName);
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"					=> TRUE,
							"arrCallCentrePermissions"	=> $arrCallCentrePermissions,
							"arrVendors"				=> $arrVendors
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
	
	public function getImportedFiles()
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			$qryQuery	= new Query();
			
			// Get list of Imported Files
			$resResult	= $qryQuery->Execute("SELECT FileImport.Id as file_import_id, FileImport.FileName AS file_name, FileImport.ImportedOn as file_imported_on, tfp.dealer_id, dealer.first_name AS dealer_first_name, dealer.last_name AS dealer_last_name, tfp.customer_group_id, CustomerGroup.ExternalName AS customer_group_name " .
												"FROM (( FileImport JOIN telemarketing_fnn_proposed tfp ON tfp.proposed_list_file_import_id = FileImport.Id) JOIN CustomerGroup ON CustomerGroup.Id = tfp.customer_group_id) JOIN dealer ON dealer.id = tfp.dealer_id " .
												"WHERE tfp.do_not_call_file_export_id IS NULL AND tfp.telemarketing_fnn_proposed_status_id = ".TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED." " .
												"GROUP BY FileImport.Id " .
												"ORDER BY file_imported_on DESC, file_name ASC");
			if ($resResult === false)
			{
				throw new Exception("There was an error retrieving the required data for this page.  If this problem continues to occur, please notify YBS." . (($bolVerboseErrors) ? "\n".$qryQuery->Error() : ''));
			}
			$arrImportedFiles	= array();
			while ($arrImportedFile = $resResult->fetch_assoc())
			{
				$arrImportedFiles[$arrImportedFile['file_import_id']]	= $arrImportedFile;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> TRUE,
							"arrImportedFiles"	=> $arrImportedFiles
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
	
	public function getDNCRFiles()
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			$qryQuery	= new Query();
			
			// Get list of Imported Files
			$resResult	= $qryQuery->Execute("SELECT FileExport.Id as file_export_id, FileExport.FileName AS file_name, FileExport.ExportedOn as file_exported_on " .
												"FROM FileExport JOIN telemarketing_fnn_proposed tfp ON tfp.do_not_call_file_export_id = FileExport.Id " .
												"WHERE tfp.do_not_call_file_import_id IS NULL AND tfp.telemarketing_fnn_proposed_status_id = ".TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED." " .
												"GROUP BY FileExport.Id " .
												"ORDER BY file_exported_on DESC, file_name ASC");
			if ($resResult === false)
			{
				throw new Exception("There was an error retrieving the required data for this page.  If this problem continues to occur, please notify YBS." . (($bolVerboseErrors) ? "\n".$qryQuery->Error() : ''));
			}
			$arrExportedFiles	= array();
			while ($arrExportedFile = $resResult->fetch_assoc())
			{
				$arrExportedFiles[$arrExportedFile['file_export_id']]	= $arrExportedFile;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> TRUE,
							"arrExportedFiles"	=> $arrExportedFiles
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
	
	public function getExportReadyFiles()
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		try
		{
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				return array(
								'Success'			=> false,
								'ErrorMessage'		=> "Insufficient privileges",
								'HasPermissions'	=> false,
							);
			}
			
			$qryQuery	= new Query();
			
			// Get list of Imported Files
			$resResult	= $qryQuery->Execute("SELECT FileImport.Id as file_import_id, FileImport.FileName AS file_name, FileImport.ImportedOn as file_imported_on, tfp.dealer_id, dealer.first_name AS dealer_first_name, dealer.last_name AS dealer_last_name, tfp.customer_group_id, CustomerGroup.ExternalName AS customer_group_name " .
												"FROM (( FileImport JOIN telemarketing_fnn_proposed tfp ON tfp.proposed_list_file_import_id = FileImport.Id) JOIN CustomerGroup ON CustomerGroup.Id = tfp.customer_group_id) JOIN dealer ON dealer.id = tfp.dealer_id " .
												"WHERE tfp.do_not_call_file_export_id IS NOT NULL AND tfp.do_not_call_file_import_id IS NOT NULL AND tfp.telemarketing_fnn_proposed_status_id = ".TELEMARKETING_FNN_PROPOSED_STATUS_IMPORTED." " .
												"GROUP BY FileImport.Id " .
												"ORDER BY file_imported_on DESC, file_name ASC");
			if ($resResult === false)
			{
				throw new Exception("There was an error retrieving the required data for this page.  If this problem continues to occur, please notify YBS." . (($bolVerboseErrors) ? "\n".$qryQuery->Error() : ''));
			}
			$arrImportedFiles	= array();
			while ($arrImportedFile = $resResult->fetch_assoc())
			{
				$arrImportedFiles[$arrImportedFile['file_import_id']]	= $arrImportedFile;
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"			=> TRUE,
							"arrImportedFiles"	=> $arrImportedFiles
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ErrorMessage"	=> 'ERROR: '.$e->getMessage()
						);
		}
	}
}

?>

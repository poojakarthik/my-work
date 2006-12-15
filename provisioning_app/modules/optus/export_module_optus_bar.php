<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_optus_bar
//----------------------------------------------------------------------------//
/**
 * module_export_optus_bar
 *
 * Optus Export Module for the provisioning engine (Barring)
 *
 * Optus Export Module for the provisioning engine (Barring)
 *
 * @file		module_export_optus_bar.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportOptusBar
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportOptusBar
 *
 * Optus Export Module for the provisioning engine (Barring)
 *
 * Optus Export Module for the provisioning engine. (Barring)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportOptusBar
 */
 class ProvisioningModuleExportOptusBar extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportOptusBar
	 *
	 * Constructor method for ProvisioningModuleExportOptusBar
	 *
	 * @return		ProvisioningModuleExportOptusBar
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName 	= "Optus";
		
		parent::__construct($ptrDB);
		
		$this->_selGetRequests	= new StatementSelect("Requests", "*", "Carrier = ".CARRIER_OPTUS." AND RequestType = ".REQUEST_BAR_SOFT."Status = ".REQUEST_STATUS_WAITING);
 	}

  	//------------------------------------------------------------------------//
	// BuildRequest()
	//------------------------------------------------------------------------//
	/**
	 * BuildRequest()
	 *
	 * Builds a request file
	 *
	 * Builds a request file to be sent off, based on info from the DB
	 *
	 * @param		array		$arrRequest		Array of information on the request to generate
	 * 											Taken straight from the DB
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function BuildRequest($arrRequest)
	{
		// Clean the request array
		$arrBuiltRequest = Array();
				
		$arrBuiltRequest['ServiceNumber']		= str_pad($arrRequest['FNN'], 48, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['BillableAccountNo']	= str_pad(CUSTOMER_NUMBER_OPTUS, 14, "0", STR_PAD_LEFT);
		$arrBuiltRequest['ServiceType']			= str_pad("UT", 10, " ", STR_PAD_RIGHT);
		$arrBuiltRequest['CustomerReference']	= str_pad("", 10, " ", STR_PAD_RIGHT);	// No Reference needed
		
		// Append to the array for this file
		$this->_arrPreselectionRecords[]		= implode("|", $arrBuiltRequest);
	} 	
 	
  	//------------------------------------------------------------------------//
	// SendRequest()
	//------------------------------------------------------------------------//
	/**
	 * SendRequest()
	 *
	 * Sends the current request
	 *
	 * Sends the current request
	 *
	 * @return		integer						Number of requests sent in the file
	 *
	 * @method
	 */
 	function SendRequest()
	{
		// Build Header Row
		$strPreselectionFilename	= "LOCL_".CUSTOMER_NUMBER_OPTUS."_".date("YmdHis").".dat";
		$strPreselectionHeaderRow	= $strPreselectionFilename;
		
		// Get list of requests to generate
		$this->_selGetRequests->Execute();
		$arrResults = $this->_selGetRequests->FetchAll();
		
		$intNumPreselectionRecords	= count($this->_arrPreselectionRecords);
	
		// Build Footer Rows
		$strPreselectionFooterRow	= "T".str_pad($intNumPreselectionRecords, 5, "0", STR_PAD_LEFT);
		
		// Create Local Preselection File
		if($intNumPreselectionRecords > 0)
		{
			// Only do this if there are records to write
			$resPreselectionFile = fopen(OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename, "w");
			fwrite($resPreselectionFile, $strPreselectionHeaderRow."\n");
			
			foreach($this->_arrPreselectionRecords as $strRecord)
			{
				fwrite($resPreselectionFile, $strRecord."\n");
			}
			
			fwrite($resPreselectionFile, $strPreselectionFooterRow."\n");
			fclose($resPreselectionFile);
		}
		
		// Upload to FTP
		/* TODO: Uncomment this later on
		$resFTPConnection = ftp_connect(OPTUS_PROVISIONING_SERVER);
		ftp_login($resFTPConnection, OPTUS_PROVISIONING_USERNAME, OPTUS_PROVISIONING_PASSWORD);
		
		if(file_exists(OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename))
		{
			// Upload the Preselection File
			ftp_chdir($resFTPConnection, OPTUS_REMOTE_PRESELECTION_DIR);
			ftp_put($resFTPConnection, $strPreselectionFilename, OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
		}
		
		ftp_close($resFTPConnection);
		*/
		
		// Return the number of records uploaded
		return $intNumPreselectionRecords;
	} 	
	
 }

?>

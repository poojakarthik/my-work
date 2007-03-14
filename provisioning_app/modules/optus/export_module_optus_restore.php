<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_optus_restore
//----------------------------------------------------------------------------//
/**
 * module_export_optus_restore
 *
 * Optus Export Module for the provisioning engine (Restore)
 *
 * Optus Export Module for the provisioning engine (Restyore)
 *
 * @file		module_export_optus_restore.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportOptusRestore
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportOptusRestore
 *
 * Optus Export Module for the provisioning engine (Restore)
 *
 * Optus Export Module for the provisioning engine. (Restore)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportOptusRestore
 */
 class ProvisioningModuleExportOptusRestore extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportOptusRestore
	 *
	 * Constructor method for ProvisioningModuleExportOptusRestore
	 *
	 * @return		ProvisioningModuleExportOptusRestore
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName 	= "Optus";
		$this->_intCarrier		= CARRIER_OPTUS;
		
		parent::__construct($ptrDB);
		
		//$this->_selGetRequests	= new StatementSelect("Requests", "*", "Carrier = ".CARRIER_OPTUS." AND RequestType = ".REQUEST_UNBAR_SOFT."Status = ".REQUEST_STATUS_WAITING);
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
		// Get the FNN
		$selFNN = new StatementSelect("Service", "FNN", "Id = <Service>");
		$selFNN->Execute(Array('Service' => $arrRequest['Service']));
		$arrFNN = $selFNN->Fetch();
		
		// Append to the array for this file
		$this->_arrPreselectionRecords[]		= $arrFNN['FNN'];
		
		$this->_arrLog['Request']		= $arrRequest['Id'];
		$this->_arrLog['Service']		= $arrRequest['Service'];
		$this->_arrLog['Type']			= $arrRequest['RequestType'];
		$this->_arrLog['Description']	= "Request Sent Successfully";
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
		$intNumPreselectionRecords	= count($this->_arrPreselectionRecords);
		
		if($intNumPreselectionRecords > 0)
		{
			// Generate Excel 5 file
			$xlsBarring					= new PhpSimpleXlsGen();
			$xlsBarring->totalcol		= 4;
			$strPreselectionFilename	= OPTUS_LOCAL_PRESELECTION_DIR."restore_".date("Hi_Ymd").".xls";
		
			// Add header row
			$xlsBarring->InsertText('Service Number');
			$xlsBarring->InsertText('Billable Account Number');
			$xlsBarring->InsertText('Service Type');
			$xlsBarring->InsertText('Customer Reference');

			// add data rows
			foreach($this->_arrPreselectionRecords as $strFNN)
			{
				$xlsBarring->NewLine();
				$xlsBarring->InsertText($strFNN);
				$xlsBarring->InsertText(CUSTOMER_NUMBER_OPTUS);
				$xlsBarring->InsertText('UT');
				$xlsBarring->InsertText('');
			}
			
			// Write output
			$xlsBarring->SaveFile($strPreselectionFilename);
			
			// Email to Optus (as an attachment)
			//mail_attachment("provisioning@voiptel.com.au", "rich@voiptelsystems.com.au", "Restoration File", "Attached: Telco Blue Automatically Generated Restoration Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename)
			//mail_attachment("provisioning@voiptel.com.au", "long.distance.spsg@optus.com.au", "Restoration File", "Attached: Telco Blue Automatically Generated Restoration Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
			if (!mail_attachment("provisioning@voiptel.com.au", "rich@voiptelsystems.com.au", "Restoration File", "Attached: Telco Blue Automatically Generated Restoration Request File", $strPreselectionFilename))
			{
				Debug("Email failed!");
				return FALSE;
			}
		}
		
		// Return the number of records uploaded
		return $intNumPreselectionRecords;
	} 	
	
 }

?>

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
		
		return TRUE;
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
			$this->_bolSending = TRUE;
			
			$strPreselectionFilename	= OPTUS_LOCAL_PRESELECTION_DIR."unbar_".date("Hi_Ymd").".xls";
			
			// Generate Excel 5 Workbook
			$wkbWorkbook = new Spreadsheet_Excel_Writer($strPreselectionFilename);
			$wksWorksheet =& $wkbWorkbook->addWorksheet();
			
			// Title Row format
			$fmtTitle =& $wkbWorkbook->addFormat();
			$fmtTitle->setBold();
			$fmtTitle->setFgColor(22);
			$fmtTitle->setBorder(1);
		
			// Add header row
			$wksWorksheet->writeString(0, 0, 'Service Number'			, $fmtTitle);
			$wksWorksheet->writeString(0, 1, 'Billable Account Number'	, $fmtTitle);
			$wksWorksheet->writeString(0, 2, 'Service Type'				, $fmtTitle);
			$wksWorksheet->writeString(0, 3, 'Customer Reference'		, $fmtTitle);

			// add data rows
			$intRow = 0;
			foreach($this->_arrPreselectionRecords as $strFNN)
			{
				$intRow++;
				$wksWorksheet->writeString($intRow, 0, $strFNN);
				$wksWorksheet->writeString($intRow, 1, CUSTOMER_NUMBER_OPTUS);
				$wksWorksheet->writeString($intRow, 2, 'UT');
				$wksWorksheet->writeString($intRow, 3, '');
			}
			
			// Write output
			$wkbWorkbook->close();

			$mimMimeEmail = new Mail_Mime("\n");
 			$mimMimeEmail->setTXTBody("Unbar Request File for ".date("Y-m-d H:i:s", time())." for Customer ".CUSTOMER_NUMBER_OPTUS);
		 	$mimMimeEmail->addAttachment($strPreselectionFilename, 'application/x-msexcel');
		 	$emlMail =& Mail::factory('mail');
		 	
 			$arrExtraHeaders = Array(
 										'From'		=> "provisioning@voiptel.com.au",
 										'Subject'	=> "Unbar Request File for ".date("Y-m-d H:i:s", time())
 									);
 			
 			$strContent = $mimMimeEmail->get();
 			$arrHeaders = $mimMimeEmail->headers($arrExtraHeaders);
			
			// Email to Optus (as an attachment)
			//mail_attachment("provisioning@voiptel.com.au", "rdavis@ybs.net.au", "Restoration File", "Attached: Telco Blue Automatically Generated Barring Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename)
			//mail_attachment("provisioning@voiptel.com.au", "long.distance.spsg@optus.com.au", "Restoration File", "Attached: Telco Blue Automatically Generated Barring Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
			if (!$emlMail->send('long.distance.spsg@optus.com.au', $arrHeaders, $strContent))
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

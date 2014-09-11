<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_optus_preselection
//----------------------------------------------------------------------------//
/**
 * module_export_optus_preselection
 *
 * Optus Export Module for the provisioning engine (Preselection)
 *
 * Optus Export Module for the provisioning engine (Preselection)
 *
 * @file		module_export_optus_preselection.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportOptusPreselection
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportOptusPreselection
 *
 * Optus Export Module for the provisioning engine (Preselection)
 *
 * Optus Export Module for the provisioning engine. (Preselection)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportOptusPreselection
 */
 class ProvisioningModuleExportOptusPreselection extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportOptusPreselection
	 *
	 * Constructor method for ProvisioningModuleExportOptusPreselection
	 *
	 * @return		ProvisioningModuleExportOptusPreselection
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName = "Optus";
		$this->_intCarrier		= CARRIER_OPTUS;
		
		parent::__construct($ptrDB);
		
		//$this->_selGetRequests	= new StatementSelect("Request", "*", "Carrier = ".CARRIER_OPTUS." AND RequestType = ".REQUEST_PRESELECTION." AND Status = ".REQUEST_STATUS_WAITING);
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
		
		$this->_selGetSequence->Execute(Array('Name' => "OptusBatchNo", 'Module' => "Optus"));
		if(!$arrSequenceNo = $this->_selGetSequence->Fetch())
		{
			// Sequence number should be set to 1
			$this->_intSequenceNo = 1;
		}
		$this->_intSequenceNo = $arrSequenceNo['Value'] + 1;
		
		// Get the FNN
		$selFNN = new StatementSelect("Service", "FNN", "Id = <Service>");
		$selFNN->Execute(Array('Service' => $arrRequest['Service']));
		$arrFNN = $selFNN->Fetch();
		
		// Build the request Array
		$arrBuiltRequest['BatchNo']				= $this->_intSequenceNo;
		$arrBuiltRequest['IdNo']				= 12;
		$arrBuiltRequest['SPName']				= "TelcoBlue";
		$arrBuiltRequest['SPCassNo']			= CUSTOMER_NUMBER_OPTUS;
		$arrBuiltRequest['ServiceNo']			= $arrFNN['FNN'];
		$arrBuiltRequest['CADate']				= date("d/m/Y");
		$arrBuiltRequest['CARequired']			= "n";
		$arrBuiltRequest['Lessee']				= "n";
		
		foreach ($this->_arrPreselectionRecords as $arrRecord)
		{
			if ($arrRecord['ServiceNo'] == $arrBuiltRequest['ServiceNo'])
			{
				// This request already exists in the file - DO NOT DUPLICATE
				return REQUEST_STATUS_DUPLICATE;
			}
		}
		
		// Append this request
		$this->_arrPreselectionRecords[]		= $arrBuiltRequest;
		
		// Add additional logging data
		$this->_arrLog['Request']	= $arrRequest['Id'];
		$this->_arrLog['Service']	= $arrRequest['Service'];
		$this->_arrLog['Type']		= $arrRequest['RequestType'];
		
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
		// Build Header Row
		$strPreselectionFilename	= OPTUS_LOCAL_PRESELECTION_DIR.date("Hi_Y-m-d_").$this->_intSequenceNo.".xls";
		
		$intNumPreselectionRecords	= count($this->_arrPreselectionRecords);
		
		// Create Local Preselection File
		if($intNumPreselectionRecords > 0)
		{
			$this->_bolSending = TRUE;
			
			// Generate Excel 5 Workbook
			$wkbWorkbook = new Spreadsheet_Excel_Writer($strPreselectionFilename);
			$wksWorksheet =& $wkbWorkbook->addWorksheet();
			
			// Title Row format
			$fmtTitle =& $wkbWorkbook->addFormat();
			$fmtTitle->setBold();
			$fmtTitle->setFgColor(22);
			$fmtTitle->setBorder(1);
			
			// Add header row
			$wksWorksheet->writeString(0, 0, 'Batch No'					, $fmtTitle);
			$wksWorksheet->writeString(0, 1, 'ID No'					, $fmtTitle);
			$wksWorksheet->writeString(0, 2, 'SP Name'					, $fmtTitle);
			$wksWorksheet->writeString(0, 3, 'SP CASS A/C No'			, $fmtTitle);
			$wksWorksheet->writeString(0, 4, 'Service No with area code', $fmtTitle);
			$wksWorksheet->writeString(0, 5, 'CA Date dd/mm/yyy'		, $fmtTitle);
			$wksWorksheet->writeString(0, 6, 'CA Required'				, $fmtTitle);
			$wksWorksheet->writeString(0, 7, 'Lessee Yes/No'			, $fmtTitle);

			// add data rows
			$intRow = 0;
			foreach($this->_arrPreselectionRecords as $arrBuiltRequest)
			{
				$intRow++;
				$intCol = 0;
				foreach ($arrBuiltRequest as $mixField)
				{
					$wksWorksheet->writeString($intRow, $intCol, $mixField);
					$intCol++;
				}
			}
			
			// Write output
			$wkbWorkbook->close();
			
			$mimMimeEmail = new Mail_Mime("\n");
 			$mimMimeEmail->setTXTBody("LD Churn Request File for ".date("Y-m-d H:i:s", time())." for Customer ".CUSTOMER_NUMBER_OPTUS);
		 	$mimMimeEmail->addAttachment($strPreselectionFilename, 'application/x-msexcel');
		 	$emlMail =& Mail::factory('mail');
		 	
 			$arrExtraHeaders = Array(
 										'From'		=> "provisioning@voiptel.com.au",
 										'Subject'	=> "LD Churn Request File for ".date("Y-m-d H:i:s", time())
 									);
 			
 			$strContent = $mimMimeEmail->get();
 			$arrHeaders = $mimMimeEmail->headers($arrExtraHeaders);
 			
			// Update sequence no
			$this->_updSetSequence->Execute(Array('Value' => $this->_intSequenceNo), Array('Name' => "OptusBatchNo", 'Module' => "Optus"));
			// 679 is the starting sequence no
			
			// Email to Optus (as an attachment)
			//mail_attachment("provisioning@voiptel.com.au", "rdavis@ybs.net.au", "Activation File", "Attached: Telco Blue Automatically Generated Barring Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename)
			//mail_attachment("provisioning@voiptel.com.au", "long.distance.spsg@optus.com.au", "Activation File", "Attached: Telco Blue Automatically Generated Barring Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
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

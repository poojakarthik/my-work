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
		
		parent::__construct($ptrDB);
		
		$this->_selGetRequests	= new StatementSelect("Request", "*", "Carrier = ".CARRIER_OPTUS." AND RequestType = ".REQUEST_PRESELECTION." AND Status = ".REQUEST_STATUS_WAITING);
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
		$this->_intSequenceNo = $arrSequenceNo['Value']++;
		
		// Build the request Array
		$arrBuiltRequest['BatchNo']				= $this->_intSequenceNo;
		$arrBuiltRequest['IdNo']				= "12";
		$arrBuiltRequest['SPName']				= "Telco Blue";
		$arrBuiltRequest['SPCassNo']			= CUSTOMER_NUMBER_OPTUS;
		$arrBuiltRequest['ServiceNo']			= $arrRequest['FNN'];
		$arrBuiltRequest['CADate']				= date("d/m/Y");
		$arrBuiltRequest['CARequired']			= "n";
		$arrBuiltRequest['Lessee']				= "n";
		
		// Append this request
		$this->_arrPreselectionRecords[]		= implode(",", $arrBuiltRequest);
		
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
		$strPreselectionFilename	= date("Y-m-d_Hi").".xls";	// Is actually a CSV, but try to fool Optus :P
		$strPreselectionHeaderRow	= "Batch No,ID No,SP Name,SP CASS A/C No,Service No with area code,CA Date dd/mm/yyy,CA Required,Lessee Yes/No";
		
		// Get list of requests to generate
		$arrResults = $this->_selGetRequests->FetchAll();
		
		$intNumPreselectionRecords	= count($this->_arrPreselectionRecords);
		
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
			fclose($resPreselectionFile);
		}
		else
		{
			return TRUE;
		}
		
		// Email to Optus (as an attachment)
		//mail_attachment("provisioning@voiptel.com.au", "long.distance.spsg@optus.com.au", "Activation Files", "Attached: Telco Blue Automatically Generated Activation Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
		if (!mail_attachment("provisioning@voiptel.com.au", "rich@voiptelsystems.com.au", "Activation Files", "Attached: Telco Blue Automatically Generated Activation Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename))
		{
			Debug("Email failed!");
			return FALSE;
		}
		
		// Update sequence no
		$this->_updSetSequence->Execute(Array('Value' => $this->_intSequenceNo), Array('Name' => "OptusBatchNo", 'Module' => "Optus"));
		
		// Return the number of records uploaded
		return $intNumPreselectionRecords;
	} 	
	
 }

?>

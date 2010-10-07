<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_export_optus_preselection_reverse
//----------------------------------------------------------------------------//
/**
 * module_export_optus_preselection_reverse
 *
 * Optus Export Module for the provisioning engine (Preselection Reversal)
 *
 * Optus Export Module for the provisioning engine (Preselection Reversal)
 *
 * @file		module_export_optus_preselection_reverse.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExportOptusPreselectionReverse
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExportOptusPreselectionReverse
 *
 * Optus Export Module for the provisioning engine (Barring)
 *
 * Optus Export Module for the provisioning engine. (Barring)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportOptusPreselectionReverse
 */
 class ProvisioningModuleExportOptusPreselectionReverse extends ProvisioningModuleExport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExportOptusPreselectionReverse
	 *
	 * Constructor method for ProvisioningModuleExportOptusPreselectionReverse
	 *
	 * @return		ProvisioningModuleExportOptusPreselectionReverse
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName 	= "Optus";
		$this->_intCarrier		= CARRIER_OPTUS;
		
		parent::__construct($ptrDB);
		
		//$this->_selGetRequests	= new StatementSelect("Requests", "*", "Carrier = ".CARRIER_OPTUS." AND RequestType = ".REQUEST_BAR_SOFT."Status = ".REQUEST_STATUS_WAITING);
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
			
			$strContent = "Telco Blue (Customer ".CUSTOMER_NUMBER_OPTUS.") believes that it has received LD Churns for the following Service Numbers in error.\n\n";
			$strContent .= implode("\n", $this->_arrPreselectionRecords);
			$strContent .= "\n\nThanks in advance\n\nTelco Blue Provisioning";
			
			$mimMimeEmail = new Mail_Mime("\n");
 			$mimMimeEmail->setTXTBody($strContent);
		 	$emlMail =& Mail::factory('mail');
		 	
 			$arrExtraHeaders = Array(
 										'From'		=> "provisioning@voiptel.com.au",
 										'Subject'	=> "LD Churn Reversal"
 									);
 			$strContent = $mimMimeEmail->get();
 			$arrHeaders = $mimMimeEmail->headers($arrExtraHeaders);
			
			// Email to Optus (as an attachment)
			//mail_attachment("provisioning@voiptel.com.au", "rdavis@ybs.net.au", "Barring File", "Attached: Telco Blue Automatically Generated Barring Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename)
			//mail_attachment("provisioning@voiptel.com.au", "long.distance.spsg@optus.com.au", "Barring File", "Attached: Telco Blue Automatically Generated Barring Request File", OPTUS_LOCAL_PRESELECTION_DIR.$strPreselectionFilename);
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

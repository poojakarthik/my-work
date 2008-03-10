<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// base_export_module
//----------------------------------------------------------------------------//
/**
 * base_export_module
 *
 * Export Module for the provisioning engine
 *
 * Export Module for the provisioning engine.  There is one per file type.
 *
 * @file		base_export_module.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleExport
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleExport
 *
 * Export Module for the provisioning engine
 *
 * Export Module for the provisioning engine.  There is one per file type.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExport
 */
 abstract class ProvisioningModuleExport
 {
	protected $_arrData;
	protected $_arrDefineInput;
	protected $_arrLog;
	
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleExport
	 *
	 * Constructor method for ProvisioningModuleExport
	 *
	 * @return		ProvisioningModuleExport
	 *
	 * @method
	 */
 	function __construct($ptrDB)
 	{
		// Set up this->db
		$this->db = $ptrDB;
		
		$arrRequestColumns['ExportFile']		= NULL;
		$arrRequestColumns['GainDate']			= NULL;
		$arrRequestColumns['LossDate']			= NULL;
		$arrRequestColumns['Status']			= NULL;
		$arrServiceColumns['LineStatus']	 	= NULL;
		$arrServiceColumns['Carrier']		 	= NULL;
		$arrServiceColumns['CarrierPreselect']	= NULL;
				
		$this->_selMatchRequest					= new StatementSelect("Request", "*",
			"Service = <Service> AND Carrier = <Carrier> AND RequestType = <RequestType>", "RequestDateTime DESC", "1");
		$this->_ubiRequest						= new StatementUpdateById("Request", $arrRequestColumns);
		$this->_selMatchService 				= new StatementSelect("Service", "*", "FNN = <FNN>", "CreatedOn DESC", "1");
		$this->_ubiService						= new StatementUpdateById("Service", $arrServiceColumns);
		$this->_selMatchLog						= new StatementSelect("ProvisioningLog", "Id", "Date > <Date>");
		$this->_selGetSequence					= new StatementSelect("Config", "Name, Value", "Application = ".APPLICATION_PROVISIONING." AND Module = <Module> AND Name = <Name>");
		$this->_updSetSequence					= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = <Module> AND Name = <Name>", Array('Value' => NULL));
		$this->_selGetFullServiceRequests		= new StatementSelect("Request JOIN Service ON Request.Service = Service.Id", "Request.*, Service.FNN", "Request.Carrier = <Carrier> AND Request.Status = ".REQUEST_STATUS_WAITING." AND Request.RequestType = ".REQUEST_FULL_SERVICE);
		$this->_selGetPreselectRequests			= new StatementSelect("Request JOIN Service ON Request.Service = Service.Id", "Request.*, Service.FNN", "Request.Carrier = <Carrier> AND Request.Status = ".REQUEST_STATUS_WAITING." AND Request.RequestType = ".REQUEST_PRESELECTION);
		
		$this->_insAddToLog						= new StatementInsert("ProvisioningLog");
		
		// Default delimeter is NULL (fixedwidth)
		$this->_strDelimiter	= NULL;
		
		$this->_arrFullServiceRecords	= Array();
		$this->_arrPreselectionRecords	= Array();
		
		$this->_bolSending = FALSE;
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
 	abstract function BuildRequest($arrRequest);
 	
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
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function SendRequest();	 	

	//------------------------------------------------------------------------//
	// AddToProvisioningLog
	//------------------------------------------------------------------------//
	/**
	 * AddToProvisioningLog()
	 *
	 * Adds the record to the log
	 *
	 * Adds the record to the log
	 * 
	 * @return	boolean					
	 *
	 * @method
	 */
	 function AddToProvisioningLog()
	 {
		// If there is an FNN and no Service specified, then attempt to match
		if (isset($this->_arrLog['FNN']) && !isset($this->_arrLog['Service']))
		{
			$this->_selMatchService->Execute(Array('FNN' => $this->_arrLog['FNN']));
			if (!$this->_arrLog['Service'] = $this->_selMatchService->Fetch())
			{
				// This request doesn't belong to us
				return FALSE;
			}
		}
		
		// Write to the Provisioning Log
		$this->_arrLog['Carrier']	= $this->_intCarrier;
		$this->_arrLog['Direction']	= REQUEST_DIRECTION_OUTGOING;
		$this->_arrLog['Date']		= date("Y-m-d");
		$mixResponse = $this->_insAddToLog->Execute($this->_arrLog);
		if ($mixResponse === FALSE)
		{
			Debug($this->_insAddToLog->Error());
		}
		return $mixResponse;
	 }	 	

	//------------------------------------------------------------------------//
	// AddToProvisioningExport
	//------------------------------------------------------------------------//
	/**
	 * AddToProvisioningExport()
	 *
	 * Adds the record to the Export table
	 *
	 * Adds the record to the Export table
	 * 
	 * @return	boolean					
	 *
	 * @method
	 */
	 function AddToProvisioningExport($intStatus)
	 {
		// Check to see if we're sending a file
		if ($this->_bolSending)
		{
			// Set the data
			$arrInsert = Array();
			$arrInsert['Location']	= $this->_strFile;
			$arrInsert['Carrier']	= $this->_intCarrier;
			$arrInsert['Status']	= $intStatus;
			
			$insProvisioningExport = new StatementInsert("ProvisioningExport");
			return $insProvisioningExport->Execute($arrInsert);
		}
		return TRUE;
	 }

	//------------------------------------------------------------------------//
	// _SplitLine
	//------------------------------------------------------------------------//
	/**
	 * _SplitLine()
	 *
	 * Split a Line into an array
	 *
	 * Split a Line into an array
	 * 
	 * @param	string		strLine		Line to split
	 *
	 * @return	array					Split data					
	 *
	 * @method
	 */
	 protected function _SplitLine($strLine)
	 {
		// build the array
	 	if ($this->_strDelimiter)
		{
			// delimited record
			$arrRawData = explode($this->_strDelimiter, rtrim($strLine, "\n"));
			foreach($this->_arrDefineInput as $strKey=>$strValue)
			{
				$_arrData[$strKey] = $arrRawData[$strValue['Index']];
				// delimited fields may have fixed width contents
				if (isset($strValue['Start']) && $strValue['Length'])
				{
					$_arrData[$strKey] = substr($_arrData[$strKey], $strValue['Start'], $strValue['Length']);
				}
			}
		}
		else
		{
			// fixed width record
			foreach($this->_arrDefineInput as $strKey=>$strValue)
			{
				$_arrData[$strKey] = trim(substr($strLine, $strValue['Start'], $strValue['Length']));
			}
		}
		
		return $_arrData;
	 }
	 
 }
?>

<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_import_unitel_order
//----------------------------------------------------------------------------//
/**
 * module_import_unitel_order
 *
 * Unitel Import Module for the provisioning engine (Daily Order Report)
 *
 * Unitel Import Module for the provisioning engine (Daily Order Report)
 *
 * @file		module_import_unitel_order.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportUnitelOrder
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportUnitelOrder
 *
 * Unitel Module for the provisioning engine (Daily Order Report)
 *
 * Unitel Module for the provisioning engine.  (Daily Order Report)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImportUnitelOrder
 */
 class ProvisioningModuleImportUnitelOrder extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportUnitelOrder
	 *
	 * Constructor method for ProvisioningModuleImportUnitelOrder
	 *
	 * @return		ProvisioningModuleImportUnitelOrder
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName = "Unitel";
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));
		$this->_selGetRequest					= new StatementSelect("Request JOIN ProvisioningExport ON Request.ExportFile = ProvisioningExport.Id", "*", "Request.Sequence = <Sequence> AND ProvisioningExport.Location = <Location>", NULL, "1");
		$this->_ubiRequest						= new StatementUpdateById("Request");
				
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 2;
		
		
		// define the carrier input format
		$arrDefine ['RecordType']	['Start']		= 0;
		$arrDefine ['RecordType']	['Length']		= 1;
		
		$arrDefine ['Sequence']		['Start']		= 1;
		$arrDefine ['Sequence']		['Length']		= 5;
		
		$arrDefine ['Description']	['Start']		= 6;
		$arrDefine ['Description']	['Length']		= 9;
		
		$this->_arrDefineInput = $arrDefine;
		
		//##----------------------------------------------------------------##//
		
 	}

 	//------------------------------------------------------------------------//
	// Normalise()
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises a line
	 *
	 * Normalises a line, and sets it as the "current" line
	 *
	 * @return		mixed				TRUE: pass
	 * 									int	: Error code
	 *
	 * @method
	 */
 	function Normalise($strLine)
	{
		// Split the line
		$arrLineData = $this->_SplitLine($strLine);
		
		// Ignore header and trailer line
		if($arrLineData['RecordType'] == "T")
		{
			// Make sure we NULLify the referenced file
			$this->_strReferencedFile = NULL;
			return PRV_TRAILER_RECORD;
		}
		elseif($arrLineData['RecordType'] == "H")
		{
			// Find out which daily order file we're referencing
			$this->_strReferencedFile = substr($strLine, 25, 23);
			return PRV_HEADER_RECORD;
		}
		elseif(!$this->_strReferencedFile)
		{
			// We don't have reference to a Daily Order File
			return PRV_HEADER_EXPECTED;
		}
		
		// Set the Log date
		$this->_arrLog['Date']	= date("Y-m-d");
		
		// Grab the data we need from the line
		$arrData['Sequence']	= (int)$this->_arrDefineInput['Sequence'];
		$arrData['Location']	= UNITEL_DAILY_ORDER_DIR.$this->_strReferencedFile;
		$this->_selGetRequest->Execute($arrData);
		
		if ($arrResult = $this->_selGetRequest->Fetch())
		{
			// Set the request for this entry in the Log array
			$this->_arrLog['Request']		= $arrResult['Id'];
			$this->_arrLog['Description']	= $this->_arrDefineInput['Description'];
			$this->_arrLog['Type']			= $arrResult['Type'];
			$this->_arrLog['Service']		= $arrResult['Service'];
			
			$arrResult['Reason']		= $this->_arrDefineInput['Description'];
			
			// Update to say if the file has been accepted/rejected
			if(stripos($this->_arrDefineInput['Description'], "accept"))
			{
				$arrResult['Status']	= PRV_FILE_ACCEPTED;
			}
			else
			{
				$arrResult['Status']	= PRV_FILE_REJECTED;
			}
			
			// Commit the changes back to the database
			$this->_ubiRequest->Execute($arrResult);
		}
		else
		{
			// No match - ERR0R
			return PRV_MISSING_REQUEST;
		}
		
		return TRUE;
	} 	

 	//------------------------------------------------------------------------//
	// UpdateRequests()
	//------------------------------------------------------------------------//
	/**
	 * UpdateRequests()
	 *
	 * Updates the Request table
	 *
	 * Updates the Request table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function UpdateRequests()
	{
		// We don't implement this method, so return TRUE
		return TRUE;
	}
 	
 	//------------------------------------------------------------------------//
	// UpdateService()
	//------------------------------------------------------------------------//
	/**
	 * UpdateService()
	 *
	 * Updates the Service table
	 *
	 * Updates the Service table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function UpdateService()
	{
		// We don't implement this method, so return TRUE
		return TRUE;
	}
 
 	//------------------------------------------------------------------------//
	// _ConvertDate()
	//------------------------------------------------------------------------//
	/**
	 * _ConvertDate()
	 *
	 * Converts from Unitel to Internal date format
	 *
	 * Converts from YYYYMMDD to YYYY-MM-DD format
	 *
	 * @param		string		$strDate		Date to convert
	 *
	 * @return		string
	 *
	 * @method
	 */
 	function _ConvertDate($strDate)
	{
		$strReturn = substr($strDate, 0, 4)."-".substr($strDate, 3, 2)."-".substr($strDate, 5, 2);
		return $strReturn;
	} 

	//------------------------------------------------------------------------//
	// _GetCarrierName()
	//------------------------------------------------------------------------//
	/**
	 * _GetCarrierName()
	 *
	 * Gets the name of a carrier from a carrier code
	 *
	 * Gets the name of a carrier from a carrier code
	 *
	 * @param		string		$strCode		Code to match
	 *
	 * @return		string
	 *
	 * @method
	 */
 	function _GetCarrierName($strCode)
	{
		// TODO: waiting for codes from Scott
		return "Undefined Carrier (Internal Code: ".$strCode.")";
	} 
	
 }

?>

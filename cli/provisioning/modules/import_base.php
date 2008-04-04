<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import_base
//----------------------------------------------------------------------------//
/**
 * import_base
 *
 * Parses a Provisioning Import File
 *
 * Parses a Provisioning Import File
 *
 * @file		import_base.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ImportBase
//----------------------------------------------------------------------------//
/**
 * ImportBase
 *
 * Parses a Provisioning Import File
 *
 * Parses a Provisioning Import File
 *
 * @prefix		imp
 *
 * @package		provisioning
 * @class		ImportBase
 */
 class ImportBase extends CarrierModule
 {
 	public $intLineNumber;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * Constructor
	 * 
	 * @param	integer	$intCarrier				The Carrier using this Module
	 * 
	 * @return	ImportBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier, MODULE_TYPE_PROVISIONING_INPUT);
 		
 		// Defaults
 		$this->intCarrier		= NULL;
 		$this->_strDelimiter	= ",";
 		$this->_strEndOfLine	= "\n";
 		$this->_strEnclosed		= '';
 		$this->_arrDefine		= Array();
 		$this->_arrModuleConfig	= Array();
 		
 		// Statements
 		$this->_selRequestByCarrierRef	= new StatementSelect("ProvisioningRequest", "Id", "CarrierRef = <CarrierRef>");
 		$this->_selRequestByFNN			= new StatementSelect("ProvisioningRequest", "Id", 
												"FNN = <FNN> AND Type = <Type> AND Status = ".REQUEST_STATUS_PENDING);
		$this->_selTranslateCarrierCode	= new StatementSelect("ProvisioningTranslation", "Description", "Context = <Context> AND CarrierCode = <CarrierCode>");
		
		$this->_selCarrierModule		= new StatementSelect("CarrierModule", "*", "Carrier = <Carrier> AND Module = <Module> AND Type = ".MODULE_TYPE_PROVISIONING_INPUT);
 	}
 	
 	//------------------------------------------------------------------------//
	// PreProcess
	//------------------------------------------------------------------------//
	/**
	 * PreProcess()
	 *
	 * Pre-processes a file
	 *
	 * Pre-processes a file
	 * 
	 * @param	array	$arrRawData		File Data to parse
	 * 
	 * @return	array					Parsed data
	 *
	 * @method
	 */
 	function PreProcess($arrRawData)
 	{
 		// Just return the data.  Function can be overridden to pre-process
 		return $arrRawData;
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises a line from a Provisioning File
	 *
	 * Normalises a line from a Provisioning File
	 * 
	 * @param	string	$strLine		Line to parse
	 * 
	 * @return	array					Parsed data
	 *
	 * @method
	 */
 	function Normalise($arrNormalised, $intLineNumber)
 	{
 		DebugBacktrace();
 		throw new Exception("ImportBase::Normalised() is a virtual function!");
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validates a Normalised Line
	 *
	 * Validates a Normalised Line
	 * 
	 * @param	array	$arrLine		Line to verify
	 * 
	 * @return	array					['Pass'] : boolean
	 * 									['Message'] : string
	 *
	 * @method
	 */
 	function Validate($arrLine)
 	{
 		// Validate Line
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
			foreach($this->_arrDefine as $strKey=>$strValue)
			{
				
				
				$_arrData[$strKey] = $arrRawData[$strValue['Index']];
				// delimited fields may have fixed width contents
				if (isset($strValue['Start']) && $strValue['Length'])
				{
					$_arrData[$strKey] = substr($_arrData[$strKey], $strValue['Start'], $strValue['Length']);
				}
				$_arrData[$strKey] = trim($_arrData[$strKey]);
			}
		}
		else
		{
			// fixed width record
			foreach($this->_arrDefine as $strKey=>$strValue)
			{
				$_arrData[$strKey] = trim(substr($strLine, $strValue['Start'], $strValue['Length']));
			}
		}
		
		return $_arrData;
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// LinkToRequest
	//------------------------------------------------------------------------//
	/**
	 * LinkToRequest()
	 *
	 * Attempts to link a Response to a Request
	 *
	 * Attempts to link a Response to a Request
	 * 
	 * @param	array	$arrResponse	Response to match against
	 * 
	 * @return	integer					Request Id
	 *
	 * @method
	 */
	 function LinkToRequest($arrResponse)
	 {
	 	// Match by FNN and Type
 		if ($this->_selRequestByFNN->Execute($arrResponse))
 		{
 			// Found a match, return the Id
 			$arrReturn = $this->_selRequestByFNN->Fetch();
 			return $arrReturn['Id'];
 		}
	 	
	 	// Run the default matcher
	 	return NULL;
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// TranslateCarrierCode
	//------------------------------------------------------------------------//
	/**
	 * TranslateCarrierCode()
	 *
	 * Translates a Carrier Code using the ProvisioningTranslation table
	 *
	 * Translates a Carrier Code using the ProvisioningTranslation table
	 * 
	 * @param	integer	$intContext		Context Group for the Constant (eg. PROVISIONING_CONTEXT_EPID)
	 * @param	mixed	$mixValue		The Code to Translate
	 * 
	 * @return	mixed					string	: Description
	 * 									FALSE	: Failed					
	 *
	 * @method
	 */
	 function TranslateCarrierCode($intContext, $mixValue)
	 {
	 	$arrWhere	= Array();
	 	$arrWhere['Context']		= (int)$intContext;
	 	$arrWhere['CarrierCode']	= (string)$mixValue;
	 	if (!$this->_selTranslateCarrierCode->Execute($arrWhere))
	 	{
	 		return FALSE;
	 	}
	 	
	 	$arrValue	= $this->_selTranslateCarrierCode->Fetch();
	 	return $arrValue['Description'];
	 }
 }
?>

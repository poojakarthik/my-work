<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// base_module
//----------------------------------------------------------------------------//
/**
 * base_module
 *
 * Normalisation Module Base Class
 *
 * Normalisation Module Base Class
 *
 * @file		base_module.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// NormalisationModule
//----------------------------------------------------------------------------//
/**
 * NormalisationModule
 *
 * Normalisation Module Base Class
 *
 * Normalisation Module Base Class
 *
 *
 * @prefix		nrm
 *
 * @package		vixen
 * @class		<ClassName||InstanceName>
 */
abstract class NormalisationModule
{
	//------------------------------------------------------------------------//
	// _arrRawData
	//------------------------------------------------------------------------//
	/**
	 * arrRawData
	 *
	 * Stores the split raw data from the CDR
	 *
	 * Stores the split raw data from the CDR
	 *
	 * @type	array
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	protected $_arrRawData; 

	//------------------------------------------------------------------------//
	// _arrNormalisedData
	//------------------------------------------------------------------------//
	/**
	 * arrNormalisedData
	 *
	 * Stores the normalised data from the CDR
	 *
	 * Stores the normalised raw data from the CDR
	 *
	 * @type	array
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	protected $_arrNormalisedData; 
	
	//------------------------------------------------------------------------//
	// rptNormalisationReport
	//------------------------------------------------------------------------//
	/**
	 * rptNormalisationReport
	 *
	 * Normalisation report
	 *
	 * Normalisation Report, including information on errors, failed import
	 * and normalisations, and a total of each
	 *
	 * @type		Report
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	protected $_rptNormalisationReport;

 	
	//------------------------------------------------------------------------//
	// _strDelimiter
	//------------------------------------------------------------------------//
	/**
	 * _strDelimiter
	 *
	 * Delimiter for delimited CDR files
	 *
	 * Delimiter for delimited CDR files
	 *
	 * @type	string
	 *
	 * @property
	 */
	protected $_strDelimiter;
	
	//------------------------------------------------------------------------//
	// _intStartRow
	//------------------------------------------------------------------------//
	/**
	 * _intStartRow
	 *
	 * First row of file that contains CDR data
	 *
	 * First row of file that contains CDR data
	 * Row numbers start at 1
	 *
	 * @type	int
	 *
	 * @property
	 */
	protected $_intStartRow;
	
	//------------------------------------------------------------------------//
	// _arrDefineCarrier
	//------------------------------------------------------------------------//
	/**
	 * _arrDefineCarrier
	 *
	 * Defines the Carrier CDR format
	 *
	 * Defines the Carrier CDR format
	 *
	 * @type	array
	 *
	 * @property
	 */
	protected $_arrDefineCarrier;
	
	//------------------------------------------------------------------------//
	// _arrDefineCarrier
	//------------------------------------------------------------------------//
	/**
	 * _arrDefineCarrier
	 *
	 * Defines the Output CDR format
	 *
	 * Defines the Output CDR format
	 *
	 * @type	array
	 *
	 * @property
	 */
	protected $_arrDefineOutput;
	
	//------------------------------------------------------------------------//
	// errErrorHandler
	//------------------------------------------------------------------------//
	/**
	 * errErrorHandler
	 *
	 * Application Error Handler instance
	 *
	 * Application Error Handler instance
	 *
	 * @type		ErrorHandler
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	protected $_errErrorHandler;
	
	function __construct($errErrorHandler=NULL, $rptNormalisationReport=NULL)
	{
		// The purpose of this is to have a generic constructor for all Normalisation
		// modules.  It will never be called to instanciate an object of type
		// NormalisationModule, though
		
		$this->_errErrorHander 			= $errErrorHandler;
		$this->_rptNormalisationReport 	= $rptNormalisationReport;
	}
	
	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate Normalised Data
	 *
	 * Validate Normalised Data
	 *
	 * @return	boolean				true	: Data matches
	 * 								false	: Data doesn't match
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Validate()
	{
		// Validate our normalised data
		$arrValid = Array();
		
		// $this->_arrNormalisedData["Id"];
		$arrValid[] = preg_match("^0\d{9}[i]?|13\d{4}|1[89]00\d{6}$", 	$this->_arrNormalisedData["FNN"]);
		$arrValid[] = ($this->_arrNormalisedData["CarrierRef"] != "");
		
		if ($this->_arrNormalisedData["Source"] != "")
		{
			$arrValid[] = preg_match("^\+?\d+$", 	$this->_arrNormalisedData["Source"]);
		}
		else
		{
			$arrValid[] = true;
		}
		
		if ($this->_arrNormalisedData["Destination"] != "")
		{
			$arrValid[] = preg_match("^\+?\d+$", 	$this->_arrNormalisedData["Destination"]);
		}
		else
		{
			$arrValid[] = true;
		}

		$arrValid[] = preg_match("^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$",	$this->_arrNormalisedData["StartDatetime"]);

		if ($this->_arrNormalisedData["EndDatetime"] != "")
		{
			$arrValid[] = preg_match("^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$", 	$this->_arrNormalisedData["EndDatetime"]);
		}
		else
		{
			$arrValid[] = true;
		}

		$arrValid[] = ((is_int($this->_arrNormalisedData["Units"])) && ($this->_arrNormalisedData["Units"] != 0));
		$arrValid[] = is_float($this->_arrNormalisedData["Cost"]);
		$arrValid[] = ($this->_arrNormalisedData["Description"] != "");
		$arrValid[] = (strlen($this->_arrNormalisedData["DestinationCode"]) <= 3);
		
		foreach ($arrValid as $bolValid)
		{
			if(!$bolValid)
			{
				return false;
			}
		}
		
		return true;
	}
	

	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises raw data from the CDR
	 *
	 * Normalises raw data from the CDR
	 * 
	 * @param	array		arrCDR		Array returned from SELECT query on CDR
	 *
	 * @return	array					Normalised Data, ready for direct UPDATE
	 * 									into DB
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */	
	abstract function Normalise($arrCDR);
	
	//------------------------------------------------------------------------//
	// RemoveAusCode
	//------------------------------------------------------------------------//
	/**
	 * RemoveAusCode()
	 *
	 * Removes +61 from FNNs
	 *
	 * Removes the +61 from the start of an FNN, replacing it with a 0
	 * 
	 * @param	string		$strFNN		FNN to be parsed
	 *
	 * @return	string					Modified FNN
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */	
	protected function RemoveAusCode($strFNN)
	{
		return str_replace("+61", "0", $strFNN);
	}
	
	//------------------------------------------------------------------------//
	// IsValidFNN
	//------------------------------------------------------------------------//
	/**
	 * IsValidFNN()
	 *
	 * Checks if FNN is valid
	 *
	 * Checks if FNN is valid.  Valid FNN examples are:	0734581649	(Landlines and Mobiles)
	 * 													0246784194i (ADSL numbers)
	 * 													131888		(13-numbers)
	 * 													1800513454	(1800-numbers)
	 * 													1900451354	(1900-numbers)
	 * 
	 * @param	string		$strFNN		FNN to be parsed
	 *
	 * @return	boolean					true	: FNN is valid
	 * 									false	: FNN is not valid
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */	
	protected function IsValidFNN($strFNN)
	{
		return preg_match("^0\d{9}[i]?|13\d{4}|1[89]00\d{6}$");
	}
	
	//------------------------------------------------------------------------//
	// _SplitRawCDR
	//------------------------------------------------------------------------//
	/**
	 * _SplitRawCDR()
	 *
	 * Split a Raw CDR record into an array
	 *
	 * Split a Raw CDR record into an array
	 * 
	 * @param	string		strCDR		CDR record
	 *
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _SplitCDR($strCDR)
	 {
	 	// clean the array
		$this->_arrRawData = array();
		
		// build the array
	 	if ($this->_strDelimiter)
		{
			// delimited record
			$arrRawData = explode($this->_strDelimiter, rtrim($strCDR, "\n"));
			foreach($this->_arrDefineCarrier as $strKey=>$strValue)
			{
				$this->_arrRawData[$strKey] = $arrRawData[$strValue['Index']];
				// delimited fields may have fixed width contents
				if (isset($strValue['Start']) && $strValue['Length'])
				{
					$this->_arrRawData[$strKey] = substr($this->_arrRawData[$strKey], $strValue['Start'], $strValue['Length']);
				}
			}
		}
		else
		{
			// fixed width record
			foreach($this->_arrDefineCarrier as $strKey=>$strValue)
			{
				$this->_arrRawData[$strKey] = substr($strCDR, $strValue['Start'], $strValue['Length']);
			}
		}

	 }
	 
	//------------------------------------------------------------------------//
	// _ValidateRawCDR
	//------------------------------------------------------------------------//
	/**
	 * _ValidateRawCDR()
	 *
	 * Validate contents of Raw CDR record
	 *
	 * Validate contents of Raw CDR record
	 * 
	 *
	 * @return	bool	TRUE if record is valid, FALSE otherwise				
	 *
	 * @method
	 */
	 protected function _ValidateRawCDR()
	 {
	 	foreach($this->_arrDefineCarrier as $strKey=>$strValue)
		{
			if ($strValue['Validate'])
			{
				if (!preg_match($strValue['Validate'], $this->_arrRawData[$strKey]))
				{
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	 }
	
	//------------------------------------------------------------------------//
	// _FetchRawCDR
	//------------------------------------------------------------------------//
	/**
	 * _FetchRawCDR()
	 *
	 * Fetch a field from the raw CDR
	 *
	 * Fetch a field from the raw CDR
	 * 
	 * @param	string		strKey		field key
	 *
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _FetchRawCDR($strKey)
	 {
	 	return $this->_arrRawData[$strKey];
	 }
	 
	//------------------------------------------------------------------------//
	// _NewCDR
	//------------------------------------------------------------------------//
	/**
	 * _NewCDR()
	 *
	 * Create a new default CDR record
	 *
	 * Create a new default CDR record
	 * 
	 *
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _NewCDR()
	 {
	 	$this->_arrNormalisedData = Array();
	 }
	 
	//------------------------------------------------------------------------//
	// _AppendCDR
	//------------------------------------------------------------------------//
	/**
	 * _AppendCDR()
	 *
	 * Add a field to the output CDR
	 *
	 * Add a field to the output CDR
	 * 
	 * @param	string		strKey		field key
	 * @param	mixed		mixValue	field value
	 *
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _AppendCDR($strKey, $mixValue)
	 {
	 	$this->_arrNormalisedData[$strKey] = $mixValue;
	 }
	 
	//------------------------------------------------------------------------//
	// _OutputCDR
	//------------------------------------------------------------------------//
	/**
	 * _OutputCDR()
	 *
	 * Output CDR
	 *
	 * Output CDR
	 * 
	 *
	 * @return	array					
	 *
	 * @method
	 */
	 protected function _OutputCDR()
	 {
	 	return $this->_arrNormalisedData;
	 }
	 
	//------------------------------------------------------------------------//
	// _OutputCDR
	//------------------------------------------------------------------------//
	/**
	 * _OutputCDR()
	 *
	 * Output an error CDR
	 *
	 * Output an error CDR
	 * 
	 * @param	int		intStatus		status to set in CDR
	 *
	 * @return	array					
	 *
	 * @method
	 */
	 protected function _ErrorCDR($intStatus)
	 {
	 	return array("Status" => $intStatus);
	 }
	 

	//------------------------------------------------------------------------//
	// ApplyOwnership
	//------------------------------------------------------------------------//
	/**
	 * ApplyOwnership()
	 *
	 * Applies ownership based on the FNN
	 *
	 * Applies ownership based on the FNN
	 * 
	 * @param	
	 *
	 * @return	array					
	 *
	 * @method
	 */
	 protected function ApplyOwnership()
	 {
	 	$selLinkAccounts = new StatmentSelect("Service", "AccountGroup, Account, Id", "FNN = <fnn>");
	 	$arrResult = $selLinkAccount->Execute(Array("fnn" => (string)$this->_arrNormalisedData['FNN']));
	 	
	 	if ($arrResult == 1)
	 	{
	 		$this->_arrNormalisedData['AccountGroup']	= $arrResult['AccountGroup'];
	 		$this->_arrNormalisedData['Account']		= $arrResult['Account'];
	 		$this->_arrNormalisedData['Service']		= $arrResult['Id'];
	 		return true;
	 	}
	 	
		// Return false if there was no match, or more than one match
	 	return false;
	 }

	//------------------------------------------------------------------------//
	// _GenerateUID
	//------------------------------------------------------------------------//
	/**
	 * _GenerateUID()
	 *
	 * Generate a Unique ID for a CDR record
	 *
	 * Generate a Unique ID for a CDR record
	 * 
	 * @param	int		strFileName		Name of CDR file
	 * @param	int		intSequenceNo	Sequence no. of CDR record
	 *
	 * @return	string					
	 *
	 * @method
	 */
	 protected function _GenerateUID($strFileName, $intSequenceNo)
	 {
	 	return "UID_{$strFileName}_{$intSequenceNo}";
	 }
	 
}

?>

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
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * Context of the current CDR
	 *
	 * Context of the current CDR
	 *
	 * @type	int
	 *
	 * @property
	 */
	protected $_intContext; 
	
	//------------------------------------------------------------------------//
	// _arrRawData
	//------------------------------------------------------------------------//
	/**
	 * _arrRawData
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
	 * _arrNormalisedData
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
	// _arrDefineOutput
	//------------------------------------------------------------------------//
	/**
	 * _arrDefineOutput
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

	//------------------------------------------------------------------------//
	// _selFindOwner
	//------------------------------------------------------------------------//
	/**
	 * _selFindOwner
	 *
	 * Used to associate FNN with account
	 *
	 * Used to associate FNN with account
	 *
	 * @type		StatementSelect
	 *
	 * @property
	 */	
	protected $_selFindOwner;

	//------------------------------------------------------------------------//
	// _selFindOwnerIndial100
	//------------------------------------------------------------------------//
	/**
	 * _selFindOwnerIndial100
	 *
	 * Used to associate FNN with account, including Indial100 numbers
	 *
	 * Used to associate FNN with account, including Indial100 numbers
	 *
	 * @type		StatementSelect
	 *
	 * @property
	 */
	protected $_selFindOwnerIndial100;
	
	public $strFNN;
	
	function __construct($errErrorHandler=NULL, $rptNormalisationReport=NULL)
	{
		// The purpose of this is to have a generic constructor for all Normalisation
		// modules.  It will never be called to instanciate an object of type
		// NormalisationModule, though
		
		$this->_errErrorHander 			= $errErrorHandler;
		$this->_rptNormalisationReport 	= $rptNormalisationReport;
		
		$this->_selFindOwner 			= new StatementSelect("Service", "AccountGroup, Account, Id", "FNN = <fnn> AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC", "1");
		$this->_selFindRecordType		= new StatementSelect("RecordType", "Id, Context", "ServiceType = <ServiceType> AND Code = <Code>", "", "1");
		$this->_selFindRecordCode		= new StatementSelect("RecordTypeTranslation", "Code", "Carrier = <Carrier> AND CarrierCode = <CarrierCode>", "", "1");
		$this->_selFindOwnerIndial100	= new StatementSelect("Service", "AccountGroup, Account, Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE)AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC", "1");
	
		$strTables						= "DestinationCode";
		$strData						= "Id, Code, Description";
		$strWhere						= "Carrier = <Carrier> AND CarrierCode = <CarrierCode> AND Context = <Context>";
		$this->_selFindDestination		= new StatementSelect($strTables, $strData, $strWhere, "", "1");
	
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
	 */
	function Validate()
	{
		// Validate our normalised data
		$arrValid = Array();
		
		// $this->_arrNormalisedData["Id"];
		
		// FNN : valid FNN
		$arrValid[] = preg_match("/^0\d{9}[i]?|13\d{4}|1[89]00\d{6}$/", 	$this->_arrNormalisedData["FNN"]);	// 1
		
		// CarrierRef : required (non empty)
		$arrValid[] = ($this->_arrNormalisedData["CarrierRef"] != "");											// 2
		
		// source : empty or valid FNN
		if ($this->_arrNormalisedData["Source"] != "")															// 3
		{
			$arrValid[] = preg_match("/^\d+$|^\+\d+$|^\d{5}(X+|\d+| +|\d{2}REV)I?$/", 	$this->_arrNormalisedData["Source"]);
		}
		else
		{
			$arrValid[] = true;
		}
		
		// destination : empty or valid FNN
		if ($this->_arrNormalisedData["Destination"] != "")														// 4
		{
			$arrValid[] = preg_match("/^\d+$|^\+\d+$|^\d{5}(X+|\d+| +|\d{2}REV)I?$/", 	$this->_arrNormalisedData["Destination"]);
		}
		else
		{
			$arrValid[] = true;
		}
																												// 5
		// start time : valid date/time
		$arrValid[] = preg_match("/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/",	$this->_arrNormalisedData["StartDatetime"]);

		// end time : empty or valid date/time
		if ($this->_arrNormalisedData["EndDatetime"] != "")														// 6
		{
			$arrValid[] = preg_match("/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/", $this->_arrNormalisedData["EndDatetime"]);
		}
		else
		{
			$arrValid[] = true;
		}
		
		// units : numeric
		$arrValid[] = is_numeric($this->_arrNormalisedData["Units"]);											// 7
		
		// cost : numeric
		$arrValid[] = is_numeric($this->_arrNormalisedData["Cost"]);											// 8
		
		// DestinationCode : required for any record type with a context
		if ($this->_intContext > 0)
		{
			// requires a destination code
			$arrValid[] = is_numeric($this->_arrNormalisedData["DestinationCode"]);	// 9
		}
		else
		{
			// doesn't require a destination code
			$arrValid[] = (!$this->_arrNormalisedData["DestinationCode"] || is_numeric($this->_arrNormalisedData["DestinationCode"]));	// 9
		}
		
		
		$i = 0;
		foreach ($arrValid as $bolValid)
		{
			$i++;
			if(!$bolValid)
			{
				$this->_arrNormalisedData['Status']	= CDR_CANT_NORMALISE_INVALID;
				Debug((string)$i);
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
	 * 
	 * @param	string		$strFNN		FNN to be parsed
	 *
	 * @return	boolean					true	: FNN is valid
	 * 									false	: FNN is not valid
	 *
	 * @method
	 */	
	protected function IsValidFNN($strFNN)
	{
		return preg_match("/^0\d{9}[i]?|13\d{4}|1[89]00\d{6}$/", $strFNN);
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
	 protected function _SplitRawCDR($strCDR)
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
				$this->_arrRawData[$strKey] = trim(substr($strCDR, $strValue['Start'], $strValue['Length']));
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
	 	if (is_array($this->_arrDefineCarrier))
		{
			foreach($this->_arrDefineCarrier as $strKey=>$strValue)
			{
				if ($strValue['Validate'])
				{
					if (!preg_match($strValue['Validate'], $this->_arrRawData[$strKey]))
					{
						Debug("$strKey: '".$this->_arrRawData[$strKey]."' != '".$strValue['Validate']."'");
						return FALSE;
					}
				}
			}
			return TRUE;
		}
		// retfrn false if there is no define array for the carrier (should never happen)
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
	  * @param	array		arrCDR	CDR array
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _NewCDR($arrCDR)
	 {
	 	// set CDR
	 	$this->_arrNormalisedData = $arrCDR;
		
		// set Default Context
		$this->_intContext = 0;
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
	// _ErrorCDR
	//------------------------------------------------------------------------//
	/**
	 * _ErrorCDR()
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
	 	$this->_arrNormalisedData['Status'] = $intStatus;
		return $this->_arrNormalisedData;
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
	 *
	 * @return	bool					
	 *
	 * @method
	 */
	 protected function ApplyOwnership()
	 {

	 	$intResult = $this->_selFindOwner->Execute(Array("fnn" => (string)$this->_arrNormalisedData['FNN'], "date" => (string)$this->_arrNormalisedData['StartDatetime']));
		
	 	if ($arrResult = $this->_selFindOwner->Fetch())
	 	{
	 		$this->_arrNormalisedData['AccountGroup']	= $arrResult['AccountGroup'];
	 		$this->_arrNormalisedData['Account']		= $arrResult['Account'];
	 		$this->_arrNormalisedData['Service']		= $arrResult['Id'];
	 		return true;
	 	}
	 	else
	 	{
	 		$arrParams['fnn'] = substr((string)$this->_arrNormalisedData['FNN'], 0, -2) . "__";
	 		
	 		$intResult = $this->_selFindOwnerIndial100->Execute($arrParams);
	 		if(($arrResult = $this->_selFindOwnerIndial100->Fetch()))
	 		{
	 			$this->_arrNormalisedData['AccountGroup']	= $arrResult['AccountGroup'];
	 			$this->_arrNormalisedData['Account']		= $arrResult['Account'];
	 			$this->_arrNormalisedData['Service']		= $arrResult['Id'];
	 			return true;
	 		}
	 	}
	 	
		// Return false if there was no match, or more than one match
		$this->_arrNormalisedData['Status']	= CDR_BAD_OWNER;
		//Debug("Cannot match FNN: ".$this->_arrNormalisedData['FNN']);
		$this->strFNN = $this->_arrNormalisedData['FNN'];
	 	return false;
	 }
	
	//------------------------------------------------------------------------//
	// FindRecordCode
	//------------------------------------------------------------------------//
	/**
	 * FindRecordCode()
	 *
	 * Find the Vixen record type from a Carrier Record type code
	 *
	 * Find the Vixen record type from a Carrier Record type code
	 * 
	 *
	 * @param	mixed	mixCarrierCode		Carrier Record type code
	 * @return	string	Record Type Code					
	 *
	 * @method
	 */
	 protected function FindRecordCode($mixCarrierCode)
	 {

	 	$intResult = $this->_selFindRecordCode->Execute(Array("Carrier" => $this->_arrNormalisedData["Carrier"], "CarrierCode" => $mixCarrierCode));
		
	 	if ($arrResult = $this->_selFindRecordCode->Fetch())
	 	{
	 		return $arrResult['Code'];
	 	}
	 	
		// Return false if there was no match
		$this->_arrNormalisedData['Status']	= CDR_BAD_RECORD_TYPE;
	 	return false;
	 }
	
	//------------------------------------------------------------------------//
	// FindRecordType
	//------------------------------------------------------------------------//
	/**
	 * FindRecordType()
	 *
	 * Find the record type from a Service Type & Record Code
	 *
	 * Find the record type from a Service Type & Record Code
	 * 
	 *
	 * @param	int		intServiceType		Service Type Constant
	 * @param	string	strRecordCode		Vixen Record Type Code
	 * @return	int		Record Type Id					
	 *
	 * @method
	 */
	 protected function FindRecordType($intServiceType, $strRecordCode)
	 {

	 	$intResult = $this->_selFindRecordType->Execute(Array("ServiceType" => $intServiceType, "Code" => $strRecordCode));
		
	 	if ($arrResult = $this->_selFindRecordType->Fetch())
	 	{
			$this->_intContext = $arrResult['Context'];
	 		return $arrResult['Id'];
	 	}
		
		// Return false if there was no match
		$this->_arrNormalisedData['Status']	= CDR_BAD_RECORD_TYPE;
	 	return false;
	 }
	 

	//------------------------------------------------------------------------//
	// FindDestination
	//------------------------------------------------------------------------//
	/**
	 * FindDestination()
	 *
	 * Find the Destination Details from a Carrier Destination code
	 *
	 * Find the Destination Details from a Carrier Destination code
	 * 
	 *
	 * @param	mixed	mixCarrierCode		Carrier Destination code
	 * @return	array	Destination Details, Code & Description		
	 *
	 * @method
	 */
	 protected function FindDestination($mixCarrierCode)
	 {
	 	$arrData = Array("Carrier" => $this->_arrNormalisedData["Carrier"], "CarrierCode" => $mixCarrierCode, "Context" => $this->_intContext);
		$intResult = $this->_selFindDestination->Execute($arrData);
		
	 	if ($arrResult = $this->_selFindDestination->Fetch())
	 	{
	 		return $arrResult;
	 	}
	 	
		//TODO!!!! - add this to a report so we can see any missing destinations

		// Set an error status
		$this->_arrNormalisedData['Status']	= CDR_BAD_DESTINATION;
		
		// Return false if there was no match
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
	 *
	 * @return	string					
	 *
	 * @method
	 */
	 protected function _GenerateUID()
	 {
	 	return "UID_{$this->_arrNormalisedData["FileName"]}_{$this->_arrNormalisedData["SequenceNo"]}";
	 }
	 
	//------------------------------------------------------------------------//
	// _IsInbound
	//------------------------------------------------------------------------//
	/**
	 * _IsInbound()
	 *
	 * Check if an FNN is an Inbound Service
	 *
	 * Check if an FNN is an Inbound Service
	 * 
	 * @param	string	strFNN		FNN to check
	 * @return	bool					
	 *
	 * @method
	 */
	 protected function _IsInbound($strFNN)
	 {
	 	$strPrefix = substr(trim($strFNN), 0, 2);
	 	if ($strPrefix === '13' || $strPrefix === '18')
		{
			return TRUE;
		}
		return FALSE;
	 }
	 
	 
	//------------------------------------------------------------------------//
	// _IsCredit
	//------------------------------------------------------------------------//
	/**
	 * _IsCredit()
	 *
	 * Check if this CDR is a credit, and changes normalised data to suit.
	 *
	 * Check if this CDR is a credit, and changes normalised data to suit.
	 * Must be called after Cost and Units are set.
	 *
	 * @return	bool					
	 *
	 * @method
	 */
	 protected function _IsCredit()
	 {
	 	if(!isset($this->_arrNormalisedData['Units']) || !isset($this->_arrNormalisedData['Cost']))
	 	{
	 		// Either Units or Cost are not set yet
	 		return FALSE;
	 	}
	 	
	 	$intUnits	= (int)$this->_arrNormalisedData['Units'];
	 	$fltCost	= (float)$this->_arrNormalisedData['Cost'];
	 	if ($intUnits < 0 && $fltCost < 0.0)
	 	{
	 		$intUnits	= abs($intUnits);
	 		$fltCost	= abs($fltCost);
	 		$this->_AppendCDR('Credit', 1);
	 		return TRUE;
	 	}
	 	else
	 	{
	 		$this->_AppendCDR('Credit', 0);
	 		return FALSE;
	 	}
	 }
	 
}

?>

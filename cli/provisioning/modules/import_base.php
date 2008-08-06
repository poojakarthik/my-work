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
		
		$this->_selLineStatus			= new StatementSelect("Service", "LineStatus, LineStatusDate, PreselectionStatus, PreselectionStatusDate", "Id = <Id>");
		
		$arrColumns	= Array();
		$arrColumns['LineStatus']				= NULL;
		$arrColumns['LineStatusDate']			= NULL;
		$arrColumns['PreselectionStatus']		= NULL;
		$arrColumns['PreselectionStatusDate']	= NULL;
		$this->_ubiLineStatus			= new StatementUpdateById("Service", $arrColumns);
		
		$this->_selLineStatusAction		= new StatementSelect("provisioning_type LEFT JOIN service_line_status_update ON service_line_status_update.provisioning_type = provisioning_type.id", "new_line_status, provisioning_type_nature", "(current_line_status = <LineStatus> OR current_line_status IS NULL) AND provisioning_request_status = <RequestStatus> AND provisioning_type = <Request>", "ISNULL(current_line_status) ASC", 1);

		$this->_selProvisioningType		= new StatementSelect("provisioning_type", "*", "id = <id>");
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
 	
 	
 	//------------------------------------------------------------------------//
	// FindFNNOwner
	//------------------------------------------------------------------------//
	/**
	 * FindFNNOwner()
	 *
	 * Finds the owner of the FNN for this Response
	 *
	 * Finds the owner of the FNN for this Response
	 * 
	 * @param	array	$arrPDR			Provisioning Data Record to find an owner for
	 * 
	 * @return	array					Modified $arrPDR with Ownership details or Status set to RESPONSE_STATUS_BAD_OWNER				
	 *
	 * @method
	 */
	 function FindFNNOwner($arrPDR)
	 {
		// Find Owner
		if (is_array($arrOwner = FindFNNOwner($arrPDR['FNN'], $arrPDR['EffectiveDate'])))
		{
			$arrPDR = array_merge($arrOwner, $arrPDR);
		}
		else
		{
			$arrPDR['Status']	= RESPONSE_STATUS_BAD_OWNER;
		}
	 	
	 	return $arrPDR;
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// UpdateLineStatus
	//------------------------------------------------------------------------//
	/**
	 * UpdateLineStatus()
	 *
	 * Checks the Line Status and updates if necessary
	 *
	 * Checks the Line Status and updates if necessary
	 * 
	 * @param	integer	$intService				Id for the Service to update
	 * @param	array	$arrResponse			The Response received from the Carrier
	 * @param	string	$strEffectiveDate		Id for the Service to update
	 * 
	 * @return	mixed							TRUE: Pass; string: Error Message; FALSE: Redundant				
	 *
	 * @method
	 */
	function UpdateLineStatus($intService, $arrResponse, $strEffectiveDate)
	{
		// Get Current Line Status for the Service
		if ($this->_selLineStatus->Execute(Array('Service' => $intService)))
		{
			$arrLineStatus	= $this->_selLineStatus->Fetch();
			
			// Get the Provisioning Type Nature Details
			if ($this->_selProvisioningType->Execute(Array('id' => $arrResponse['Type'])))
			{
				$arrProvisioningType	= $this->_selProvisioningType->Fetch();
				if ($arrProvisioningType['provisioning_type_nature'] === REQUEST_TYPE_NATURE_PRESELECTION)
				{
					// Land Line Preselection Status
					$strCurrentEffectiveDate	= &$arrLineStatus['PreselectionStatusDate'];
					$intCurrentLineStatus		= &$arrLineStatus['PreselectionStatus'];
				}
				else
				{
					// Line Status
					$strCurrentEffectiveDate	= &$arrLineStatus['LineStatusDate'];
					$intCurrentLineStatus		= &$arrLineStatus['LineStatus'];
				}
				
				// Is this Status newer than the current Status?
				if ($strEffectiveDate > $strCurrentEffectiveDate)
				{
					// Current Status is older than this Status
					$intCurrentLineStatus	= NULL;
					
				}
				elseif ($strEffectiveDate === $strCurrentEffectiveDate)
				{
					// Same Date
					$intActionLineStatus	= $intCurrentLineStatus;
				}
				else
				{
					// Current Status is newer, don't update
					return FALSE;
				}
				
				// Get the Update Details for the Current Status + the Request Type
				if ($this->_selLineStatusAction->Execute(Array('LineStatus' => $intCurrentLineStatus, 'Request' => $arrProvisioningType['id'], 'RequestStatus' => $arrResponse['RequestStatus'])))
				{
					$arrLineStatusAction		= $this->_selLineStatusAction->Fetch();
					$strCurrentEffectiveDate	= $strEffectiveDate;
					$intCurrentLineStatus		= $arrLineStatusAction['new_line_status'];
					
					// Save the new Line Status
					if ($this->_ubiLineStatus->Execute($arrLineStatus) === FALSE)
					{
						return "DB Error for _ubiLineStatus: ".$this->_ubiLineStatus->Error();
					}
					else
					{
						return TRUE;
					}
				}
				else
				{
					// No Definition or Default for this Relationship, don't update
					return TRUE;
				}
			}
			elseif ($this->_selProvisioningType->Error())
			{
				// Error
				return "DB Error for _selProvisioningType: ".$this->_selProvisioningType->Error();
			}
			else
			{
				return "Unable to retrieve ProvisioningType details for id '{$arrResponse['Type']}'";
			}
		}
		elseif ($this->_selLineStatus->Error())
		{
			// Error
			return "DB Error for _selLineStatus: ".$this->_selLineStatus->Error();
		}
		else
		{
			return "Unable to retrieve Line Status Details for Service '{$intService}'";
		}
	}
 }
?>
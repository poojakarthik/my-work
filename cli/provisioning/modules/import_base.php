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
 		$this->_selRequestByCarrierRef	= new StatementSelect("ProvisioningRequest", "*", "CarrierRef = <CarrierRef>");
 		$this->_selRequestByFNN			= new StatementSelect(	"ProvisioningRequest", "*", 
																"Carrier = <Carrier> AND FNN = <FNN> AND Type = <Type> AND RequestedOn < <EffectiveDate>");
		$this->_selTranslateCarrierCode	= new StatementSelect("ProvisioningTranslation", "flex_code", "Context = <Context> AND CarrierCode = <CarrierCode>");
		
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
 			return $arrReturn;
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
	 	return $arrValue['flex_code'];
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
		if (is_array($arrOwner = FindFNNOwner($arrPDR['FNN'], $arrPDR['EffectiveDate'], TRUE)))
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
	 * @param	array	$arrResponse			The Response received from the Carrier
	 * 
	 * @return	mixed							TRUE: Pass; string: Error Message; FALSE: Redundant				
	 *
	 * @method
	 */
	static function UpdateLineStatus($arrResponse)
	{
		//Debug($arrResponse);
		
		// Init Statements
		static $selLineStatus		= NULL;
		static $selProvisioningType	= NULL;
		static $selLineStatusAction	= NULL;
		static $ubiLineStatus		= NULL;
		
		if (!isset($selLineStatus))
		{
			$selLineStatus			= new StatementSelect("Service", "Id, LineStatus, LineStatusDate, PreselectionStatus, PreselectionStatusDate", "Id = <Service>");
		}
		if (!isset($selProvisioningType))
		{
			$selProvisioningType	= new StatementSelect("provisioning_type", "*", "id = <id>");
		}
		if (!isset($selLineStatusAction))
		{
			$selLineStatusAction	= new StatementSelect("provisioning_type LEFT JOIN service_line_status_update ON service_line_status_update.provisioning_type = provisioning_type.id", "new_line_status, provisioning_type_nature", "(current_line_status = <LineStatus> OR current_line_status IS NULL) AND provisioning_request_status = <RequestStatus> AND provisioning_type = <Request>", "ISNULL(current_line_status) ASC", 1);
		}
		if (!isset($ubiLineStatus))
		{
			$arrColumns	= Array();
			$arrColumns['LineStatus']				= NULL;
			$arrColumns['LineStatusDate']			= NULL;
			$arrColumns['PreselectionStatus']		= NULL;
			$arrColumns['PreselectionStatusDate']	= NULL;
			$ubiLineStatus			= new StatementUpdateById("Service", $arrColumns);
		}
		
		// Get Current Line Status for the Service
		if ($selLineStatus->Execute($arrResponse))
		{
			$arrLineStatus	= $selLineStatus->Fetch();
			
			// Get the Provisioning Type Nature Details
			if ($selProvisioningType->Execute(Array('id' => $arrResponse['Type'])))
			{
				$arrProvisioningType	= $selProvisioningType->Fetch();
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
				if (strtotime($arrResponse['EffectiveDate']) > strtotime($strCurrentEffectiveDate))
				{
					// Current Status is older than this Status
					$intCurrentLineStatus	= NULL;
					
				}
				elseif (strtotime($arrResponse['EffectiveDate']) === strtotime($strCurrentEffectiveDate))
				{
					// Same Date
					$intActionLineStatus	= $intCurrentLineStatus;
				}
				else
				{
					// Current Status is newer, don't update
					//CliEcho("({$arrResponse['Id']}) -- Current Status ($strCurrentEffectiveDate) is newer than ({$arrResponse['EffectiveDate']})");
					return FALSE;
				}
				
				// Get the Update Details for the Current Status + the Request Type
				if ($selLineStatusAction->Execute(Array('LineStatus' => $intCurrentLineStatus, 'Request' => $arrProvisioningType['id'], 'RequestStatus' => $arrResponse['request_status'])))
				{
					$arrLineStatusAction		= $selLineStatusAction->Fetch();
					$strCurrentEffectiveDate	= $arrResponse['EffectiveDate'];
					$intCurrentLineStatus		= $arrLineStatusAction['new_line_status'];
					
					// Save the new Line Status
					if ($ubiLineStatus->Execute($arrLineStatus) === FALSE)
					{
						return "DB Error for _ubiLineStatus: ".$ubiLineStatus->Error();
					}
					else
					{
						//CliEcho("Line Status Updated to ".GetConstantDescription($arrLineStatusAction['new_line_status'], 'service_line_status'));
						return TRUE;
					}
				}
				else
				{
					// No Definition or Default for this Relationship, don't update
					CliEcho("No Definition for {$arrResponse['Id']} (LineStatus: {$intCurrentLineStatus}; Type: {$arrProvisioningType['id']}; RequestStatus: {$arrResponse['request_status']}) -- not updating");
					return TRUE;
				}
			}
			elseif ($selProvisioningType->Error())
			{
				// Error
				return "DB Error for _selProvisioningType: ".$selProvisioningType->Error();
			}
			else
			{
				return "Unable to retrieve ProvisioningType details for id '{$arrResponse['Type']}'";
			}
		}
		elseif ($selLineStatus->Error())
		{
			// Error
			return "DB Error for _selLineStatus: ".$selLineStatus->Error();
		}
		else
		{
			Debug($arrResponse);
			return "Unable to retrieve Line Status Details for Service '{$arrResponse['Service']}'";
		}
	}
 }
?>
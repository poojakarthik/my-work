<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_commander
//----------------------------------------------------------------------------//
/**
 * module_commander.php
 *
 * Normalisation module for Commander Mobile batch files
 *
 * Normalisation module for Commander Mobile batch files
 *
 * @file			module_commander.php
 * @language		PHP
 * @package			vixen
 * @author			Rich Davis
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// NormalisationModuleCommander
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleCommander
 *
 * Normalisation module for Commander Mobile batch files
 *
 * Normalisation module for Commander Mobile batch files
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			<ClassName||InstanceName>
 */
class NormalisationModuleCommander extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_UNITEL;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Normalising Module
	 *
	 * Constructor for the Normalising Module
	 *
	 *
	 * @method
	 */
	function __construct($intCarrier)
	{
		// call parent constructor
		parent::__construct($intCarrier);
		
		// define the column delimiter
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the carrier CDR format
		$arrDefine ['EventId']			['Index']		= 0;	// Unique Identifier
		$arrDefine ['RecordType']		['Index']		= 1;	//
		$arrDefine ['Datetime']			['Index']		= 2;	// Starting Datetime of the call
		$arrDefine ['Duration']			['Index']		= 3;	// Duration in seconds
		$arrDefine ['OriginNo']			['Index']		= 4;	// Originating phone number
		$arrDefine ['DestinationNo']	['Index']		= 5;	// Destination phone number
		$arrDefine ['ChargedParty']		['Index']		= 6;	// Charged phone number
		$arrDefine ['Currency']			['Index']		= 7;	// Usually AUD
		$arrDefine ['Price']			['Index']		= 8;	// Price charged to VOIPTel
		$arrDefine ['PlanId']			['Index']		= 9;	// Unitel Plan ID
		$arrDefine ['CallType']			['Index']		= 10;	// Unitel Call Type
		$arrDefine ['Feedcode']			['Index']		= 11;	// Will always be 5
		$arrDefine ['RateId']			['Index']		= 12;	// Unitel Rate ID
		$arrDefine ['Location']			['Index']		= 13;	// "Loose" location of call
		$arrDefine ['TotalKB']			['Index']		= 14;	// Total KB of transfer (CallType 336 only)

		$arrDefine ['EventId']			['Validate']	= "/^\d+$/";
		$arrDefine ['RecordType']		['Validate']	= "/^[178]$/";
		$arrDefine ['Datetime']			['Validate']	= "/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/";
		$arrDefine ['Duration']			['Validate']	= "/^-?\d+$/";
		$arrDefine ['OriginNo']			['Validate']	= "/^(\+?\d+(REV|I)?|)$/";
		$arrDefine ['DestinationNo']	['Validate']	= "/^(\+?\d+(REV|I)?|)$/";
		$arrDefine ['ChargedParty']		['Validate']	= "/^\+?\d+$/";
		$arrDefine ['Currency']			['Validate']	= "/^AUD$/";
		$arrDefine ['Price']			['Validate']	= "/^-?\d+\.\d\d*$/";
		$arrDefine ['CallType']			['Validate']	= "/^\d+$/";
		$arrDefine ['RateId']			['Validate']	= "/^\d+$/";
		
		$this->_arrDefineCarrier = $arrDefine;
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
	function Normalise($arrCDR)
	{
		// set up CDR
		$this->_NewCDR($arrCDR);
		
		// ignore header rows
		if ((int)$arrCDR["SequenceNo"] < 1)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_BAD_SEQ_NO);
		}
		elseif ((int)$arrCDR["SequenceNo"] < $this->_intStartRow)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_HEADER);
		}
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR"]);

		// ignore non-CDR rows
		$intRowType = (int)$this->_FetchRawCDR('RecordType');
		if ($intRowType != 1 && $intRowType != 7 && $intRowType != 8)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		
		// FNN
		$mixValue 						= $this->_FetchRawCDR('ChargedParty');
		$this->_AppendCDR('FNN', $this->RemoveAusCode($mixValue));
		
		// ServiceType
		$intServiceType = SERVICE_TYPE_MOBILE;
		$this->_AppendCDR('ServiceType', $intServiceType);
		
		// RecordType
		$mixRateId 						= $this->_FetchRawCDR('RateId');
		$mixCallType					= ($mixRateId === '915') ? '915' : $this->_FetchRawCDR('CallType');		// Telstra On-Bill
		$strRecordCode 					= $this->FindRecordCode($mixCallType);
		$intRecordType 					= $this->FindRecordType($intServiceType, $strRecordCode); 
		$this->_AppendCDR('RecordType', $intRecordType);

		// Destination Code & Description (only if we have a context)
		if ($this->_intContext > 0)
		{
			$arrDestinationCode 			= $this->FindDestination($mixRateId);
			$this->_AppendCDR('DestinationCode', $arrDestinationCode['Code']);
			
			// Determine Description
			if ($arrDestinationCode['bolUnknownDestination'] === true)
			{
				// Use the Raw Description
				$strDescription	= $this->RawDescription();
			}
			else
			{
				// Use the Destination's Description
				$strDescription = $arrDestinationCode['Description'];
			}
			$this->_AppendCDR('Description', $strDescription);
		}
		
		// CarrierRef
		$mixValue 						= $this->_FetchRawCDR('EventId');
		$this->_AppendCDR('CarrierRef', $mixValue);

		// StartDateTime & EndDateTime
	 	$mixValue						= $this->_FetchRawCDR('Datetime');
	 	$this->_AppendCDR('StartDatetime', $mixValue);
		 	
	 	$mixValue						= date("Y-m-d H:i:s", strtotime($this->_FetchRawCDR('Datetime') . " +" . abs($this->_FetchRawCDR('Duration')) . "seconds"));
		$this->_AppendCDR('EndDatetime', $mixValue);
		
		// Units
		//TODO!!!! - Is this correct!?!?!
		if ($this->_FetchRawCDR('CallType') == "336")
		{
		 	// For Data calls
		 	$mixValue					= $this->_FetchRawCDR('TotalKB');
		}
		elseif($intRecordType == 10 || $intRecordType == 15)
		{
			// SMS & MMS have nothing to specify units <<STUPID!!!!!!!>>
			$mixValue					=  1;
		}
		else
		{
		 	// For normal calls
		 	$mixValue					=  $this->_FetchRawCDR('Duration');
		 	
		}
		$this->_AppendCDR('Units', (int)$mixValue);
		
		// Description
		$strDescription = '';
		//TODO-LATER !!!! - add description
		if ($strDescription)
		{
			$this->_AppendCDR('Description', $strDescription);
		}

		// Cost
		$mixValue						=  $this->_FetchRawCDR('Price');
		$this->_AppendCDR('Cost', (float)$mixValue);

		// Source
		$mixValue 						= $this->_FetchRawCDR('OriginNo');
		$this->_AppendCDR('Source', $this->RemoveAusCode($mixValue));
		
		// Destination
		$mixValue = $this->_FetchRawCDR('DestinationNo');
		$this->_AppendCDR('Destination', $this->RemoveAusCode($mixValue));
		
		// Is Credit?
		$this->_IsCredit();
		
		//--------------------------------------------------------------------//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}

	//------------------------------------------------------------------------//
	// ConvertTime
	//------------------------------------------------------------------------//
	/**
	 * ConvertTime()
	 *
	 * Convert time format
	 *
	 * Converts a datetime string from carrier's format to our own
	 *
	 * @param	string	$strTime	Datetime string to be converted
	 * @return	string				Converted Datetime string
	 *
	 * @method
	 */
	function ConvertTime($strTime)
	{
		$strReturn 	= substr($strTime, 6, 4);				// Year
		$strReturn .=  "-" . substr($strTime, 3, 2);		// Month
		$strReturn .=  "-" . substr($strTime, 0, 2);		// Day
		$strReturn .=  substr($strTime, 11, 18);			// Time
		
		return $strReturn;
	}
	
	//------------------------------------------------------------------------//
	// RawDestinationCode
	//------------------------------------------------------------------------//
	/**
	 * RawDestinationCode()
	 *
	 * Returns the Raw Destination Code from the CDR
	 *
	 * Returns the Raw Destination Code from the CDR
	 * 
	 *
	 * @return	mixed					Raw Destination Code
	 *
	 * @method
	 */
	 function RawDestinationCode()
	 {
	 	return $this->_FetchRawCDR('RateId');
	 }
	 
	//------------------------------------------------------------------------//
	// RawDescription
	//------------------------------------------------------------------------//
	/**
	 * RawDescription()
	 *
	 * Returns the Raw Description from the CDR
	 *
	 * Returns the Raw Description from the CDR
	 * 
	 *
	 * @return	mixed					Raw Description
	 *
	 * @method
	 */
	 function RawDescription()
	 {
	 	return $this->_FetchRawCDR('Location');
	 }
	 
	//------------------------------------------------------------------------//
	// RawRecordType
	//------------------------------------------------------------------------//
	/**
	 * RawRecordType()
	 *
	 * Returns the Raw RawRecord Type from the CDR
	 *
	 * Returns the Raw RawRecord Type from the CDR
	 * 
	 *
	 * @return	mixed					Raw RawRecord Type
	 *
	 * @method
	 */
	 function RawRecordType()
	 {
	 	return (int)$this->_FetchRawCDR('CallType');
	 }
}
	
	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleCommander
	//------------------------------------------------------------------------//
?>

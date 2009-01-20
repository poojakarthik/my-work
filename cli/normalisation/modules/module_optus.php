<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_optus
//----------------------------------------------------------------------------//
/**
 * module_optus.php
 *
 * Normalisation module for Optus batch files
 *
 * Normalisation module for Optus batch files
 *
 * @file			module_optus.php
 * @language		PHP
 * @package			vixen
 * @author			Jared 'flame' Herbohn
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// NormalisationModuleOptus
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleOptus
 *
 * Normalisation module for Optus batch files
 *
 * Normalisation module for Optus batch files
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			NormalisationModuleOptus
 */
class NormalisationModuleOptus extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_OPTUS;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD;
	
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
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 1;
		
		
		// define the carrier CDR format
		$arrDefine ['RecordType']	['Start']		= 0;
		$arrDefine ['RecordType']	['Length']		= 2;
		
		$arrDefine ['CDRId']		['Start']		= 12;
		$arrDefine ['CDRId']		['Length']		= 2;
		
		$arrDefine ['AccountNo']	['Start']		= 14;
		$arrDefine ['AccountNo']	['Length']		= 14;
		
		$arrDefine ['ServiceNo']	['Start']		= 28;
		$arrDefine ['ServiceNo']	['Length']		= 24;
		
		$arrDefine ['ServiceNoType']['Start']		= 52;
		$arrDefine ['ServiceNoType']['Length']		= 6;
		
		$arrDefine ['PointOrigin']	['Start']		= 58;
		$arrDefine ['PointOrigin']	['Length']		= 24;
		
		$arrDefine ['PointTarget']	['Start']		= 82;
		$arrDefine ['PointTarget']	['Length']		= 24;
		
		$arrDefine ['Jurisdiction']	['Start']		= 106;
		$arrDefine ['Jurisdiction']	['Length']		= 6;
		
		$arrDefine ['CallDate']		['Start']		= 112;
		$arrDefine ['CallDate']		['Length']		= 14;
				
		$arrDefine ['BillClass']	['Start']		= 126;
		$arrDefine ['BillClass']	['Length']		= 6;
		
		$arrDefine ['TypeIdUsage']	['Start']		= 132;
		$arrDefine ['TypeIdUsage']	['Length']		= 6;
		
		$arrDefine ['ElementId']	['Start']		= 138;
		$arrDefine ['ElementId']	['Length']		= 6;
		
		$arrDefine ['Units']		['Start']		= 144;
		$arrDefine ['Units']		['Length']		= 10;
		
		$arrDefine ['Completed']	['Start']		= 154;
		$arrDefine ['Completed']	['Length']		= 3;
		
		$arrDefine ['Latitude']		['Start']		= 157;
		$arrDefine ['Latitude']		['Length']		= 8;
		
		$arrDefine ['Longitude']	['Start']		= 165;
		$arrDefine ['Longitude']	['Length']		= 8;
		
		$arrDefine ['OriginDesc']	['Start']		= 173;
		$arrDefine ['OriginDesc']	['Length']		= 32;
		
		$arrDefine ['TargetDesc']	['Start']		= 205;
		$arrDefine ['TargetDesc']	['Length']		= 32;
		
		$arrDefine ['RatePeriod']	['Start']		= 237;
		$arrDefine ['RatePeriod']	['Length']		= 1;
		
		$arrDefine ['RatedUnits']	['Start']		= 238;
		$arrDefine ['RatedUnits']	['Length']		= 10;
		
		$arrDefine ['ThirdUnits']	['Start']		= 258;		// Unused
		$arrDefine ['ThirdUnits']	['Length']		= 10;
		
		$arrDefine ['FileId']		['Start']		= 268;
		$arrDefine ['FileId']		['Length']		= 10;
		
		$arrDefine ['OldSeqNo']		['Start']		= 278;		// For withdrawn CDRs only
		$arrDefine ['OldSeqNo']		['Length']		= 6;
		
		$arrDefine ['Amount']		['Start']		= 284;		// Charge in cents
		$arrDefine ['Amount']		['Length']		= 8;
		
		$arrDefine ['RateClass']	['Start']		= 292;
		$arrDefine ['RateClass']	['Length']		= 6;
		
		$arrDefine ['ProviderClass']['Start']		= 298;
		$arrDefine ['ProviderClass']['Length']		= 6;
		
		$arrDefine ['ProviderId']	['Start']		= 304;
		$arrDefine ['ProviderId']	['Length']		= 6;
		
		$arrDefine ['CurrencyCode']	['Start']		= 310;
		$arrDefine ['CurrencyCode']	['Length']		= 6;
		
		$arrDefine ['EquimentType']	['Start']		= 316;
		$arrDefine ['EquimentType']	['Length']		= 6;
		
		$arrDefine ['ServiceClass']	['Start']		= 322;		// Unused
		$arrDefine ['ServiceClass']	['Length']		= 6;
		
		$arrDefine ['RateUnitsType']['Start']		= 328;
		$arrDefine ['RateUnitsType']['Length']		= 6;
		
		$arrDefine ['DistBandId']	['Start']		= 334;
		$arrDefine ['DistBandId']	['Length']		= 3;
		
		$arrDefine ['ZoneClass']	['Start']		= 337;
		$arrDefine ['ZoneClass']	['Length']		= 3;
		
		$arrDefine ['CDRStatus']	['Start']		= 340;
		$arrDefine ['CDRStatus']	['Length']		= 3;
		
		
		$arrDefine ['ServiceNo']	['Validate']	= "/^\+?\d+$/";
		$arrDefine ['CDRId']		['Validate']	= "/^\d+$/";
		$arrDefine ['CallDate']		['Validate']	= "/^\d{14}$/";
		$arrDefine ['Units']		['Validate']	= "/^\d+$/";
		$arrDefine ['RecordType']	['Validate']	= "/^5[10]$/";
		$arrDefine ['PointOrigin']	['Validate']	= "/^\d+$/";
		$arrDefine ['PointTarget']	['Validate']	= "/^\d+$/";
		$arrDefine ['Amount']		['Validate']	= "/^\d+$/";
		
		$this->_arrDefineCarrier = $arrDefine;
		
		//##----------------------------------------------------------------##//
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
	 * @return	mixed					Normalised Data (Array, ready for direct UPDATE
	 * 									into DB. Returns an error code (constant) on failure
	 *
	 * @method
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

		// Make sure the record is a CDR
		$strRecordType = $this->_FetchRawCDR('RecordType');
		if ($strRecordType != "50" && $strRecordType != "51")
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}
	
		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
		//##----------------------------------------------------------------##//
		// add fields to CDR
		//##----------------------------------------------------------------##//
		
		//--------------------------------------------------------//
		// Required Fields
		//--------------------------------------------------------//
		
		// FNN
		$strFNN 						= $this->_FetchRawCDR('ServiceNo');
		$this->_AppendCDR('FNN', $strFNN);
		
		// ServiceType
		if ($this->_IsInbound($strFNN))
		{
			$intServiceType 			= SERVICE_TYPE_INBOUND;
			$this->_AppendCDR('Description', $this->_FetchRawCDR('PointOrigin'));
		}
		else
		{
			$intServiceType 			= SERVICE_TYPE_LAND_LINE;
		}
		$this->_AppendCDR('ServiceType', $intServiceType);
		
		// RecordType
		$mixCarrierCode					= (int)$this->_FetchRawCDR('TypeIdUsage');
		$strRecordCode 					= $this->FindRecordCode((string)$mixCarrierCode);
		$mixValue 						= $this->FindRecordType($intServiceType, $strRecordCode); 
		$this->_AppendCDR('RecordType', $mixValue);
		
		// DestinationCode & Description (only if we have a context)
		if ($this->_intContext > 0)
		{
			$mixCarrierCode 				= (int)$this->_FetchRawCDR('Jurisdiction');
			$arrDestinationCode 			= $this->FindDestination($mixCarrierCode);
			
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
		$mixValue 						= $this->_FetchRawCDR('CDRId');
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue 						= $this->ConvertTime($this->_FetchRawCDR('CallDate'));
		$this->_AppendCDR('StartDatetime', $mixValue);
		
		// Units
		if ($strRecordType == "51")
		{
			// Credit
			$mixValue 						= 0 - (int)$this->_FetchRawCDR('Units');
		}
		else
		{
			$mixValue 						= $this->_FetchRawCDR('Units');
		}
		$this->_AppendCDR('Units', $mixValue);
		
		// Description
		$strDescription = '';
		//TODO-LATER !!!! - add description
		if ($strDescription)
		{
			$this->_AppendCDR('Description', $strDescription);
		}

		//--------------------------------------------------------//
		// Optional Fields
		//--------------------------------------------------------//

		// Source
		$mixValue 						= $this->_FetchRawCDR('PointOrigin');
		$this->_AppendCDR('Source', $mixValue);
		
		// Destination
		$mixValue 						= $this->_FetchRawCDR('PointTarget');
		$this->_AppendCDR('Destination', $mixValue);
		
		// EndDatetime
		$intTimestamp					= strtotime(" +" . $this->_FetchRawCDR('Units') . "seconds", strtotime($this->_FetchRawCDR('CallDate')));
		$mixValue 						= date("Y-m-d H:i:s", $intTimestamp);
		$this->_AppendCDR('EndDatetime', $mixValue);
		
		// Cost
		if ($strRecordType == "51")
		{
			$mixValue 						= 0 - ((float)$this->_FetchRawCDR('Amount') / 100);
		}
		else
		{
			$mixValue 						= ((float)$this->_FetchRawCDR('Amount') / 100);
		}
		$this->_AppendCDR('Cost', $mixValue);

		//##----------------------------------------------------------------##//
		
		// Is Credit?
		$this->_IsCredit();
		
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
		$strReturn 	= substr($strTime, 0, 4);				// Year
		$strReturn .=  "-" . substr($strTime, 4, 2);		// Month
		$strReturn .=  "-" . substr($strTime, 6, 2);		// Day
		$strReturn .=  " ".substr($strTime, 8, 2) . ":" . substr($strTime, 10, 2) . ":" . substr($strTime, 12, 2);			// Time
		
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
	 	return $this->_FetchRawCDR('Jurisdiction');
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
	 	return $this->_FetchRawCDR('TargetDesc');
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
	 	return (int)$this->_FetchRawCDR('TypeIdUsage');
	 }
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleOptus
	//------------------------------------------------------------------------//
	
	// define any constants here that will only ever be used internaly by 
	// the module. Prefix the constants with the module name.
?>

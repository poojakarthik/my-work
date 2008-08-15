<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_iseek
//----------------------------------------------------------------------------//
/**
 * module_iseek.php
 *
 * Normalisation module for iSeek batch files
 *
 * Normalisation module for iSeek batch files
 *
 * @file			module_iseek.php
 * @language		PHP
 * @package			vixen
 * @author			Jared 'flame' Herbohn
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// NormalisationModuleIseek
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleIseek
 *
 * Normalisation module for iSeek batch files
 *
 * Normalisation module for iSeek batch files
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			NormalisationModuleIseek
 */
class NormalisationModuleIseek extends NormalisationModule
{
	public $intBaseCarrier	= CARRIER_ISEEK;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_ADSL1;
	
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
		$this->_strDelimiter = "\t";
		
		// define row start (account for header rows)
		$this->_intStartRow = 2;
		
		// define the carrier CDR format
		$arrDefine ['RecordType']	['Index']	= 0;
		$arrDefine ['Service']		['Index']	= 1;
		$arrDefine ['Megabytes']	['Index']	= 2;
		$arrDefine ['DateStart']	['Index']	= 3;
		$arrDefine ['DateEnd']		['Index']	= 4;
		
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
	
		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		
		// FNN
		$mixValue 						= $this->_FetchRawCDR('Service');
		$this->_AppendCDR('FNN', $mixValue);
		
		// CarrierRef
		$mixValue 						= $this->_GenerateUID();
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue 						= $this->ConvertTime($this->_FetchRawCDR('DateStart'));
		$this->_AppendCDR('StartDatetime', $mixValue);
		
		// EndDatetime
		$mixValue 						= $this->ConvertTime($this->_FetchRawCDR('DateEnd'));
		$this->_AppendCDR('EndDatetime', $mixValue);
		
		// Units
		$mixValue 						= (int)($this->_FetchRawCDR('Megabytes') * 1024);
		$this->_AppendCDR('Units', $mixValue);
		
		// Description
		$mixValue 						= ISEEK_ADSL_USAGE_DESCRIPTION;
		$this->_AppendCDR('Description', $mixValue);
		
		// ServiceType
		$intServiceType 				= SERVICE_TYPE_ADSL;
		$this->_AppendCDR('ServiceType', $intServiceType);
		
		// RecordType
		$mixValue 						= $this->FindRecordType($intServiceType, 'MonthlyUsage'); 
		$this->_AppendCDR('RecordType', $mixValue);

		//--------------------------------------------------------------------//
		
		// apply ownership
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
		
		return $strReturn;
	}
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleIseek
	//------------------------------------------------------------------------//
	
	define("ISEEK_ADSL_USAGE_DESCRIPTION"		, "ADSL Usage");
?>

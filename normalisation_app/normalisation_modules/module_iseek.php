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
	function __construct()
	{
		// call parent constructor
		parent::__construct();
		
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
	 * 									into DB. Returns FALSE if record fails validation
	 *
	 * @method
	 */	
	function Normalise($arrCDR)
	{
		// ignore header rows
		if ((int)$arrCDR["CDR.SequenceNo"] < 1)
		{
			return CDR_CANT_NORMALISE_BAD_SEQ_NO;
		}
		elseif ((int)$arrCDR["CDR.SequenceNo"] < $this->_intStartRow)
		{
			return CDR_CANT_NORMALISE_HEADER;
		}
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR.CDR"]);
	
		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return CDR_CANT_NORMALISE_RAW;
		}
		
		// build a new output CDR
		$this->_NewCDR();
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		
		// FNN
		$mixValue = $this->_FetchRawCDR('Service');
		$this->_AppendCDR('FNN', $mixValue);
		
		// CarrierRef
		$mixValue = $this->_FetchRawCDR('Service');
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue = $this->_FetchRawCDR('DateStart');
		$this->_AppendCDR('StartDatetime', $mixValue);
		
		// EndDatetime
		$mixValue = $this->_FetchRawCDR('DateEnd');
		$this->_AppendCDR('EndDatetime', $mixValue);
		
		// Units
		$mixValue = (int)($this->_FetchRawCDR('Megabytes') / 1024);
		$this->_AppendCDR('Units', $mixValue);
		
		// Description
		$mixValue = ISEEK_ADSL_USAGE;
		$this->_AppendCDR('Description', $mixValue);
		
		// RecordType
		//$mixValue = ; // needs to match database
		$this->_AppendCDR('RecordType', $mixValue);
		
		// ServiceType
		$mixValue = SERVICE_TYPE_ADSL;
		$this->_AppendCDR('ServiceType', $mixValue);


		//--------------------------------------------------------------------//
		
		// return output array
		return $this->_OutputCDR();
	}
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleIseek
	//------------------------------------------------------------------------//
	
	define("ISEEK_ADSL_USAGE_DESCRIPTION"		, "ADSL Usage");
?>

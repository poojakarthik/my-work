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
		
		$arrDefine ['CDRId']		['Start']		= 2;
		$arrDefine ['CDRId']		['Length']		= 12;
		
		$arrDefine ['AccountNo']	['Start']		= 14;
		$arrDefine ['AccountNo']	['Length']		= 14;
		
		$arrDefine ['CDRId']		['Start']		= 4;
		$arrDefine ['CDRId']		['Length']		= 12;
		
		$arrDefine ['CDRId']		['Start']		= 4;
		$arrDefine ['CDRId']		['Length']		= 12;
		
		$arrDefine ['CDRId']		['Start']		= 4;
		$arrDefine ['CDRId']		['Length']		= 12;
		
		$arrDefine ['CDRId']		['Start']		= 4;
		$arrDefine ['CDRId']		['Length']		= 12;
		
		$arrDefine ['CDRId']		['Start']		= 4;
		$arrDefine ['CDRId']		['Length']		= 12;
		
		$arrDefine ['CDRId']		['Start']		= 4;
		$arrDefine ['CDRId']		['Length']		= 12;
		
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
		
		//##----------------------------------------------------------------##//
		// add fields to CDR
		//##----------------------------------------------------------------##//
		
		//--------------------------------------------------------//
		// Required Fields
		//--------------------------------------------------------//
		
		// FNN
		$mixValue = $this->_FetchRawCDR('ColumnName1');
		$this->_AppendCDR('FNN', $mixValue);
		
		// CarrierRef
		$mixValue = $this->_GenerateUID($arrCDR["FileName"], $arrCDR["CDR.SequenceNo"]);
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue = $this->_FetchRawCDR('ColumnName2');
		$this->_AppendCDR('StartDatetime', $mixValue);
		
		// Units
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('Units', $mixValue);
		
		// Description
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('Description', $mixValue);
		
		// RecordType
		$mixValue = $this->_FetchRawCDR(''); // needs to match database
		$this->_AppendCDR('RecordType', $mixValue);
		
		// ServiceType
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('ServiceType', $mixValue);

		//--------------------------------------------------------//
		// Optional Fields
		//--------------------------------------------------------//

		// Source
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('Source', $mixValue);
		
		// Destination
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('Destination', $mixValue);
		
		// EndDatetime
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('EndDatetime', $mixValue);
		
		// Cost
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('Cost', $mixValue);
		
		// DestinationCode
		$mixValue = $this->_FetchRawCDR('');
		$this->_AppendCDR('DestinationCode', $mixValue);

		//##----------------------------------------------------------------##//
		
		// return output array
		return $this->_OutputCDR();
	}
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleSkel
	//------------------------------------------------------------------------//
	
	// define any constants here that will only ever be used internaly by 
	// the module. Prefix the constants with the module name.
?>

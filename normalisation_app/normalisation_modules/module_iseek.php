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
	// ValidateRaw
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate Raw Data against file desctriptions
	 *
	 * Validate Raw Data against file desctriptions
	 *
	 * @return	boolean				true	: Data matches
	 * 								false	: Data doesn't match
	 *
	 * @method
	 */
	function ValidateRaw()
	{
		// TODO
		
		// Return true for now
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
	function Normalise($arrCDR)
	{
	
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR.CDR"]);
	
		// build a new output CDR
		$this->_NewCDR();
		
		
		// add field to CDR
		$this->_AppendCDR($strName, $mixValue);

		// return output array
		return $this->_OutputCDR();
	}
	
	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleIseek
	//------------------------------------------------------------------------//
	// TODO
}
?>

<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_skel
//----------------------------------------------------------------------------//
/**
 * module_skel.php
 *
 * Skeleton Normalisation module for batch files
 *
 * Skeleton Normalisation module for batch files
 *
 * @file			module_skel.php
 * @language		PHP
 * @package			vixen
 * @author			Jared 'flame' Herbohn
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// NormalisationModuleSkel
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleSkel
 *
 * Skeleton Normalisation module for batch files
 *
 * Skeleton Normalisation module for batch files
 * Use this as a base to create new normalisation modules
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			NormalisationModuleSkel
 */
class NormalisationModuleSkel extends NormalisationModule
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
		$this->_intStartRow = 2;
		
		//####################################################################//
		// USE FOR DELIMITED FILE  ###########################################//
		
		// define the column delimiter
		$this->_strDelimiter = "";
		
		// define the carrier CDR format
		$arrDefine ['ColumnName1']	['Index']		= 0; 		// index of the column
		$arrDefine ['ColumnName1']	['Validate']	= "^\d+$";	// optional RegEx for validation of the column
		
		$arrDefine ['ColumnName2']	['Index']		= 1;
		$arrDefine ['ColumnName2']	['Validate']	= "^\d+$";	// optional RegEx for validation of the column
		
		//...
		
		$this->_arrDefineCarrier = $arrDefine;
		
		// OR   ##############################################################//
		// USE FOR FIXED WIDTH FILE  #########################################//
		
		// define the carrier CDR format
		$arrDefine ['ColumnName1']	['Start']		= 0;		// start position of the column
		$arrDefine ['ColumnName1']	['Length']		= 5;		// length of the column
		$arrDefine ['ColumnName1']	['Validate']	= "^\d+$";	// optional RegEx for validation of the column
		
		$arrDefine ['ColumnName2']	['Start']		= 5;		// start position of the column
		$arrDefine ['ColumnName2']	['Length']		= 20;		// length of the column
		$arrDefine ['ColumnName2']	['Validate']	= "^\d+$";	// optional RegEx for validation of the column
		
		//...
		
		$this->_arrDefineCarrier = $arrDefine;
		//####################################################################//
		
		//##----------------------------------------------------------------##//
	}
	
	//------------------------------------------------------------------------//
	// Preprocessor
	//------------------------------------------------------------------------//
	/**
	 * Preprocessor()
	 *
	 * Preprocess raw data from the CDR
	 *
	 * Preprocess raw data from the CDR
	 * 
	 * @param	string		strCDR		CDR line
	 *
	 * @return	string					returns original or modified CDR line
	 *
	 * @method
	 */	
	function Preprocessor($strCDR)
	{
		return $strCDR;
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
	 * 									into DB.
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
		
		//##----------------------------------------------------------------##//
		// add fields to CDR
		//##----------------------------------------------------------------##//
		
		//--------------------------------------------------------//
		// Required Fields
		//--------------------------------------------------------//
		
		// FNN
		$mixValue 						= $this->_FetchRawCDR('ColumnName1');
		$this->_AppendCDR('FNN', $mixValue);
		
		// CarrierRef
		$mixValue 						= $this->_GenerateUID($arrCDR["FileName"], $arrCDR["SequenceNo"]);
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue 						= $this->_FetchRawCDR('ColumnName2');
		$this->_AppendCDR('StartDatetime', $mixValue);
		
		// Units
		$mixValue 						= $this->_FetchRawCDR('');
		$this->_AppendCDR('Units', $mixValue);
		
		// ServiceType
		$intServiceType 				= $this->_FetchRawCDR('');
		$this->_AppendCDR('ServiceType', $intServiceType);
		
		// RecordType
		$mixCarrierCode					= $this->_FetchRawCDR('');
		$strRecordCode 					= $this->FindRecordCode($mixCarrierCode);
		$mixValue 						= $this->FindRecordType($intServiceType, $strRecordCode); 
		$this->_AppendCDR('RecordType', $mixValue);
		

		//--------------------------------------------------------//
		// Optional Fields
		//--------------------------------------------------------//

		// Description
		$mixValue 						= $this->_FetchRawCDR('');
		$this->_AppendCDR('Description', $mixValue);
		
		// Source
		$mixValue 						= $this->_FetchRawCDR('');
		$this->_AppendCDR('Source', $mixValue);
		
		// Destination
		$mixValue 						= $this->_FetchRawCDR('');
		$this->_AppendCDR('Destination', $mixValue);
		
		// EndDatetime
		$mixValue 						= $this->_FetchRawCDR('');
		$this->_AppendCDR('EndDatetime', $mixValue);
		
		// Cost
		$mixValue 						= $this->_FetchRawCDR('');
		$this->_AppendCDR('Cost', $mixValue);
		
		// Destination Code & Description (only if we have a context)
		// $this->FindRecordType() must be run firs to set the context ($this->_intContext)
		if ($this->_intContext > 0)
		{
			$mixCarrierCode 				= $this->_FetchRawCDR('');
			$arrDestinationCode 			= $this->FindDestination($mixCarrierCode);
			if ($arrDestinationCode)
			{
				$this->_AppendCDR('DestinationCode', $arrDestinationCode['Code']);
				$this->_AppendCDR('Description', $arrDestinationCode['Description']);
			}
		}
		//##----------------------------------------------------------------##//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
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
	 	return $this->_FetchRawCDR('');
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
	 	return $this->_FetchRawCDR('');
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
	 	return (int)$this->_FetchRawCDR('');
	 }
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleSkel
	//------------------------------------------------------------------------//
	
	// define any constants here that will only ever be used internaly by 
	// the module. Prefix the constants with the module name.
?>

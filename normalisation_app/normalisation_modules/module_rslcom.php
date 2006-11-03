<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_rslcom
//----------------------------------------------------------------------------//
/**
 * module_rslcom.php
 *
 * Normalisation module for RSLCOM batch files
 *
 * Normalisation module for RSLCOM batch files
 *
 * @file			module_rslcom.php
 * @language		PHP
 * @package			vixen
 * @author			Rich Davis
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// NormalisationModuleRSLCOM
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleRSLCOM
 *
 * Normalisation module for RSLCOM batch files
 *
 * Normalisation module for RSLCOM batch files
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			<ClassName||InstanceName>
 */
class NormalisationModuleRSLCOM extends NormalisationModule
{
	//------------------------------------------------------------------------//
	// arrRawData
	//------------------------------------------------------------------------//
	/**
	 * arrRawData
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
	//protected $_arrRawData; 

	//------------------------------------------------------------------------//
	// arrNormalisedData
	//------------------------------------------------------------------------//
	/**
	 * arrNormalisedData
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
	//protected $_arrNormalisedData; */

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
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the carrier CDR format
		$arrDefine ['EventId']			['Index']		= 0;
		$arrDefine ['RecordType']		['Index']		= 1;
		$arrDefine ['DateTime']			['Index']		= 2;
		$arrDefine ['Duration']			['Index']		= 3;
		$arrDefine ['OriginNo']			['Index']		= 4;
		$arrDefine ['DestinationNo']	['Index']		= 5;
		$arrDefine ['ChargedParty']		['Index']		= 6;
		$arrDefine ['Currency']			['Index']		= 7;
		$arrDefine ['Price']			['Index']		= 8;
		$arrDefine ['PlanId']			['Index']		= 9;
		$arrDefine ['Distance']			['Index']		= 10;
		$arrDefine ['IsLocal']			['Index']		= 11;
		$arrDefine ['CallType']			['Index']		= 12;
		$arrDefine ['BeginDate']		['Index']		= 13;
		$arrDefine ['EndDate']			['Index']		= 14;
		$arrDefine ['Description']		['Index']		= 15;
		$arrDefine ['ItemCount']		['Index']		= 16;
		$arrDefine ['CarrierId']		['Index']		= 17;
		$arrDefine ['RateId']			['Index']		= 18;
		
		$arrDefine ['EventId']			['Validation']	= "^\d+$";
		$arrDefine ['RecordType']		['Validation']	= "^[178]$";
		$arrDefine ['DateTime']			['Validation']	= "^[0-3]\d/[01]\d/\d{4} [0-2]\d:[0-5]\d:[0-5]\d$";
		$arrDefine ['Currency']			['Validation']	= "^AUD$";
		$arrDefine ['Price']			['Validation']	= "^\d+\.\d\d?$";
		

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
	 * @see	<MethodName()||typePropertyName>
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
	 * @see	<MethodName()||typePropertyName>
	 */	
	function Normalise($arrCDR)
	{
		// Explode the string on commas into an indexed array
		$this->_arrRawData = explode(",", rtrim($arrCDR["CDR"], "\n"));
		
		// $arrCDR["Id"];
		$arrCDR["FNN"]					= $this->RemoveAusCode($this->_arrRawData[6]);
		// $arrCDR["CDRFilename"];
		// $arrCDR["Carrier"];
		$arrCDR["CarrierRef"]			= $this->_arrRawData[0];
		$arrCDR["Source"]				= $this->_arrRawData[4];
		$arrCDR["Destination"]			= $this->_arrRawData[5];
		
		if ($this->_arrRawData[1] == "1")
		{
		 	// For normal usage CDRs
		 	$arrCDR["StartDatetime"]	= $this->_arrRawData[2];
		 	$dttDateTime				= strtotime("+" . $this->_arrRawData[3] . "seconds", $this->_arrRawData[2]);
			$arrCDR["EndDatetime"]		= $dttDateTime;
		}
		else
		{
		 	// For S&E and OC&C CDRs
		 	$arrCDR["StartDatetime"]	= $this->_arrRawData[14];
		 	$arrCDR["EndDatetime"]		= $this->_arrRawData[15];
		}

		$arrCDR["Units"]				= $this->_arrRawData[3];
		
		$selSelectStatement				= new StatementSelect("Service", "Id, Account, AccountGroup", "FNN = <FNN>");
		$selSelectStatement->Execute(Array("FNN" => $this->_arrRawData[6]));
		$arrAccountInfo					= $selSelectStatement->Fetch();
		
		$arrCDR["AccountGroup"]			= $arrAccountInfo["AccountGroup"];
		$arrCDR["Account"]				= $arrAccountInfo["Account"];
		$arrCDR["Service"]				= $arrAccountInfo["Id"];
		$arrCDR["Cost"]					= $this->_arrRawData[8];
		// $arrCDR["Status"];											// Only after data is validated
		// $arrCDR["CDR"];
		
		// Only add a Description if its S&E or OC&C
		if ($this->_arrRawData[1] == "7" || $this->_arrRawData[1] == "8")
		{
			$arrCDR["Description"]		= $this->_arrRawData[16];		// TODO: Find list!!  Is this an index or text!?
		}

		// $arrCDR["DestinationCode"];									// Unitel doesn't tell us this
		// $arrCDR["RecordType"];										// TODO: How to convert!?
		$arrCDR["ServiceType"]			= constant($this->_arrRawData[13]);
		// $arrCDR["Charge"];											// Done in Rating engine
		$arrCDR["Rate"];												// Need Rate table
		// $arrCDR["NormalisedOn"];										// Only do when we commit to database
		// $arrCDR["RatedOn"];
		// $arrCDR["Invoice"];
		// $arrCDR["SequenceNo"];
		
		$this->_arrNormalisedData = $arrCDR;
	}
	
	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleRSLCOM
	//------------------------------------------------------------------------//
	// TODO
}
?>

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
	// Compare
	//------------------------------------------------------------------------//
	/**
	 * Compare()
	 *
	 * Compares Raw Data to Normalised Data
	 *
	 * Compares Raw Data to Normalised Data
	 *
	 * @return	boolean				true	: Data matches
	 * 								false	: Data doesn't match
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Compare()
	{
		// TODO
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
		
		/*
		 *  RSLCOM File Format
		 * 
		 * 	0	Event ID			Unique Identifier
		 * 	1	Record Type			1: Usage; 7: S&E; 8: OC&C
		 * 	2	Date/Time			Start of session
		 * 	3	Duration			In seconds
		 * 	4	Originating #		Calling number
		 * 	5	Terminating #		Called number
		 * 	6	Charged Party		Service Number to be billed
		 * 	7	Currency			Usually AUD (otherwise report and dont process)
		 * 	8	Price				Charged to Wholesaler
		 * 	9	Plan ID				RSLCOM's rate plan ID
		 * 	10	Distance			KM
		 * 	12	Is Local			1: Local; 0: Non-Local
		 * 	13	Type of Call		... See list?
		 * 	14	Begin Date			Starting date of charge (Rec Type 7+8 only)
		 * 	15	End Date			Ending date of charge (Rec Type 7+8 only)
		 * 	16	Description			Rec Type 7+8 only... See list?
		 * 	17	Number of Items		Rec Type 7+8 only
		 * 	18	Carrier ID			1: Telstra; 2: Optus; 3: RSLCOM
		 *  19	Rate ID				RSLCOM's Rate ID
		 */
		 
		 //$arrCDR["Id"];
		 $arrCDR["FNN"]					= $this->StripFNN($this->_arrRawData[6]);
		 //$arrCDR["CDRFilename"];
		 //$arrCDR["Carrier"];
		 $arrCDR["CarrierRef"]			= $this->_arrRawData[0] . "";
		 $arrCDR["Source"]				= $this->_arrRawData[4] . "";
		 $arrCDR["Destination"]			= $this->_arrRawData[5] . "";
		 $arrCDR["StartDatetime"];
		 $arrCDR["EndDatetime"];
		 $arrCDR["Units"];
		 $arrCDR["Customer"];
		 $arrCDR["Account"];
		 $arrCDR["Service"];
		 $arrCDR["Cost"];
		 $arrCDR["Status"];
		 $arrCDR["CDR"];
		 $arrCDR["DestinationText"];
		 $arrCDR["DestinationCode"];
		 $arrCDR["RecordType"];
		 $arrCDR["ServiceType"];
		 //$arrCDR["Charge"];								// 
		 $arrCDR["Rate"];									// Need Rate table
		 $arrCDR["NormalisedOn"]		= "NOW()";			// FIXME: This wont work.  Use PHP datetime?
		 //$arrCDR["RatedOn"];
		 //$arrCDR["Invoice"];
		 //$arrCDR["SequenceNo"];
	}
}
?>

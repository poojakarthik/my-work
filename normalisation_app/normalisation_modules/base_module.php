<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// base_module
//----------------------------------------------------------------------------//
/**
 * base_module
 *
 * Normalisation Module Base Class
 *
 * Normalisation Module Base Class
 *
 * @file		base_module.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// NormalisationModule
//----------------------------------------------------------------------------//
/**
 * NormalisationModule
 *
 * Normalisation Module Base Class
 *
 * Normalisation Module Base Class
 *
 *
 * @prefix		nrm
 *
 * @package		vixen
 * @class		<ClassName||InstanceName>
 */
abstract class NormalisationModule
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
	protected $_arrRawData; 

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
	protected $_arrNormalisedData; 

	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate Normalised Data
	 *
	 * Validate Normalised Data
	 *
	 * @return	boolean				true	: Data matches
	 * 								false	: Data doesn't match
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Validate()
	{
		// Validate our normalised data
		$arrValid = Array();
		
		// $this->_arrNormalisedData["Id"];
		$arrValid[] = IsValidFNN($this->_arrNormalisedData["FNN"]);
		// $this->_arrNormalisedData["CDRFilename"];
		// $this->_arrNormalisedData["Carrier"];
		$arrValid[] = $this->_arrNormalisedData["CarrierRef"];
		$arrValid[] = $this->_arrNormalisedData["Source"];
		$arrValid[] = $this->_arrNormalisedData["Destination"];
		$arrValid[] = $this->_arrNormalisedData["StartDatetime"];
		$arrValid[] = $this->_arrNormalisedData["EndDatetime"];
		$arrValid[] = $this->_arrNormalisedData["Units"];
		$arrValid[] = $this->_arrNormalisedData["AccountGroup"];
		$arrValid[] = $this->_arrNormalisedDatarCDR["Account"];
		$arrValid[] = $this->_arrNormalisedData["Service"];
		$arrValid[] = $this->_arrNormalisedData["Cost"];
		// $this->_arrNormalisedData["Status"];
		// $this->_arrNormalisedData["CDR"];
		$arrValid[] = $this->_arrNormalisedData["Description"];
		$arrValid[] = $this->_arrNormalisedData["DestinationCode"];
		$arrValid[] = $this->_arrNormalisedData["RecordType"];
		$arrValid[] = $this->_arrNormalisedData["ServiceType"];
		// $this->_arrNormalisedData["Charge"];
		$arrValid[] = $this->_arrNormalisedData["Rate"];
		// $this->_arrNormalisedData["NormalisedOn"];
		// $this->_arrNormalisedData["RatedOn"];
		// $this->_arrNormalisedData["Invoice"];
		// $this->_arrNormalisedData["SequenceNo"];
		
		// Now call the ValidateRaw() class, implemented by the the child
		$this->ValidateRaw();
	}
	
	//------------------------------------------------------------------------//
	// ValidateRaw
	//------------------------------------------------------------------------//
	/**
	 * ValidateRaw()
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
	abstract function ValidateRaw()
	{
		// Abstract methods don't have an implementation 
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
	abstract function Normalise($arrCDR)
	{
		// Abstract methods don't have an implementation
	}
	
	//------------------------------------------------------------------------//
	// RemoveAusCode
	//------------------------------------------------------------------------//
	/**
	 * RemoveAusCode()
	 *
	 * Removes +61 from FNNs
	 *
	 * Removes the +61 from the start of an FNN, replacing it with a 0
	 * 
	 * @param	string		$strFNN		FNN to be parsed
	 *
	 * @return	string					Modified FNN
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */	
	protected function RemoveAusCode($strFNN)
	{
		return str_replace("+61", "0", $strFNN);
	}
	
	//------------------------------------------------------------------------//
	// IsValidFNN
	//------------------------------------------------------------------------//
	/**
	 * IsValidFNN()
	 *
	 * Checks if FNN is valid
	 *
	 * Checks if FNN is valid
	 * 
	 * @param	string		$strFNN		FNN to be parsed
	 *
	 * @return	boolean					true	: FNN is valid
	 * 									false	: FNN is not valid
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */	
	protected function IsValidFNN($strFNN)
	{
		return preg_match("", $strFNN);
	}
}

?>

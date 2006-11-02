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
	abstract function Compare()
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
}

?>

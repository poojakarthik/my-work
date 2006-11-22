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
 * Module for the provisioning engine
 *
 * Module for the provisioning engine.  There is one per carrier.
 *
 * @file		base_module.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModule
//----------------------------------------------------------------------------//
/**
 * ProvisioningModule
 *
 * Module for the provisioning engine
 *
 * Module for the provisioning engine.  There is one per carrier.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModule
 */
 abstract class ProvisioningModule
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModule
	 *
	 * Constructor method for ProvisioningModule
	 *
	 * @return		ProvisioningModule
	 *
	 * @method
	 */
 	function __construct($ptrDB)
 	{
		// Set up this->db
		$this->db = $ptrDB;
 	}
 	
 	//------------------------------------------------------------------------//
	// Add()
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Adds a line to the module from a file
	 *
	 * Parses and adds a "line" to the module from a line status file.
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function Add($strLine);
 	
 	//------------------------------------------------------------------------//
	// NextLine()
	//------------------------------------------------------------------------//
	/**
	 * NextLine()
	 *
	 * Advances to next "line" in the module
	 *
	 * Advances to next "line" in the module
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function NextLine()
 	{
		return next($this->_arrLines);
 	}


 	//------------------------------------------------------------------------//
	// UpdateRequests()
	//------------------------------------------------------------------------//
	/**
	 * UpdateRequests()
	 *
	 * Updates the Request table
	 *
	 * Updates the Request table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function NextLine();
 	
 	//------------------------------------------------------------------------//
	// UpdateService()
	//------------------------------------------------------------------------//
	/**
	 * UpdateService()
	 *
	 * Updates the Service table
	 *
	 * Updates the Service table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function NextLine();
 	 	
 }



?>

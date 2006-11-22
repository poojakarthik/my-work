<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_unitel_reject
//----------------------------------------------------------------------------//
/**
 * module_unitel_reject
 *
 * Unitel Rejection Module for the provisioning engine
 *
 * Unitel Rejection Module for the provisioning engine
 *
 * @file		module_unitel_reject.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleUnitelReject
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
 class ProvisioningModuleUnitelReject
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleUnitelReject
	 *
	 * Constructor method for ProvisioningModuleUnitelReject
	 *
	 * @return		ProvisioningModuleUnitelReject
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		parent::__construct($ptrDB);
 	}
 }



?>

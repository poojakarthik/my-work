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
// ProvisioningModuleUnitel
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleUnitel
 *
 * Unitel Module for the provisioning engine
 *
 * Unitel Module for the provisioning engine.  There is one per carrier.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleUnitel
 */
 class ProvisioningModuleUnitel
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleUnitel
	 *
	 * Constructor method for ProvisioningModuleUnitel
	 *
	 * @return		ProvisioningModuleUnitel
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		parent::__construct($ptrDB);
 	}
 }



?>

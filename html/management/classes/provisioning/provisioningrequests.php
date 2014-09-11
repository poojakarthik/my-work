<?php

	//----------------------------------------------------------------------------//
	// ProvisioningRequests.php
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequests.php
	 *
	 * Contains the Class that Controls a Provision Log
	 *
	 * Contains the Class that Controls a Provision Log
	 *
	 * @file		ProvisioningRequests.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningRequests
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequests
	 *
	 * Controls Searching for an existing Service
	 *
	 * Controls Searching for an existing Service
	 *
	 *
	 * @prefix		prl
	 *
	 * @package		intranet_app
	 * @class		ProvisioningRequests
	 * @extends		dataObject
	 */
	
	class ProvisioningRequests extends Search
	{
	
		//------------------------------------------------------------------------//
		// _srvService
		//------------------------------------------------------------------------//
		/**
		 * _srvService
		 *
		 * The Service of the Provisioning Log
		 *
		 * The Service that the Provisioning Log is Constrained Against
		 *
		 * @type	Service
		 *
		 * @property
		 */
		 
		private $_srvService;
	
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a Provisioning Log
		 *
		 * Constructs a Provisioning Log
		 *
		 *
		 * @param	Service		$srvService		The service which the provisioning log is requested
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('ProvisioningRequests', 'Request', 'ProvisioningRequest');
			
			// By default - put it in reverse chronological order ...
			$this->Order ('RequestDateTime', FALSE);
		}
	}
	
?>

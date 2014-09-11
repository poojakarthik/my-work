<?php

	//----------------------------------------------------------------------------//
	// provisioninglog.php
	//----------------------------------------------------------------------------//
	/**
	 * provisioninglog.php
	 *
	 * Contains the Class that Controls a Provision Log
	 *
	 * Contains the Class that Controls a Provision Log
	 *
	 * @file		provisioninglog.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningLog
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningLog
	 *
	 * Controls Searching for an existing Service
	 *
	 * Controls Searching for an existing Service
	 *
	 *
	 * @prefix		pll
	 *
	 * @package		intranet_app
	 * @class		ProvisioningLog
	 * @extends		dataObject
	 */
	
	class ProvisioningLog extends Search
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
		 
		function __construct ($srvService)
		{
			parent::__construct ('ProvisioningLog', 'ProvisioningLog', 'ProvisioningRecord');
			
			// Save the Service
			$this->_srvService = $srvService;
			
			// Constrain Against the Service (Newest First)
			$this->Constrain ('Service', '=', $srvService->Pull ('Id')->getValue ());
			$this->Order ('Date', FALSE);
		}
	}
	
?>

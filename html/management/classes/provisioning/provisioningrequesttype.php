<?php

	//----------------------------------------------------------------------------//
	// ProvisioningRequestType.php
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequestType.php
	 *
	 * Contains the ProvisioningRequestType object
	 *
	 * Contains the ProvisioningRequestType object
	 *
	 * @file		ProvisioningRequestType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningRequestType
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequestType
	 *
	 * Allows Textual (named) Representation of the Constants which form a ProvisioningRequestType
	 *
	 * Allows Textual (named) Representation of the Constants which form a ProvisioningRequestType
	 *
	 *
	 * @prefix	prt
	 *
	 * @package	intranet_app
	 * @class	ProvisioningRequestType
	 * @extends	dataEnumerative
	 */
	
	class ProvisioningRequestType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the ProvisioningRequestType
		 *
		 * The Id of the ProvisioningRequestType
		 *
		 * @type	dataInteger
		 *
		 * @property
		 */
		
		private $_oblintType;
		
		//------------------------------------------------------------------------//
		// _oblstrName
		//------------------------------------------------------------------------//
		/**
		 * _oblstrName
		 *
		 * The name of the ProvisioningRequestType
		 *
		 * The name of the ProvisioningRequestType
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Holds ProvisioningRequestType Constant Information
		 *
		 * Holds ProvisioningRequestType Constant Information
		 *
		 * @param	Integer		$intType			The Id of the ProvisioningRequestType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('ProvisioningRequestType');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case REQUEST_FULL_SERVICE:
					$strName = 'Full Service';
					break;
					
				case REQUEST_PRESELECTION:
					$strName = 'Preselection';
					break;
					
				case REQUEST_BAR_SOFT:
					$strName = 'Soft Bar';
					break;
					
				case REQUEST_UNBAR_SOFT:
					$strName = 'Soft Bar Reversal';
					break;
					
				case REQUEST_ACTIVATION:
					$strName = 'Activation';
					break;
					
				case REQUEST_DEACTIVATION:
					$strName = 'Deactivation';
					break;
					
				case REQUEST_PRESELECTION_REVERSE:
					$strName = 'Preselection Reversal';
					break;
					
				case REQUEST_FULL_SERVICE_REVERSE:
					$strName = 'Full Service Reversal';
					break;
					
				case REQUEST_BAR_HARD:
					$strName = 'Hard Bar';
					break;
					
				case REQUEST_UNBAR_HARD:
					$strName = 'Hard Bar Reversal';
					break;
				
				case REQUEST_VIRTUAL_PRESELECTION:
					$strName = 'Virtual Preselection';
					break;
					
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

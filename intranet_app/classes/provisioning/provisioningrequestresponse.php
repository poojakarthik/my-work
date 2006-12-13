<?php

	//----------------------------------------------------------------------------//
	// ProvisioningRequestResponse.php
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequestResponse.php
	 *
	 * Contains the ProvisioningRequestResponse object
	 *
	 * Contains the ProvisioningRequestResponse object
	 *
	 * @file		ProvisioningRequestResponse.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningRequestResponse
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequestResponse
	 *
	 * Allows Textual (named) Representation of the Constants which form a ProvisioningRequestResponse
	 *
	 * Allows Textual (named) Representation of the Constants which form a ProvisioningRequestResponse
	 *
	 *
	 * @prefix	prr
	 *
	 * @package	intranet_app
	 * @class	ProvisioningRequestResponse
	 * @extends	dataEnumerative
	 */
	
	class ProvisioningRequestResponse extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the ProvisioningRequestResponse
		 *
		 * The Id of the ProvisioningRequestResponse
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
		 * The name of the ProvisioningRequestResponse
		 *
		 * The name of the ProvisioningRequestResponse
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
		 * Holds ProvisioningRequestResponse Constant Information
		 *
		 * Holds ProvisioningRequestResponse Constant Information
		 *
		 * @param	Integer		$intType			The Id of the ProvisioningRequestResponse (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('ProvisioningRequestResponse');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case REQUEST_STATUS_WAITING:
					$strName = 'Waiting';
					break;
					
				case REQUEST_STATUS_PENDING:
					$strName = 'Pending';
					break;
					
				case REQUEST_STATUS_REJECTED:
					$strName = 'Rejected';
					break;
					
				case REQUEST_STATUS_COMPLETED:
					$strName = 'Completed';
					break;
					
				case REQUEST_STATUS_CANCELLED:
					$strName = 'Cancelled';
					break;
					
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

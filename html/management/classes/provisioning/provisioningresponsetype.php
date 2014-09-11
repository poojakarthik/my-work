<?php

	//----------------------------------------------------------------------------//
	// ProvisioningResponseType.php
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningResponseType.php
	 *
	 * Contains the ProvisioningResponseType object
	 *
	 * Contains the ProvisioningResponseType object
	 *
	 * @file		ProvisioningResponseType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningResponseType
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningResponseType
	 *
	 * Allows Textual (named) Representation of the Constants which form a ProvisioningResponseType
	 *
	 * Allows Textual (named) Representation of the Constants which form a ProvisioningResponseType
	 *
	 *
	 * @prefix	pqt
	 *
	 * @package	intranet_app
	 * @class	ProvisioningResponseType
	 * @extends	dataEnumerative
	 */
	
	class ProvisioningResponseType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the ProvisioningResponseType
		 *
		 * The Id of the ProvisioningResponseType
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
		 * The name of the ProvisioningResponseType
		 *
		 * The name of the ProvisioningResponseType
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
		 * Holds ProvisioningResponseType Constant Information
		 *
		 * Holds ProvisioningResponseType Constant Information
		 *
		 * @param	Integer		$intType			The Id of the ProvisioningResponseType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('ProvisioningResponseType');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case LINE_ACTION_OTHER:
					$strName = 'Other (Identified)';
					break;
					
				case LINE_ACTION_GAIN:
					$strName = 'Line Gained';
					break;
					
				case LINE_ACTION_LOSS:
					$strName = 'Line Lossed';
					break;
					
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

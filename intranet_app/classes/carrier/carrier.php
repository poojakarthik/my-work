<?php

	//----------------------------------------------------------------------------//
	// servicetype.php
	//----------------------------------------------------------------------------//
	/**
	 * servicetype.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		servicetype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	ServiceType
	 * @extends	dataEnumerative
	 */
	
	class ServiceType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the Service Type
		 *
		 * The Id of the Service Type
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
		 * The name of the Service Type
		 *
		 * The name of the Service Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrName;
		
		//------------------------------------------------------------------------//
		// ServiceType
		//------------------------------------------------------------------------//
		/**
		 * ServiceType()
		 *
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the Service Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('ServiceType');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case SERVICE_TYPE_ADSL:
					$strName = 'ADSL Connection';
					break;
					
				case SERVICE_TYPE_MOBILE:
					$strName = 'Mobile Telephone';
					break;
					
				case SERVICE_TYPE_LAND_LINE:
					$strName = 'Land Line Telephone';
					break;
					
				case SERVICE_TYPE_INBOUND:
					$strName = 'Inbound Call Number';
					break;
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

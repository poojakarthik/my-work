<?php

	//----------------------------------------------------------------------------//
	// ServiceStreetSuffixType.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetSuffixType.php
	 *
	 * Contains the ServiceStreetSuffixType object
	 *
	 * Contains the ServiceStreetSuffixType object
	 *
	 * @file		ServiceStreetSuffixType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceStreetSuffixType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStreetSuffixType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Street Suffix Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Street Suffix Types
	 *
	 *
	 * @prefix	sst
	 *
	 * @package	intranet_app
	 * @class	ServiceStreetSuffixType
	 * @extends	dataEnumerative
	 */
	
	class ServiceStreetSuffixType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrType
		//------------------------------------------------------------------------//
		/**
		 * _oblstrType
		 *
		 * The Id of the Service Type
		 *
		 * The Id of the Service Type
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrType;
		
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
		// ServiceStreetSuffixType
		//------------------------------------------------------------------------//
		/**
		 * ServiceStreetSuffixType()
		 *
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	String		$strType			The Id of the ServiceStreetSuffixType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($strType)
		{
			parent::__construct ('ServiceStreetSuffixType');
			
			$strName = 'Unknown';
			
			switch ($strType)
			{
			
				case SERVICE_STREET_SUFFIX_TYPE_CENTRAL:
					$strName = "Central";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_EAST:
					$strName = "East";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_EXTENSION:
					$strName = "Extension";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_LOWER:
					$strName = "Lower";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_NORTH:
					$strName = "North";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_NORTH_EAST:
					$strName = "North East";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_NORTH_WEST:
					$strName = "North West";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_SOUTH:
					$strName = "South";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_SOUTH_EAST:
					$strName = "South East";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_SOUTH_WEST:
					$strName = "South West";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_UPPER:
					$strName = "Upper";
					break;
					
				case SERVICE_STREET_SUFFIX_TYPE_WEST:
					$strName = "West";
					break;
			}
			
			$this->oblstrType		= $this->Push (new dataString	('Id',		$strType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

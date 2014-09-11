<?php

	//----------------------------------------------------------------------------//
	// servicetypes.php
	//----------------------------------------------------------------------------//
	/**
	 * servicetypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		servicetypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceTypes
	//----------------------------------------------------------------------------//
	/**
	 * ServiceTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * @prefix	svt
	 *
	 * @package	intranet_app
	 * @class	ServiceType
	 * @extends	dataEnumerative
	 */
	
	class ServiceTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// _ADSL
		//------------------------------------------------------------------------//
		/**
		 * _ADSL
		 *
		 * Used when the ServiceType is an ADSL line
		 *
		 * Used when the ServiceType is an ADSL line
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_ADSL;
		
		//------------------------------------------------------------------------//
		// _MOBILE
		//------------------------------------------------------------------------//
		/**
		 * _MOBILE
		 *
		 * Used when the ServiceType is a Mobile Number
		 *
		 * Used when the ServiceType is a Mobile Number
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_MOBLE;
		
		//------------------------------------------------------------------------//
		// _LAND_LINE
		//------------------------------------------------------------------------//
		/**
		 * _LAND_LINE
		 *
		 * Used when the ServiceType is a Land Line Number
		 *
		 * Used when the ServiceType is a Land Line Number
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_LAND_LINE;
		
		//------------------------------------------------------------------------//
		// _INBOUND
		//------------------------------------------------------------------------//
		/**
		 * _INBOUND
		 *
		 * Used when the ServiceType is an Inbound (13/1300/1800) number
		 *
		 * Used when the ServiceType is an Inbound (13/1300/1800) number
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_INBOUND;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of ServiceType
		 *
		 * Controls a List of ServiceType
		 *
		 * @param	Integer		$intServiceType			[Optional] An Integer representation of a Service type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intServiceType=null)
		{
			parent::__construct ('ServiceTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_ADSL		= $this->Push (new ServiceType (SERVICE_TYPE_ADSL));
			$this->_MOBILE		= $this->Push (new ServiceType (SERVICE_TYPE_MOBILE));
			$this->_LAND_LINE	= $this->Push (new ServiceType (SERVICE_TYPE_LAND_LINE));
			$this->_INBOUND		= $this->Push (new ServiceType (SERVICE_TYPE_INBOUND));
			
			$this->setValue ($intServiceType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Service Type
		 *
		 * Change the Selected Service Type to another Service Type
		 *
		 * @param	Integer		$intServiceType		The value of the ServiceType Constant wishing to be set
		 * @return	Boolean							Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intServiceType)
		{
			// Select the value
			switch ($intServiceType)
			{
				case SERVICE_TYPE_ADSL:			$this->Select ($this->_ADSL);		return true;
				case SERVICE_TYPE_MOBILE:		$this->Select ($this->_MOBILE);		return true;
				case SERVICE_TYPE_LAND_LINE:	$this->Select ($this->_LAND_LINE);	return true;
				case SERVICE_TYPE_INBOUND:		$this->Select ($this->_INBOUND);	return true;
				default:						return false;
			}
		}
	}
	
?>

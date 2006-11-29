<?php

//----------------------------------------------------------------------------//
// namedservicetype.php
//----------------------------------------------------------------------------//
/**
 * servicetype.php
 *
 * Contains the ServiceType object
 *
 * Contains the ServiceType object
 *
 * @file		namedservicetype.php
 * @language	PHP
 * @package		intranet_app
 * @author		Bashkim 'Bash' Isai
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// NamedServiceType
	//----------------------------------------------------------------------------//
	/**
	 * NameServiceType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 *
	 * @prefix	svt
	 *
	 * @package	intranet_app
	 * @class	ServiceType
	 * @extends	dataEnumerative
	 */
	
	class NamedServiceType extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// _UNKNOWN
		//------------------------------------------------------------------------//
		/**
		 * _UNKNOWN
		 *
		 * Used when the ServiceType can not be distinguished
		 *
		 * Used when the ServiceType can not be distinguished
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_UNKNOWN;
		
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
		// NamedServiceType
		//------------------------------------------------------------------------//
		/**
		 * NamedServiceType()
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
			parent::__construct ("NamedServiceTypes");
			
			// Instantiate the Variable Values for possible selection
			$this->_ADSL		= $this->Push (new ServiceType (SERVICE_TYPE_ADSL,		"ADSL Connection"));
			$this->_MOBILE		= $this->Push (new ServiceType (SERVICE_TYPE_MOBILE,	"Mobile Telephone"));
			$this->_LAND_LINE	= $this->Push (new ServiceType (SERVICE_TYPE_LAND_LINE,	"Land Line Telephone"));
			$this->_INBOUND		= $this->Push (new ServiceType (SERVICE_TYPE_INBOUND,	"Inbound Call Number"));
			
			// Select the value
			switch ($intServiceType)
			{
				case SERVICE_TYPE_ADSL:		$this->Select ($this->_ADSL);		break;
				case SERVICE_TYPE_MOBILE:	$this->Select ($this->_MOBILE);		break;
				case SERVICE_TYPE_LAND_LINE:	$this->Select ($this->_LAND_LINE);	break;
				case SERVICE_TYPE_INBOUND:	$this->Select ($this->_INBOUND);	break;
			}
		}
	}
	
?>

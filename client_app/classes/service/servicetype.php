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
 * @file	servicetype.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim 'Bash' Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
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
	 * @prefix	svt
	 *
	 * @package	client_app
	 * @class	ServiceType
	 * @extends	dataEnumerative
	 */
	
	class ServiceType extends dataEnumerative
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
		// ServiceType
		//------------------------------------------------------------------------//
		/**
		 * ServiceType()
		 *
		 * Controls a Named Value for a ServiceType
		 *
		 * Controls a Named Value for a ServiceType
		 *
		 * @param	String		$strServiceTypeName	The tag name of the ServiceType we wish to use
		 * @param	Integer		$intServiceType		An Integer representation of a Service type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strServiceTypeName, $intServiceType)
		{
			parent::__construct ($strServiceTypeName);
			
			// Instantiate the Variable Values for possible selection
			$this->_ADSL		= $this->Push (new dataString ($this->tagName (), "ADSL Connection"));
			$this->_MOBILE		= $this->Push (new dataString ($this->tagName (), "Mobile Telephone"));
			$this->_LAND_LINE	= $this->Push (new dataString ($this->tagName (), "Land Line Telephone"));
			$this->_INBOUND		= $this->Push (new dataString ($this->tagName (), "Inbound Call Number"));
			$this->_UNKNOWN		= $this->Push (new dataString ($this->tagName (), "Unspecified"));
			
			// Select the value
			switch ($intServiceType)
			{
				case SERVICE_TYPE_ADSL:		$this->Select ($this->_ADSL);		break;
				case SERVICE_TYPE_MOBILE:	$this->Select ($this->_MOBILE);		break;
				case SERVICE_TYPE_LAND_LINE:	$this->Select ($this->_LAND_LINE);	break;
				case SERVICE_TYPE_INBOUND:	$this->Select ($this->_INBOUND);	break;
				default:			$this->Select ($this->_UNKNOWN);	break;
			}
		}
	}
	
?>

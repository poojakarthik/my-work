<?php

	//----------------------------------------------------------------------------//
	// ProvisioningRequestTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequestTypes.php
	 *
	 * Contains the ProvisioningRequestTypes object
	 *
	 * Contains the ProvisioningRequestTypes object
	 *
	 * @file		ProvisioningRequestTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ProvisioningRequestTypes
	//----------------------------------------------------------------------------//
	/**
	 * ProvisioningRequestTypes
	 *
	 * Textual ProvisioningRequestTypes Types
	 *
	 * Allows Textual (named) Representation of the Constants which form ProvisioningRequestType Types
	 *
	 * @prefix	prl
	 *
	 * @package	intranet_app
	 * @class	ProvisioningRequestTypes
	 * @extends	dataEnumerative
	 */
	
	class ProvisioningRequestTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// _UNITEL
		//------------------------------------------------------------------------//
		/**
		 * _UNITEL
		 *
		 * Used when the ProvisioningRequestType is Unitel
		 *
		 * Used when the ProvisioningRequestType is Unitel
		 *
		 * @type	ProvisioningRequestType
		 *
		 * @property
		 */
		
		private $_UNITEL;
		
		//------------------------------------------------------------------------//
		// _OPTUS
		//------------------------------------------------------------------------//
		/**
		 * _OPTUS
		 *
		 * Used when the ProvisioningRequestType is Optus
		 *
		 * Used when the ProvisioningRequestType is Optus
		 *
		 * @type	ProvisioningRequestType
		 *
		 * @property
		 */
		
		private $_OPTUS;
		
		//------------------------------------------------------------------------//
		// _AAPT
		//------------------------------------------------------------------------//
		/**
		 * _LAND_LINE
		 *
		 * Used when the ProvisioningRequestType is AAPT
		 *
		 * Used when the ProvisioningRequestType is AAPT
		 *
		 * @type	ProvisioningRequestType
		 *
		 * @property
		 */
		
		private $_AAPT;
		
		//------------------------------------------------------------------------//
		// _ISEEK
		//------------------------------------------------------------------------//
		/**
		 * _ISEEK
		 *
		 * Used when the ProvisioningRequestType is ISEEK
		 *
		 * Used when the ProvisioningRequestType is ISEEK
		 *
		 * @type	ProvisioningRequestType
		 *
		 * @property
		 */
		
		private $_ISEEK;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of ProvisioningRequestType Objects
		 *
		 * Controls a List of ProvisioningRequestType Objects
		 *
		 * @param	Integer		$intProvisioningRequestType			[Optional] An Integer representation of the default ProvisioningRequestType
		 *
		 * @method
		 */
		
		function __construct ($intProvisioningRequestType=null)
		{
			parent::__construct ('ProvisioningRequestTypes');
			
			// Instantiate the Variable Values for possible selection
			
			$this->_FULL_SERVICE			= $this->Push (new ProvisioningRequestType (REQUEST_FULL_SERVICE));
			$this->_PRESELECTION			= $this->Push (new ProvisioningRequestType (REQUEST_PRESELECTION));
			$this->_BAR_SOFT				= $this->Push (new ProvisioningRequestType (REQUEST_BAR_SOFT));
			$this->_UNBAR_SOFT				= $this->Push (new ProvisioningRequestType (REQUEST_UNBAR_SOFT));
			$this->_ACTIVATION				= $this->Push (new ProvisioningRequestType (REQUEST_ACTIVATION));
			$this->_DEACTIVATION			= $this->Push (new ProvisioningRequestType (REQUEST_DEACTIVATION));
			$this->_PRESELECTION_REVERSE	= $this->Push (new ProvisioningRequestType (REQUEST_PRESELECTION_REVERSE));
			$this->_FULL_SERVICE_REVERSE	= $this->Push (new ProvisioningRequestType (REQUEST_FULL_SERVICE_REVERSE));
			$this->_BAR_HARD				= $this->Push (new ProvisioningRequestType (REQUEST_BAR_HARD));
			$this->_UNBAR_HARD				= $this->Push (new ProvisioningRequestType (REQUEST_UNBAR_HARD));
			$this->_VIRTUAL_PRESELECTION	= $this->Push (new ProvisioningRequestType (REQUEST_VIRTUAL_PRESELECTION));
			
			$this->setValue ($intProvisioningRequestType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected ProvisioningRequestType
		 *
		 * Change the Selected ProvisioningRequestType
		 *
		 * @param	Integer		$intProvisioningRequestType			The value of the new ProvisioningRequestType Constant
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function setValue ($intProvisioningRequestType)
		{
			// Select the value
			switch ($intProvisioningRequestType)
			{
				case REQUEST_FULL_SERVICE:					$this->Select ($this->_FULL_SERVICE);			return true;
				case REQUEST_PRESELECTION:					$this->Select ($this->_PRESELECTION);			return true;
				case REQUEST_BAR_SOFT:						$this->Select ($this->_BAR_SOFT);				return true;
				case REQUEST_UNBAR_SOFT:					$this->Select ($this->_UNBAR_SOFT);				return true;
				case REQUEST_ACTIVATION:					$this->Select ($this->_ACTIVATION);				return true;
				case REQUEST_DEACTIVATION:					$this->Select ($this->_DEACTIVATION);			return true;
				case REQUEST_PRESELECTION_REVERSE:			$this->Select ($this->_PRESELECTION_REVERSE);	return true;
				case REQUEST_FULL_SERVICE_REVERSE:			$this->Select ($this->_FULL_SERVICE_REVERSE);	return true;
				case REQUEST_BAR_HARD:						$this->Select ($this->_BAR_HARD);				return true;
				case REQUEST_UNBAR_HARD:					$this->Select ($this->_UNBAR_HARD);				return true;
				case REQUEST_VIRTUAL_PRESELECTION:			$this->Select ($this->_VIRTUAL_PRESELECTION);	return true;
				default:																					return false;
			}
		}
	}
	
?>

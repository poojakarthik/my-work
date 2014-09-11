<?php

	//----------------------------------------------------------------------------//
	// ServiceStateTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStateTypes.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		ServiceStateTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceStateTypes
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStateTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service State Types
	 *
	 * @prefix	svt
	 *
	 * @package	intranet_app
	 * @class	ServiceStateTypes
	 * @extends	dataEnumerative
	 */
	
	class ServiceStateTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of ServiceStateType
		 *
		 * Controls a List of ServiceStateType
		 *
		 * @param	String		$strId			[Optional] A representation of a Service State Type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($strId=null)
		{
			parent::__construct ('ServiceStateTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_ACT		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_ACT));
			$this->_NSW		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_NSW));
			$this->_NT		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_NT));
			$this->_QLD		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_QLD));
			$this->_SA		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_SA));
			$this->_TAS		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_TAS));
			$this->_VIC		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_VIC));
			$this->_WA		= $this->Push (new ServiceStateType (SERVICE_STATE_TYPE_WA));
			
			$this->setValue ($strId);
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
		 * @param	String		$strId			The ID of the ServiceStateType Constant wishing to be set
		 * @return	Boolean						Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($strId)
		{
			// Select the value
			switch ($strId)
			{
				case SERVICE_STATE_TYPE_ACT:	$this->Select ($this->_ACT);	return true;
				case SERVICE_STATE_TYPE_NSW:	$this->Select ($this->_NSW);	return true;
				case SERVICE_STATE_TYPE_NT:		$this->Select ($this->_NT);		return true;
				case SERVICE_STATE_TYPE_QLD:	$this->Select ($this->_QLD);	return true;
				case SERVICE_STATE_TYPE_SA:		$this->Select ($this->_SA);		return true;
				case SERVICE_STATE_TYPE_TAS:	$this->Select ($this->_TAS);	return true;
				case SERVICE_STATE_TYPE_VIC:	$this->Select ($this->_VIC);	return true;
				case SERVICE_STATE_TYPE_WA:		$this->Select ($this->_WA);		return true;
				default:						return false;
			}
		}
	}
	
?>

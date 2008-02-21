<?php

	//----------------------------------------------------------------------------//
	// ServiceStateType.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStateType.php
	 *
	 * Contains the ServiceStateType object
	 *
	 * Contains the ServiceStateType object
	 *
	 * @file		ServiceStateType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceStateType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceStateType
	 *
	 * Allows Textual (named) Representation of the Constants which form ServiceStateType
	 *
	 * Allows Textual (named) Representation of the Constants which form ServiceStateType
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	ServiceStateType
	 * @extends	dataEnumerative
	 */
	
	class ServiceStateType extends dataObject
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
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Holds ServiceStateType Constant Information
		 *
		 * Holds ServiceStateType Constant Information
		 *
		 * @param	String		$strId			The Id of the ServiceStateType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($strId)
		{
			parent::__construct ('ServiceStateType');
			
			$strName = 'Unknown';
			
			switch ($strId)
			{
				case SERVICE_STATE_TYPE_ACT:
					$strName = 'Australian Capital Territory';
					break;
					
				case SERVICE_STATE_TYPE_NSW:
					$strName = 'New South Wales';
					break;
					
				case SERVICE_STATE_TYPE_NT:
					$strName = 'Northern Territory';
					break;
					
				case SERVICE_STATE_TYPE_QLD:
					$strName = 'Queensland';
					break;
					
				case SERVICE_STATE_TYPE_SA:
					$strName = 'South Australia';
					break;
					
				case SERVICE_STATE_TYPE_TAS:
					$strName = 'Tasmania';
					break;
					
				case SERVICE_STATE_TYPE_VIC:
					$strName = 'Victoria';
					break;
					
				case SERVICE_STATE_TYPE_WA:
					$strName = 'Western Australia';
					break;
					
			}
			
			$this->oblstrType		= $this->Push (new dataString	('Id',		$strId));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

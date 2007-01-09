<?php

	//----------------------------------------------------------------------------//
	// ServiceEndUserTitleType.php
	//----------------------------------------------------------------------------//
	/**
	 * ServiceEndUserTitleType.php
	 *
	 * Contains the ServiceEndUserTitleType object
	 *
	 * Contains the ServiceEndUserTitleType object
	 *
	 * @file		ServiceEndUserTitleType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ServiceEndUserTitleType
	//----------------------------------------------------------------------------//
	/**
	 * ServiceEndUserTitleType
	 *
	 * Allows Textual (named) Representation of the Constants which form End User Title Types
	 *
	 * Allows Textual (named) Representation of the Constants which form End User Title Types
	 *
	 *
	 * @prefix	srt
	 *
	 * @package	intranet_app
	 * @class	ServiceEndUserTitleType
	 * @extends	dataEnumerative
	 */
	
	class ServiceEndUserTitleType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrType
		//------------------------------------------------------------------------//
		/**
		 * _oblstrType
		 *
		 * The Id of the ServiceEndUserTitleType
		 *
		 * The Id of the ServiceEndUserTitleType
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
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	String		$strType			The Id of the Service Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($strType)
		{
			parent::__construct ('ServiceEndUserTitleType');
			
			$strName = 'Unknown';
			
			switch ($strType)
			{
				case END_USER_TITLE_TYPE_MASTER:
					$strName = "Master";
					break;
					
				case END_USER_TITLE_TYPE_MISTER:
					$strName = "Mr.";
					break;
					
				case END_USER_TITLE_TYPE_MRS:
					$strName = "Mrs.";
					break;
					
				case END_USER_TITLE_TYPE_MS:
					$strName = "Ms.";
					break;
					
				case END_USER_TITLE_TYPE_MISS:
					$strName = "Miss";
					break;
					
				case END_USER_TITLE_TYPE_DOCTOR:
					$strName = "Dr.";
					break;
					
				case END_USER_TITLE_TYPE_PROFESSOR:
					$strName = "Prof.";
			}
			
			$this->oblstrType		= $this->Push (new dataString	('Id',		$strType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

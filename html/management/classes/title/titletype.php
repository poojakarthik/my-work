<?php

	//----------------------------------------------------------------------------//
	// TitleType.php
	//----------------------------------------------------------------------------//
	/**
	 * TitleType.php
	 *
	 * Contains the TitleType object
	 *
	 * Contains the TitleType object
	 *
	 * @file		TitleType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// TitleType
	//----------------------------------------------------------------------------//
	/**
	 * TitleType
	 *
	 * Allows Textual (named) Representation of the Constants which form End User Title Types
	 *
	 * Allows Textual (named) Representation of the Constants which form End User Title Types
	 *
	 *
	 * @prefix	tit
	 *
	 * @package	intranet_app
	 * @class	TitleType
	 * @extends	dataObject
	 */
	
	class TitleType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrType
		//------------------------------------------------------------------------//
		/**
		 * _oblstrType
		 *
		 * The Id of the TitleType
		 *
		 * The Id of the TitleType
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
			parent::__construct ('TitleType');
			
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

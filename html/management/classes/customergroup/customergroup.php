<?php

	//----------------------------------------------------------------------------//
	// CustomerGroup.php
	//----------------------------------------------------------------------------//
	/**
	 * CustomerGroup.php
	 *
	 * Contains the CustomerGroup object
	 *
	 * Contains the CustomerGroup object
	 *
	 * @file		CustomerGroup.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CustomerGroup
	//----------------------------------------------------------------------------//
	/**
	 * CustomerGroup
	 *
	 * Allows Textual (named) Representation of the Constants which form a CustomerGroup
	 *
	 * Allows Textual (named) Representation of the Constants which form a CustomerGroup
	 *
	 *
	 * @prefix	cgr
	 *
	 * @package	intranet_app
	 * @class	CustomerGroup
	 * @extends	dataEnumerative
	 */
	
	class CustomerGroup extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the CustomerGroup
		 *
		 * The Id of the CustomerGroup
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
		 * The name of the CustomerGroup
		 *
		 * The name of the CustomerGroup
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
		 * Holds CustomerGroup Constant Information
		 *
		 * Holds CustomerGroup Constant Information
		 *
		 * @param	Integer		$intType			The Id of the CustomerGroup (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType, $strName=NULL)
		{
			parent::__construct ('CustomerGroup');
			
			if ($strName === NULL)
			{
				// The CustomerGroup's name was not supplied, retrieve it from the database
				$selCustomerGroup	= new StatementSelect("CustomerGroup", "internal_name", "Id = <Id>");
				$selCustomerGroup->Execute(Array("Id" => $intType));
				$arrCustomerGroup	= $selCustomerGroup->Fetch();
				$strName			= $arrCustomerGroup['internal_name'];
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

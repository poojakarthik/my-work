<?php

	//----------------------------------------------------------------------------//
	// CustomerGroups.php
	//----------------------------------------------------------------------------//
	/**
	 * CustomerGroups.php
	 *
	 * Contains the CustomerGroups object
	 *
	 * Contains the CustomerGroups object
	 *
	 * @file		CustomerGroups.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CustomerGroups
	//----------------------------------------------------------------------------//
	/**
	 * CustomerGroups
	 *
	 * Textual CustomerGroup Types
	 *
	 * Allows Textual (named) Representation of the Constants which form many CustomerGroups
	 *
	 * @prefix	cgs
	 *
	 * @package	intranet_app
	 * @class	CustomerGroups
	 * @extends	dataEnumerative
	 */
	
	class CustomerGroups extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of CustomerGroups
		 *
		 * Controls a List of CustomerGroups
		 *
		 * @param	Integer		$intCustomerGroup			[Optional] An Integer representation of a CustomerGroup type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intCustomerGroup=null)
		{
			parent::__construct ('CustomerGroups');
			
			// Instantiate the Variable Values for possible selection
			$this->_TELCOBLUE	= $this->Push (new CustomerGroup (CUSTOMER_GROUP_TELCOBLUE));
			$this->_VOICETALK	= $this->Push (new CustomerGroup (CUSTOMER_GROUP_VOICETALK));
			$this->_IMAGINE		= $this->Push (new CustomerGroup (CUSTOMER_GROUP_IMAGINE));
			
			$this->setValue ($intCustomerGroup);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected CustomerGroup Type
		 *
		 * Change the Selected CustomerGroup Type to another CustomerGroup Type
		 *
		 * @param	Integer		$intCustomerGroup		The value of the CustomerGroup Constant wishing to be set
		 * @return	Boolean								Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intCustomerGroup)
		{
			// Select the value
			switch ($intCustomerGroup)
			{
				case CUSTOMER_GROUP_TELCOBLUE:	$this->Select ($this->_TELCOBLUE);	return true;
				case CUSTOMER_GROUP_VOICETALK:	$this->Select ($this->_VOICETALK);	return true;
				case CUSTOMER_GROUP_IMAGINE:	$this->Select ($this->_IMAGINE);	return true;
				default:						return false;
			}
		}
	}
	
?>

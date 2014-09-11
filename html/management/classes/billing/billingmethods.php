<?php

	//----------------------------------------------------------------------------//
	// BillingMethods.php
	//----------------------------------------------------------------------------//
	/**
	 * BillingMethods.php
	 *
	 * Contains the ServiceType object
	 *
	 * Contains the ServiceType object
	 *
	 * @file		BillingMethods.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// __construct
	//----------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingMethod
	 *
	 * @prefix	svt
	 *
	 * @package	intranet_app
	 * @class	BillingMethods
	 * @extends	dataEnumerative
	 */
	
	class BillingMethods extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// _POST
		//------------------------------------------------------------------------//
		/**
		 * _POST
		 *
		 * Used when the BillingMethod is via POST
		 *
		 * Used when the BillingMethod is via POST
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_POST;
		
		//------------------------------------------------------------------------//
		// _EMAIL
		//------------------------------------------------------------------------//
		/**
		 * _EMAIL
		 *
		 * Used when the BillingMethod is via Email
		 *
		 * Used when the BillingMethod is via Email
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_EMAIL;
		
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
		 * @param	Integer		$intBillingMethod			[Optional] An Integer representation of a Service type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intBillingMethod=null)
		{
			parent::__construct ('BillingMethods');
			
			// Instantiate the Variable Values for possible selection
			$this->_POST		= $this->Push (new BillingMethod (DELIVERY_METHOD_POST));
			$this->_EMAIL		= $this->Push (new BillingMethod (DELIVERY_METHOD_EMAIL));
			
			$this->setValue ($intBillingMethod);
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
		 * @param	Integer		$intBillingMethod		The value of the ServiceType Constant wishing to be set
		 * @return	Boolean		Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intBillingMethod)
		{
			// Select the value
			switch ($intBillingMethod)
			{
				case DELIVERY_METHOD_POST:		$this->Select ($this->_POST);		return true;
				case DELIVERY_METHOD_EMAIL:		$this->Select ($this->_EMAIL);		return true;
				default:						return false;
			}
		}
	}
	
?>

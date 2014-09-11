<?php

	//----------------------------------------------------------------------------//
	// BillingTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * BillingTypes.php
	 *
	 * Contains the BillingTypes object
	 *
	 * Contains the BillingTypes object
	 *
	 * @file		BillingTypes.php
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
	 * Textual BillingType
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingType
	 *
	 * @prefix	bts
	 *
	 * @package	intranet_app
	 * @class	BillingTypes
	 * @extends	dataEnumerative
	 */
	
	class BillingTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of BillingType Objects
		 *
		 * Controls a List of BillingType Objects
		 *
		 * @param	Integer		$intBillingType			[Optional] An Integer representation of a BillingType type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intBillingType=null)
		{
			parent::__construct ('BillingTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_DIRECT_DEBIT	= $this->Push (new BillingType (BILLING_TYPE_DIRECT_DEBIT));
			$this->_CREDIT_CARD		= $this->Push (new BillingType (BILLING_TYPE_CREDIT_CARD));
			$this->_ACCOUNT			= $this->Push (new BillingType (BILLING_TYPE_ACCOUNT));
			
			$this->setValue ($intBillingType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected BillingType
		 *
		 * Change the Selected BillingType to another Service Type
		 *
		 * @param	Integer		$intBillingType		The value of the ServiceType Constant wishing to be set
		 * @return	Boolean		Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intBillingType)
		{
			// Select the value
			switch ($intBillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:		$this->Select ($this->_DIRECT_DEBIT);	return true;
				case BILLING_TYPE_CREDIT_CARD:		$this->Select ($this->_CREDIT_CARD);	return true;
				case BILLING_TYPE_ACCOUNT:			$this->Select ($this->_ACCOUNT);		return true;
				default:							return false;
			}
		}
	}
	
?>

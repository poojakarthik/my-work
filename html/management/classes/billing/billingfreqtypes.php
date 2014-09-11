<?php

	//----------------------------------------------------------------------------//
	// BillingFreqTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * BillingFreqTypes.php
	 *
	 * Contains the BillingFreqTypes object
	 *
	 * Contains the BillingFreqTypes object
	 *
	 * @file		BillingFreqTypes.php
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
	 * Textual BillingFreqType
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingFreqType
	 *
	 * @prefix	bfl
	 *
	 * @package	intranet_app
	 * @class	BillingFreqTypes
	 * @extends	dataEnumerative
	 */
	
	class BillingFreqTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of BillingFreqType Objects
		 *
		 * Controls a List of BillingFreqType Objects
		 *
		 * @param	Integer		$intBillingFreqType			[Optional] An Integer representation of a BillingFreqType type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intBillingFreqType=null)
		{
			parent::__construct ('BillingFreqTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_DAY				= $this->Push (new BillingFreqType (BILLING_FREQ_DAY));
			$this->_MONTH			= $this->Push (new BillingFreqType (BILLING_FREQ_MONTH));
			$this->_HALF_MONTH		= $this->Push (new BillingFreqType (BILLING_FREQ_HALF_MONTH));
			
			$this->setValue ($intBillingFreqType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected BillingFreqType
		 *
		 * Change the Selected BillingFreqType to another Service Type
		 *
		 * @param	Integer		$intBillingFreqType		The value of the ServiceType Constant wishing to be set
		 * @return	Boolean		Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intBillingFreqType)
		{
			// Select the value
			switch ($intBillingFreqType)
			{
				case BILLING_FREQ_DAY:				$this->Select ($this->_DAY);			return true;
				case BILLING_FREQ_MONTH:			$this->Select ($this->_MONTH);			return true;
				case BILLING_FREQ_HALF_MONTH:		$this->Select ($this->_HALF_MONTH);		return true;
				default:							return false;
			}
		}
	}
	
?>

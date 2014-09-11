<?php

	//----------------------------------------------------------------------------//
	// CreditCardTypes.php
	//----------------------------------------------------------------------------//
	/**
	 * CreditCardTypes.php
	 *
	 * Contains the CreditCardTypes object
	 *
	 * Contains the CreditCardTypes object
	 *
	 * @file		CreditCardTypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CreditCardTypes
	//----------------------------------------------------------------------------//
	/**
	 * CreditCardTypes
	 *
	 * Textual CreditCardType
	 *
	 * Allows Textual (named) Representation of the Constants which form many CreditCardTypes
	 *
	 * @prefix	cgs
	 *
	 * @package	intranet_app
	 * @class	CreditCardTypes
	 * @extends	dataEnumerative
	 */
	
	class CreditCardTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of CreditCardTypes
		 *
		 * Controls a List of CreditCardTypes
		 *
		 * @param	Integer		$intCreditCardType			[Optional] An Integer representation of a CreditCardType type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intCreditCardType=null)
		{
			parent::__construct ('CreditCardTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_VISA		= $this->Push (new CreditCardType (CREDIT_CARD_VISA));
			$this->_MASTERCARD	= $this->Push (new CreditCardType (CREDIT_CARD_MASTERCARD));
			$this->_AMEX		= $this->Push (new CreditCardType (CREDIT_CARD_AMEX));
			$this->_DINERS		= $this->Push (new CreditCardType (CREDIT_CARD_DINERS));
			
			$this->setValue ($intCreditCardType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected CreditCardType Type
		 *
		 * Change the Selected CreditCardType Type to another CreditCardType Type
		 *
		 * @param	Integer		$intCreditCardType		The value of the CreditCardType Constant wishing to be set
		 * @return	Boolean								Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intCreditCardType)
		{
			// Select the value
			switch ($intCreditCardType)
			{
				case CREDIT_CARD_VISA:			$this->Select ($this->_VISA);		return true;
				case CREDIT_CARD_MASTERCARD:	$this->Select ($this->_MASTERCARD);	return true;
				case CREDIT_CARD_AMEX:			$this->Select ($this->_AMEX);		return true;
				case CREDIT_CARD_DINERS:		$this->Select ($this->_DINERS);		return true;
				default:						return false;
			}
		}
	}
	
?>

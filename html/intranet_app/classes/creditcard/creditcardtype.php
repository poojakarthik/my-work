<?php

	//----------------------------------------------------------------------------//
	// CreditCardType.php
	//----------------------------------------------------------------------------//
	/**
	 * CreditCardType.php
	 *
	 * Contains the CreditCardType object
	 *
	 * Contains the CreditCardType object
	 *
	 * @file		CreditCardType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CreditCardType
	//----------------------------------------------------------------------------//
	/**
	 * CreditCardType
	 *
	 * Allows Textual (named) Representation of the Constants which form a CreditCardType
	 *
	 * Allows Textual (named) Representation of the Constants which form a CreditCardType
	 *
	 *
	 * @prefix	cgr
	 *
	 * @package	intranet_app
	 * @class	CreditCardType
	 * @extends	dataEnumerative
	 */
	
	class CreditCardType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the CreditCardType
		 *
		 * The Id of the CreditCardType
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
		 * The name of the CreditCardType
		 *
		 * The name of the CreditCardType
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
		 * Holds CreditCardType Constant Information
		 *
		 * Holds CreditCardType Constant Information
		 *
		 * @param	Integer		$intType			The Id of the CreditCardType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('CreditCardType');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case CREDIT_CARD_VISA:
					$strName = 'Visa';
					break;
					
				case CREDIT_CARD_MASTERCARD:
					$strName = 'MasterCard';
					break;
					
				case CREDIT_CARD_AMEX:
					$strName = 'American Express';
					break;
					
				case CREDIT_CARD_DINERS:
					$strName = 'Diners Club';
					break;
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

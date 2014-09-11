<?php

	//----------------------------------------------------------------------------//
	// BillingType.php
	//----------------------------------------------------------------------------//
	/**
	 * BillingType.php
	 *
	 * Contains the BillingType object
	 *
	 * Contains the BillingType object
	 *
	 * @file		BillingType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// BillingType
	//----------------------------------------------------------------------------//
	/**
	 * BillingType
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingType
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingType
	 *
	 *
	 * @prefix	bty
	 *
	 * @package	intranet_app
	 * @class	BillingType
	 * @extends	dataEnumerative
	 */
	
	class BillingType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the BillingType Type
		 *
		 * The Id of the BillingType Type
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
		 * The name of the BillingType Type
		 *
		 * The name of the BillingType Type
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
		 * Holds BillingType Type Constant Information
		 *
		 * Holds BillingType Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the BillingType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('BillingType');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case BILLING_TYPE_ACCOUNT:
					$strName = 'Account Billing';
					break;
					
				case BILLING_TYPE_CREDIT_CARD:
					$strName = 'Credit Card Billing';
					break;
					
				case BILLING_TYPE_DIRECT_DEBIT:
					$strName = 'Direct Debit Billing';
					break;
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

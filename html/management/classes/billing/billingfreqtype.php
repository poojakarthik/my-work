<?php

	//----------------------------------------------------------------------------//
	// BillingFreqType.php
	//----------------------------------------------------------------------------//
	/**
	 * BillingFreqType.php
	 *
	 * Contains the BillingFreqType object
	 *
	 * Contains the BillingFreqType object
	 *
	 * @file		BillingFreqType.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// BillingFreqType
	//----------------------------------------------------------------------------//
	/**
	 * BillingFreqType
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingFreqType
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingFreqType
	 *
	 *
	 * @prefix	bft
	 *
	 * @package	intranet_app
	 * @class	BillingFreqType
	 * @extends	dataEnumerative
	 */
	
	class BillingFreqType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the BillingFreqType Type
		 *
		 * The Id of the BillingFreqType Type
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
		 * The name of the BillingFreqType Type
		 *
		 * The name of the BillingFreqType Type
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
		 * Holds BillingFreqType Type Constant Information
		 *
		 * Holds BillingFreqType Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the BillingFreqType (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('BillingFreqType');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case BILLING_FREQ_DAY:
					$strName = 'Day';
					break;
					
				case BILLING_FREQ_MONTH:
					$strName = 'Month';
					break;
					
				case BILLING_FREQ_HALF_MONTH:
					$strName = 'Half Month';
					break;
					
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

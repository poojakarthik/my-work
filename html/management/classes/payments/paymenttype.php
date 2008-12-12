<?php

	//----------------------------------------------------------------------------//
	// paymenttype.php
	//----------------------------------------------------------------------------//
	/**
	 * paymenttype.php
	 *
	 * Contains the PaymentType object
	 *
	 * Contains the PaymentType object
	 *
	 * @file		paymenttype.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// PaymentType
	//----------------------------------------------------------------------------//
	/**
	 * PaymentType
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 *
	 * @prefix	pmt
	 *
	 * @package	intranet_app
	 * @class	PaymentType
	 * @extends	dataEnumerative
	 */
	
	class PaymentType extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the Service Type
		 *
		 * The Id of the Service Type
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
		// PaymentType
		//------------------------------------------------------------------------//
		/**
		 * PaymentType()
		 *
		 * Holds Service Type Constant Information
		 *
		 * Holds Service Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the Service Type (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('PaymentType');
			
			$strName = GetConstantDescription($intType, 'payment_type');
			
			if (!$strName)
			{
					$strName = 'Unknown';
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

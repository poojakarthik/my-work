<?php

	//----------------------------------------------------------------------------//
	// BillingMethod.php
	//----------------------------------------------------------------------------//
	/**
	 * BillingMethod.php
	 *
	 * Contains the BillingMethod object
	 *
	 * Contains the BillingMethod object
	 *
	 * @file		BillingMethod.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// BillingMethod
	//----------------------------------------------------------------------------//
	/**
	 * BillingMethod
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingMethod
	 *
	 * Allows Textual (named) Representation of the Constants which form a BillingMethod
	 *
	 *
	 * @prefix	bme
	 *
	 * @package	intranet_app
	 * @class	BillingMethod
	 * @extends	dataEnumerative
	 */
	
	class BillingMethod extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblintType
		//------------------------------------------------------------------------//
		/**
		 * _oblintType
		 *
		 * The Id of the BillingMethod Type
		 *
		 * The Id of the BillingMethod Type
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
		 * The name of the BillingMethod Type
		 *
		 * The name of the BillingMethod Type
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
		 * Holds BillingMethod Type Constant Information
		 *
		 * Holds BillingMethod Type Constant Information
		 *
		 * @param	Integer		$intType			The Id of the BillingMethod (Constant Value)
		 *
		 * @method
		 */
		
		function __construct ($intType)
		{
			parent::__construct ('BillingMethod');
			
			$strName = 'Unknown';
			
			switch ($intType)
			{
				case DELIVERY_METHOD_POST:
					$strName = 'Post';
					break;
					
				case DELIVERY_METHOD_EMAIL:
					$strName = 'Email';
					break;
			}
			
			$this->oblintType		= $this->Push (new dataInteger	('Id',		$intType));
			$this->oblstrName		= $this->Push (new dataString	('Name',	$strName));
		}
	}
	
?>

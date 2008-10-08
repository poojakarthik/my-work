<?php

	//----------------------------------------------------------------------------//
	// chargetypes.php
	//----------------------------------------------------------------------------//
	/**
	 * chargetypes.php
	 *
	 * Contains the Class that Controls ChargeType Searching
	 *
	 * Contains the Class that Controls ChargeType Searching
	 *
	 * @file		chargetypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ChargeTypes
	//----------------------------------------------------------------------------//
	/**
	 * ChargeTypes
	 *
	 * Controls Searching for an existing ChargeType
	 *
	 * Controls Searching for an existing ChargeType
	 *
	 *
	 * @prefix		ocl
	 *
	 * @package		intranet_app
	 * @class		ChargeTypes
	 * @extends		dataObject
	 */
	
	class ChargeTypes extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a ChargeType Searching Routine
		 *
		 * Constructs a ChargeType Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('ChargeTypes', 'ChargeType', 'ChargeType');
		}
		
		//------------------------------------------------------------------------//
		// UnarchivedChargeType
		//------------------------------------------------------------------------//
		/**
		 * UnarchivedChargeType()
		 *
		 * Attempts to pull an Unarchived Charge Type
		 *
		 * Attempts to pull an Unarchived Charge Type
		 *
		 * @param	String		$strChargeType			The value of the Charge Type Name
		 * @return	Integer
		 *
		 * @method
		 */
		public function UnarchivedChargeType ($strChargeType)
		{
			$selChargeType = new StatementSelect ('ChargeType', 'Id', 'Archived = 0 AND ChargeType LIKE <ChargeType>');
			$selChargeType->Execute (Array ('ChargeType' => $strChargeType));
			
			if ($selChargeType->Count () == 0)
			{
				return false;
			}
			
			$arrChargeId = $selChargeType->Fetch ();
			
			return $arrChargeId ['Id'];
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Create a new ChargeType
		 *
		 * Create a new ChargeType
		 *
		 * @param	Array		$arrDetails			Associative Array of ChargeType Information
		 * @return	Integer
		 *
		 * @method
		 */
		
		public function Add ($arrDetails)
		{
			if (!$arrDetails ['ChargeType'])
			{
				return false;
			}
			
			if (!$arrDetails ['Description'])
			{
				return false;
			}
			
			$fltAmount = $arrDetails ['Amount'];
			$fltAmount = preg_replace ('/\$/', '', $fltAmount);
			$fltAmount = preg_replace ('/\s/', '', $fltAmount);
			$fltAmount = preg_replace ('/\,/', '', $fltAmount);
			
			if (!preg_match ('/^([\d]+)(\.[\d]+){0,1}$/', $fltAmount))
			{
				throw new Exception ('Amount Invalid');
			}
			
			$arrData = Array (
				"ChargeType"			=> $arrDetails ['ChargeType'],
				"Description"			=> $arrDetails ['Description'],
				"Amount"				=> $fltAmount,
				"Nature"				=> $arrDetails ['Nature'],
				"Fixed"					=> (isset($arrDetails['Fixed']) ? $arrDetails['Fixed'] : 0),
				"Archived"				=> 0,
				"automatic_only"		=> isset($arrDetails['automatic_only'])? $arrDetails['automatic_only'] : 0
			);
			
			$insChargeType = new StatementInsert ('ChargeType');
			$intChargeType = $insChargeType->Execute ($arrData);
			
			return $intChargeType;
		}
	}
	
?>

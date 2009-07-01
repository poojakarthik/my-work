<?php

	//----------------------------------------------------------------------------//
	// recurringchargetypes.php
	//----------------------------------------------------------------------------//
	/**
	 * recurringchargetypes.php
	 *
	 * Contains the Class that Controls RecurringChargeType Searching
	 *
	 * Contains the Class that Controls RecurringChargeType Searching
	 *
	 * @file		recurringchargetypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RecurringChargeTypes
	//----------------------------------------------------------------------------//
	/**
	 * RecurringChargeTypes
	 *
	 * Controls Searching for an existing RecurringChargeType
	 *
	 * Controls Searching for an existing RecurringChargeType
	 *
	 *
	 * @prefix		rcl
	 *
	 * @package		intranet_app
	 * @class		RecurringChargeTypes
	 * @extends		dataObject
	 */
	
	class RecurringChargeTypes extends Search
	{
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a RecurringChargeType Searching Routine
		 *
		 * Constructs a RecurringChargeType Searching Routine
		 *
		 * @method
		 */
		 
		function __construct ()
		{
			parent::__construct ('RecurringChargeTypes', 'RecurringChargeType', 'RecurringChargeType');
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
			$selChargeType = new StatementSelect ('RecurringChargeType', 'Id', 'Archived = 0 AND ChargeType = <ChargeType>');
			$selChargeType->Execute (Array ('ChargeType' => $strChargeType));
			
			if ($selChargeType->Count () == 0)
			{
				return false;
			}
			
			$arrRecurringChargeId = $selChargeType->Fetch ();
			
			return $arrRecurringChargeId ['Id'];
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Create a new RecurringChargeType
		 *
		 * Create a new RecurringChargeType
		 *
		 * @param	Array		$arrDetails			Associative Array of RecurringChargeType Information
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
			
			$fltRecursionCharge = $arrDetails ['RecursionCharge'];
			$fltRecursionCharge = preg_replace ('/\$/', '', $fltRecursionCharge);
			$fltRecursionCharge = preg_replace ('/\s/', '', $fltRecursionCharge);
			$fltRecursionCharge = preg_replace ('/\,/', '', $fltRecursionCharge);
			
			if (!preg_match ('/^([\d]+)(\.[\d]+){0,1}$/', $fltRecursionCharge))
			{
				throw new Exception ('RecursionCharge Invalid');
			}
			
			$fltMinCharge = $arrDetails ['MinCharge'];
			
			if (!empty ($fltMinCharge))
			{
				$fltMinCharge = preg_replace ('/\$/', '', $fltMinCharge);
				$fltMinCharge = preg_replace ('/\s/', '', $fltMinCharge);
				$fltMinCharge = preg_replace ('/\,/', '', $fltMinCharge);
				
				if (!preg_match ('/^([\d]+)(\.[\d]+){0,1}$/', $fltMinCharge))
				{
					throw new Exception ('MinCharge Invalid');
				}
				
				$fltCancellationFee = $arrDetails ['CancellationFee'];
			}
			
			if (!empty ($fltCancellationFee))
			{
				$fltCancellationFee = preg_replace ('/\$/', '', $fltCancellationFee);
				$fltCancellationFee = preg_replace ('/\s/', '', $fltCancellationFee);
				$fltCancellationFee = preg_replace ('/\,/', '', $fltCancellationFee);
				
				if (!preg_match ('/^([\d]+)(\.[\d]+){0,1}$/', $fltCancellationFee))
				{
					throw new Exception ('CancellationFee Invalid');
				}
			}
			
			$arrData = Array (
				"ChargeType"			=> $arrDetails ['ChargeType'],
				"Description"			=> $arrDetails ['Description'],
				"Nature"				=> $arrDetails ['Nature'],
				"Fixed"					=> (isset ($arrDetails ['Fixed'])			? $arrDetails ['Fixed']			: 0),
				"RecurringFreqType"		=> $arrDetails ['RecurringFreqType'],
				"RecurringFreq"			=> $arrDetails ['RecurringFreq'],
				"MinCharge"				=> $fltMinCharge,
				"RecursionCharge"		=> $fltRecursionCharge,
				"CancellationFee"		=> $fltCancellationFee,
				"Continuable"			=> (isset ($arrDetails ['Continuable'])		? $arrDetails ['Continuable']	: 0),
				"PlanCharge"			=> (isset ($arrDetails ['PlanCharge'])		? $arrDetails ['PlanCharge']	: 0),
				"UniqueCharge"			=> (isset ($arrDetails ['UniqueCharge'])	? $arrDetails ['UniqueCharge']	: 0),
				"approval_required"		=> (array_key_exists('approval_required', $arrDetails)? $arrDetails['approval_required'] : 0),
				"Archived"				=> 0
			);
			
			$insRecurringChargeType = new StatementInsert ('RecurringChargeType');
			$intRecurringChargeType = $insRecurringChargeType->Execute ($arrData);

			return $intRecurringChargeType;
		}
	}
	
?>

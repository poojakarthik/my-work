<?php
	
	//----------------------------------------------------------------------------//
	// rateplan.php
	//----------------------------------------------------------------------------//
	/**
	 * rateplan.php
	 *
	 * File containing Rate Plan Information
	 *
	 * File containing Rate Plan Information
	 *
	 * @file		rateplan.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// RatePlan
	//----------------------------------------------------------------------------//
	/**
	 * RatePlan
	 *
	 * Class for Rate Plan Information
	 *
	 * Class for Rate Plan Information
	 *
	 *
	 * @prefix		rrp
	 *
	 * @package		intranet_app
	 * @class		RatePlan
	 * @extends		dataObject
	 */
	
	class RatePlan extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Pulls Rate Plan Inforamtion
		 *
		 * Pulls Rate Plan Inforamtion
		 *
		 * @param	Integer		$intId		The Id of the Rate Plan Requested
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('RatePlan', $intId);
			
			$selRatePlan = new StatementSelect ('RatePlan', '*', 'Id = <Id>');
			$selRatePlan->useObLib (TRUE);
			$selRatePlan->Execute (Array ('Id' => $intId));
			
			if ($selRatePlan->Count () <> 1)
			{
				throw new Exception ('Rate Plan Not Found: ' . $intId);
			}
			
			$selRatePlan->Fetch ($this);
			
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// RecurringChargeTypes
		//------------------------------------------------------------------------//
		/**
		 * RecurringChargeTypes()
		 *
		 * Pulls a list of Associated Recurring Charge Types
		 *
		 * Pulls a list of Associated Recurring Charge Types
		 *
		 * @return	dataArray [RecurringChargeType]
		 *
		 * @method
		 */
		 
		public function RecurringChargeTypes ()
		{
			$oblarrRecurringChargeTypes = new dataArray ('RecurringChargeTypes', 'RecurringChargeType');
			
			$selRatePlannRecurringChargeTypes = new StatementSelect ('RatePlanRecurringChargeType', 'RecurringChargeType', 'RatePlan = <RatePlan>');
			$selRatePlannRecurringChargeTypes->Execute (Array ('RatePlan' => $this->Pull ('Id')->getValue ()));
			
			foreach ($selRatePlannRecurringChargeTypes->FetchAll () as $arrRatePlannRecurringChargeTypes)
			{
				$oblarrRecurringChargeTypes->Push (
					new RecurringChargeType (
						$arrRatePlannRecurringChargeTypes ['RecurringChargeType']
					)
				);
			}
			
			return $oblarrRecurringChargeTypes;
		}
		
		//------------------------------------------------------------------------//
		// RateGroups
		//------------------------------------------------------------------------//
		/**
		 * RateGroups()
		 *
		 * Pulls a list of Associated RateGroups
		 *
		 * Pulls a list of Associated RateGroups
		 *
		 * @return	dataArray [RateGroup]
		 *
		 * @method
		 */
		
		public function RateGroups ()
		{
			$oblarrGroups	= new dataArray ('RateGroups', 'RateGroup');
			
			$selRateGroups	= new StatementSelect ('RatePlanRateGroup', 'RateGroup', 'RatePlan = <RatePlan>');
			$selRateGroups->Execute (Array ('RatePlan' => $this->Pull ('Id')->getValue ()));
			
			foreach ($selRateGroups->FetchAll () as $arrRate)
			{
				$oblarrGroups->Push (new RateGroup ($arrRate ['RateGroup']));
			}
			
			return $oblarrGroups;
		}
	}
	
?>

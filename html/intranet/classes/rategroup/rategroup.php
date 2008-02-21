<?php
	
	//----------------------------------------------------------------------------//
	// rategroup.php
	//----------------------------------------------------------------------------//
	/**
	 * rategroup.php
	 *
	 * File containing Rate Group Class
	 *
	 * File containing Rate Group Class
	 *
	 * @file		rategroup.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RateGroup
	//----------------------------------------------------------------------------//
	/**
	 * RateGroup
	 *
	 * Class that Holds Rate Group Information
	 *
	 * Class that Holds Rate Group Information
	 *
	 *
	 * @prefix		rgp
	 *
	 * @package		intranet_app
	 * @class		RateGroup
	 * @extends		dataObject
	 */
	
	class RateGroup extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new RateGroup with its Information Contained
		 *
		 * Constructs a new RateGroup with its Information Contained
		 *
		 * @param	Integer		$intId			The Id of the RateGroup
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			parent::__construct ('RateGroup', $intId);
			
			$selRateGroup = new StatementSelect ('RateGroup', '*', 'Id = <Id>', null, 1);
			$selRateGroup->useObLib (TRUE);
			$selRateGroup->Execute (Array ('Id' => $intId));
			
			if ($selRateGroup->Count () <> 1)
			{
				throw new Exception ('Rate Group Not Found: ' . $intId);
			}
			
			$selRateGroup->Fetch ($this);
			
			$this->Push (new ServiceTypes ($this->Pull ('ServiceType')->getValue ()));
			
			$intRecordType = $this->Pop ("RecordType")->getValue ();
			$this->Push (new RecordType ($intRecordType));
		}
		
		//------------------------------------------------------------------------//
		// RatesListing
		//------------------------------------------------------------------------//
		/**
		 * RatesListing()
		 *
		 * Gets the Rates in this Rate Group and returns only the Id
		 *
		 * Gets the Rates in this Rate Group and returns only the Id
		 *
		 * @param	Integer		$intLimit		[Optional] The number of Rates you want to return. NULL = all
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function RatesListing ($intLimit=NULL)
		{
			$oblarrRates = new dataArray ('Rates');
			
			$selRates = new StatementSelect ('RateGroupRate', 'Rate', 'RateGroup = <RateGroup>', null, $intLimit);
			$selRates->Execute (Array ('RateGroup' => $this->Pull ('Id')->getValue ()));
			
			foreach ($selRates->FetchAll () as $arrRate)
			{
				$oblarrRates->Push (new dataString ('Rate', $arrRate ['Rate']));
			}
			
			return $oblarrRates;
		}
		
		//------------------------------------------------------------------------//
		// Rates
		//------------------------------------------------------------------------//
		/**
		 * Rates()
		 *
		 * Gets the Rates in this Rate Group
		 *
		 * Gets the Rates in this Rate Group
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function Rates ()
		{
			$selRates = new StatementSelect ('RateGroupRate', 'Rate', 'RateGroup = <RateGroup>');
			$selRates->Execute (Array ('RateGroup' => $this->Pull ('Id')->getValue ()));
			
			$oblarrRates = new dataArray ('Rates', 'Rate');
			
			foreach ($selRates->FetchAll () as $arrRate)
			{
				$oblarrRates->Push (new Rate ($arrRate ['Rate']));
			}
			
			return $oblarrRates;
		}
		
		//------------------------------------------------------------------------//
		// RatePlans
		//------------------------------------------------------------------------//
		/**
		 * RatePlans()
		 *
		 * Gets the RatePlans that use this Rate Group
		 *
		 * Gets the RatePlans that use this Rate Group
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function RatePlans ()
		{
			$selPlans = new StatementSelect ('RatePlanRateGroup', 'RatePlan', 'RateGroup = <RateGroup>');
			$selPlans->Execute (Array ('RateGroup' => $this->Pull ('Id')->getValue ()));

			$oblarrPlans = new dataArray ('RatePlans', 'RatePlan');

			foreach ($selPlans->FetchAll () as $arrPlan)
			{
				$oblarrPlans->Push (new RatePlan ($arrPlan ['RatePlan']));
			}
			
			return $oblarrPlans;
		}
	}
	
?>

<?php
	
	//----------------------------------------------------------------------------//
	// rategrouprate.php
	//----------------------------------------------------------------------------//
	/**
	 * rategrouprate.php
	 *
	 * File containing Rate Group Rate Class
	 *
	 * File containing Rate Group Rate Class
	 *
	 * @file		rategrouprate.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// RateGroupRate
	//----------------------------------------------------------------------------//
	/**
	 * RateGroupRate
	 *
	 * Class that Holds Rate Group Information
	 *
	 * Class that Holds Rate Group Information
	 *
	 *
	 * @prefix		rgr
	 *
	 * @package		intranet_app
	 * @class		RateGroupRate
	 * @extends		dataCollation
	 */
	
	class RateGroupRate extends dataCollation
	{
		
		private $_intRateGroup;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new RateGroupRate Search with its Information Contained
		 *
		 * Constructs a new RateGroupRate Search with its Information Contained
		 *
		 * @param	Integer		$intId			The Id of the RateGroup
		 *
		 * @method
		 */
		
		function __construct ($intRateGroup)
		{
			$this->_intRateGroup = $intRateGroup;
			
			$selRates = new StatementSelect ('RateGroupRate', 'count(Rate) AS collationLength', 'RateGroup = <RateGroup>');
			$selRates->Execute (Array ('RateGroup' => $this->_intRateGroup));
			
			$arrRates = $selRates->Fetch ();
			
			parent::__construct ('RateGroupRate', 'Rate', $arrRates ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Starts a new Rate
		 *
		 * Starts a new Rate
		 *
		 * @param	Integer		$intId			The Id of the Result
		 * @return	Rate
		 *
		 * @method
		 */
		
		public function ItemId ($intId)
		{
			return new Rate ($intId);
		}
		
		//------------------------------------------------------------------------//
		// ItemIndex
		//------------------------------------------------------------------------//
		/**
		 * ItemIndex()
		 *
		 * Get the Result by Search Index
		 *
		 * Get a particular result depending on its position in the search
		 *
		 * @param	Integer		$intIndex		The Index of the Result
		 * @return	Rate
		 *
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			$selRates = new StatementSelect (
				'RateGroupRate g INNER JOIN Rate r ON (g.Rate = r.Id)', 
				'r.Id', 
				'g.RateGroup = <RateGroup>', 
				'r.Name',
				$intIndex . ', 1'
			);
			
			$selRates->Execute (Array ('RateGroup' => $this->_intRateGroup));
			
			$arrRate = $selRates->Fetch ();
			
			return $this->ItemId ($arrRate ['Id']);
		}
		
		//------------------------------------------------------------------------//
		// ItemList
		//------------------------------------------------------------------------//
		/**
		 * ItemList()
		 *
		 * Return a list of results
		 *
		 * Return a list of results that are pagination controlled
		 *
		 * @param	Integer		$intStart 		The number of the Starting Index
		 * @param	Integer		$intLength 		The number of results to return
		 * @return	Array
		 *
		 * @method
		 */
		
		public function ItemList ($intStart, $intLength)
		{
			$_DATA = Array ();
			
			// Pull all Id values which match against the Constraints
			// that are within the page limit
			$selRates = new StatementSelect (
				'RateGroupRate g INNER JOIN Rate r ON (g.Rate = r.Id)', 
				'r.Id', 
				'g.RateGroup = <RateGroup>', 
				'r.Name',
				$intStart . ', ' . $intLength
			);
			
			$selRates->Execute (Array ('RateGroup' => $this->_intRateGroup));
			
			// Store the Results as Objects in an array
			while ($arrRate = $selRates->Fetch ())
			{
				$_DATA [] = $this->Push ($this->ItemId ($arrRate ['Id']));
			}
			
			return $_DATA;
		}
	}
	
?>

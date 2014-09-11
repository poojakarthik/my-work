<?php
	
	//----------------------------------------------------------------------------//
	// rates.php
	//----------------------------------------------------------------------------//
	/**
	 * rates.php
	 *
	 * Rates Searching Definition File
	 *
	 * Rates Searching Definition File
	 *
	 * @file		rates.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Rates
	//----------------------------------------------------------------------------//
	/**
	 * Rates
	 *
	 * Class for Searching for Rates
	 *
	 * Class for Searching for Rates
	 *
	 *
	 * @prefix		rrl
	 *
	 * @package		intranet_app
	 * @class		Rates
	 * @extends		Search
	 */
	
	class Rates extends Search
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructs a new Rates Search
		 *
		 * Constructs a new Rates Search
		 *
		 * @method
		 */
		
		function __construct ()
		{
			parent::__construct ('Rates', 'Rate', 'Rate');
		}
		
		//------------------------------------------------------------------------//
		// UnarchivedNameExists
		//------------------------------------------------------------------------//
		/**
		 * UnarchivedNameExists()
		 *
		 * Check Name Availability
		 *
		 * Check to see if there is a Rate with the same Name. There can only be 1
		 * unarchived item with the same name
		 *
		 * @param	String		$strName		The name of the Rate being Searched For
		 * @return	<type>
		 *
		 * @method
		 * @see	<MethodName()||typePropertyName>
		 */
		
		public function UnarchivedNameExists ($strName)
		{
			$selRate = new StatementSelect ('Rate', 'count(*) AS Length', 'Name = <Name> AND Archived = 0');
			$selRate->Execute (Array ('Name' => $strName));
			$arrLength = $selRate->Fetch ();
			
			return $arrLength ['Length'] <> 0;
		}
		
		//------------------------------------------------------------------------//
		// Add
		//------------------------------------------------------------------------//
		/**
		 * Add()
		 *
		 * Add a new Rate
		 *
		 * Adds a new Rate to the System
		 *
		 * @param	Array		$arrRate 		Associative array of Rate Information
		 * @return	Integer						The Id of the new Rate
		 *
		 * @method
		 */
		
		public function Add ($arrRate)
		{
			// Make it unarchived
			$arrRate ['Archived']		= 0;
			$arrRate ['StartTime']		= $arrRate ['StartTime'] . ':00';
			$arrRate ['EndTime']		= $arrRate ['EndTime'] . ':59';
			
			$insRate = new StatementInsert ('Rate');
			return $insRate->Execute ($arrRate);
		}
		
		//------------------------------------------------------------------------//
		// getRates
		//------------------------------------------------------------------------//
		/**
		 * getRates()
		 *
		 * Get a list of Rates
		 *
		 * Get a list of Rates (Identified by an Array)
		 *
		 * @param	Array		$arrRates 		Associative array of Rates
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function getRates ($arrRates)
		{
			$oblarrRates = new dataArray ('Rates', 'Rate');
			
			foreach ($arrRates as $intRate => $strRate)
			{
				$oblarrRates->Push (new Rate ($intRate));
			}
			
			return $oblarrRates;
		}
	}
	
?>

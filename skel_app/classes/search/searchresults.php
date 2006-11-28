<?php
	
//----------------------------------------------------------------------------//
// searchresults.php
//----------------------------------------------------------------------------//
/**
 * searchresults.php
 *
 * File containing class for Search Results
 *
 * File containing class for Search Results
 *
 * @file	searchresults.php
 * @language	PHP
 * @package	intranet_app
 * @author	Bashkim 'bash' Isai
 * @version	6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	
	//----------------------------------------------------------------------------//
	// SearchResults
	//----------------------------------------------------------------------------//
	/**
	 * SearchResults
	 *
	 * Class for Search Result Handling
	 *
	 * Class that contains the results for searching the database
	 *
	 *
	 * @prefix	ser
	 *
	 * @package	intranet_app
	 * @class	SearchResults
	 * @extends	dataCollation
	 */
	 
	class SearchResults extends dataCollation
	{
		
		private $_strTable;
		
		private $_strResultClass;
		
		//------------------------------------------------------------------------//
		// _arrConditions
		//------------------------------------------------------------------------//
		/**
		 * _arrConditions
		 *
		 * The Conditions being applied to a Search
		 *
		 * The Conditions being applied to a Search
		 *
		 * @type	Array
		 *
		 * @property
		 */
		
		private $_arrConditions;
		
		private $_strOrderClause;
		
		//------------------------------------------------------------------------//
		// SearchResults
		//------------------------------------------------------------------------//
		/**
		 * SearchResults()
		 *
		 * Constructor for Searching the Database
		 *
		 * Constructor for Searching the Database
		 *
		 * @method
		 */
		
		function __construct ($strTable, $strResultClass, $oblarrConstraints, $seoOrder)
		{
			$this->_strTable = $strTable;
			$this->_strResultClass = $strResultClass;
			
			$this->_arrConditions = Array ();
			
			foreach ($oblarrConstraints AS $Item)
			{
				$this->_arrConditions [$Item->getName ()] = Array (
					'Operator'	=> $Item->getOperator (),
					'Value'		=> $Item->getValue ()
				);
			}
			
			if ($seoOrder->getColumn () !== '')
			{
				$this->_strOrderClause = $seoOrder->getColumn () . ' ' . (($seoOrder->isAscending () === TRUE) ? 'ASC' : 'DESC');
			}
			
			$selSearchResults = new StatementSelect (
				$this->_strTable, 
				'count(*) AS collationLength', 
				$this->_arrConditions
			);
			
			$selSearchResults->Execute($this->_arrConditions);
			$arrLength = $selSearchResults->Fetch ();
			
			parent::__construct ('Results', $this->_strResultClass, $arrLength ['collationLength']);
		}
		
		public function ItemId ($intId)
		{
			$Item = $this->Pull ($intId);
			
			if ($Item !== null)
			{
				return $Item;
			}
			
			return new $this->_strResultClass ($intId);
		}
		
		public function ItemIndex ($intIndex)
		{
			$selItem = new StatementSelect (
				$this->_strTable, 
				'Id', 
				$this->_arrConditions,
				$this->_strOrderClause,
				$intIndex . ', 1'
			);
			
			
			$selItem->Execute ($this->_arrConditions);
			$Item = $selItem->Fetch ();
			
			$this->ItemId ($Item ['Id']);
		}
		
		public function ItemList ($intStart, $intLength)
		{
			$_DATA = Array ();
			
			$selSearchResults = new StatementSelect (
				$this->_strTable, 
				'Id', 
				$this->_arrConditions,
				$this->_strOrderClause,
				$intStart . ', ' . $intLength
			);
			
			$selSearchResults->Execute ($this->_arrConditions);
			
			while ($Item = $selSearchResults->Fetch ())
			{
				$_DATA [] = $this->Push ($this->ItemId ($Item ['Id']));
			}
			
			return $_DATA;
		}
	}
	
?>

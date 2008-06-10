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
		
		//------------------------------------------------------------------------//
		// _strTable
		//------------------------------------------------------------------------//
		/**
		 * _strTable
		 *
		 * The table being Searched
		 *
		 * The table being Searched
		 *
		 * @type	String
		 *
		 * @property
		 */
		
		private $_strTable;
		
		//------------------------------------------------------------------------//
		// _strResultClass
		//------------------------------------------------------------------------//
		/**
		 * _strResultClass
		 *
		 * The class that is instantiated with the Id for a Search Result
		 *
		 * The class that is instantiated with the Id for a Search Result
		 *
		 * @type	String
		 *
		 * @property
		 */
		
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
		
		//------------------------------------------------------------------------//
		// _strOrderClause
		//------------------------------------------------------------------------//
		/**
		 * _strOrderClause
		 *
		 * The SearchOrder object represented as a String
		 *
		 * The string that is used to define how information is Ordered
		 *
		 * @type	String
		 *
		 * @property
		 */
		
		private $_strOrderClause;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for Searching the Database
		 *
		 * Constructor for Searching the Database
		 *
		 * @param	String			$strTable			The table where information is attempted to be found
		 * @param	String			$strResultClass		The class instantiated when a result is retrieved
		 * @param	dataArray		$oblarrConstraint	An ObLib array of Constraints to search for
		 * @param	SearchOrder		$seoOrder			Information holding Sorting/Order information
		 *
		 * @method
		 */

		//------------------------------------------------------------------------//
		// _database
		//------------------------------------------------------------------------//
		/**
		 * _database
		 *
		 * The database to connect to. Default is FLEX_DATABASE_CONNECTION_DEFAULT.
		 *
		 * The database to connect to. Default is FLEX_DATABASE_CONNECTION_DEFAULT.
		 *
		 * @type int	
		 *
		 * @property
		 */
		private $_database;
		
		function __construct ($strTable, $strResultClass, $oblarrConstraints, $seoOrder, $database=FLEX_DATABASE_CONNECTION_DEFAULT)
		{
			// Because the Table and Result Class are strings, they can be sent to the object
			// straight away
			$this->_strTable = $strTable;
			$this->_strResultClass = $strResultClass;
			
			$this->_database = $database;
			
			// Put all of the dataArray Constraints into a PHP Array
			// so that we can do a Search against the constraint
			$this->_arrConditions = Array ();

			foreach ($oblarrConstraints AS $Item)
			{
				$this->_arrConditions [uniqid ()] = Array (
					'Column'	=> $Item->getName (),
					'Operator'	=> $Item->getOperator (),
					'Value'		=> $Item->getValue ()
				);
			}
			
			// If we have a Column to sort by, then Sort!
			if ($seoOrder->getColumn () !== '')
			{
				$this->_strOrderClause = $seoOrder->getColumn () . ' ' . (($seoOrder->isAscending () === TRUE) ? 'ASC' : 'DESC');
			}
			
			// Now that we know what we're dealing with, we can find out
			// how many rows/results we have for this particular search.
			
			// Notice that here, we are not using the OrderBy so we can 
			// save time
			$selSearchResults = new StatementSelect ($this->_strTable, 'count(*) AS collationLength', $this->_arrConditions, '', '', '', $this->_database);
			$selSearchResults->Execute($this->_arrConditions);
			$arrLength = $selSearchResults->Fetch ();
			
			// Instantiate the dataCollation
			parent::__construct ('Results', $this->_strResultClass, $arrLength ['collationLength']);
		}
		
		//------------------------------------------------------------------------//
		// ItemId
		//------------------------------------------------------------------------//
		/**
		 * ItemId()
		 *
		 * Instantiates a Result Class
		 *
		 * Instantiates a Result Class based on the Id of the Result
		 *
		 * @param	Integer		$intId			The Id of the Result
		 * @return	Mixed						(namely - $this->_strResultClass)
		 *
		 * @method
		 */
		
		public function ItemId ($intId)
		{
			return new $this->_strResultClass ($intId);
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
		 * @return	Mixed						(namely - $this->_strResultClass)
		 *
		 * @method
		 */
		
		public function ItemIndex ($intIndex)
		{
			$selItem = new StatementSelect (
				$this->_strTable, 
				'Id', 
				$this->_arrConditions,
				$this->_strOrderClause,
				$intIndex . ', 1',
				'',
				$this->_database
			);
			
			
			$selItem->Execute ($this->_arrConditions);
			$Item = $selItem->Fetch ();
			
			$this->ItemId ($Item ['Id']);
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
			$selSearchResults = new StatementSelect (
				$this->_strTable, 
				'Id', 
				$this->_arrConditions,
				$this->_strOrderClause,
				$intStart . ', ' . $intLength,
				'',
				$this->_database
			);
			
			$selSearchResults->Execute ($this->_arrConditions);
			
			// Store the Results as Objects in an array
			while ($Item = $selSearchResults->Fetch ())
			{
				$_DATA [] = $this->Push ($this->ItemId ($Item ['Id']));
			}
			
			return $_DATA;
		}
	}
	
?>

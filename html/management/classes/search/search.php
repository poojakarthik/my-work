<?php

	//----------------------------------------------------------------------------//
	// search.php
	//----------------------------------------------------------------------------//
	/**
	 * search.php
	 *
	 * Contains the Abstract Class that Controls Data Searching
	 *
	 * This file contains the Abstract Class that is used for Searching or Filtering
	 * Information in the Database.
	 *
	 * @file		search.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Search
	//----------------------------------------------------------------------------//
	/**
	 * Search
	 *
	 * Abstraction for searching the Database
	 *
	 * Abstraction for searching the Database
	 *
	 * @package		intranet_app
	 * @class		Search
	 * @extends		dataObject
	 */
	
	class Search extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _strTable
		//------------------------------------------------------------------------//
		/**
		 * _strTable
		 *
		 * The table name
		 *
		 * The name of the Table information is being retrieved from
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
		 * The result class
		 *
		 * The class to instantiate when we have a result
		 *
		 * @type	String
		 *
		 * @property
		 */
		 
		private $_strResultClass;
	
		//------------------------------------------------------------------------//
		// _oblarrConstraints
		//------------------------------------------------------------------------//
		/**
		 * _oblarrConstraints
		 *
		 * Constrains for the Search
		 *
		 * Contains the Information regarding the Constraints and Filters that will
		 * be applied to a Search
		 *
		 * @type	dataArray
		 *
		 * @property
		 */
		
		private $_oblarrConstraints;
		
		//------------------------------------------------------------------------//
		// _seoOrder
		//------------------------------------------------------------------------//
		/**
		 * _seoOrder
		 *
		 * Contains Ordering/Sorting Information
		 *
		 * Contains information which is related to the Ordering or Sorting of
		 * a field in the Search.
		 *
		 * @type	SearchOrder
		 *
		 * @property
		 */
		
		private $_seoOrder;

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
		
		//------------------------------------------------------------------------//
		// AccountSearch
		//------------------------------------------------------------------------//
		/**
		 * AccountSearch()
		 *
		 * Constructs an Account Searching Routine
		 *
		 * Contorls the Bulk of the work required for searching for a particular account
		 *
		 * @method
		 */
		 //TODO!gui! fix this docblock
		 
		function __construct ($strSearchName, $strTable, $strResultClass, $database=FLEX_DATABASE_CONNECTION_DEFAULT)
		{
			parent::__construct ($strSearchName);
			
			$this->_strTable = $strTable;
			$this->_strResultClass = $strResultClass;
			
			$this->_database = $database;
			
			$this->_oblarrConstraints = $this->Push (new dataArray ('Constraints', 'SearchConstraint'));
			$this->_seoOrder = $this->Push (new SearchOrder ($strTable));
		}
		
		//------------------------------------------------------------------------//
		// Constrain
		//------------------------------------------------------------------------//
		/**
		 * Constrain()
		 *
		 * Defines searching constraints
		 *
		 * Defines what constrains will be applied to the Search Request.
		 *
		 * @param	String		$strFieldName			The name of the Field (Antecedent)
		 * @param	String		$strFieldConstraint		The type of Constraint to apply (Operator)
		 * @param	Mixed		$mixFieldValue			The value of the Field (Consequent)
		 *
		 * @return	void
		 *
		 * @method
		 */
		
		public function Constrain ($strFieldName, $strFieldConstraint, $mixFieldValue)
		{
			$scoConstraintObject = null;
			
			// If the Name of the Field is Id, we won't find it in our array but 
			// it is still valid - so set it as a dataInteger
			if ($strFieldName == 'Id')
			{
				$scoConstraintObject = new SearchConstraint
				(
					'Id',
					'EQUALS',
					'dataInteger',
					$mixFieldValue
				);
			}
			else
			{
				// Make sure the Field Exists
				$arrModel = Flex_Data_Model::get($this->_strTable);
				if ($arrModel === NULL || !isset ($arrModel['Column'][$strFieldName]))
				{
					throw new Exception ('Field does not exist.');
				}
				
				// Get the definition and set the Constaint
				$arrDefinition = $arrModel['Column'][$strFieldName];
				
				$scoConstraintObject = new SearchConstraint 
				(
					$strFieldName,
					$strFieldConstraint,
					$arrDefinition ['ObLib'],
					$mixFieldValue
				);
			}
			
			$this->_oblarrConstraints->Push ($scoConstraintObject);
		}
		
		//------------------------------------------------------------------------//
		// Order
		//------------------------------------------------------------------------//
		/**
		 * Order()
		 *
		 * Orders Results
		 *
		 * Set the flag for ordering results
		 *
		 * @param	String		$strOrderColumn	The name of the Column to order by
		 * @param	Boolean		$bolOrderMethod		Whether to Sort by Ascending (TRUE) or Descending (FALSE)
		 *
		 * @return	void
		 *
		 * @method
		 */
		
		public function Order ($strOrderColumn, $bolOrderMethod=TRUE)
		{
			$this->_seoOrder->setColumn ($strOrderColumn);
			
			if ($bolOrderMethod === TRUE)
			{
				$this->_seoOrder->setAscending ();
			}
			else
			{
				$this->_seoOrder->setDescending ();
			}
		}
		
		//------------------------------------------------------------------------//
		// Sample
		//------------------------------------------------------------------------//
		/**
		 * Sample()
		 *
		 * Attaches a Sample
		 *
		 * Attaches a result set to the search with items that fall between two values of records for the search
		 *
		 * @param	Integer		$intPage		The Page number Searched for (1, 2, 3, ...)
		 * @param	Integer		$intLength		The number of Items per Page (10, 20, 50, 100, ... )
		 *
		 * @return	void
		 *
		 * @method
		 */
		
		public function Sample ($intPage=1, $intLength=NULL)
		{
			$serResults = new SearchResults (
				$this->_strTable, 
				$this->_strResultClass,
				$this->_oblarrConstraints, 
				$this->_seoOrder,
				$this->_database
			);
			
			return $this->Push ($serResults->Sample ($intPage, $intLength));
		}
	}
	
?>

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
 * @file	search.php
 * @language	PHP
 * @package	intranet_app
 * @author	Bashkim 'bash' Isai
 * @version	6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
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
	 * @package	intranet_app
	 * @class	Search
	 * @extends	dataObject
	 */
	
	class Search extends dataObject
	{
		
		private $_strTable;
		
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
		 
		function __construct ($strSearchName, $strTable, $strResultClass)
		{
			parent::__construct ($strSearchName);
			
			$this->_strTable = $strTable;
			$this->_strResultClass = $strResultClass;
			
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
		 * @method
		 */
		
		public function Constrain ($fieldName, $fieldConstraint, $fieldValue)
		{
			$scoConstraintObject = null;
			
			if ($fieldName == 'Id')
			{
				$scoConstraintObject = new SearchConstraint
				(
					'Id',
					'EQUALS',
					'dataInteger',
					$fieldValue
				);
			}
			else
			{
				if (!isset ($GLOBALS['arrDatabaseTableDefine'][$this->_strTable]['Column'][$fieldName]))
				{
					throw new Exception ('Field not exists.');
				}
				
				$arrDefinition = $GLOBALS['arrDatabaseTableDefine'][$this->_strTable]['Column'][$fieldName];
				$scoConstraintObject = new SearchConstraint 
				(
					$fieldName,
					$fieldConstraint,
					$arrDefinition ['ObLib'],
					$fieldValue
				);
			}
			
			$this->_oblarrConstraints->Push ($scoConstraintObject);
		}
		
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
		
		public function Sample ($intPage=1, $intLength=NULL)
		{
			$serResults = new SearchResults (
				$this->_strTable, 
				$this->_strResultClass,
				$this->_oblarrConstraints, 
				$this->_seoOrder
			);
			
			$this->Push ($serResults->Sample ($intPage, $intLength));
		}
	}
	
?>

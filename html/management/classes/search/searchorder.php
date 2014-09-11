<?php
	
	//----------------------------------------------------------------------------//
	// searchorder.php
	//----------------------------------------------------------------------------//
	/**
	 * searchorder.php
	 *
	 * File that contains the Search Order Class
	 *
	 * File that contains the Search Order Class
	 *
	 * @file		searchorder.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.10
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// SearchOrder
	//----------------------------------------------------------------------------//
	/**
	 * SearchOrder
	 *
	 * Holds Search Ordering Information
	 *
	 * Holds Search Ordering Information
	 *
	 *
	 * @prefix	seo
	 *
	 * @package	intranet_app
	 * @class	SearchOrder
	 * @extends	dataObject
	 */
	
	class SearchOrder extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _oblstrOrderColumn
		//------------------------------------------------------------------------//
		/**
		 * _oblstrOrderColumn
		 *
		 * The name of the Order Column
		 *
		 * The name of the Column you would like to Order the search results by
		 *
		 * @type	dataString
		 *
		 * @property
		 */
		
		private $_oblstrOrderColumn;
		
		//------------------------------------------------------------------------//
		// _oblbolOrderMethod
		//------------------------------------------------------------------------//
		/**
		 * _oblbolOrderMethod
		 *
		 * The Direction of Ordering
		 *
		 * The Direction of Ordering
		 * TRUE:		Sort the list in Ascending Order
		 * FALSE:		Sort the list in Descending Order
		 *
		 * @type	dataBoolean
		 *
		 * @property
		 */
		
		private $_oblbolOrderMethod;
		
		//------------------------------------------------------------------------//
		// _strTable
		//------------------------------------------------------------------------//
		/**
		 * _strTable
		 *
		 * The Table where Data is being Retrieved From
		 *
		 * The Table where Data is being Retrieved From
		 *
		 * @type	String
		 *
		 * @property
		 */
		
		private $_strTable;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Start a new Search Order
		 *
		 * Start a new Search Order
		 *
		 * @param	String		$strTable		The name of the table information is retrieved from
		 *
		 * @method
		 */
		
		function __construct ($strTable)
		{
			parent::__construct ('Order');
			
			$this->_strTable = $strTable;
			
			$this->_oblstrOrderColumn = $this->Push (new dataString ('Column', ''));
			$this->_oblbolOrderMethod = $this->Push (new dataBoolean ('Method', TRUE));
		}
		
		//------------------------------------------------------------------------//
		// setColumn
		//------------------------------------------------------------------------//
		/**
		 * setColumn()
		 *
		 * Set the name of the Column to Order By
		 *
		 * Set the name of the Column to Order By
		 *
		 * @param	String		$strColumnName		The name of the Column to Sort By
		 * @return	Void
		 *
		 * @method
		 */
		
		public function setColumn ($strColumnName)
		{
			// Unless the name of the Column is 'Id', we need to check
			// if the Column exists for the table
			if ($strColumnName != 'Id')
			{
				// If the column does not exist
				$arrModel = Flex_Data_Model::get($this->_strTable);
				if ($arrModel === NULL || !isset ($arrModel['Column'][$strColumnName]))
				{
					// Throw an Error
					throw new Exception ('Field not exists.');
				}
			}
			
			$this->_oblstrOrderColumn->setValue ($strColumnName);
		}
		
		//------------------------------------------------------------------------//
		// getColumn
		//------------------------------------------------------------------------//
		/**
		 * getColumn()
		 *
		 * Get the name of the column currently being Ordered By
		 *
		 * Get the name of the column currently being Ordered By
		 *
		 * @return	String
		 *
		 * @method
		 */
		
		public function getColumn ()
		{
			return $this->_oblstrOrderColumn->getValue ();
		}
		
		//------------------------------------------------------------------------//
		// setAscending
		//------------------------------------------------------------------------//
		/**
		 * setAscending()
		 *
		 * Alter the results to order Ascendingly
		 *
		 * Alter the results to order Ascendingly
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function setAscending ()
		{
			$this->_oblbolOrderMethod->setTrue ();
		}
		
		//------------------------------------------------------------------------//
		// setDescending
		//------------------------------------------------------------------------//
		/**
		 * setDescending()
		 *
		 * Alter the results to order Descendingly (reverse order)
		 *
		 * Alter the results to order Descendingly (reverse order)
		 *
		 * @return	Void
		 *
		 * @method
		 */
		
		public function setDescending ()
		{
			$this->_oblbolOrderMethod->setFalse ();
		}
		
		//------------------------------------------------------------------------//
		// isAscending
		//------------------------------------------------------------------------//
		/**
		 * isAscending()
		 *
		 * Find out whether or not the table is ordered Ascendingly
		 *
		 * Find out whether or not the table is ordered Ascendingly
		 *
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function isAscending ()
		{
			return $this->_oblbolOrderMethod->isTrue ();
		}
		
		//------------------------------------------------------------------------//
		// isDescending
		//------------------------------------------------------------------------//
		/**
		 * isDescending()
		 *
		 * Find out whether or not the table is ordered Descendingly
		 *
		 * Find out whether or not the table is ordered Descendingly
		 *
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function isDescending ()
		{
			return $this->_oblbolOrderMethod->isFalse ();
		}
	}
	
?>

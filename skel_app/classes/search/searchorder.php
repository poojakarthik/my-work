<?php
	
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
		
		function __construct ($strTable)
		{
			parent::__construct ('Order');
			
			$this->_strTable = $strTable;
			
			$this->_oblstrOrderColumn = $this->Push (new dataString ('Column', ''));
			$this->_oblbolOrderMethod = $this->Push (new dataBoolean ('Method', TRUE));
		}
		
		public function setColumn ($strColumnName)
		{
			if ($strColumnName != 'Id')
			{
				if (!isset ($GLOBALS['arrDatabaseTableDefine'][$this->_strTable]['Column'][$strColumnName]))
				{
					throw new Exception ('Field not exists.');
				}
			}
			
			$this->_oblstrOrderColumn->setValue ($strColumnName);
		}
		
		public function getColumn ()
		{
			return $this->_oblstrOrderColumn->getValue ();
		}
		
		public function setAscending ()
		{
			$this->_oblbolOrderMethod->setTrue ();
		}
		
		public function setDescending ()
		{
			$this->_oblbolOrderMethod->setFalse ();
		}
		
		public function isAscending ()
		{
			return $this->_oblbolOrderMethod->isTrue ();
		}
		
		public function isDescending ()
		{
			return $this->_oblbolOrderMethod->isFalse ();
		}
	}
	
?>

<?php
	
	//----------------------------------------------------------------------------//
	// datareport.php
	//----------------------------------------------------------------------------//
	/**
	 * datareport.php
	 *
	 * File containing DataReport Class
	 *
	 * File containing DataReport Class
	 *
	 * @file		datareport.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// DataReport
	//----------------------------------------------------------------------------//
	/**
	 * DataReport
	 *
	 * A DataReport in the Database
	 *
	 * A DataReport in the Database
	 *
	 *
	 * @prefix	rpt
	 *
	 * @package		intranet_app
	 * @class		DataReport
	 * @extends		dataObject
	 */
	
	class DataReport extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new DataReport
		 *
		 * Constructor for a new DataReport
		 *
		 * @param	Integer		$intId		The Id of the DataReport being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the DataReport information and Store it ...
			$selDataReport = new StatementSelect ('DataReport', '*', 'Id = <Id>', null, 1);
			$selDataReport->useObLib (TRUE);
			$selDataReport->Execute (Array ('Id' => $intId));
			
			if ($selDataReport->Count () <> 1)
			{
				throw new Exception ('DataReport does not exist.');
			}
			
			$selDataReport->Fetch ($this);
			
			// Construct the object
			parent::__construct ('DataReport', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Execute
		//------------------------------------------------------------------------//
		/**
		 * Execute()
		 *
		 * Execute the DataReport
		 *
		 * Execute the DataReport
		 *
		 * @param	Array		$arrSelects		A filled Associative Array of Fields that will be Selected
		 * @param	Array		$arrFields		A filled Associative Array (from the Serialized DataReportFields field)
		 *
		 * @return	Array
		 *
		 * @method
		 */
		
		public function Execute ($arrSelects, $arrFields)
		{
			// This deals with turning the SQLSelect Serialized Array 
			// into a String: Field1, Field2, Field3 [, ... ]
			$arrSelect = unserialize ($this->Pull ('SQLSelect')->getValue ());
			
			$i = 0;
			foreach ($arrSelects as $strField)
			{
				if ($arrSelect [$strField])
				{
					if ($i != 0)
					{
						$strSelect .= ", ";
					}
					
					$strSelect .= $arrSelect [$strField];
					
					++$i;
				}
			}
			
			// This starts the SQL Statement
			
			$selResult = new StatementSelect (
				$this->Pull ('SQLTable')->getValue (), 
				$strSelect, 
				$this->Pull ('SQLWhere')->getValue (), 
				null,
				null,
				$this->Pull ('SQLGroupBy')->getValue ()
			);
			
			// From here, we may need to process values. For example, dates
			// come into the system as an Array [day, month, year]. We need
			// to change them to a string of YYYY-MM-DD
			
			$arrInputs = unserialize ($this->Pull ('SQLFields')->getValue ());
			$arrValues = Array ();
			
			foreach ($arrInputs as $strName => $arrInput)
			{
				switch ($arrInput ['Type'])
				{
					case "dataDate":
						$arrValues [$strName] = date (
							"Y-m-d", 
							mktime (0, 0, 0, $arrFields [$strName]['month'], $arrFields [$strName]['day'], $arrFields [$strName]['year'])
						);
						
						break;
						
					case "dataDatetime":
						$arrValues [$strName] = date (
							"Y-m-d H:i:s", 
							mktime (
								$arrFields [$strName]['hour'], $arrFields [$strName]['minute'], $arrFields [$strName]['second'],
								$arrFields [$strName]['month'], $arrFields [$strName]['day'], $arrFields [$strName]['year']
							)
						);
						
						break;
						
					case "dataString":
						$arrValues [$strName] = "%" . $arrFields [$strName] . "%";
						
						break;
						
					default:
						$arrValues [$strName] = $arrFields [$strName];
						break;
				}
			}
			
			// Execute the Result
			$selResult->Execute ($arrValues);
			
			// Return the Result
			return $selResult;
		}
		
		//------------------------------------------------------------------------//
		// Documentation
		//------------------------------------------------------------------------//
		/**
		 * Documentation()
		 *
		 * Get a list of Documentation objects required
		 *
		 * Get a list of Documentation objects required
		 *
		 * @return	Array
		 *
		 * @method
		 */
		
		public function Documentation ()
		{
			return unserialize ($this->Pull ('Documentation')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Inputs
		//------------------------------------------------------------------------//
		/**
		 * Inputs()
		 *
		 * Turn the Serialised Input Array into ObLib
		 *
		 * Turn the Serialised Input Array into ObLib
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function Inputs ()
		{
			$arrInputs = unserialize ($this->Pull ('SQLFields')->getValue ());
			
			$oblarrInputs = new dataArray ('Inputs');
			
			if (is_array ($arrInputs))
			{
				foreach ($arrInputs as $strName => $arrInput)
				{
					$oblarrField		= $oblarrInputs->Push (new dataArray ('Input'));
					$oblstrName			= $oblarrField->Push (new dataString ('Name', $strName));
					
					$oblstrDocEntity	= $oblarrField->Push (new dataString ('Documentation-Entity',	$arrInput ['Documentation-Entity']));
					$oblstrDocField		= $oblarrField->Push (new dataString ('Documentation-Field',	$arrInput ['Documentation-Field']));
					
					$oblstrType			= $oblarrField->Push (new dataString ('Type',					$arrInput ['Type']));
					
					if (class_exists ($arrInput ['Type']))
					{
						if (is_subclass_of ($arrInput ['Type'], "dataPrimitive") || is_subclass_of ($arrInput ['Type'], "dataObject"))
						{
							$oblarrField->Push (new $arrInput ['Type'] ('Value'));
						}
						else
						{
							$oblarrValue		= $oblarrField->Push (new dataArray  ('Value'));
							$oblarrValue->Push (new $arrInput ['Type'] ());
						}
					}
				}
			}
			
			return $oblarrInputs;
		}
		
		//------------------------------------------------------------------------//
		// Selects
		//------------------------------------------------------------------------//
		/**
		 * Selects()
		 *
		 * Returns the Select Options as an ObLib Array
		 *
		 * Returns the Select Options as an ObLib Array
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function Selects ()
		{
			$arrInputs = unserialize ($this->Pull ('SQLSelect')->getValue ());
			
			$oblarrSelects = new dataArray ('Selects');
			
			if (is_array ($arrInputs))
			{
				foreach ($arrInputs as $strName => $strValue)
				{
					$oblarrSelect		= $oblarrSelects->Push	(new dataArray	('Select'));
					$oblstrName			= $oblarrSelect->Push	(new dataString	('Name', $strName));
					$oblstrValue		= $oblarrSelect->Push	(new dataString	('Value', $strValue));
				}
			}
			
			return $oblarrSelects;
		}
	}
	
?>

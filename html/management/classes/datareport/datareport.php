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
		
		public function Execute ($arrSelects, $arrFields, $intLimit)
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
					
					$strSelect .= $arrSelect [$strField]['Value'] . " AS \"" . str_replace ("\"", "\\\"", $strField) . "\"";
					
					++$i;
				}
			}
			
			// This starts the SQL Statement
			
			$selResult = new StatementSelect (
				$this->Pull ('SQLTable')->getValue (), 
				$strSelect, 
				$this->Pull ('SQLWhere')->getValue (), 
				null,
				(is_numeric ($intLimit) ? $intLimit : null),
				$this->Pull ('SQLGroupBy')->getValue ()
			);
			
			// From here, we may need to process values. For example, dates
			// come into the system as an Array [day, month, year]. We need
			// to change them to a string of YYYY-MM-DD
			$arrValues = $this->ConvertInput($arrFields);
			
			// Execute the Result
			try
			{
				if ($selResult->Execute($arrValues) === false)
				{
					throw new Exception($selResult->Error()."\n\n\n".$selResult->_strQuery);
				}
			}
			catch (Exception $oException)
			{
				throw new Exception(print_r($selResult->_arrPlaceholders, true));
				throw new Exception($selResult->_strQuery);
			}
			
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
			$arrInputs = unserialize($this->Pull('SQLFields')->getValue());
			//Debug(unserialize($this->Pull('SQLFields')->getValue()));
			//die;
			
			$oblarrInputs = new dataArray('Inputs');
			
			if (is_array($arrInputs))
			{
				foreach($arrInputs as $strName=>$arrInput)
				{
					$oblarrField		= $oblarrInputs->Push(new dataArray('Input'));
					$oblstrName			= $oblarrField->Push(new dataString('Name', $strName));
					
					$oblstrDocEntity	= $oblarrField->Push(new dataString('Documentation-Entity',	$arrInput['Documentation-Entity']));
					$oblstrDocField		= $oblarrField->Push(new dataString('Documentation-Field',	$arrInput['Documentation-Field']));
					
					$oblstrType			= $oblarrField->Push(new dataString('Type',					$arrInput['Type']));
					
					if (class_exists($arrInput['Type']))
					{
						// Is it a Statement?
						if (array_key_exists('DBSelect', $arrInput))
						{
							$oblarrValue		= $oblarrField->Push(new dataArray('Options'));
							
							// We need to fetch values from the DB
							$selStatement = new StatementSelect($arrInput['DBSelect']['Table'], $arrInput['DBSelect']['Columns'], $arrInput['DBSelect']['Where'], $arrInput['DBSelect']['OrderBy'], $arrInput['DBSelect']['Limit'], $arrInput['DBSelect']['GroupBy']);
							//Debug($selStatement);
							//die;
							$selStatement->Execute();
							while ($arrStatement = $selStatement->Fetch())
							{
								//Debug($arrStatement);
								$oblOption =& new $arrInput['DBSelect']['ValueType']('Option', $arrStatement['Value']);
								$oblOption->setAttribute('Label', $arrStatement['Label']);
								$oblarrValue->Push($oblOption);
							}
							//die;
							
							// Does this have an ALL/IGNORE option?
							if (is_array($arrInput['DBSelect']['IgnoreField']))
							{
								$oblOption =& new $arrInput['DBSelect']['ValueType']('Option', $arrInput['DBSelect']['IgnoreField']['Value']);
								$oblOption->setAttribute('Label', $arrInput['DBSelect']['IgnoreField']['Label']);
								$oblarrValue->Push($oblOption);
							}
						}
						elseif (array_key_exists('DBQuery', $arrInput))
						{
							// The input is based on the results of the SQL Query defined in $arrInput['DBQuery']
							$oblarrValue		= $oblarrField->Push(new dataArray('Options'));
							
							// Does this have an ALL/IGNORE option?
							if (is_array($arrInput['DBQuery']['IgnoreField']))
							{
								$oblOption =& new $arrInput['DBQuery']['ValueType']('Option', $arrInput['DBQuery']['IgnoreField']['Value']);
								$oblOption->setAttribute('Label', $arrInput['DBQuery']['IgnoreField']['Label']);
								$oblarrValue->Push($oblOption);
							}

							// We need to fetch values from the DB
							$objQuery = new Query();

							$objRecordSet = $objQuery->Execute($arrInput['DBQuery']['Query']);
							
							if ($objRecordSet === false)
							{
								throw new Exception("Failed to retrieve values for DataReport constraint field: $strName. Error: ". $objQuery->Error());
							}
							
							while ($arrRecord = $objRecordSet->fetch_assoc())
							{
								$oblOption =& new $arrInput['DBQuery']['ValueType']('Option', $arrRecord['Value']);
								$oblOption->setAttribute('Label', $arrRecord['Label']);
								$oblarrValue->Push($oblOption);
							}
						}
						elseif (is_subclass_of($arrInput['Type'], "dataPrimitive") || is_subclass_of($arrInput ['Type'], "dataObject"))
						{
							$oblarrField->Push(new $arrInput['Type']('Value'));
						}
						else
						{
							$oblarrValue		= $oblarrField->Push(new dataArray('Value'));
							$oblarrValue->Push(new $arrInput['Type']());
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
				foreach ($arrInputs as $strName => $arrProperties)
				{
					$oblarrSelect		= $oblarrSelects->Push	(new dataArray	('Select'));
					$oblstrName			= $oblarrSelect->Push	(new dataString	('Name', $strName));
					$oblstrValue		= $oblarrSelect->Push	(new dataString	('Value', $arrProperties['Value']));
				}
			}
			
			return $oblarrSelects;
		}
		
		
		//------------------------------------------------------------------------//
		// ConvertInputToWhere
		//------------------------------------------------------------------------//
		/**
		 * ConvertInputToWhere()
		 *
		 * Turn the Serialised Input Array into Statement Where Array
		 *
		 * Turn the Serialised Input Array into Statement Where Array
		 *
		 * @return	array
		 *
		 * @method
		 */
		public function ConvertInput($arrRaw)
		{
			$arrFields	= unserialize($this->Pull('SQLFields')->getValue());
			
			if (is_array($arrRaw))
			{
				foreach ($arrFields as $strName=>$arrInput)
				{
					switch ($arrInput['Type'])
					{
						case "dataDate":
							$arrWhere[$strName] = date(
								"Y-m-d", 
								mktime (0, 0, 0, $arrRaw[$strName]['month'], $arrRaw[$strName]['day'], $arrRaw[$strName]['year'])
							);
							
							break;
							
						case "dataDatetime":
							$arrWhere[$strName] = date(
								"Y-m-d H:i:s", 
								mktime (
									$arrRaw[$strName]['hour']	, $arrRaw[$strName]['minute']	, $arrRaw[$strName]['second'],
									$arrRaw[$strName]['month']	, $arrRaw[$strName]['day']		, $arrRaw[$strName]['year']
								)
							);
							
							break;
							
						case "dataString":
							$arrWhere[$strName] = "%" . $arrRaw[$strName] . "%";
							
							break;
							
						case "dataInteger":
							$arrWhere[$strName] = (int)$arrRaw[$strName];
							
							break;
							
						default:
							$arrWhere[$strName] = $arrRaw[$strName];
							break;
					}
				}
			}
			
			return $arrWhere;
		}
	}
	
?>

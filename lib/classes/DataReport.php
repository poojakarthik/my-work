<?php
//----------------------------------------------------------------------------//
// DataReport
//----------------------------------------------------------------------------//
/**
 * DataReport
 *
 * Models a record of the DataReport table
 *
 * Models a record of the DataReport table
 *
 * @class	DataReport
 */
class DataReport extends ORM_Cached
{	
	protected 			$_strTableName			= "DataReport";
	protected static	$_strStaticTableName	= "DataReport";
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mObjects)
	{
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false)
	{
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false)
	{
		return parent::getAll($bForceReload, __CLASS__);
	}
		
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// convertInput
	//------------------------------------------------------------------------//
	public function convertInput($aRaw)
	{
		// Run through each editable field for the report, 
		// grab the value from the raw data and perform any manipulation necessary.
		$aWhere		= array();
		$aFields	= unserialize($this->SQLFields);
		
		if (is_array($aRaw))
		{
			foreach ($aFields as $sName=>$aInput)
			{
				switch ($aInput['Type'])
				{
					case "dataDate":
						// Cast the date data to an array if needed
						if (is_array($aRaw[$sName]))
						{
							$aDate	= $aRaw[$sName];
						}
						else
						{
							$aDate	= (array)$aRaw[$sName];
						}
						
						$aWhere[$sName] = date(
							"Y-m-d", 
							mktime (0, 0, 0, $aDate['month'], $aDate['day'], $aDate['year'])
						);
						break;
						
					case "dataDatetime":
						// Cast the date data to an array if needed
						if (is_array($aRaw[$sName]))
						{
							$aDate	= $aRaw[$sName];
						}
						else
						{
							$aDate	= (array)$aRaw[$sName];
						}
						
						$aWhere[$sName] = date(
							"Y-m-d H:i:s", 
							mktime (
								$aRaw[$sName]['hour']	, $aDate['minute']	, $aDate['second'],
								$aRaw[$sName]['month']	, $aDate['day']		, $aDate['year']
							)
						);
						break;
						
					case "dataString":
						$aWhere[$sName] = "%" . $aRaw[$sName] . "%";
						break;
						
					case "dataInteger":
						$aWhere[$sName] = (int)$aRaw[$sName];
						break;

					default:
						$aWhere[$sName] = $aRaw[$sName];
						break;
				}
			}
		}
		
		return $aWhere;
	}
	
	//------------------------------------------------------------------------//
	// execute
	//------------------------------------------------------------------------//
	public function execute($aSelects, $aFields, $iLimit)
	{
		// This deals with turning the SQLSelect Serialized Array 
		// into a String: Field1, Field2, Field3 [, ... ]
		$aSelect 	= unserialize($this->SQLSelect);
		$i 			= 0;
		
		foreach ($aSelects as $sField)
		{
			if ($aSelect[$sField])
			{
				if ($i != 0)
				{
					$sSelect .= ", ";
				}
				
				$sSelect .= $aSelect[$sField]['Value']." AS \"".str_replace("\"", "\\\"", $sField)."\"";
				++$i;
			}
		}
		
		// This starts the SQL Statement
		$oResult	= 	new StatementSelect (
							$this->SQLTable, 
							$sSelect, 
							$this->SQLWhere, 
							null,
							(is_numeric ($iLimit) ? $iLimit : null),
							$this->SQLGroupBy
						);
		
		// From here, we may need to process values. For example, dates
		// come into the system as an Array [day, month, year]. We need
		// to change them to a string of YYYY-MM-DD
		$aValues = $this->ConvertInput($aFields);
		
		// Execute the Result
		try
		{
			if ($oResult->Execute($aValues) === false)
			{
				throw new Exception($oResult->Error()."\n\n\n".$oResult->_strQuery);
			}
		}
		catch (Exception $oException)
		{
			throw new Exception(print_r($oResult->_arrPlaceholders, true));
			throw new Exception($oResult->_strQuery);
		}
		
		// Return the Result
		return $oResult;
	}
	
	public function getEmployees()
	{
		return Data_Report_Employee::getForDataReportId($this->id);
	}
	
	public function getOperationProfiles()
	{
		return Data_Report_Operation_Profile::getForDataReportId($this->id);
	}
	
	public function UserHasPerm($iEmployeeId)
	{
		$oEmployee	= Employee::getForId($iEmployeeId);
		
		if ($oEmployee->isGod() ||
			$this->employeeIsPermitted($iEmployeeId) || 
			$this->containsPermittedProfile($oEmployee->getOperationProfiles()))
		{
			return true;
		}
		
		return false;
	}
	
	public function employeeIsPermitted($iEmployeeId)
	{
		$aEmployees	= $this->getEmployees();
		return (array_key_exists($iEmployeeId, $aEmployees));
	}
	
	public function containsPermittedProfile($aOperationProfiles)
	{
		$aSelfProfiles		= $this->getOperationProfiles();
		$aMatchingProfiles	= array_intersect_key($aOperationProfiles, $aSelfProfiles);
		return (count($aMatchingProfiles) > 0);
	}
	
	public static function getForEmployeeId($iEmployeeId)
	{
		$oEmployee					= Employee::getForId($iEmployeeId);
		$oEmployeeOperationProfiles	= $oEmployee->getOperationProfiles();
		$aDataReports				= self::getAll();
		$aPermitted					= array();
		
		foreach ($aDataReports as $iId => $oDataReport)
		{
			if ($oEmployee->isGod() ||
				$oDataReport->employeeIsPermitted($iEmployeeId) || 
				$oDataReport->containsPermittedProfile($oEmployeeOperationProfiles))
			{
				$aPermitted[$iId]	= $oDataReport;
			}
		}
		
		return $aPermitted;
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "Name");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}
?>
<?php
/**
 * Address_Locality_CSV
 *
 * Models a Locality CSV Database Record
 *
 * @class	Address_Locality_CSV
 */
class Address_Locality_CSV
{
	const	LOAD_MODE_TO_REVISION		= 1;
	const	LOAD_MODE_FOR_REVISION		= 2;
	const	LOAD_MODE_AFTER_REVISION	= 3;
	
	const	DATABASE_FLEX_PATH			= 'files/rollout/import/country/locality/';
	
	protected static	$_arrDatabaseCache;
	
	protected static	$_arrImportColumns	=	array
												(
													'name'					=> 0,
													'description'			=> 1,
													'code'					=> 2,
													'Flex_Rollout_Version'	=> 3
												);
	
	protected	$_strName;
	protected	$_strPostcode;
	
	protected	$_objAddressStateCSV;
	protected	$_objState;
	
	protected	$_objAddressLocality;
	
	/**
	 * __construct()
	 *
	 * Constructor
	 * 
	 * @constructor
	 */
	protected function __construct($strName, $strPostcode, $mixState)
	{
		$this->_strName		= $strName;
		$this->_strPostcode	= $strPostcode;
		
		
		if ($mixState instanceof Address_State_CSV)
		{
			// CSV State object
			$this->_objAddressStateCSV	= $mixState;
		}
		elseif ($mixState instanceof State)
		{
			// Flex State object
			$this->_objAddressStateCSV	= $mixState;
		}
		else
		{
			// Assume Flex State Id -- load from the DB
			$objState	= State::getForId((int)$mixState);
		}
	}
	
	/**
	 * load()
	 *
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the object with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public static function load($objAddressCountryCSV, $intRolloutVersion=null, $intLoadMode=null)
	{
		// Cache the CSV Database in memory if possible
		if (!self::$_arrDatabaseCache)
		{
			$strDatabasePath	= Flex::getBase().self::DATABASE_FLEX_PATH.strtolower(trim($objAddressCountryCSV->strCode)).'.csv';
			$strErrorTail		= "for Country '{$objAddressCountryCSV->strName}' at path '{$strDatabasePath}'";
			
			// Load the CSV Database
			if (!$resInputFile = @fopen($strDatabasePath, 'r'))
			{
				throw new Exception("Unable to load Localities {$strErrorTail}: \n\n".print_r(error_get_last(), true));
			}
			
			// Parse Header
			if (!$arrHeader = fgetcsv($resInputFile))
			{
				throw new Exception("Unable to parse File Header {$strErrorTail}");
				
				foreach (self::$_arrImportColumns as $strAlias=>$intColumn)
				{
					if (!array_key_exists($intColumn, $arrHeader))
					{
						throw new Exception("Header column at Index {$intColumn} does not exist {$strErrorTail}");
					}
					elseif ($arrHeader[$intColumn] !== $strAlias)
					{
						throw new Exception("Header column at Index {$intColumn} expected '{$strAlias}'; found '{$arrHeader[$intColumn]}' {$strErrorTail}");
					}
				}
			}
			
			// Parse Data
			while ($arrData = fgetcsv($resInputFile))
			{
				$objAddressLocalityCSV	= new Address_Locality_CSV();
			}
		}
	}
	
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>
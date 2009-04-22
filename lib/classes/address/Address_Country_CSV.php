<?php
/**
 * Address_Country_CSV
 *
 * Models a Locality CSV Database Record
 *
 * @class	Address_Country_CSV
 */
class Address_Country_CSV
{
	const	GET_MODE_BEFORE_REVISION	= 1;
	const	GET_MODE_TO_REVISION		= 2;
	const	GET_MODE_FOR_REVISION		= 3;
	const	GET_MODE_AFTER_REVISION		= 4;
	
	const	DATABASE_FLEX_PATH			= 'lib/rollout/support/country/';
	
	protected static	$_arrDatabaseCache;
	
	protected static	$_arrImportColumns	=	array
												(
													'name'					=> 0,
													'code_2_char'			=> 1,
													'code_3_char'			=> 2,
													'has_postcode'			=> 3,
													'Flex_Rollout_Version'	=> 4
												);
	
	protected	$_strName;
	protected	$_strCode2Char;
	protected	$_strCode3Char;
	protected	$_intHasPostcode;
	protected	$_intRolloutVersion;
	
	/**
	 * __construct()
	 *
	 * Constructor
	 * 
	 * @constructor
	 */
	protected function __construct($strName, $strCode2Char, $strCode3Char, $intHasPostcode, $intRolloutVersion)
	{
		$this->_strName				= $strName;
		$this->_strCode2Char		= $strCode2Char;
		$this->_strCode3Char		= $strCode3Char;
		$this->_intHasPostcode		= (int)$intHasPostcode;
		$this->_intRolloutVersion	= (int)$intRolloutVersion;
	}
	
	/**
	 * getAll()
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
	public static function getAll($intGetMode=null, $intRolloutVersion=null)
	{
		self::_load();
		
		$arrCountries	= array();
		foreach (self::$_arrDatabaseCache as $intCacheRollout=>$arrCountries)
		{
			if ((!$intGetMode || !$intRolloutVersion) || 
				($intGetMode == self::GET_MODE_BEFORE_REVISION && $intCacheRollout < $intRolloutVersion) || 
				($intGetMode == self::GET_MODE_TO_REVISION && $intCacheRollout <= $intRolloutVersion) || 
				($intGetMode == self::GET_MODE_FOR_REVISION && $intCacheRollout == $intRolloutVersion) || 
				($intGetMode == self::GET_MODE_AFTER_REVISION && $intCacheRollout > $intRolloutVersion))
			{
				foreach ($arrCountries as $arrCountry)
				{
					$arrCountries[]	= $arrCountry;
				}
			}
		}
		return $arrCountries;
	}
	
	/**
	 * load()
	 *
	 * Loads the Country CSV Database
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	protected static function _load()
	{
		// Cache the CSV Database in memory if possible
		if (!self::$_arrDatabaseCache)
		{
			$strDatabasePath	= Flex::getBase().self::DATABASE_FLEX_PATH.'country.csv';
			$strErrorTail		= "for Country '{$objAddressCountryCSV->strName}' at path '{$strDatabasePath}'";
			
			// Load the CSV Database
			if (!$resInputFile = @fopen($strDatabasePath, 'r'))
			{
				throw new Exception("Unable to load Countries {$strErrorTail}: \n\n".print_r(error_get_last(), true));
			}
			
			// Parse Header
			if (!$arrHeader = fgetcsv($resInputFile))
			{
				throw new Exception("Unable to parse File Header {$strErrorTail}");
			}
			foreach (self::$_arrImportColumns as $strAlias=>$intColumn)
			{
				echo "Checking Header Column '{$strAlias}' @ index {$intColumn}...\n";
				if (!array_key_exists($intColumn, $arrHeader))
				{
					throw new Exception("Header column at Index {$intColumn} does not exist {$strErrorTail}");
				}
				elseif ($arrHeader[$intColumn] !== $strAlias)
				{
					throw new Exception("Header column at Index {$intColumn} expected '{$strAlias}'; found '{$arrHeader[$intColumn]}' {$strErrorTail}");
				}
				else
				{
					echo "\t[+] Header Match ('{$strAlias}' === '{$arrHeader[$intColumn]}')\n";
				}
			}
			
			// Parse Data
			while ($arrData = fgetcsv($resInputFile))
			{
				$intRolloutVersion		= (int)$arrData[self::$_arrImportColumns['Flex_Rollout_Version']];
				$objAddressLocalityCSV	= new Address_Country_CSV(
																	$arrData[self::$_arrImportColumns['name']],
																	$arrData[self::$_arrImportColumns['code_2_char']],
																	$arrData[self::$_arrImportColumns['code_3_char']],
																	$arrData[self::$_arrImportColumns['has_postcode']],
																	$intRolloutVersion
																);
				
				self::$_arrDatabaseCache[$intRolloutVersion]	= (!self::$_arrDatabaseCache[$intRolloutVersion]) ? array() : self::$_arrDatabaseCache[$intRolloutVersion];
				self::$_arrDatabaseCache[$intRolloutVersion][]	= $objAddressLocalityCSV;
			}
		}
	}
}
?>
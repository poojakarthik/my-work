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
	protected function __construct($strName, $strPostcode, Address_State_CSV $objState)
	{
		$this->_strName		= $strName;
		$this->_strPostcode	= $strPostcode;
		
		// CSV State object
		$this->_objAddressStateCSV	= $objState;
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
}
?>
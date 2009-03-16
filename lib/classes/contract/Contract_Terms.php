<?php
/**
 * Contract_Terms
 *
 * Models a record of the contract_terms table
 *
 * @class	Contract_Terms
 */
class Contract_Terms extends ORM
{
	protected			$_strTableName				= "contract_terms";
	protected static	$_strStaticTableName		= "contract_terms";
	
	/**
	 * __construct()
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
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * getContractTerms()
	 *
	 * Retrieves the current Contract Terms
	 * 
	 * @param	[boolean	$bolAsArray			]	TRUE	: Return as Associative Array
	 * 												FALSE	: Return as Contract_Terms object (default)
	 * @param	[boolean	$bolForceRecache	]	TRUE	: Reload the Contract Terms details from the Database
	 * 												FALSE	: Use cache if available
	 * 
	 * @return	mixed
	 * 
	 * @method
	 */
	public static function getCurrent($bolAsArray=false, $bolForceRecache=false)
	{
		static	$objInstance	= null;
		if (!$objInstance || $bolForceRecache)
		{
			$selCurrent	= self::_preparedStatement('selCurrent');
			$resCurrent	= $selCurrent->Execute();
			if ($resCurrent === false)
			{
				throw new Exception($selCurrent->Error());
			}
			elseif ($arrCurrent = $selCurrent->Fetch())
			{
				$objInstance	= new Contract_Terms($arrCurrent);
			}
			else
			{
				throw new Exception("There are no Contract Terms defined in Flex");
			}
		}
		
		// Return Instance
		return ($bolAsArray) ? $objInstance->toArray() : $objInstance;
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
				case 'selCurrent':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id DESC", 1);
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
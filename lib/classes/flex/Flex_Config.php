<?php
/**
 * Flex_Config
 *
 * Handles general Flex configuration, and interacts with the flex_config Table
 *
 * @class	Service
 */
class Flex_Config extends ORM
{
	protected	$_strTableName	= "flex_config";
	
	static protected	$_objInstance;
	
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
	private function __construct($arrProperties)
	{
		// Parent constructor
		parent::__construct($arrProperties, false);
	}
	
	/**
	 * instance()
	 *
	 * Returns an instance of Flex_Config
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public static function instance()
	{
		// If there isn't an instance yet, create one
		if (!isset(self::$_objInstance) || !(self::$_objInstance instanceof Flex_Config))
		{
			$selCurrentConfig	= self::_preparedStatement('selCurrentConfig');
			$mixResult			= $selCurrentConfig->Execute();
			if ($mixResult === false)
			{
				throw new Exception($selCurrentConfig->Error());
			}
			elseif (!$mixResult)
			{
				throw new Exception("No Flex Config has been defined");
			}
			
			// Set instance
			self::$_objInstance	= new Flex_Config($selCurrentConfig->Fetch());
		}
		
		// Return the Instance
		return self::$_objInstance;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Charge", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selCurrentConfig':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"flex_config", "*", "1", "id DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Charge");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Charge");
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
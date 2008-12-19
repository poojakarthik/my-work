<?php
/**
 * Resource_Type
 *
 * Models a record of the resource_type table
 *
 * @class	Service
 */
class Resource_Type extends ORM
{
	protected	$_strTableName	= "resource_type";
	
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
	 * getFor()
	 *
	 * constructor
	 *
	 * @param	string	$strWhere					WHERE clause (can also include GROUP BY, ORDER BY and LIMIT clauses)
	 * @param	boolean	$bolAsArray		[optional]	If set to TRUE, will return Associative Arrays instead of objects
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public static function getFor($strWhere, $bolAsArray=false)
	{
		static	$qryQuery;
		$qryQuery	= ($qryQuery) ? $qryQuery : new Query();
		
		// Perform Query
		$strSQL		= "SELECT * FROM resource_type WHERE {$strWhere}";
		$resResult	= $qryQuery->Execute($strSQL);
		if ($resResult === false)
		{
			throw new Exception($qryQuery->Error());
		}
		else
		{
			// Return records as an array of either associative arrays, or Resource_Type objects
			$arrRecords	= array();
			while ($arrRecord = $resResult->fetch_assoc())
			{
				$arrRecords[]	= ($bolAsArray) ? $arrRecord : new Resource_Type($arrRecord);
			}
			return $arrRecords;
		}
	}
	
	/**
	 * validateFileName()
	 *
	 * constructor
	 *
	 * @param	integer	$intResourceType			WHERE clause (can also include GROUP BY, ORDER BY and LIMIT clauses)
	 * @param	boolean	$bolAsArray		[optional]	If set to TRUE, will return Associative Arrays instead of objects
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function validateFileName($intResourceTypeId, $strFileName)
	{
		$intResourceTypeId	= (int)$intResourceTypeId;
		$arrResourceTypes	= Resource_Type::getFor("id = {$intResourceTypeId}", true);
		
		if ($arrResourceTypes[0])
		{
			return ($arrResourceTypes[0]['file_name_regex']) ? preg_match($arrResourceTypes[0]['file_name_regex'], $strFileName) : true;
		}
		else
		{
			return false;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"resource_type", "*", "id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("resource_type");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("resource_type");
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
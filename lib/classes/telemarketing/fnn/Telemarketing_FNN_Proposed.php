<?php
/**
 * Telemarketing_FNN_Proposed
 *
 * Models a record of the telemarketing_fnn_proposed table
 *
 * @class	Telemarketing_FNN_Proposed
 */
class Telemarketing_FNN_Proposed extends ORM
{
	protected	$_strTableName	= "telemarketing_fnn_proposed";
	const		TABLE_NAME		= "telemarketing_fnn_proposed";
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
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
		$strSQL		= "SELECT * FROM ".self::TABLE_NAME." WHERE {$strWhere}";
		$resResult	= $qryQuery->Execute($strSQL);
		if ($resResult === false)
		{
			throw new Exception($qryQuery->Error());
		}
		else
		{
			// Return records as an array of either associative arrays, or ORM objects
			$arrRecords	= array();
			while ($arrRecord = $resResult->fetch_assoc())
			{
				$arrRecords[]	= ($bolAsArray) ? $arrRecord : new Resource_Type($arrRecord);
			}
			return $arrRecords;
		}
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"telemarketing_fnn_proposed", "*", "id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(	"telemarketing_fnn_proposed");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(	"telemarketing_fnn_proposed");
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
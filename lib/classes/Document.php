<?php
/**
 * Document
 *
 * Models a record of the document table
 *
 * @class	Document
 */
class Document extends ORM
{
	protected			$_strTableName			= "document";
	protected static	$_strStaticTableName	= "document";
	
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
	 * getContent()
	 *
	 * Retrieves the Content for this Document
	 * 
	 * @param	[mixed			$mixRevision]						Revision of the Content to retrieve
	 * 																TRUE|0		: Latest Revision (default)
	 * 																FALSE		: Earliest Revision
	 * 																-ve integer	: X Revisions ago (eg. -3 = 3 revisions ago)
	 * 																+ve integer	: Revision number X (eg. 2 = second revision)
	 * 
	 * @return	Document_Content									The requested Statement
	 *
	 * @method
	 */
	public function getContent($mixRevision=true)
	{
		static	$qryQuery;
		static	$arrRevisionCache	= array();
		$qryQuery	= (isset($qryQuery) && $qryQuery instanceof Query) ? $qryQuery : new Query();
		
		$mixRevision	= (is_bool($mixRevision)) ? $mixRevision : (int)$mixRevision;
		if ($mixRevision === false)
		{
			// FALSE -- First Revision
			$strLimit	= "1";
			$strOrderBy	= "id ASC";
		}
		elseif ($mixRevision > 0)
		{
			// Positive Integer -- Revision number $mixRevision
			$strLimit	= "1 OFFSET ".($mixRevision-1);
			$strOrderBy	= "id ASC";
		}
		elseif ($mixRevision < 0)
		{
			// Negative Integer -- $mixRevision Revisions ago
			$strLimit	= "1 OFFSET ".(abs($mixRevision));
			$strOrderBy	= "id DESC";
		}
		else//if ($mixRevision === true)
		{
			// Default -- to Current Revision
			$strLimit	= "1";
			$strOrderBy	= "id DESC";
		}
		
		// Retrieve and return the content
		$strQuery		= "SELECT * FROM document_content WHERE document_id = {$this->id} ORDER BY {$strOrderBy} LIMIT {$strLimit}";
		$resRevision	= $qryQuery->Execute($strQuery);
		if ($resRevision === false)
		{
			throw new Exception($qryQuery->Error());
		}
		return ($resRevision->num_rows) ? $resRevision->fetch_assoc() : null;
	}
	
	/**
	 * getPath()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	[mixed			$mixRevision]						Revision of the Content to retrieve
	 * 																TRUE	: Latest Revision (default)
	 * 																FALSE	: Earliest Revision
	 * 																integer	: X Revisions ago (0 = current)
	 * 
	 * @return	Document_Content									The requested Statement
	 *
	 * @method
	 */
	public function getPath($mixRevision=true)
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery) && $qryQuery instanceof Query) ? $qryQuery : new Query();
		
		if ($mixRevision === true)
		{
			$strLimit	= "1";
			$strOrderBy	= "id DESC";
		}
		elseif ($mixRevision === false)
		{
			$strLimit	= "1";
			$strOrderBy	= "id ASC";
		}
		else
		{
			$strLimit	= "1 OFFSET ".((int)$mixRevision);
			$strOrderBy	= "id DESC";
		}
		
		$strQuery		= "SELECT * FROM document_content WHERE document_id = {$this->id} ORDER BY {$strOrderBy} LIMIT {$strLimit}";
		$resRevision	= $qryQuery->Execute($strQuery);
		if ($resRevision === false)
		{
			throw new Exception($qryQuery->Error());
		}
	}
	
	/**
	 * getForPath()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	[mixed			$mixRevision]						Revision of the Content to retrieve
	 * 																TRUE	: Latest Revision (default)
	 * 																FALSE	: Earliest Revision
	 * 																integer	: X Revisions ago (0 = current)
	 * 
	 * @return	Document_Content									The requested Statement
	 *
	 * @method
	 */
	public static function getForPath($strPath)
	{
		
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <id>", NULL, 1);
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
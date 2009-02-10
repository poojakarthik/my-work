<?php
/**
 * Document_Content
 *
 * Models a record of the document_content table
 *
 * @class	Document_Content
 */
class Document_Content extends ORM
{
	protected			$_strTableName			= "document_content";
	protected static	$_strStaticTableName	= "document_content";
	
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
	 * getFriendlyName()
	 *
	 * Retrieves the 'friendly' name for a Document.  If none is set, it will return the regular name.
	 * 
	 * @return	mixed									Friendly Name
	 *
	 * @method
	 */
	public function getFriendlyName()
	{
		$mixFriendlyName	= ($this->constant_group) ? GetConstantDescription($this->name, $this->constant_group) : $this->name;
		return ($mixFriendlyName) ? $mixFriendlyName : new $this->name;
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
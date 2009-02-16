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
	
	protected			$_bolCanSave			= true;
	
	public				$bolHasContent;
	
	/**
	 * __construct()
	 *
	 * constructor
	 *
	 * @param	array	$arrProperties 				[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById				[optional]	Automatically load the object with the passed Id
	 * @param	boolean	$bolDetailsOnly				[optional]	TRUE	: Does not load Binary Data and is unsaveable
	 * 															FALSE	: Loads Binary Data (Default)
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=false, $bolDetailsOnly=false)
	{
		$this->_bolCanSave	= !$bolDetailsOnly;
		
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
		
		$this->bolHasContent	= ($this->content) ? true : false;
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
		return ($mixFriendlyName) ? $mixFriendlyName : $this->name;
	}
	
	/**
	 * getFileName()
	 *
	 * Retrieves the file name for this Document Content
	 * 
	 * @return	mixed									Friendly Name
	 *
	 * @method
	 */
	public function getFileName()
	{
		$strFriendlyName	= $this->getFriendlyName();
		
		if ($this->file_type_id)
		{
			$objFileType		= new File_Type(array('id'=>$this->file_type_id), true);
			$strFriendlyName	.= ".{$objFileType->extension}";
		}
		return $strFriendlyName;
	}
	
	/**
	 * save()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	public function save()
	{
		if ($this->_bolCanSave)
		{
			parent::save();
		}
		else
		{
			// You cannot save a Details-only Document_Content
			throw new Exception("You cannot save a Document_Content object which has been loaded in Details-Only Mode");
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
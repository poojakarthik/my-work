<?php
/**
 * File_Type
 *
 * Models a record of the file_type table
 *
 * @class	File_Type
 */
class File_Type extends ORM
{
	protected			$_strTableName				= "file_type";
	protected static	$_strStaticTableName		= "file_type";
	
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
	 * getForExtensionAndMimeType()
	 *
	 * Returns a File_Type based on the file extension and mime type
	 *
	 * @param	string		$strExtension 			File Extension
	 * @param	string		$strMimeType 			MIME Content Type
	 * @param	[boolean	$bolAsArray			]	TRUE	: Return an associative array
	 * 												FALSE	: Return a File_Type object (default)
	 * 
	 * @return	mixed								Associative Array or File_Type object
	 * 
	 * @method
	 */
	public static function getForExtensionAndMimeType($strExtension, $strMimeType, $bolAsArray=false)
	{
		$selByExtensionMimeType	= self::_preparedStatement('selByExtensionMimeType');
		$mixResult				= $selByExtensionMimeType->Execute(array('extension'=>trim($strExtension, '.'), 'mime_content_type'=>$strMimeType));
		
		if ($mixResult === false)
		{
			throw new Exception($selByExtensionMimeType->Error());
		}
		elseif (!$mixResult)
		{
			return null;
		}
		else
		{
			$arrFileType	= $selByExtensionMimeType->Fetch();
			
			return ($bolAsArray) ? $arrFileType : new File_Type($arrFileType);
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
				case 'selByExtensionMimeType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("file_type JOIN mime_type ON file_type.mime_type_id = mime_type.id", "file_type.*", "file_type.extension = <extension> AND mime_type.mime_content_type = <mime_content_type>", NULL, 1);
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
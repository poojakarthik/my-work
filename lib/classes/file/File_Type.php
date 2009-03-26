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
	
	protected static 	$_arrAllowableResolutions	= array(16, 64);
	
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
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE, $bolDetailsOnly=false)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
		
		
	}
	
	/**
	 * getPreferredMIMEType()
	 *
	 * Returns a the preferred MIME Type Object for this File_Type
	 * 
	 * @param	[boolean	$bolForceRefresh	]	TRUE	: Refresh cache
	 * 												FALSE	: Use cached value if available (default)
	 * 
	 * @return	Mime_Type
	 * 
	 * @method
	 */
	public function getPreferredMIMEType($bolForceRefresh=false)
	{
		static	$objCache;
		
		if (!isset($objCache) || $bolForceRefresh)
		{
			$selPreferredMimeType	= $this->_preparedStatement('selPreferredMimeType');
			$resPreferredMimeType	= $selPreferredMimeType->Execute($this->toArray());
			if ($resPreferredMimeType === false)
			{
				throw new Exception($selPreferredMimeType->Error());
			}
			elseif ($arrMimeType = $selPreferredMimeType->Fetch())
			{
				$objCache	= new Mime_Type($arrMimeType);
			}
			else
			{
				return null;
			}
		}
		
		return $objCache;
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
	 * hasIcon()
	 *
	 * Returns whether a given File Type has an icon
	 *
	 * @param	integer		$intFileTypeId					File Type to check
	 * @param	[integer	$intResolution				]	Only check RxR resolution (Default: null)
	 * 
	 * @return	boolean
	 * 
	 * @method
	 */
	public static function hasIcon($intFileTypeId, $intResolution=null)
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		$intFileTypeId	= (int)$intFileTypeId;
		
		if ($intResolution > 0)
		{
			if (!in_array($intResolution, self::$_arrAllowableResolutions))
			{
				throw new Exception("Unsupported File_Type Icon Resolution: {$intResolution}x{$intResolution}");
			}
			else
			{
				$strColumn	= "(icon_{$intResolution}x{$intResolution} IS NOT NULL)";
			}
		}
		else
		{
			$arrWhere	= array();
			foreach (self::$_arrAllowableResolutions as $intSupportedResolution)
			{
				$arrWhere[]	 = "(icon_{$intSupportedResolution}x{$intSupportedResolution} IS NOT NULL)";
			}
			$strColumn	= "(".implode(' OR ', $arrWhere).")";
		}
		
		$strColumn	.= " AS has_icon";
		$resHasIcon	= $qryQuery->Execute("SELECT {$strColumn} FROM file_type WHERE id = {$intFileTypeId} LIMIT 1");
		if ($resHasIcon === false)
		{
			throw new Exception($qryQuery->Error());
		}
		elseif ($arrHasIcon = $resHasIcon->fetch_assoc())
		{
			return (bool)$arrHasIcon['has_icon'];
		}
		else
		{
			throw new Exception("Unknown File Type with Id '{$intFileTypeId}'");
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("file_type JOIN file_type_mime_type ftmt ON file_type.id = ftmt.file_type_id JOIN mime_type ON ftmt.mime_type_id = mime_type.id", "file_type.*", "file_type.extension = <extension> AND mime_type.mime_content_type = <mime_content_type>", NULL, 1);
					break;
				case 'selPreferredMimeType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("file_type_mime_type ftmt JOIN mime_type mt ON ftmt.mime_type_id = mt.id", "mt.*", "ftmt.file_type = <id>", "ftmt.id DESC", 1);
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
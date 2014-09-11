<?php
/**
 * File_Type
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	ORM_Cached_Example
 */
class File_Type extends ORM_Cached
{
	protected 			$_strTableName			= "file_type";
	protected static	$_strStaticTableName	= "file_type";
	
	protected static 	$_aAllowableResolutions	= array(16, 64);
	
	protected			$_oPreferredMIMEType;
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	
	/**
	 * getPreferredMIMEType()
	 *
	 * Returns a the preferred MIME Type Object for this File_Type
	 * 
	 * @param	[boolean	$bForceRefresh	]	TRUE	: Refresh cache
	 * 												FALSE	: Use cached value if available (default)
	 * 
	 * @return	Mime_Type
	 * 
	 * @method
	 */
	public function getPreferredMIMEType($bForceRefresh=false) {
		if (!isset($this->_oPreferredMIMEType) || $bForceRefresh) {
			$oPreferredMimeType	=  self::_preparedStatement('selPreferredMimeType');
			$mResult	= $oPreferredMimeType->Execute($this->toArray());
			if ($mResult === false) {
				throw new Exception_Database($oPreferredMimeType->Error());
			} elseif ($aMimeType = $oPreferredMimeType->Fetch()) {
				$this->_oPreferredMIMEType	= new Mime_Type($aMimeType);
			} else {
				return null;
			}
		}
		
		return $this->_oPreferredMIMEType;
	}
	
	/**
	 * getForExtensionAndMimeType()
	 *
	 * Returns a File_Type based on the file extension and mime type
	 *
	 * @param	string		$sExtension 			File Extension
	 * @param	string		$sMimeType 			MIME Content Type
	 * @param	[boolean	$bAsArray			]	TRUE	: Return an associative array
	 * 												FALSE	: Return a File_Type object (default)
	 * 
	 * @return	mixed								Associative Array or File_Type object
	 * 
	 * @method
	 */
	public static function getForExtensionAndMimeType($sExtension, $sMimeType, $bAsArray=false) {
		$oByExtensionMimeType	= self::_preparedStatement('selByExtensionMimeType');
		$mResult				= $oByExtensionMimeType->Execute(array('extension'=>trim($sExtension, '.'), 'mime_content_type'=>$sMimeType));
		
		if ($mResult === false) {
			throw new Exception_Database($oByExtensionMimeType->Error());
		} elseif (!$mResult) {
			return null;
		} else {
			$aFileType	= $oByExtensionMimeType->Fetch();
			
			return ($bAsArray) ? $aFileType : new File_Type($aFileType);
		}
	}
	
	/**
	 * hasIcon()
	 *
	 * Returns whether a given File Type has an icon
	 *
	 * @param	integer		$iFileTypeId					File Type to check
	 * @param	[integer	$iResolution				]	Only check RxR resolution (Default: null)
	 * 
	 * @return	boolean
	 * 
	 * @method
	 */
	public static function hasIcon($iFileTypeId, $iResolution=null) {
		static	$oQuery;
		$oQuery	= (isset($oQuery)) ? $oQuery : new Query();
		
		$iFileTypeId	= (int)$iFileTypeId;
		
		if ($iResolution > 0) {
			if (!in_array($iResolution, self::$_aAllowableResolutions)) {
				throw new Exception("Unsupported File_Type Icon Resolution: {$iResolution}x{$iResolution}");
			} else {
				$sColumn	= "(icon_{$iResolution}x{$iResolution} IS NOT NULL)";
			}
		} else {
			$aWhere	= array();
			foreach (self::$_aAllowableResolutions as $iSupportedResolution) {
				$aWhere[]	 = "(icon_{$iSupportedResolution}x{$iSupportedResolution} IS NOT NULL)";
			}
			$sColumn	= "(".implode(' OR ', $aWhere).")";
		}
		
		$sColumn	.= " AS has_icon";
		$oHasIcon	= $oQuery->Execute("SELECT {$sColumn} FROM file_type WHERE id = {$iFileTypeId} LIMIT 1");
		if ($oHasIcon === false) {
			throw new Exception_Database($oQuery->Error());
		} elseif ($aHasIcon = $oHasIcon->fetch_assoc()) {
			return (bool)$aHasIcon['has_icon'];
		} else {
			throw new Exception("Unknown File Type with Id '{$iFileTypeId}'");
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
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selByExtensionMimeType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("file_type JOIN file_type_mime_type ftmt ON file_type.id = ftmt.file_type_id JOIN mime_type ON ftmt.mime_type_id = mime_type.id", "file_type.*", "file_type.extension = <extension> AND mime_type.mime_content_type = <mime_content_type>", NULL, 1);
					break;
				case 'selPreferredMimeType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("file_type_mime_type ftmt JOIN mime_type mt ON ftmt.mime_type_id = mt.id", "mt.*", "ftmt.file_type_id = <id>", "ftmt.is_preferred_mime_type DESC, ftmt.id DESC", 1);
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
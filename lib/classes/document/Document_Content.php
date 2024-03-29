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
	
	private				$_strDecompressedContent	= null;
	
	public				$bolHasContent;
	public				$intContentSize;
	
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
		
		if ($this->content)
		{
			$this->content	= $this->_decompressContent($this->content);
			parent::__set('uncompressed_file_size', strlen($this->content));
		}
		
		$this->bolHasContent	= ($this->content) ? true : false;
		$this->intContentSize	= $this->uncompressed_file_size;
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
		static	$qryQuery;
		$qryQuery	= ($qryQuery) ? $qryQuery : new Query();
		
		$mixFriendlyName	= null;
		
		$strConstantGroup	= trim($this->constant_group);
		$arrMatch			= array();
		if (preg_match("/^(\w+)\:(\w+)\,(\w+)$/", $this->constant_group, $arrMatch))
		{
			// Database lookup (format -- 'table_name:id_field,name_field')
			$strLookupSQL	= "SELECT {$arrMatch[3]} AS friendly_name FROM {$arrMatch[1]} WHERE {$arrMatch[2]} = ".((int)$this->name)." LIMIT 1";
			$resLookup		= $qryQuery->Execute($strLookupSQL);
			if ($resLookup === false)
			{
				throw new Exception_Database($qryQuery->Error());
			}
			elseif ($arrLookup = $resLookup->fetch_assoc())
			{
				$mixFriendlyName	= $arrLookup['friendly_name'];
			}
		}
		elseif ($objConstantGroup = Constant_Group::getConstantGroup($this->constant_group, true))
		{
			// Constant Group
			$mixFriendlyName	= $objConstantGroup->getConstantDescription($this->name);
		}
		return ($mixFriendlyName !== null) ? $mixFriendlyName : $this->name;
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
			// BZIP the Content
			$strUncompressedContent	= $this->content;
			$this->content			= $this->_compressContent($this->content);
			
			// Ensure that the uncompressed_file_size Field is up to date
			parent::__set('uncompressed_file_size', ($strUncompressedContent === null) ? null : strlen($strUncompressedContent));
			
			parent::save();
			$this->content			= $strUncompressedContent;
		}
		else
		{
			// You cannot save a Details-only Document_Content
			throw new Exception("You cannot save a Document_Content object which has been loaded in Details-Only Mode");
		}
	}
	
	private function _compressContent($mixValue)
	{
		if ($mixValue !== null)
		{
			$mixCompressed	= bzcompress($mixValue);
			if (is_int($mixCompressed))
			{
				// Error
				throw new Exception("Unable to compress Content for Document {$this->document_id} (Revision: ".($this->id ? $this->id : 'Unsaved')."): Error #{$mixCompressed}");
			}
		}
		else
		{
			$mixCompressed	= null;
		}
		return $mixCompressed;
	}
	
	private function _decompressContent($mixValue)
	{
		if ($mixValue !== null)
		{
			$mixDecompressed	= bzdecompress($mixValue);
			if (is_int($mixDecompressed))
			{
				// Error
				throw new Exception("Unable to decompress Content for Document {$this->document_id} (Revision: ".($this->id ? $this->id : 'Unsaved')."): Error #{$mixDecompressed}");
			}
		}
		else
		{
			$mixDecompressed	= null;
		}
		//throw new Exception(">>>\n".$mixDecompressed."\n<<<");
		return $mixDecompressed;
	}
	
	public function __set($strName, $mixValue)
	{
		$strTidyName	= self::tidyName($strName);
		switch ($strTidyName)
		{
			case 'content':
				parent::__set('uncompressed_file_size', ($mixValue === null) ? null : strlen($mixValue));
				break;
			
			case 'uncompressed_file_size':
				throw new Exception("You cannot set Document_Content.uncompressed_file_size directly.  It is automatically updated when you set the Content.");
				break;
		}
		
		// Call Parent Set
		parent::__set($strName, $mixValue);
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
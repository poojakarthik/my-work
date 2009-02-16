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
	const				PATH_DIRECTORY_DELIMITER	= '/';
	
	protected			$_strTableName				= "document";
	protected static	$_strStaticTableName		= "document";
	
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
	 * getContentDetails()
	 *
	 * Retrieves the details of the Content for this Document
	 * 
	 * @param	[mixed			$mixRevision				]		Revision of the Content to retrieve
	 * 																TRUE|0		: Latest Revision (default)
	 * 																FALSE		: Earliest Revision
	 * 																-ve integer	: X Revisions ago (eg. -3 = 3 revisions ago)
	 * 																+ve integer	: Revision number X (eg. 2 = second revision)
	 * 
	 * @return	Document_Content									The requested Statement
	 *
	 * @method
	 */
	public function getContentDetails($mixRevision=true)
	{
		return new Document_Content($this->_getContent(true, true));
	}
	
	/**
	 * getContent()
	 *
	 * Retrieves the Content for this Document
	 * 
	 * @param	[mixed			$mixRevision				]		Revision of the Content to retrieve
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
		// Return the Content including the Binary Data
		return new Document_Content($this->_getContent(false, true));
	}
	
	/**
	 * _getContent()
	 *
	 * Retrieves the Content for this Document
	 * 
	 * @param	[mixed			$mixRevision				]		Revision of the Content to retrieve
	 * 																TRUE|0		: Latest Revision (default)
	 * 																FALSE		: Earliest Revision
	 * 																-ve integer	: X Revisions ago (eg. -3 = 3 revisions ago)
	 * 																+ve integer	: Revision number X (eg. 2 = second revision)
	 * 
	 * @return	Document_Content									The requested Statement
	 *
	 * @method
	 */
	private function _getContent($bolDetailsOnly=false, $mixRevision=true)
	{
		if (!$this->id)
		{
			throw new Exception("Document Id has not been defined!");
		}
		
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
		elseif (!is_bool($mixRevision) && $mixRevision > 0)
		{
			// Positive Integer -- Revision number $mixRevision
			$strLimit	= "1 OFFSET ".($mixRevision-1);
			$strOrderBy	= "id ASC";
		}
		elseif (!is_bool($mixRevision) && $mixRevision < 0)
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
		
		$objEmptyDocumentContent	= new Document_Content();
		$arrColumns					= $objEmptyDocumentContent->toArray();
		unset($objEmptyDocumentContent);
		unset($arrColumns['content']);
		$strColumns					= implode(', ', array_keys($arrColumns));
		
		// Retrieve and return the content
		$strQuery		= "SELECT {$strColumns}, CASE WHEN content IS NULL THEN 0 ELSE 1 END AS has_content FROM document_content WHERE document_id = {$this->id} ORDER BY {$strOrderBy} LIMIT {$strLimit}";
		$resRevision	= $qryQuery->Execute($strQuery);
		if ($resRevision === false)
		{
			throw new Exception($qryQuery->Error());
		}
		
		if ($resRevision->num_rows)
		{
			$arrDocumentContent	= $resRevision->fetch_assoc();
			$objDocumentContent	= new Document_Content($arrDocumentContent, false, $bolDetailsOnly);
			$objDocumentContent->bolHasContent	= (bool)$arrDocumentContent['has_content'];
			
			return $objDocumentContent;
		}
		else
		{
			return null;
		}
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
		
	}
	
	/**
	 * getByPath()
	 *
	 * Retrieves a Document based on a passed pseudo-path
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
	public static function getByPath($strPath, $bolAsArray=false)
	{
		$arrPath	= explode(self::PATH_DIRECTORY_DELIMITER, $strPath);
		//throw new Exception(print_r($arrPath, true));
		
		$intParentId	= null;
		foreach ($arrPath as $strNode)
		{
			if ($strNode)
			{
				// Check if this node exists
				$selByNameAndParent	= self::_preparedStatement('selByNameAndParent');
				$mixResult			= $selByNameAndParent->Execute(array('name'=>$strNode, 'parent_document_id'=>$intParentId));
				if ($mixResult === false)
				{
					throw new Exception($selByNameAndParent->Error());
				}
				elseif (!$mixResult)
				{
					// TODO: Do we want to throw a custom exception?
					return null;
				}
				
				$arrDocument	= $selByNameAndParent->Fetch();
				$intParentId	= $arrDocument['id'];
			}
		}
		
		return ($bolAsArray) ? $arrDocument : new Document($arrDocument);
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
				case 'selByNameAndParent':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("document JOIN document_content ON document.id = document_content.document_id", "document.*", "parent_document_id <=> <parent_document_id> AND name = <name> AND document_content.id = (SELECT Id FROM document_content WHERE document_id = document.id ORDER BY id DESC LIMIT 1)", NULL, 1);
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
<?php
//----------------------------------------------------------------------------//
// Note_Type
//----------------------------------------------------------------------------//
/**
 * Note_Type
 *
 * Models a record of the NoteType table
 *
 * Models a record of the NoteType table
 *
 * @class	Note_Type
 */
class Note_Type extends ORM
{	
	protected $_strTableName	= "NoteType";

	/**
	 * __construct()
	 *
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining a record of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the record from the database table with the passed Id
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
	 * getAll()
	 *
	 * Returns array of Note_Type objects representing each record in associated table
	 * This uses NoteType.Id for the key for the records
	 * These will be sorted by NoteType.TypeLabel ASC
	 *
	 * @return		array of Note_Type objects
	 * @method
	 */
	public static function getAll()
	{
		static $arrNote_Types;
		if (!isset($arrNote_Types))
		{
			$arrNote_Types = array();
			
			$selNote_Types = self::_preparedStatement('selAll');
			
			if (($arrRecordSet = $selNote_Types->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all Note_Types from the data source: ". $selNote_Types->Error());
			}
	
			while ($arrRecord = $selNote_Types->Fetch())
			{
				$arrNote_Types[$arrRecord['Id']] = new self($arrRecord);
			}
		}
		
		return $arrNote_Types;
	}

	/**
	 * getForId()
	 *
	 * Returns Note_Type object for the NoteType.id supplied
	 * 
	 * @param	int		$intId						id of the NoteType record to return
	 * @param	bool	$bolSilentFail				Optional. Defaults to FALSE. If FALSE then an Exception_ORM_LoadById exception will be thrown if the record cannot be found
	 * 												if TRUE, then NULL will be returned if the record cannot be found
	 *
	 * @return	mixed			Note_Type	: if record can be found
	 * 							NULL		: if record can't be found and $bolSilentFail == TRUE
	 * @method
	 */
	public static function getForId($intId, $bolSilentFail=false)
	{
		$arrAll = self::getAll();
		
		if (array_key_exists($intId, $arrAll))
		{
			// Found it
			return $arrAll[$intId];
		}
		else
		{
			// Could not find the Note_Type
			if ($bolSilentFail)
			{
				return NULL;
			}
			else
			{
				throw new Exception_ORM_LoadById(__CLASS__, $intId);
			}
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("NoteType", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("NoteType", "*", "", "TypeLabel ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("NoteType");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("NoteType");
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
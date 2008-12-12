<?php
//----------------------------------------------------------------------------//
// Note
//----------------------------------------------------------------------------//
/**
 * Note
 *
 * Models a record of the Note table
 *
 * Models a record of the Note table
 *
 * @class	Note
 */
class Note extends ORM
{	
	const SYSTEM_NOTE_TYPE_ID = 7;
	
	protected	$_strTableName	= "Note";
	
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
	
	public static function createSystemNote($strContent, $intEmployeeId, $intAccountGroupId, $intAccountId, $intServiceId=NULL, $intContactId=NULL)
	{
		return self::createNote(self::SYSTEM_NOTE_TYPE_ID, $strContent, $intEmployeeId, $intAccountGroupId, $intAccountId, $intServiceId, $intContactId);
	}
	
	// Creates the note, and saves it and returns the object
	// Will throw an Exception on error
	// Will always return a Note object, if an exception is not thrown
	public static function createNote($intNoteTypeId, $strContent, $intEmployeeId, $intAccountGroupId, $intAccountId, $intServiceId=NULL, $intContactId=NULL)
	{
	 	if ($intEmployeeId === NULL)
	 	{
	 		// Use the System User Employee Id
	 		$intEmployeeId = Employee::SYSTEM_EMPLOYEE_ID;
	 	}
	 	
	 	if ($intNoteTypeId === NULL)
	 	{
	 		$intNoteTypeId = self::SYSTEM_NOTE_TYPE_ID;
	 	}
	 	
	 	// Insert the note
	 	$arrData = Array();
	 	$arrData['Note']			= $strContent;
	 	$arrData['AccountGroup']	= $intAccountGroupId;
	 	$arrData['Contact']			= $intContactId;
	 	$arrData['Account']			= $intAccountId;
	 	$arrData['Service']			= $intServiceId;
	 	$arrData['Employee']		= $intEmployeeId;
	 	$arrData['Datetime']		= Data_Source_Time::currentTimestamp();
	 	$arrData['NoteType']		= $intNoteTypeId;

		$objNote = new self($arrData);
		
		try
		{
			$objNote->save();
		}
		catch (Exception $e)
		{
			throw new Exception(__METHOD__ ." Failed to save note - ". $e->getMessage());
		}
	 	
	 	return $objNote;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Note", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Note");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Note");
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
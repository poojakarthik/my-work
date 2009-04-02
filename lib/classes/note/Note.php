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
	const GENERAL_NOTE_TYPE_ID	= 1;
	const SYSTEM_NOTE_TYPE_ID	= 7;
	
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
	
	public static function createSystemNote($strContent, $intEmployeeId, $intAccountId=NULL, $intServiceId=NULL, $intContactId=NULL)
	{
		return self::createNote(self::SYSTEM_NOTE_TYPE_ID, $strContent, $intEmployeeId, $intAccountId, $intServiceId, $intContactId);
	}
	
	// Creates the note, and saves it and returns the object
	// Will throw an Exception on error
	// Will always return a Note object, if an exception is not thrown
	// $strContent does not currently need to be escaped because the record will be added using a StatementUpdate, which handles the escaping of strings
	// At least one of $intAccountId, $intServiceId or $intContactId must be specified (not NULL)
	public static function createNote($intNoteTypeId, $strContent, $intEmployeeId, $intAccountId=NULL, $intServiceId=NULL, $intContactId=NULL)
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

		try
		{
		 	if ($intAccountId == NULL && $intServiceId == NULL && $intContactId == NULL)
		 	{
		 		throw new Exception("Have not specified what account/service/contact owns the note");
		 	}
		 	
		 	// Retrieve an associated account object so we can specify the AccountGroup, because this is a column of the Note table, but will be deprecated soon
		 	$objAccount = null;
		 	if ($intAccountId)
		 	{
		 		$objAccount = Account::getForId($intAccountId);
		 		if ($objAccount == NULL)
		 		{
		 			throw new Exception("Can't find account: $intAccountId");
		 		}
		 	}
		 	elseif ($intServiceId)
		 	{
		 		// This will throw an exception if the service cannot be found
		 		$objService = Service::getForId($intServiceId);
		 		
		 		// This will return NULL if the account cannot be found
		 		$objAccount = Account::getForId($objService->account);
		 		if ($objAccount == NULL)
		 		{
		 			throw new Exception("Can't find account: {$objService->account}");
		 		}
		 	}
		 	elseif ($intContactId)
		 	{
		 		// This will return NULL if the contact cannot be found
		 		$objContact = Contact::getForId($intContactId);
		 		if ($objContact == NULL)
		 		{
		 			throw new Exception("Can't find contact: $intContactId");
		 		}
		 		$objAccount = Account::getForId($objContact->account);
		 		if ($objAccount == NULL)
		 		{
		 			throw new Exception("Can't find account: {$objContact->account}");
		 		}
		 	}
		 	
		 	// We now have an account object
		 	
		 	// Insert the note
		 	$arrData = Array();
		 	$arrData['Note']			= $strContent;
		 	$arrData['AccountGroup']	= $objAccount->accountGroup;
		 	$arrData['Contact']			= $intContactId;
		 	$arrData['Account']			= $intAccountId;
		 	$arrData['Service']			= $intServiceId;
		 	$arrData['Employee']		= $intEmployeeId;
		 	$arrData['Datetime']		= Data_Source_Time::currentTimestamp();
		 	$arrData['NoteType']		= $intNoteTypeId;
	
			$objNote = new self($arrData);
		
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
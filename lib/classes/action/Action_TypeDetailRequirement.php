<?php
//----------------------------------------------------------------------------//
// Action_TypeDetailRequirement
//----------------------------------------------------------------------------//
/**
 * Action_TypeDetailRequirement
 *
 * Models a record of the action_type_detail_requirement table
 *
 * Models a record of the action_type_detail_requirement table
 *
 * @class	Action_TypeDetailRequirement
 */
class Action_TypeDetailRequirement extends ORM
{	
	protected $_strTableName	= "action_type_detail_requirement";

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

	//------------------------------------------------------------------------//
	// getAll
	//------------------------------------------------------------------------//
	/**
	 * getAll()
	 *
	 * Returns array of Action_TypeDetailRequirement objects representing each record in associated table
	 * 
	 * Returns array of Action_TypeDetailRequirement objects representing each record in associated table
	 * This uses action_type_detail_requirement.id for the key for the records
	 * These will be sorted by action_type_detail_requirement.name ASC
	 *
	 * @return		array of Action_TypeDetailRequirement objects
	 * @method
	 */
	public static function getAll()
	{
		static $arrAction_TypeDetailRequirements;
		if (!isset($arrAction_TypeDetailRequirements))
		{
			$arrAction_TypeDetailRequirements = array();
			
			$selAction_TypeDetailRequirements = self::_preparedStatement('selAll');
			
			if (($arrRecordSet = $selAction_TypeDetailRequirements->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all Action_TypeDetailRequirements from the data source: ". $selAction_TypeDetailRequirements->Error());
			}
	
			while ($arrRecord = $selAction_TypeDetailRequirements->Fetch())
			{
				$arrAction_TypeDetailRequirements[$arrRecord['id']] = new self($arrRecord);
			}
		}
		
		return $arrAction_TypeDetailRequirements;
	}

	//------------------------------------------------------------------------//
	// getForId
	//------------------------------------------------------------------------//
	/**
	 * getForId()
	 *
	 * Returns Action_TypeDetailRequirement object for the action_type_detail_requirement.id supplied
	 * 
	 * Returns Action_TypeDetailRequirement object for the action_type_detail_requirement.id supplied
	 *
	 * @param	int		$intId						id of the action_type_detail_requirement record to return
	 * @param	bool	$bolSilentFail				Optional. Defaults to FALSE. If FALSE then an Exception_ORM_LoadById exception will be thrown if the record cannot be found
	 * 												if TRUE, then NULL will be returned if the record cannot be found
	 *
	 * @return	mixed			Action_TypeDetailRequirement	: if record can be found
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
			// Could not find the Action_TypeDetailRequirement
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_type_detail_requirement", "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_type_detail_requirement", "*", "", "name ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("action_type_detail_requirement");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("action_type_detail_requirement");
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
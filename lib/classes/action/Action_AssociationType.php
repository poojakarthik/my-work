<?php
//----------------------------------------------------------------------------//
// Action_AssociationType
//----------------------------------------------------------------------------//
/**
 * Action_AssociationType
 *
 * Models a record of the action_association_type table
 *
 * Models a record of the action_association_type table
 *
 * @class	Action_AssociationType
 */
class Action_AssociationType extends ORM
{	
	protected $_strTableName	= "action_association_type";

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
	 * Returns array of Action_AssociationType objects representing each record in associated table
	 * This uses action_association_type.id for the key for the records
	 * These will be sorted by action_association_type.name ASC
	 *
	 * @return		array of Action_AssociationType objects
	 * @method
	 */
	public static function getAll()
	{
		static $arrAction_AssociationTypes;
		if (!isset($arrAction_AssociationTypes))
		{
			$arrAction_AssociationTypes = array();
			
			$selAction_AssociationTypes = self::_preparedStatement('selAll');
			
			if (($arrRecordSet = $selAction_AssociationTypes->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all Action_AssociationTypes from the data source: ". $selAction_AssociationTypes->Error());
			}
	
			while ($arrRecord = $selAction_AssociationTypes->Fetch())
			{
				$arrAction_AssociationTypes[$arrRecord['id']] = new self($arrRecord);
			}
		}
		
		return $arrAction_AssociationTypes;
	}

	/**
	 * getForId()
	 *
	 * Returns Action_AssociationType object for the action_association_type.id supplied
	 * 
	 * @param	int		$intId						id of the action_association_type record to return
	 * @param	bool	$bolSilentFail				Optional. Defaults to FALSE. If FALSE then an Exception_ORM_LoadById exception will be thrown if the record cannot be found
	 * 												if TRUE, then NULL will be returned if the record cannot be found
	 *
	 * @return	mixed			Action_AssociationType	: if record can be found
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
			// Could not find the Action_AssociationType
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_association_type", "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_association_type", "*", "", "name ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("action_association_type");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("action_association_type");
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
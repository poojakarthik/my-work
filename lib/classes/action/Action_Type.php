<?php
//----------------------------------------------------------------------------//
// Action_Type
//----------------------------------------------------------------------------//
/**
 * Action_Type
 *
 * Models a record of the action_type table
 *
 * Models a record of the action_type table
 * Note that any Action_Type objects returned by the static functions will directly refer to an Action_Type object in the cache, not a clone of the object.
 * Therefore any changes made to the object will change the object in the cache
 *
 * @class	Action_Type
 */
class Action_Type extends ORM
{	
	protected $_strTableName = "action_type";
	protected static $_cache = array();
	
	protected $_arrAllowableActionAssociationTypes = null;

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
	public function __construct($arrProperties=Array(), $bolLoadById=false)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	/**
	 * getAllowableActionAssociationTypes()
	 *
	 * Returns an array of Action_AssociationType objects representing the things that the Action_Type can be associated with
	 * id of the Action_AssociationType will be used as the key into the array
	 *
	 * @return	array of Action_AssociationType objects	(array[object->id] = object)
	 * @method
	 */
	public function getAllowableActionAssociationTypes()
	{
		if ($this->_arrAllowableActionAssociationTypes === null)
		{
			// The allowable ActionAssociationTypes have not yet been retreived for this ActionType object
			$selAssociations = self::_preparedStatement('selAllowedActionAssociationTypes');
			if ($selAssociations->Execute(array('ActionTypeId'=>$this->id)) === false)
			{
				throw new Exception("Failed to retrieve allowable ActionAssociationTypes, from the data source, for the ActionType: {$this->name} ({$this->id}) - ". $selAssociations->Error());
			}
	
			// Build list
			$this->_arrAllowableActionAssociationTypes = array();
			while ($arrRecord = $selAssociations->Fetch())
			{
				$this->_arrAllowableActionAssociationTypes[$arrRecord['action_association_type_id']] = Action_AssociationType::getForId($arrRecord['action_association_type_id']);
			}
		}
		return $this->_arrAllowableActionAssociationTypes;
	}

	/**
	 * setAllowbaleActionAssociationTypes()
	 *
	 * Sets the Action_AssociationType objects that the Action_Type can be associated with
	 * Note that this will actually insert/delete records in the action_type_action_association_type table
	 * 
	 * @param	array	$arrActionAssociationTypeIds	array of ids of ALL the Action_AssociationTypes to allow for this Action_Type 
	 *
	 * @return	void	(throws exception on error)
	 * @method
	 */
	public function setAllowableActionAssociationTypes($arrActionAssociationTypeIds)
	{
		if (!$this->id)
		{
			throw new Exception("Cannot set ActionAssociationTypes for an ActionType that has not been saved in the database yet");
		}
		
		// Get an array of the current allowable action association types (their ids)
		$arrCurrentAllowableAATypeIds = array_keys($this->getAllowableActionAssociationTypes());
		
		$arrAllAATypes	= Action_AssociationType::getAll();
		$arrAATypeIdsToRemove	= array();
		$arrAATypeIdsToAdd		= array();
		
		foreach ($arrAllAATypes as $intId=>$actionAssociationType)
		{
			// Check if it is going to be used 
			if (in_array($intId, $arrActionAssociationTypeIds))
			{
				// It's going to be used
				if (in_array($intId, $arrCurrentAllowableAATypeIds))
				{
					// It's already associated with the ActionType, so we don't have to add it
				}
				else
				{
					// It's not already associated with the ActionType, so we have to add it
					$arrAATypeIdsToAdd[] = $intId;
				}
			}
			else
			{
				// It's not going to be used
				if (in_array($intId, $arrCurrentAllowableAATypeIds))
				{
					// It's currently associated with the ActionType, so we have to remove this association
					$arrAATypeIdsToRemove[] = $intId;
				}
				else
				{
					// It's not currently associated with the ActionType, so we don't have to do anything
				}
			}
		}
		
		// Check that none of the ones to remove have actions associated with them
		foreach ($arrAATypeIdsToRemove as $intId)
		{
			if ($this->hasActions($intId))
			{
				// Can't remove it because there are already actions made with this ActionType/ActionAssociationType combination
				throw new Exception("Cannot disassociate the ActionAssociationType, '{$arrAllAATypes[$intId]->name}' with the action type, '{$this->name}', because actions of this ActionType/ActionAssociationType have already been made");
			}
		}
		
		$qryQuery = new Query();
		
		// Remove the ones to remove
		if (count($arrAATypeIdsToRemove))
		{
			// There are ones to remove
			$strRemovalQuery = "DELETE FROM action_type_action_association_type WHERE action_type_id = {$this->id} AND action_association_type_id IN (". implode(", ", $arrAATypeIdsToRemove) .");";
			if ($qryQuery->Execute($strRemovalQuery) === false)
			{
				throw new Exception("Failed to remove action_type_action_association_type records - ". $qryQuery->Error() ." - Query: $strRemovalQuery");
			}
		}
		
		// Add the ones to add
		if (count($arrAATypeIdsToAdd))
		{
			// There are ones to add
			$insAssociations = self::_preparedStatement('insActionTypeActionAssociationType');
			
			$arrRecordToInsert = array("action_type_id"=>$this->id);
			foreach ($arrAATypeIdsToAdd as $intId)
			{
				$arrRecordToInsert['action_association_type_id'] = $intId;
				
				if ($insAssociations->Execute($arrRecordToInsert) === false)
				{
					throw new Exception("Failed to add record to action_type_action_association_type table - ". $insAssociations->Error());
				}
			}
		}
		
		// Update the allowable ActionAssociationTypes to reflect what they now are
		$this->_arrAllowableActionAssociationTypes = array();
		foreach ($arrActionAssociationTypeIds as $intId)
		{
			$this->_arrAllowableActionAssociationTypes[$intId] = $arrAllAATypes[$intId];
		}

		// All done
	}
	
	/**
	 * hasActions()
	 *
	 * Checks if there are any actions of this particular ActionType or any associated with the ActionAssociationType passed
	 * 
	 * @param	int		$intActionAssociationTypeId		Optional defaults to NULL.  If a particular ActionAssociationType id is specified
	 * 													then it will only return TRUE if there are actions of this ActionType AND if at least
	 * 													1 is associated with the ActionAssociationType
	 *
	 * @return	bool	True if there are actions, false if there are not
	 * @method
	 */
	public function hasActions($intActionAssociationTypeId=null)
	{
		if (!$this->id)
		{
			return false;
		}
		
		$qryQuery = new Query();
		switch ($intActionAssociationTypeId)
		{
			case ACTION_ASSOCIATION_TYPE_ACCOUNT:
				$strQuery = "SELECT action.id FROM action INNER JOIN account_action ON action.id = account_action.action_id WHERE action.action_type_id = {$this->id} LIMIT 1;";
				break;
				
			case ACTION_ASSOCIATION_TYPE_SERVICE:
				$strQuery = "SELECT action.id FROM action INNER JOIN service_action ON action.id = service_action.action_id WHERE action.action_type_id = {$this->id} LIMIT 1;";
				break;
				
			case ACTION_ASSOCIATION_TYPE_CONTACT:
				$strQuery = "SELECT action.id FROM action INNER JOIN contact_action ON action.id = contact_action.action_id WHERE action.action_type_id = {$this->id} LIMIT 1;";
				break;
				
			default:
				$strQuery = "SELECT id FROM action WHERE action_type_id = {$this->id} LIMIT 1;";
				break;
		}
		
		$result = $qryQuery->Execute($strQuery);
		if ($result === false)
		{
			throw new Exception("Error checking if the {$this->name} (id: {$this->id}) action type has any actions - ". $qryQuery->Error() ." - Query: $strQuery");
		}
		
		return ($result->num_rows)? true : false;
	}

	// Saves the object and adds/updates it in the cache
	public function save()
	{
		// Save the object
		parent::save();
		
		// Update the cache
		self::$_cache[$this->id] = $this;
	}

	// Clears the cache of Action_Type objects
	public static function clearCache()
	{
		self::$_cache = array();
	}
	
	// Returns all Action_Type objects that are currently cached
	public static function getCache()
	{
		return self::$_cache;
	}

	/**
	 * getAllForAccounts()
	 *
	 * returns array (keyed with the action type ids) of all ActionTypes that can be associated with an account
	 * 
	 * @param	bool	[ $bolActiveOnly ]		Defaults to false.  If true then only active ActionTypes are returned
	 * 											If false then all ActiveTypes are returned
	 * @param	bool	[ $bolManualOnly ]		Defaults to false.  If true then it only returns ActionTypes that can be manually created by flex users
	 * 											If false then it returns all ActionTypes
	 *
	 * @return	array of Action_Type objects, with the id of each Action_Type as its key
	 * @method
	 */
	public static function getAllForAccounts($bolActiveOnly=false, $bolManualOnly=false)
	{
		return self::_getForActionAssociationType(ACTION_ASSOCIATION_TYPE_ACCOUNT, $bolActiveOnly, $bolManualOnly);
	}
	
	/**
	 * getAllForServices()
	 *
	 * returns array (keyed with the action type ids) of all ActionTypes that can be associated with a service
	 * 
	 * @param	bool	[ $bolActiveOnly ]		Defaults to false.  If true then only active ActionTypes are returned
	 * 											If false then all ActiveTypes are returned
	 * @param	bool	[ $bolManualOnly ]		Defaults to false.  If true then it only returns ActionTypes that can be manually created by flex users
	 * 											If false then it returns all ActionTypes
	 *
	 * @return	array of Action_Type objects, with the id of each Action_Type as its key
	 * @method
	 */
	public static function getAllForServices($bolActiveOnly=false, $bolManualOnly=false)
	{
		return self::_getForActionAssociationType(ACTION_ASSOCIATION_TYPE_SERVICE, $bolActiveOnly, $bolManualOnly);
	}
	
	/**
	 * getAllForContacts()
	 *
	 * returns array (keyed with the action type ids) of all ActionTypes that can be associated with a contact
	 * 
	 * @param	bool	[ $bolActiveOnly ]		Defaults to false.  If true then only active ActionTypes are returned
	 * 											If false then all ActiveTypes are returned
	 * @param	bool	[ $bolManualOnly ]		Defaults to false.  If true then it only returns ActionTypes that can be manually created by flex users
	 * 											If false then it returns all ActionTypes
	 *
	 * @return	array of Action_Type objects, with the id of each Action_Type as its key
	 * @method
	 */
	public static function getAllForContacts($bolActiveOnly=false, $bolManualOnly=false)
	{
		return self::_getForActionAssociationType(ACTION_ASSOCIATION_TYPE_CONTACT, $bolActiveOnly, $bolManualOnly);
	}

	/**
	 * _getForActionAssociationType()
	 *
	 * returns array (keyed with the action type ids) of all ActionTypes that can be associated with the specified ActionAssociatoinType
	 * 
	 * @param	int		$intActionAssociationTypeId		ActionAssoicationType
	 * @param	bool	[ $bolActiveOnly ]				Defaults to false.  If true then only active ActionTypes are returned
	 * 													If false then all ActiveTypes are returned
	 * @param	bool	[ $bolManualOnly ]				Defaults to false.  If true then it only returns ActionTypes that can be manually created by flex users
	 * 													If false then it returns all ActionTypes
	 *
	 * @return	array of Action_Type objects, with the id of each Action_Type as its key
	 * @method
	 */
	private static function _getForActionAssociationType($intActionAssociationTypeId, $bolActiveOnly=false, $bolManualOnly=false)
	{
		// Retrieve the records from the database
		$selActionTypes = self::_preparedStatement('selAllByActionAssociationType');
		$arrWhere = array(	'ActionAssociationTypeId'	=> $intActionAssociationTypeId,
							'ActiveOnly'				=> $bolActiveOnly,
							'ManualOnly'				=> $bolManualOnly
						);
		
		if ($selActionTypes->Execute($arrWhere) === false)
		{
			throw new Exception("Failed to retrieve Action_Types, associated with ActionAssociationType $intActionAssociationTypeId, from the data source: ". $selActionTypes->Error());
		}

		// Load each Action_Type object (and also cache it)
		$arrActionTypes = array();
		while ($arrRecord = $selActionTypes->Fetch())
		{
			if (!array_key_exists($arrRecord['id'], self::$_cache))
			{
				// Cache the object
				self::$_cache[$arrRecord['id']] = new self($arrRecord);
			}
			$arrActionTypes[$arrRecord['id']] = self::$_cache[$arrRecord['id']];
		}
		
		return $arrActionTypes;
	}

	/**
	 * getAll()
	 *
	 * Returns array of Action_Type objects representing each record in associated table
	 * This uses action_type.id for the key for the records
	 * These will be sorted by action_type.name ASC
	 * It will cache these
	 * If an action_type is already in the cache, it will NOT overwrite it with a fresh copy from the database
	 *
	 * @return		array of Action_Type objects
	 * @method
	 */
	public static function getAll()
	{
		$selActionTypes = self::_preparedStatement('selAll');
		
		if ($selActionTypes->Execute() === false)
		{
			throw new Exception("Failed to retrieve all Action_Types from the data source: ". $selActionTypes->Error());
		}

		while ($arrRecord = $selActionTypes->Fetch())
		{
			if (!array_key_exists($arrRecord['id'], self::$_cache))
			{
				// It's not currently cached
				self::$_cache[$arrRecord['id']] = new self($arrRecord);
			}
		}
		
		return self::$_cache;
	}

	/**
	 * getForId()
	 *
	 * Returns Action_Type object for the action_type.id supplied
	 *
	 * @param	int		$intId						id of the action_type record to return
	 * @param	bool	$bolSilentFail				Optional. Defaults to FALSE. If FALSE then an Exception_ORM_LoadById exception will be thrown if the record cannot be found
	 * 												if TRUE, then NULL will be returned if the record cannot be found
	 *
	 * @return	mixed			Action_Type	: if record can be found (this object will also be referenced in the cache)
	 * 							NULL		: if record can't be found and $bolSilentFail == TRUE
	 * @method
	 */
	public static function getForId($intId, $bolSilentFail=false)
	{
		if (array_key_exists($intId, self::$_cache))
		{
			// The ActionType is in the cache
			return self::$_cache[$intId];
		}
		
		// Try finding it in the database
		try
		{
			$objActionType = new self(array('id'=>$intId), true);
		}
		catch (Exception_ORM_LoadById $e)
		{
			// Could not find the record
			if ($bolSilentFail)
			{
				return null;
			}
			else
			{
				throw $e;
			}
		}
		
		// The object could be created
		self::$_cache[$intId] = $objActionType;
		return $objActionType;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_type", "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_type", "*", "", "name ASC");
					break;
				case 'selAllByActionAssociationType':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_type INNER JOIN action_type_action_association_type", "action_type.*", "action_type_action_association_type.action_association_type_id = <ActionAssociationTypeId> AND (<ActiveOnly> != TRUE OR action_type.active_status_id = ". ACTIVE_STATUS_ACTIVE .") AND (<ManualOnly> != TRUE OR action_type.is_automatic_only = 0)", "action_type.name ASC");
					break;
				case 'selAllowedActionAssociationTypes':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action_type_action_association_type", "*", "action_type_id = <ActionTypeId>", "action_association_type_id ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("action_type");
					break;
				case 'insActionTypeActionAssociationType':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("action_type_action_association_type");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("action_type");
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
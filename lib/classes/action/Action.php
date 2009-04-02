<?php
//----------------------------------------------------------------------------//
// Action
//----------------------------------------------------------------------------//
/**
 * Action
 *
 * Models a record of the Action table
 *
 * Models a record of the Action table
 *
 * @class	Action
 */
class Action extends ORM
{	
	protected $_strTableName = "Action";

	/**
	 * __construct()
	 *
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining a record of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the record from the database table with the passed Id
	 *
	 * @return	void
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}

	// The array will have the id of each account as its key (if account objects are returned), 
	// else its just an indexed list of accound ids ordered oldest association to newest
	public function getAssociatedAccounts($bolIdsOnly=false)
	{
		$arrAccountIds = $this->_getAssociatedObjectIds(ACTION_ASSOCIATION_TYPE_ACCOUNT);
		
		if ($bolIdsOnly)
		{
			return $arrAccountIds;
		}
		else
		{
			$arrAccounts = array();
			foreach ($arrAccountIds as $intAccountId)
			{
				$arrAccounts[$intAccountId] = Account::getForId($intAccountId);
			}
			return $arrAccounts;
		}
	}

	// The array will have the id of each service as its key (if Service objects are returned), 
	// else its just an indexed list of service ids ordered oldest association to newest
	public function getAssociatedServices($bolIdsOnly=false)
	{
		$arrServiceIds = $this->_getAssociatedObjectIds(ACTION_ASSOCIATION_TYPE_SERVICE);
		
		if ($bolIdsOnly)
		{
			return $arrServiceIds;
		}
		else
		{
			$arrServices = array();
			foreach ($arrServiceIds as $intServiceId)
			{
				$arrServices[$intServiceId] = Service::getForId($intServiceId);
			}
			return $arrServices;
		}
	}

	// The array will have the id of each contact as its key (if contact objects are returned), 
	// else its just an indexed list of contact ids ordered oldest association to newest
	public function getAssociatedContacts($bolIdsOnly=false)
	{
		$arrContactIds = $this->_getAssociatedObjectIds(ACTION_ASSOCIATION_TYPE_CONTACT);
		
		if ($bolIdsOnly)
		{
			return $arrContactIds;
		}
		else
		{
			$arrContacts = array();
			foreach ($arrContactIds as $intContactId)
			{
				$arrContacts[$intContactId] = new Contact(array("id"=>$intContactId), true);
			}
			return $arrContacts;
		}
	}

	// if there are multiple objects, then they will be ordered in the array from oldest association to newest
	protected function _getAssociatedObjects($intActionAssociationType)
	{
		if (!$this->id)
		{
			return array();
		}

		switch ($intActionAssociationType)
		{
			case ACTION_ASSOCIATION_TYPE_ACCOUNT:
				$strPreparedStatement	= "selAccountAction";
				break;
			case ACTION_ASSOCIATION_TYPE_SERVICE:
				$strPreparedStatement	= "selServiceAction";
				break;
			case ACTION_ASSOCIATION_TYPE_CONTACT:
				$strPreparedStatement	= "selContactAction";
				break;
			default:
				throw new Exception(__METHOD__ ." - Unknown Action Association Type (id: $intActionAssociationType)");
				break;
		}
		
		$selAssoc	= self::_preparedStatement($strPreparedStatement);
		$arrWhere	= array('action_id' => $this->id);
		if ($selAssoc->Execute($arrWhere) === false)
		{
			throw new Exception("Failed to retrieve ". GetConstantDescription($intActionAssociationType, "action_association_type") ." objects associated with action: {$this->id}, from the data source: ". $selAssoc->Error());
		}

		// Convert the retrieved recordset into an indexed array of ids relating to the objects requested
		return ConvertToSimpleArray($selAssoc->FetchAll(), "object_id");
	}


	// Note that this will not remove any existing account associations for this record
	protected function _associateWithAccount($intAccountId)
	{
		$this->_associateWithObject($intAccountId, ACTION_ASSOCIATION_TYPE_ACCOUNT);
	}
	
	// Note that this will not remove any existing service associations for this record
	protected function _associateWithService($intServiceId)
	{
		$this->_associateWithObject($intServiceId, ACTION_ASSOCIATION_TYPE_SERVICE);
	}
	
	// Note that this will not remove any existing contact associations for this record
	protected function _associateWithContact($intContactId)
	{
		$this->_associateWithObject($intContactId, ACTION_ASSOCIATION_TYPE_CONTACT);
	}
	
	// This checks that the ActionType permits associations of the specified $intActionAssociationType
	protected function _associateWithObject($intObjectId, $intActionAssociationType)
	{
		if (!$this->id)
		{
			throw new Exception(__METHOD__ ." - Cannot associate anything with this action because it has not yet been saved to the database");
		}

		switch ($intActionAssociationType)
		{
			case ACTION_ASSOCIATION_TYPE_ACCOUNT:
				$strPreparedUpdateSQLStatement	= "insAccountAction";
				$strObjectColumnName			= "account_id";
				break;
			case ACTION_ASSOCIATION_TYPE_SERVICE:
				$strPreparedUpdateSQLStatement	= "insServiceAction";
				$strObjectColumnName			= "service_id";
				break;
			case ACTION_ASSOCIATION_TYPE_CONTACT:
				$strPreparedUpdateSQLStatement	= "insContactAction";
				$strObjectColumnName			= "contact_id";
				break;
			default:
				throw new Exception(__METHOD__ ." - Unknown Action Association Type (id: $intActionAssociationType)");
				break;
		}

		// Check that an association of this type, is permitted by the ActionType
		$objActionType		= Action_Type::getForId($this->actionTypeId);
		$arrAllowableTypes	= $objActionType->getAllowableActionAssociationTypes();
		if (!array_key_exists($intActionAssociationType, $arrAllowableTypes))
		{
			throw new Exception("Cannot associate ". GetConstantDescription($intActionAssociationType, "action_association_type") ." objects with actions of type {$objActionType->name}");
		}
		
		$insAssoc			= self::_preparedStatement($strPreparedUpdateSQLStatement);
		$arrRecordToInsert	= array(	"action_id"				=> $this->id,
										$strObjectColumnName	=> $intObjectId
									);
		if ($insAssoc->Execute($arrRecordToInsert) === false)
		{
			throw new Exception("Failed to associate action (id: {$this->id}) with ". GetConstantDescription($intActionAssociationType, "action_association_type") ." (id: $intObjectId) - ". $insAssoc->Error());
		}
	}

	/**
	 * createAction()
	 *
	 * Creates an Action object and saves it to the data source
	 * This should be used instead of manually instanciating an Action object and calling the save method, because you shouldn't ever need to update an action
	 * Note: At least 1 account, service or contact should be associated with an action.  If nothing is associated with the action, an exception will be thrown
	 * 
	 * @param	Action_Type		$objActionType		The type of action being logged
	 * @param	string			$strExtraDetails	The extra details to accompany the action.  Can be NULL
	 * @param	mixed			$mixAccountIds		array	:	array of account ids, representing the accounts to associate with this action
	 * 												integer	:	account id of single account to associate with this action
	 * 												null	:	no accounts are to be associated with this action 
	 * @param	mixed			$mixServiceIds		array	:	array of service ids, representing the services to associate with this action
	 * 												integer	:	service id of single service to associate with this action
	 * 												null	:	no services are to be associated with this action 
	 * @param	mixed			$mixContactIds		array	:	array of contact ids, representing the contacts to associate with this action
	 * 												integer	:	contact id of single contact to associate with this action
	 * 												null	:	no contacts are to be associated with this action 
	 * @param	int				$intPerformedByEmployeeId		id of the employee who 'performed' the action
	 * @param	int				$intCreatedByEmployeeId			id of the employee who logged the action (almost always the same as that who performed the action)
	 *
	 * @return	Action			The newly created (and saved) action object
	 * @method
	 */
	public static function createAction($objActionType, $strExtraDetails, $mixAccountIds, $mixServiceIds, $mixContactIds, $intPerformedByEmployeeId, $intCreatedByEmployeeId)
	{
		// Validate the details
		$strExtraDetails = trim($strExtraDetails);
		$strExtraDetails = ($strExtraDetails == "")? null : $strExtraDetails;
		
		if ($strExtraDetails !== null)
		{
			// Extra details have been specified
			if ($objActionType->actionTypeDetailRequirementId == ACTION_TYPE_DETAIL_REQUIREMENT_NONE)
			{
				// They are not permitted
				throw new Exception("Extra details regarding the action, have been supplied, but aren't permitted for {$objActionType->name} actions");
			}
		}
		else
		{
			// Extra details have not been specified
			if ($objActionType->actionTypeDetailRequirementId == ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED)
			{
				// They are required
				throw new Exception("Extra details regarding the action, have not been supplied, but are required for {$objActionType->name} actions");
			}
		}
		
		if ($objActionType->activeStatusId !== ACTIVE_STATUS_ACTIVE)
		{
			throw new Exception("The {$objActionType->name} ActionType is not active");
		}
		
		// If this is an automatic only ActionType, make sure it was created by the system
		if ($objActionType->isAutomaticOnly && $intCreatedByEmployeeId != Employee::SYSTEM_EMPLOYEE_ID)
		{
			throw new Exception("{$objActionType->name} actions can only be automatically logged by the system");
		}
		
		// Normalise accounts to associate with this action
		$arrAccounts = array();
		if (is_array($mixAccountIds))
		{
			$arrAccounts = $mixAccountIds;
		}
		elseif (is_int($mixAccountIds))
		{
			$arrAccounts[] = $mixAccountIds;
		}

		// Normalise services to associate with this action
		$arrServices = array();
		if (is_array($mixServiceIds))
		{
			$arrServices = $mixServiceIds;
		}
		elseif (is_int($mixServiceIds))
		{
			$arrServices[] = $mixServiceIds;
		}

		// Normalise contacts to associate with this action
		$arrContacts = array();
		if (is_array($mixContactIds))
		{
			$arrContacts = $mixContactIds;
		}
		elseif (is_int($mixContactIds))
		{
			$arrContacts[] = $mixContactIds;
		}
		
		if ((count($arrAccounts) + count($arrServices) + count($arrContacts)) == 0)
		{
			throw new Exception("No accounts, services or contacts have been associated with the {$objActionType->name} action");
		}
		
		
		$objAction = new Action();
		$objAction->actionTypeId			= $objActionType->id;
		$objAction->details					= $strExtraDetails;
		$objAction->createdByEmployeeId		= $intCreatedByEmployeeId;
		$objAction->createdOn				= GetCurrentISODateTime();
		$objAction->performedByEmployeeId	= $intPerformedByEmployeeId;
		
		$objAction->save();
		
		foreach ($arrAccounts as $intAccountId)
		{
			$objAction->_associateWithAccount($intAccountId);
		}

		foreach ($arrServices as $intServiceId)
		{
			$objAction->_associateWithService($intServiceId);
		}

		foreach ($arrContacts as $intContactId)
		{
			$objAction->_associateWithContact($intContactId);
		}
		
		return $objAction;
	}

	/**
	 * getForId()
	 *
	 * Returns Action object for the Action.id supplied
	 * 
	 * @param	int		$intId						id of the Action record to return
	 * @param	bool	$bolSilentFail				Optional. Defaults to FALSE. If FALSE then an Exception_ORM_LoadById exception will be thrown if the record cannot be found
	 * 												if TRUE, then NULL will be returned if the record cannot be found
	 *
	 * @return	mixed			Action	: if record can be found
	 * 							NULL	: if record can't be found and $bolSilentFail == TRUE
	 * @method
	 */
	public static function getForId($intId, $bolSilentFail=false)
	{
		try
		{
			$action = new Action(array('id'=>$intId), true);
		}
		catch (Exception_ORM_LoadById $e)
		{
			// The record could not be found
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
		return $action;
	}

	

	public static function countForAccount($intAccountId, $strFilter=NULL)
	{
		// TODO! count how many records would be returned
	}

	public static function countForService($intServiceId, $strFilter=NULL)
	{
		// TODO! count how many records would be returned
	}
	
	public static function countForContact($intContactId, $strFilter=NULL)
	{
		// TODO! count how many records would be returned
	}


	public static function listForAccount($intAccountId, $strFilter=NULL, $strOrder=NULL, $strOffset=NULL, $strLimit=NULL)
	{
		// instanciate the correct prepared SELECT statement
		
		// Append the account id constraint to the filter
		
		// call listFor
	}
	
	public static function listForService($intServiceId, $strFilter=NULL, $strOrder=NULL, $strOffset=NULL, $strLimit=NULL)
	{
		// Note that this should retrieve all actions for all service records that this service record 'belongs' to
		// (multiple service records can be used to model the one logical service for a single account)
		
		// I could perhaps make a function in Service which retrieves the ids of all service records that modell a service belonging to an account, and then call that
		
	}
	
	public static function listForContact($intContactId, $strFilter=NULL, $strOrder=NULL, $strOffset=NULL, $strLimit=NULL)
	{
	}
	
	// Returns generic object
	public static function getPageInfo($intTotalRows, $intCurrentOffset, $intMaxRowsPerPage)
	{
		// TODO! return details relating to First, Previous, Current, Next, and Final pages, and the first and last records of the current page
		
		// Note that this is a very generic function, and calculates everything based on $intTotalRows, $intCurrentOffset, $intPageRowLimit, so it should probably go in
		// lib/framework/functions.php
		
	}

	private static function _listFor($selQuery, $strFilter, $strOrder, $strOffset, $strLimit)
	{
		// This updates the page info thingy
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("action", "*", "id = <Id>", NULL, 1);
					break;
				case 'selAccountAction':
					$arrColumns = array("id"		=> "id",
										"action_id"	=> "action_id",
										"object_id"	=> "account_id");
					$arrPreparedStatements[$strStatement]	= new StatementSelect("account_action", $arrColumns, "action_id = <action_id>", "id ASC");
					break;
				case 'selServiceAction':
					$arrColumns = array("id"		=> "id",
										"action_id"	=> "action_id",
										"object_id"	=> "service_id");
					$arrPreparedStatements[$strStatement]	= new StatementSelect("service_action", $arrColumns, "action_id = <action_id>", "id ASC");
					break;
				case 'selContactAction':
					$arrColumns = array("id"		=> "id",
										"action_id"	=> "action_id",
										"object_id"	=> "contact_id");
					$arrPreparedStatements[$strStatement]	= new StatementSelect("contact_action", $arrColumns, "action_id = <action_id>", "id ASC");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("action");
					break;
				case 'insAccountAction':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("account_action");
					break;
				case 'insServiceAction':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("service_action");
					break;
				case 'insContactAction':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("contact_action");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					throw new Exception("Cannot modify ". __CLASS__ ." objects, once they have been created");
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
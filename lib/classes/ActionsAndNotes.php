<?php
//----------------------------------------------------------------------------//
// ActionsAndNotes
//----------------------------------------------------------------------------//
/**
 * ActionsAndNotes
 *
 * Encapsulates the ActionsAndNotes retrieval functionality
 *
 * Encapsulates the ActionsAndNotes retrieval functionality
 * Primarily handles searches for all Actions And Notes for a given Account, Service or Contact
 *
 * @class	ActionsAndNotes
 */
class ActionsAndNotes
{	
	const TYPE_CONSTRAINT_ALL			= 'ALL';
	const TYPE_CONSTRAINT_NOTES_ONLY	= 'NOTES_ONLY';
	const TYPE_CONSTRAINT_ACTIONS_ONLY	= 'ACTIONS_ONLY';
	
	const LOGGED_BY_CONSTRAINT_ANYONE			= 'ANYONE';
	const LOGGED_BY_CONSTRAINT_MANUAL_ONLY		= 'MANUAL_ONLY';
	const LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY	= 'AUTOMATIC_ONLY';
	
	const TYPE_NOTE		= 'NOTE';
	const TYPE_ACTION	= 'ACTION';

	// This will store the pagination details of the last call to searchFor
	private static $lastSearchPaginationDetails = null;

	public static function getLastSearchPaginationDetails()
	{
		return self::$lastSearchPaginationDetails;
	}

	/**
	 * searchFor
	 *
	 * Conducts a search for Actions and Notes, 
	 * 
	 * @param	int		$intAATContextId					Action Association Type id representing the context of the search (ie. ACTION_ASSOCIATION_TYPE_ACCOUNT will return Actions and Notes associated with an account)
	 * @param	int		$intAATContextReferenceId			Reference Id specific to the context.  For example if $intAATContextId == ACTION_ASSOCIATION_TYPE_ACCOUNT then $intAATContextReferenceId should be an account id
	 * 														if $intAATContextId == ACTION_ASSOCIATION_TYPE_SERVICE, then $intAATContextReferenceId should be a service id, and the function will retrieve all Actions and Notes
	 * 														relating to $intAATContextReferenceId as well as those relating to any other Service Ids that model the same service on the account that $intAATContextReferenceId belongs to
	 * @param	bool	$bolIncludeAllRelatableAATTypes		If TRUE then all object relatable to $intAATContextReferenceId will also be retrieved.  For example if $intAATContextId == ACTION_ASSOCIATION_TYPE_ACCOUNT
	 * 														and $bolIncludeAllRelatableAATTypes == TRUE, then the function will retrieve all actions/notes directly relating to the account in question, plus all actions/notes
	 * 														relating to any of the account's services or any of the account's contacts
	 * @param	mix		$typeConstraint						defines a constraint as what types of Actions or Notes to return.  Valid values are any ActionType id, or any of the TYPE_CONSTRAINT_ constants defined in this class
	 * @param	mix		$loggedByConstraint					defines a constraint as to who logged the actions or note.  Valid values are any of the LOGGED_BY_CONSTRAINT_ constants defined in this class
	 * @param	int		[ intMaxRecordsPerPage ]			defaults to NULL meaning no limits will be applied to the search
	 * 														The maximum number of Actions / Notes to return
	 * @param	int		[ intPageOffset]					Defaults to NULL.  Only applicable if intMaxRecordsPerPage has been specifi
	 *
	 * @return	array of Action objects and Note objects, sorted in descending order of their creation timestamp
	 * @method
	 */
	public static function searchFor($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage=NULL, $intPageOffset=NULL)
	{
		$objQueries = self::_getQueries($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage, $intPageOffset); 
		
		$strSearchQuery	= $objQueries->search;
		$strCountQuery	= $objQueries->count;
		
		$qryQuery = new Query();
		
		$result = $qryQuery->Execute($strSearchQuery);
		if ($result === false)
		{
			throw new Exception("Error retrieving actions and notes - ". $qryQuery->Error() ." - Query: $strSearchQuery");
		}
		
		$arrActionsAndNotes = array();
		while ($arrRecord = $result->fetch_assoc())
		{
			if ($arrRecord['record_type'] == self::TYPE_NOTE)
			{
				$arrActionsAndNotes[] = new Note(array("Id"=> $arrRecord['id']), true);
			}
			elseif ($arrRecord['record_type'] == self::TYPE_ACTION)
			{
				$arrActionsAndNotes[] = new Action(array("id"=> $arrRecord['id']), true);
			}
			else
			{
				throw new Exception(__METHOD__ ." - Retrieved record with record_type = {$arrRecord['record_type']}");
			}
		}
		
		if ($intMaxRecordsPerPage == NULL)
		{
			// Don't bother calulating pagination details
			self::$lastSearchPaginationDetails = null;
		}
		else
		{
			$result = $qryQuery->Execute($strCountQuery);
			if ($result === false)
			{
				throw new Exception("Error retrieving pagination details for the last actions and notes search - ". $qryQuery->Error() ." - Query: $strCountQuery");
			}
			
			// This query can potentially return 2 records which must be added because one will be the total_record_count of notes, and the other will be the total_record_count of actions
			$intTotalRecordCount = 0;
			while($arrResult = $result->fetch_assoc())
			{
				$intTotalRecordCount += $arrResult['total_record_count'];
			}
			
			self::$lastSearchPaginationDetails = new PaginationDetails($intTotalRecordCount, $intMaxRecordsPerPage, intval($intPageOffset));
		}
		
		return $arrActionsAndNotes;
	}

	private static function _getQueries($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage=NULL, $intPageOffset=NULL)
	{
		switch ($intAATContextId)
		{
			case ACTION_ASSOCIATION_TYPE_ACCOUNT:
				$objQueries = self::_getQueriesForAccountContext($intAATContextReferenceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage, $intPageOffset);
				break;
				
			case ACTION_ASSOCIATION_TYPE_SERVICE:
				$objQueries = self::_getQueriesForServiceContext($intAATContextReferenceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage, $intPageOffset);
				break;
				
			case ACTION_ASSOCIATION_TYPE_CONTACT:
				$objQueries = self::_getQueriesForContactContext($intAATContextReferenceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage, $intPageOffset);
				break;
				
			default:
				throw new Exception("Unkown Action Association Type (id: $intAATContextId)");
		}
		return $objQueries;
	}
	
	// This will return t
	private static function _getQueriesForAccountContext($intAccountId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage=NULL, $intPageOffset=NULL)
	{
		$objAccount = Account::getForId($intAccountId);
		if ($objAccount === NULL)
		{
			throw new Exception("Can't find account with id: $intAccountId");
		}
		
		$arrContactIds = array();
		$arrServiceIds = array();
		
		if ($bolIncludeAllRelatableAATTypes)
		{
			// Retrieve the ids of all Contacts and Services related to the Account
			$arrContactIds = $objAccount->getContacts();
			$arrServiceIds = $objAccount->getAllServiceRecords();
		}
		
		$strContactIds	= (count($arrContactIds))? implode(", ", $arrContactIds) : NULL;
		$strServiceIds	= (count($arrServiceIds))? implode(", ", $arrServiceIds) : NULL;

		// The SELECT query for the Notes
		$strColumnsForNoteSearchQuery	= "'". ActionsAndNotes::TYPE_NOTE ."' AS record_type, Id AS id, Datetime AS created_on";
		$strColumnsForNoteCountQuery	= "COUNT(Id) AS total_record_count";
		
		$strNoteQueryFromClause = "Note";
		
		$arrORedParts = array();
		$arrORedParts[] = "Account = $intAccountId";
		if ($strContactIds !== NULL)
		{
			$arrORedParts[] = "Contact IN ($strContactIds)";
		}
		if ($strServiceIds !== NULL)
		{
			$arrORedParts[] = "Service IN ($strServiceIds)";
		}
		
		// Note.AccountGroup is included because it is indexed where as Note.Account isn't
		$strNoteQueryWhereClause = "AccountGroup = {$objAccount->accountGroup} AND (". implode(" OR ", $arrORedParts) .")";

		// The SELECT query for the Actions
		$strColumnsForActionSearchQuery	= "'". ActionsAndNotes::TYPE_ACTION ."' AS record_type, action.id AS id, created_on";
		$strColumnsForActionCountQuery	= "COUNT(action.id) AS total_record_count";

		$arrWherePartsForReleventActionTempTable = array();
		$arrWherePartsForReleventActionTempTable[] = "account_action.account_id = {$intAccountId}";
		if ($strContactIds !== NULL)
		{
			$arrWherePartsForReleventActionTempTable[] = "contact_action.contact_id IN ($strContactIds)";
		}
		if ($strServiceIds !== NULL)
		{
			$arrWherePartsForReleventActionTempTable[] = "service_action.service_id IN ($strServiceIds)";
		}
		
		$strWhereClauseForReleventActionTempTable = implode(" OR ", $arrWherePartsForReleventActionTempTable);
		
		$strActionQueryFromClause = "
action 
INNER JOIN (
	SELECT DISTINCT action.id AS id
	FROM action
	LEFT JOIN account_action ON action.id = account_action.action_id
	LEFT JOIN service_action ON action.id = service_action.action_id
	LEFT JOIN contact_action ON action.id = contact_action.action_id
	WHERE $strWhereClauseForReleventActionTempTable
) AS relevent_action ON action.id = relevent_action.id";
		
		$strActionQueryWhereClause = "TRUE";
		
		// Handle loggedByConstraint
		switch ($loggedByConstraint)
		{
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_MANUAL_ONLY:
				$strLoggedByConstraintForNotes		= "AND NOT (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions	= "AND action.created_by_employee_id != ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY:
				$strLoggedByConstraintForNotes		= "AND (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions	= "AND action.created_by_employee_id = ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_ANYONE:
			default:
				// Don't have to do anything
				$strLoggedByConstraintForNotes		= "";
				$strLoggedByConstraintForActions	= "";
				break;
		}
		
		// Handle the typeConstraint for when it's a single action type
		$strActionTypeConstraint = (is_int($typeConstraint))? "AND action.action_type_id = $typeConstraint" : NULL;
		
		$bolIncludeNotes	= true;
		$bolIncludeActions	= true;
		if ($strActionTypeConstraint !== NULL)
		{
			// The type constraint is for a single action type
			$bolIncludeNotes	= false;
			$bolIncludeActions	= true;
		}
		else
		{
			switch ($typeConstraint)
			{
				case ActionsAndNotes::TYPE_CONSTRAINT_NOTES_ONLY:
					$bolIncludeNotes	= true;
					$bolIncludeActions	= false;
					break;
				
				case ActionsAndNotes::TYPE_CONSTRAINT_ACTIONS_ONLY:
					$bolIncludeNotes	= false;
					$bolIncludeActions	= true;
					break;
					
				case ActionsAndNotes::TYPE_CONSTRAINT_ALL:
				default:
					$bolIncludeNotes	= true;
					$bolIncludeActions	= true;
					break;
				
			}
		}
		
		// Now Build the queries
		$strSearchQuery	= "";
		$strCountQuery	= "";
		
		if ($bolIncludeNotes)
		{
			$strSearchQuery .=  "SELECT $strColumnsForNoteSearchQuery ".
								"FROM $strNoteQueryFromClause ".
								"WHERE $strNoteQueryWhereClause $strLoggedByConstraintForNotes ";
			$strCountQuery .=	"SELECT $strColumnsForNoteCountQuery ".
								"FROM $strNoteQueryFromClause ".
								"WHERE $strNoteQueryWhereClause $strLoggedByConstraintForNotes ";
		}
		if ($bolIncludeActions)
		{
			if ($bolIncludeNotes)
			{
				$strSearchQuery .= " UNION ";
				$strCountQuery .= " UNION ";
			}
			
			$strSearchQuery .=  "SELECT $strColumnsForActionSearchQuery ".
								"FROM $strActionQueryFromClause ".
								"WHERE $strActionQueryWhereClause $strLoggedByConstraintForActions $strActionTypeConstraint ";
			$strCountQuery .=	"SELECT $strColumnsForActionCountQuery ".
								"FROM $strActionQueryFromClause ".
								"WHERE $strActionQueryWhereClause $strLoggedByConstraintForActions $strActionTypeConstraint ";
			
		}
		
		$strSearchQuery .= "ORDER BY created_on DESC ";
		
		$intMaxRecordsPerPage = intval($intMaxRecordsPerPage);
		$intPageOffset = intval($intPageOffset);
		if ($intMaxRecordsPerPage > 0)
		{
			// Do page stuff
			$strSearchQuery .= "LIMIT $intMaxRecordsPerPage OFFSET $intPageOffset";
		}
		
		$objQueries = new stdClass();
		$objQueries->search	= $strSearchQuery;
		$objQueries->count	= $strCountQuery;
		return $objQueries;
	}
	
	private static function _getQueriesForServiceContext($intServiceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage=NULL, $intPageOffset=NULL)
	{
		$objService = Service::getForId($intServiceId);
		
		// Retrieve A list of all Service records that have been used to model this servie, while on this account
		$arrServiceIds = ConvertToSimpleArray(Service::getFNNInstances($objService->fNN, $objService->account), "Id");
		
		if ($bolIncludeAllRelatableAATTypes)
		{
			// Don't do anything
		}
		
		$strServiceIds	= implode(", ", $arrServiceIds);

		// The SELECT query for the Notes
		$strColumnsForNoteSearchQuery	= "'". ActionsAndNotes::TYPE_NOTE ."' AS record_type, Id AS id, Datetime AS created_on";
		$strColumnsForNoteCountQuery	= "COUNT(Id) AS total_record_count";
		
		$strNoteQueryFromClause = "Note";
		
		$strNoteQueryWhereClause = "Service IN ($strServiceIds)";

		// The SELECT query for the Actions
		$strColumnsForActionSearchQuery	= "'". ActionsAndNotes::TYPE_ACTION ."' AS record_type, action.id AS id, created_on";
		$strColumnsForActionCountQuery	= "COUNT(action.id) AS total_record_count";

		$strActionQueryFromClause = "action INNER JOIN service_action ON action.id = service_action.action_id"; 
		
		$strActionQueryWhereClause = "service_action.service_id IN ($strServiceIds)";
		
		// Handle loggedByConstraint
		switch ($loggedByConstraint)
		{
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_MANUAL_ONLY:
				$strLoggedByConstraintForNotes		= "AND NOT (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions	= "AND action.created_by_employee_id != ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY:
				$strLoggedByConstraintForNotes		= "AND (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions	= "AND action.created_by_employee_id = ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_ANYONE:
			default:
				// Don't have to do anything
				$strLoggedByConstraintForNotes		= "";
				$strLoggedByConstraintForActions	= "";
				break;
		}
		
		// Handle the typeConstraint for when it's a single action type
		$strActionTypeConstraint = (is_int($typeConstraint))? "AND action.action_type_id = $typeConstraint" : NULL;
		
		$bolIncludeNotes	= true;
		$bolIncludeActions	= true;
		if ($strActionTypeConstraint !== NULL)
		{
			// The type constraint is for a single action type
			$bolIncludeNotes	= false;
			$bolIncludeActions	= true;
		}
		else
		{
			switch ($typeConstraint)
			{
				case ActionsAndNotes::TYPE_CONSTRAINT_NOTES_ONLY:
					$bolIncludeNotes	= true;
					$bolIncludeActions	= false;
					break;
				
				case ActionsAndNotes::TYPE_CONSTRAINT_ACTIONS_ONLY:
					$bolIncludeNotes	= false;
					$bolIncludeActions	= true;
					break;
					
				case ActionsAndNotes::TYPE_CONSTRAINT_ALL:
				default:
					$bolIncludeNotes	= true;
					$bolIncludeActions	= true;
					break;
				
			}
		}
		
		// Now Build the queries
		$strSearchQuery	= "";
		$strCountQuery	= "";
		
		if ($bolIncludeNotes)
		{
			$strSearchQuery .=  "SELECT $strColumnsForNoteSearchQuery ".
								"FROM $strNoteQueryFromClause ".
								"WHERE $strNoteQueryWhereClause $strLoggedByConstraintForNotes ";
			$strCountQuery .=	"SELECT $strColumnsForNoteCountQuery ".
								"FROM $strNoteQueryFromClause ".
								"WHERE $strNoteQueryWhereClause $strLoggedByConstraintForNotes ";
		}
		if ($bolIncludeActions)
		{
			if ($bolIncludeNotes)
			{
				$strSearchQuery .= " UNION ";
				$strCountQuery .= " UNION ";
			}
			
			$strSearchQuery .=  "SELECT $strColumnsForActionSearchQuery ".
								"FROM $strActionQueryFromClause ".
								"WHERE $strActionQueryWhereClause $strLoggedByConstraintForActions $strActionTypeConstraint ";
			$strCountQuery .=	"SELECT $strColumnsForActionCountQuery ".
								"FROM $strActionQueryFromClause ".
								"WHERE $strActionQueryWhereClause $strLoggedByConstraintForActions $strActionTypeConstraint ";
			
		}
		
		$strSearchQuery .= "ORDER BY created_on DESC ";
		
		$intMaxRecordsPerPage = intval($intMaxRecordsPerPage);
		$intPageOffset = intval($intPageOffset);
		if ($intMaxRecordsPerPage > 0)
		{
			// Do page stuff
			$strSearchQuery .= "LIMIT $intMaxRecordsPerPage OFFSET $intPageOffset";
		}
		
		$objQueries = new stdClass();
		$objQueries->search	= $strSearchQuery;
		$objQueries->count	= $strCountQuery;
		return $objQueries;
	}
	
	private static function _getQueriesForContactContext($intContactId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage=NULL, $intPageOffset=NULL)
	{
		if ($bolIncludeAllRelatableAATTypes)
		{
			// Don't do anything
		}
		
		// The SELECT query for the Notes
		$strColumnsForNoteSearchQuery	= "'". ActionsAndNotes::TYPE_NOTE ."' AS record_type, Id AS id, Datetime AS created_on";
		$strColumnsForNoteCountQuery	= "COUNT(Id) AS total_record_count";
		
		$strNoteQueryFromClause = "Note";
		
		$strNoteQueryWhereClause = "Contact = $intContactId";

		// The SELECT query for the Actions
		$strColumnsForActionSearchQuery	= "'". ActionsAndNotes::TYPE_ACTION ."' AS record_type, action.id AS id, created_on";
		$strColumnsForActionCountQuery	= "COUNT(action.id) AS total_record_count";

		$strActionQueryFromClause = "action INNER JOIN contact_action ON action.id = contact_action.action_id"; 
		
		$strActionQueryWhereClause = "contact_action.contact_id = $intContactId";
		
		// Handle loggedByConstraint
		switch ($loggedByConstraint)
		{
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_MANUAL_ONLY:
				$strLoggedByConstraintForNotes		= "AND NOT (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions	= "AND action.created_by_employee_id != ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY:
				$strLoggedByConstraintForNotes		= "AND (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions	= "AND action.created_by_employee_id = ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_ANYONE:
			default:
				// Don't have to do anything
				$strLoggedByConstraintForNotes		= "";
				$strLoggedByConstraintForActions	= "";
				break;
		}
		
		// Handle the typeConstraint for when it's a single action type
		$strActionTypeConstraint = (is_int($typeConstraint))? "AND action.action_type_id = $typeConstraint" : NULL;
		
		$bolIncludeNotes	= true;
		$bolIncludeActions	= true;
		if ($strActionTypeConstraint !== NULL)
		{
			// The type constraint is for a single action type
			$bolIncludeNotes	= false;
			$bolIncludeActions	= true;
		}
		else
		{
			switch ($typeConstraint)
			{
				case ActionsAndNotes::TYPE_CONSTRAINT_NOTES_ONLY:
					$bolIncludeNotes	= true;
					$bolIncludeActions	= false;
					break;
				
				case ActionsAndNotes::TYPE_CONSTRAINT_ACTIONS_ONLY:
					$bolIncludeNotes	= false;
					$bolIncludeActions	= true;
					break;
					
				case ActionsAndNotes::TYPE_CONSTRAINT_ALL:
				default:
					$bolIncludeNotes	= true;
					$bolIncludeActions	= true;
					break;
				
			}
		}
		
		// Now Build the queries
		$strSearchQuery	= "";
		$strCountQuery	= "";
		
		if ($bolIncludeNotes)
		{
			$strSearchQuery .=  "SELECT $strColumnsForNoteSearchQuery ".
								"FROM $strNoteQueryFromClause ".
								"WHERE $strNoteQueryWhereClause $strLoggedByConstraintForNotes ";
			$strCountQuery .=	"SELECT $strColumnsForNoteCountQuery ".
								"FROM $strNoteQueryFromClause ".
								"WHERE $strNoteQueryWhereClause $strLoggedByConstraintForNotes ";
		}
		if ($bolIncludeActions)
		{
			if ($bolIncludeNotes)
			{
				$strSearchQuery .= " UNION ";
				$strCountQuery .= " UNION ";
			}
			
			$strSearchQuery .=  "SELECT $strColumnsForActionSearchQuery ".
								"FROM $strActionQueryFromClause ".
								"WHERE $strActionQueryWhereClause $strLoggedByConstraintForActions $strActionTypeConstraint ";
			$strCountQuery .=	"SELECT $strColumnsForActionCountQuery ".
								"FROM $strActionQueryFromClause ".
								"WHERE $strActionQueryWhereClause $strLoggedByConstraintForActions $strActionTypeConstraint ";
			
		}
		
		$strSearchQuery .= "ORDER BY created_on DESC ";
		
		$intMaxRecordsPerPage = intval($intMaxRecordsPerPage);
		$intPageOffset = intval($intPageOffset);
		if ($intMaxRecordsPerPage > 0)
		{
			// Do page stuff
			$strSearchQuery .= "LIMIT $intMaxRecordsPerPage OFFSET $intPageOffset";
		}
		
		$objQueries = new stdClass();
		$objQueries->search	= $strSearchQuery;
		$objQueries->count	= $strCountQuery;
		return $objQueries;
	}
	
	

}
?>
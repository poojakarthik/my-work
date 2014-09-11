<?php
class ActionsAndNotes {	
	const DEBUG_LOGGING = true;

	const TYPE_CONSTRAINT_ALL = 'ALL';
	const TYPE_CONSTRAINT_NOTES_ONLY = 'NOTES_ONLY';
	const TYPE_CONSTRAINT_ACTIONS_ONLY = 'ACTIONS_ONLY';
	
	const LOGGED_BY_CONSTRAINT_ANYONE = 'ANYONE';
	const LOGGED_BY_CONSTRAINT_MANUAL_ONLY = 'MANUAL_ONLY';
	const LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY = 'AUTOMATIC_ONLY';
	
	const TYPE_NOTE = 'NOTE';
	const TYPE_ACTION = 'ACTION';

	// This will store the pagination details of the last call to searchFor
	private static $lastSearchPaginationDetails = null;

	public static function getLastSearchPaginationDetails() {
		return self::$lastSearchPaginationDetails;
	}

	public static function searchFor($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $sSearchString, $intMaxRecordsPerPage=null, $intPageOffset=null) {
		$objQueries = self::_getQueries($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, self::_extractSearchTokens($sSearchString), $intMaxRecordsPerPage, $intPageOffset); 
		
		$strSearchQuery = $objQueries->search;
		//throw new Exception($strSearchQuery);
		$strCountQuery = $objQueries->count;
		
		$qryQuery = new Query();
		
		$result = $qryQuery->Execute($strSearchQuery);
		if ($result === false) {
			throw new Exception_Database("Error retrieving actions and notes - ". $qryQuery->Error() ." - Query: {$strSearchQuery}");
		}
		
		$arrActionsAndNotes = array();
		while ($arrRecord = $result->fetch_assoc()) {
			if ($arrRecord['record_type'] == self::TYPE_NOTE) {
				$arrActionsAndNotes[] = new Note(array("Id"=> $arrRecord['id']), true);
			} elseif ($arrRecord['record_type'] == self::TYPE_ACTION) {
				$arrActionsAndNotes[] = new Action(array("id"=> $arrRecord['id']), true);
			} else {
				throw new Exception(__METHOD__ ." - Retrieved record with record_type = {$arrRecord['record_type']}");
			}
		}
		/*if (!count($arrActionsAndNotes)) {
			throw new Exception($strSearchQuery);
		}*/
		
		if ($intMaxRecordsPerPage == null) {
			// Don't bother calulating pagination details
			self::$lastSearchPaginationDetails = null;
		} else {
			$result = $qryQuery->Execute($strCountQuery);
			if ($result === false) {
				throw new Exception_Database("Error retrieving pagination details for the last actions and notes search - ". $qryQuery->Error() ." - Query: {$strCountQuery}");
			}
			
			// This query can potentially return 2 records which must be added because one will be the total_record_count of notes, and the other will be the total_record_count of actions
			$intTotalRecordCount = 0;
			while($arrResult = $result->fetch_assoc()) {
				$intTotalRecordCount += $arrResult['total_record_count'];
			}
			
			self::$lastSearchPaginationDetails = new PaginationDetails($intTotalRecordCount, $intMaxRecordsPerPage, (int)$intPageOffset);
		}
		
		return $arrActionsAndNotes;
	}

	private static function _getQueries($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage=null, $intPageOffset=null) {
		switch ($intAATContextId) {
			case ACTION_ASSOCIATION_TYPE_ACCOUNT:
				$objQueries = self::_getQueriesForAccountContext($intAATContextReferenceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage, $intPageOffset);
				break;
				
			case ACTION_ASSOCIATION_TYPE_SERVICE:
				$objQueries = self::_getQueriesForServiceContext($intAATContextReferenceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage, $intPageOffset);
				break;
				
			case ACTION_ASSOCIATION_TYPE_CONTACT:
				$objQueries = self::_getQueriesForContactContext($intAATContextReferenceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage, $intPageOffset);
				break;
				
			default:
				throw new Exception("Unkown Action Association Type (id: {$intAATContextId})");
		}
		return $objQueries;
	}
	
	// This will return t
	private static function _getQueriesForAccountContext($intAccountId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage=null, $intPageOffset=null) {
		$objAccount = Account::getForId($intAccountId);
		if ($objAccount === null) {
			throw new Exception("Can't find account with id: {$intAccountId}");
		}
		
		$arrContactIds = array();
		$arrServiceIds = array();
		
		if ($bolIncludeAllRelatableAATTypes) {
			// Retrieve the ids of all Contacts and Services related to the Account
			$arrContactIds = $objAccount->getContacts();
			$arrServiceIds = $objAccount->getAllServiceRecords();
		}
		
		$strContactIds	= (count($arrContactIds)) ? implode(", ", $arrContactIds) : null;
		$strServiceIds	= (count($arrServiceIds)) ? implode(", ", $arrServiceIds) : null;

		// The SELECT query for the Notes
		$strColumnsForNoteSearchQuery	= "'". ActionsAndNotes::TYPE_NOTE ."' AS record_type, Id AS id, Datetime AS created_on";
		$strColumnsForNoteCountQuery	= "COUNT(Id) AS total_record_count";
		
		$strNoteQueryFromClause = "Note";
		
		$arrORedParts = array();
		$arrORedParts[] = "Account = {$intAccountId}";
		if ($strContactIds !== null) {
			$arrORedParts[] = "Contact IN ({$strContactIds})";
		}
		if ($strServiceIds !== null) {
			$arrORedParts[] = "Service IN ({$strServiceIds})";
		}
		
		// Note.AccountGroup is included because it is indexed where as Note.Account isn't
		$strNoteQueryWhereClause = "AccountGroup = {$objAccount->accountGroup} AND (". implode(" OR ", $arrORedParts) .")";

		// The SELECT query for the Actions
		$strColumnsForActionSearchQuery	= "'". ActionsAndNotes::TYPE_ACTION ."' AS record_type, action.id AS id, created_on";
		$strColumnsForActionCountQuery	= "COUNT(action.id) AS total_record_count";

		$arrActionSubQueries = array();
		
		// We are always retrieving actions related to the account
		$arrActionSubQueries[] = "SELECT action_id AS id FROM account_action WHERE account_id = {$intAccountId}";

		if ($strContactIds !== null) {
			$arrActionSubQueries[] = "SELECT action_id AS id FROM contact_action WHERE contact_id IN ({$strContactIds})";
		}
		if ($strServiceIds !== null) {
			$arrActionSubQueries[] = "SELECT action_id AS id FROM service_action WHERE service_id IN ({$strServiceIds})";
		}
		
		// We want the union of the results of the subqueries
		$strActionSubQueries = implode("\n\t\tUNION\n\t\t", $arrActionSubQueries);
		
		// This from clause will retrieve the distinct id of all the actions associated with the account as well as any actions associated with services of the account, and
		// all actions associated with any contacts which are associated with the account (It's quite a doosey)
		$strActionQueryFromClause = "
action 
INNER JOIN (
	SELECT DISTINCT id
	FROM (
		{$strActionSubQueries}
	) AS non_distinct_relevent_action
) AS distinct_relevent_action ON action.id = distinct_relevent_action.id";
		
		$strActionQueryWhereClause = "TRUE";
		
		// Handle loggedByConstraint
		switch ($loggedByConstraint) {
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_MANUAL_ONLY:
				$strLoggedByConstraintForNotes = "AND NOT (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS null OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions = "AND action.created_by_employee_id != ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY:
				$strLoggedByConstraintForNotes = "AND (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS null OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions = "AND action.created_by_employee_id = ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_ANYONE:
			default:
				// Don't have to do anything
				$strLoggedByConstraintForNotes = "";
				$strLoggedByConstraintForActions = "";
				break;
		}
		
		// Handle the typeConstraint for when it's a single action type
		$strActionTypeConstraint = (is_int($typeConstraint))? "AND action.action_type_id = {$typeConstraint}" : null;

		// Handle the Search contraints
		$sSearchActionIdConstraint = '(action.id IN ('.(count($aSearchTokens['action_id']) ? implode(', ', $aSearchTokens['action_id']) : 'NULL').'))';
		$sSearchNoteIdConstraint = '(Note.id IN ('.(count($aSearchTokens['note_id']) ? implode(', ', $aSearchTokens['note_id']) : 'NULL').'))';
		$aSearchActionKeywordContraints = array();
		$aSearchNoteKeywordContraints = array();
		foreach ($aSearchTokens['keyword'] as $sKeyword) {
			$aSearchActionKeywordContraints[] = "action.details LIKE ".preg_replace('/(?<!^[\'"])%(?![\'"]$)/', '\\\\%', Query::prepareByPHPType('%'.(string)$sKeyword.'%'));
			$aSearchNoteKeywordContraints[] = "Note.Note LIKE ".preg_replace('/(?<!^[\'"])%(?![\'"]$)/', '\\\\%', Query::prepareByPHPType('%'.(string)$sKeyword.'%'));
		}
		$sSearchActionKeywordConstraints = count($aSearchActionKeywordContraints) ? '('.implode(' OR ', $aSearchActionKeywordContraints).')' : '0';
		$sSearchNoteKeywordContraints = count($aSearchNoteKeywordContraints) ? '('.implode(' OR ', $aSearchNoteKeywordContraints).')' : '0';
		$sSearchActionConstraint = "(".(int)!count($aSearchTokens['keyword'])." OR ({$sSearchActionIdConstraint} OR {$sSearchActionKeywordConstraints}))";
		$sSearchNoteConstraint = "(".(int)!count($aSearchTokens['keyword'])." OR ({$sSearchNoteIdConstraint} OR {$sSearchNoteKeywordContraints}))";
		
		$bolIncludeNotes = true;
		$bolIncludeActions = true;
		if ($strActionTypeConstraint !== null) {
			// The type constraint is for a single action type
			$bolIncludeNotes = false;
			$bolIncludeActions = true;
		} else {
			switch ($typeConstraint) {
				case ActionsAndNotes::TYPE_CONSTRAINT_NOTES_ONLY:
					$bolIncludeNotes = true;
					$bolIncludeActions = false;
					break;
				
				case ActionsAndNotes::TYPE_CONSTRAINT_ACTIONS_ONLY:
					$bolIncludeNotes = false;
					$bolIncludeActions = true;
					break;
					
				case ActionsAndNotes::TYPE_CONSTRAINT_ALL:
				default:
					$bolIncludeNotes = true;
					$bolIncludeActions = true;
					break;
			}
		}
		
		// Now Build the queries
		$strSearchQuery = "";
		$strCountQuery = "";
		
		if ($bolIncludeNotes) {
			$strSearchQuery .= "
				SELECT {$strColumnsForNoteSearchQuery}
				FROM {$strNoteQueryFromClause}
				WHERE {$strNoteQueryWhereClause} {$strLoggedByConstraintForNotes} AND {$sSearchNoteConstraint}
			";
			$strCountQuery .="
				SELECT {$strColumnsForNoteCountQuery}
				FROM {$strNoteQueryFromClause}
				WHERE {$strNoteQueryWhereClause} {$strLoggedByConstraintForNotes} AND {$sSearchNoteConstraint}
			";
		}
		if ($bolIncludeActions) {
			if ($bolIncludeNotes) {
				$strSearchQuery .= "\nUNION\n";
				$strCountQuery .= "\nUNION\n";
			}
			
			$strSearchQuery .= "
				SELECT {$strColumnsForActionSearchQuery}
				FROM {$strActionQueryFromClause}
				WHERE {$strActionQueryWhereClause} {$strLoggedByConstraintForActions} {$strActionTypeConstraint} AND {$sSearchActionConstraint}
			";
			$strCountQuery .= "
				SELECT {$strColumnsForActionCountQuery}
				FROM {$strActionQueryFromClause}
				WHERE {$strActionQueryWhereClause} {$strLoggedByConstraintForActions} {$strActionTypeConstraint} AND {$sSearchActionConstraint}
			";
		}
		
		$strSearchQuery .= "ORDER BY created_on DESC, id DESC ";
		
		$intMaxRecordsPerPage = (int)$intMaxRecordsPerPage;
		$intPageOffset = (int)$intPageOffset;
		if ($intMaxRecordsPerPage > 0) {
			// Do page stuff
			$strSearchQuery .= "LIMIT {$intMaxRecordsPerPage} OFFSET {$intPageOffset}";
		}

		$objQueries = new stdClass();
		$objQueries->search	= $strSearchQuery;
		$objQueries->count	= $strCountQuery;
		return $objQueries;
	}
	
	private static function _getQueriesForServiceContext($intServiceId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage=null, $intPageOffset=null) {
		$objService = Service::getForId($intServiceId);
		
		// Retrieve A list of all Service records that have been used to model this servie, while on this account
		$arrServiceIds = ConvertToSimpleArray(Service::getFNNInstances($objService->fNN, $objService->account), "Id");
		
		if ($bolIncludeAllRelatableAATTypes) {
			// Don't do anything
		}
		
		$strServiceIds	= implode(", ", $arrServiceIds);

		// The SELECT query for the Notes
		$strColumnsForNoteSearchQuery	= "'". ActionsAndNotes::TYPE_NOTE ."' AS record_type, Id AS id, Datetime AS created_on";
		$strColumnsForNoteCountQuery	= "COUNT(Id) AS total_record_count";
		
		$strNoteQueryFromClause = "Note";
		
		$strNoteQueryWhereClause = "Service IN ({$strServiceIds})";

		// The SELECT query for the Actions
		$strColumnsForActionSearchQuery	= "'". ActionsAndNotes::TYPE_ACTION ."' AS record_type, action.id AS id, created_on";
		$strColumnsForActionCountQuery	= "COUNT(action.id) AS total_record_count";

		$strActionQueryFromClause = "action INNER JOIN service_action ON action.id = service_action.action_id"; 
		
		$strActionQueryWhereClause = "service_action.service_id IN ({$strServiceIds})";
		
		// Handle loggedByConstraint
		switch ($loggedByConstraint) {
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_MANUAL_ONLY:
				$strLoggedByConstraintForNotes = "AND NOT (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions = "AND action.created_by_employee_id != ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY:
				$strLoggedByConstraintForNotes = "AND (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions = "AND action.created_by_employee_id = ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_ANYONE:
			default:
				// Don't have to do anything
				$strLoggedByConstraintForNotes = "";
				$strLoggedByConstraintForActions = "";
				break;
		}
		
		// Handle the typeConstraint for when it's a single action type
		$strActionTypeConstraint = (is_int($typeConstraint)) ? "AND action.action_type_id = {$typeConstraint}" : null;

		// Handle the Search contraints
		$sSearchActionIdConstraint = '(action.id IN ('.(count($aSearchTokens['action_id']) ? implode(', ', $aSearchTokens['action_id']) : 'NULL').'))';
		$sSearchNoteIdConstraint = '(Note.id IN ('.(count($aSearchTokens['note_id']) ? implode(', ', $aSearchTokens['note_id']) : 'NULL').'))';
		$aSearchActionKeywordContraints = array();
		$aSearchNoteKeywordContraints = array();
		foreach ($aSearchTokens['keyword'] as $sKeyword) {
			$aSearchActionKeywordContraints[] = "action.details LIKE ".preg_replace('/(?<!^[\'"])%(?![\'"]$)/', '\\\\%', Query::prepareByPHPType('%'.(string)$sKeyword.'%'));
			$aSearchNoteKeywordContraints[] = "Note.Note LIKE ".preg_replace('/(?<!^[\'"])%(?![\'"]$)/', '\\\\%', Query::prepareByPHPType('%'.(string)$sKeyword.'%'));
		}
		$sSearchActionKeywordConstraints = count($aSearchActionKeywordContraints) ? '('.implode(' OR ', $aSearchActionKeywordContraints).')' : '0';
		$sSearchNoteKeywordContraints = count($aSearchNoteKeywordContraints) ? '('.implode(' OR ', $aSearchNoteKeywordContraints).')' : '0';
		$sSearchActionConstraint = "(".(int)!count($aSearchTokens['keyword'])." OR ({$sSearchActionIdConstraint} OR {$sSearchActionKeywordConstraints}))";
		$sSearchNoteConstraint = "(".(int)!count($aSearchTokens['keyword'])." OR ({$sSearchNoteIdConstraint} OR {$sSearchNoteKeywordContraints}))";
		
		$bolIncludeNotes = true;
		$bolIncludeActions = true;
		if ($strActionTypeConstraint !== null) {
			// The type constraint is for a single action type
			$bolIncludeNotes = false;
			$bolIncludeActions = true;
		} else {
			switch ($typeConstraint) {
				case ActionsAndNotes::TYPE_CONSTRAINT_NOTES_ONLY:
					$bolIncludeNotes = true;
					$bolIncludeActions = false;
					break;
				
				case ActionsAndNotes::TYPE_CONSTRAINT_ACTIONS_ONLY:
					$bolIncludeNotes = false;
					$bolIncludeActions = true;
					break;
					
				case ActionsAndNotes::TYPE_CONSTRAINT_ALL:
				default:
					$bolIncludeNotes = true;
					$bolIncludeActions = true;
					break;
			}
		}
		
		// Now Build the queries
		$strSearchQuery = "";
		$strCountQuery = "";
		
		if ($bolIncludeNotes) {
			$strSearchQuery .= "
				SELECT {$strColumnsForNoteSearchQuery}
				FROM {$strNoteQueryFromClause}
				WHERE {$strNoteQueryWhereClause} {$strLoggedByConstraintForNotes} AND {$sSearchNoteConstraint}
			";
			$strCountQuery .= "
				SELECT {$strColumnsForNoteCountQuery}
				FROM {$strNoteQueryFromClause}
				WHERE {$strNoteQueryWhereClause} {$strLoggedByConstraintForNotes} AND {$sSearchNoteConstraint}
			";
		}
		if ($bolIncludeActions) {
			if ($bolIncludeNotes) {
				$strSearchQuery .= "\nUNION\n";
				$strCountQuery .= "\nUNION\n";
			}
			
			$strSearchQuery .= "
				SELECT {$strColumnsForActionSearchQuery}
				FROM {$strActionQueryFromClause}
				WHERE {$strActionQueryWhereClause} {$strLoggedByConstraintForActions} {$strActionTypeConstraint} AND {$sSearchActionConstraint}
			";
			$strCountQuery .= "
				SELECT {$strColumnsForActionCountQuery}
				FROM {$strActionQueryFromClause}
				WHERE {$strActionQueryWhereClause} {$strLoggedByConstraintForActions} {$strActionTypeConstraint} AND {$sSearchActionConstraint}
			";
		}
		
		$strSearchQuery .= "ORDER BY created_on DESC, id DESC ";
		
		$intMaxRecordsPerPage = (int)$intMaxRecordsPerPage;
		$intPageOffset = (int)$intPageOffset;
		if ($intMaxRecordsPerPage > 0) {
			// Do page stuff
			$strSearchQuery .= "LIMIT {$intMaxRecordsPerPage} OFFSET {$intPageOffset}";
		}
	
		$objQueries = new stdClass();
		$objQueries->search	= $strSearchQuery;
		$objQueries->count	= $strCountQuery;
		return $objQueries;
	}
	
	private static function _getQueriesForContactContext($intContactId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $aSearchTokens, $intMaxRecordsPerPage=null, $intPageOffset=null) {
		if ($bolIncludeAllRelatableAATTypes) {
			// Don't do anything
		}
		
		// The SELECT query for the Notes
		$strColumnsForNoteSearchQuery = "'". ActionsAndNotes::TYPE_NOTE ."' AS record_type, Id AS id, Datetime AS created_on";
		$strColumnsForNoteCountQuery = "COUNT(Id) AS total_record_count";
		
		$strNoteQueryFromClause = "Note";
		
		$strNoteQueryWhereClause = "Contact = {$intContactId}";

		// The SELECT query for the Actions
		$strColumnsForActionSearchQuery = "'". ActionsAndNotes::TYPE_ACTION ."' AS record_type, action.id AS id, created_on";
		$strColumnsForActionCountQuery = "COUNT(action.id) AS total_record_count";

		$strActionQueryFromClause = "action INNER JOIN contact_action ON action.id = contact_action.action_id"; 
		
		$strActionQueryWhereClause = "contact_action.contact_id = {$intContactId}";
		
		// Handle loggedByConstraint
		switch ($loggedByConstraint) {
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_MANUAL_ONLY:
				$strLoggedByConstraintForNotes = "AND NOT (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions = "AND action.created_by_employee_id != ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY:
				$strLoggedByConstraintForNotes = "AND (NoteType = ". Note::SYSTEM_NOTE_TYPE_ID ." OR Employee IS NULL OR Employee = ". Employee::SYSTEM_EMPLOYEE_ID .")";
				$strLoggedByConstraintForActions = "AND action.created_by_employee_id = ". Employee::SYSTEM_EMPLOYEE_ID;
				break;
				
			case ActionsAndNotes::LOGGED_BY_CONSTRAINT_ANYONE:
			default:
				// Don't have to do anything
				$strLoggedByConstraintForNotes = "";
				$strLoggedByConstraintForActions = "";
				break;
		}
		
		// Handle the typeConstraint for when it's a single action type
		$strActionTypeConstraint = (is_int($typeConstraint)) ? "AND action.action_type_id = {$typeConstraint}" : null;

		// Handle the Search contraints
		$sSearchActionIdConstraint = '(action.id IN ('.(count($aSearchTokens['action_id']) ? implode(', ', $aSearchTokens['action_id']) : 'NULL').'))';
		$sSearchNoteIdConstraint = '(Note.id IN ('.(count($aSearchTokens['note_id']) ? implode(', ', $aSearchTokens['note_id']) : 'NULL').'))';
		$aSearchActionKeywordContraints = array();
		$aSearchNoteKeywordContraints = array();
		foreach ($aSearchTokens['keyword'] as $sKeyword) {
			$aSearchActionKeywordContraints[] = "action.details LIKE ".preg_replace('/(?<!^[\'"])%(?![\'"]$)/', '\\\\%', Query::prepareByPHPType('%'.(string)$sKeyword.'%'));
			$aSearchNoteKeywordContraints[] = "Note.Note LIKE ".preg_replace('/(?<!^[\'"])%(?![\'"]$)/', '\\\\%', Query::prepareByPHPType('%'.(string)$sKeyword.'%'));
		}
		$sSearchActionKeywordConstraints = count($aSearchActionKeywordContraints) ? '('.implode(' OR ', $aSearchActionKeywordContraints).')' : '0';
		$sSearchNoteKeywordContraints = count($aSearchNoteKeywordContraints) ? '('.implode(' OR ', $aSearchNoteKeywordContraints).')' : '0';
		$sSearchActionConstraint = "(".(int)!count($aSearchTokens['keyword'])." OR ({$sSearchActionIdConstraint} OR {$sSearchActionKeywordConstraints}))";
		$sSearchNoteConstraint = "(".(int)!count($aSearchTokens['keyword'])." OR ({$sSearchNoteIdConstraint} OR {$sSearchNoteKeywordContraints}))";
		
		$bolIncludeNotes = true;
		$bolIncludeActions = true;
		if ($strActionTypeConstraint !== null) {
			// The type constraint is for a single action type
			$bolIncludeNotes = false;
			$bolIncludeActions = true;
		} else {
			switch ($typeConstraint) {
				case ActionsAndNotes::TYPE_CONSTRAINT_NOTES_ONLY:
					$bolIncludeNotes = true;
					$bolIncludeActions = false;
					break;
				
				case ActionsAndNotes::TYPE_CONSTRAINT_ACTIONS_ONLY:
					$bolIncludeNotes = false;
					$bolIncludeActions = true;
					break;
					
				case ActionsAndNotes::TYPE_CONSTRAINT_ALL:
				default:
					$bolIncludeNotes = true;
					$bolIncludeActions = true;
					break;
			}
		}
		
		// Now Build the queries
		$strSearchQuery = "";
		$strCountQuery = "";
		
		if ($bolIncludeNotes) {
			$strSearchQuery .= "
				SELECT {$strColumnsForNoteSearchQuery}
				FROM {$strNoteQueryFromClause}
				WHERE {$strNoteQueryWhereClause} {$strLoggedByConstraintForNotes} AND {$sSearchNoteConstraint}
			";
			$strCountQuery .= "
				SELECT {$strColumnsForNoteCountQuery}
				FROM {$strNoteQueryFromClause}
				WHERE {$strNoteQueryWhereClause} {$strLoggedByConstraintForNotes} AND {$sSearchNoteConstraint}
			";
		}
		if ($bolIncludeActions) {
			if ($bolIncludeNotes) {
				$strSearchQuery .= " UNION ";
				$strCountQuery .= " UNION ";
			}
			
			$strSearchQuery .=  "
				SELECT {$strColumnsForActionSearchQuery}
				FROM {$strActionQueryFromClause}
				WHERE {$strActionQueryWhereClause} {$strLoggedByConstraintForActions} {$strActionTypeConstraint} AND {$sSearchActionConstraint}
			";
			$strCountQuery .= "
				SELECT {$strColumnsForActionCountQuery}
				FROM {$strActionQueryFromClause}
				WHERE {$strActionQueryWhereClause} {$strLoggedByConstraintForActions} {$strActionTypeConstraint} AND {$sSearchActionConstraint}
			";
		}
		
		$strSearchQuery .= "ORDER BY created_on DESC, id DESC ";
		
		$intMaxRecordsPerPage = (int)$intMaxRecordsPerPage;
		$intPageOffset = (int)$intPageOffset;
		if ($intMaxRecordsPerPage > 0) {
			// Do page stuff
			$strSearchQuery .= "LIMIT {$intMaxRecordsPerPage} OFFSET {$intPageOffset}";
		}
		
		$objQueries = new stdClass();
		$objQueries->search = $strSearchQuery;
		$objQueries->count = $strCountQuery;
		return $objQueries;
	}

	private static function _extractSearchTokens($sSearchString) {
		$aTokens = array(
			'action_id' => array(),
			'note_id' => array(),
			'keyword' => array()
		);
		$aMatches = array();
		if (preg_match_all('/(A(?<action_id>\d+)(?=\s|$)|N(?<note_id>\d+)(?=\s|$)|(?<id>\d+(?=\s|$))|(?<keyword>\S+))/i', $sSearchString, $aMatches, PREG_SET_ORDER)) {
			//(A(?<action_id>\d+(?=\s))|N(?<note_id>\d+(?=\s))|(?<id>\d+(?=\s))|(?<keyword>\S+))
			//throw new Exception(print_r($aMatches, true));
			// Extract matches
			foreach ($aMatches as $aMatch) {
				if ($aMatch['action_id']) {
					// Action
					$aTokens['action_id'][] = (int)$aMatch['action_id'];
					$aTokens['keyword'][] = (int)$aMatch['action_id'];
				} else if ($aMatch['note_id']) {
					// Note
					$aTokens['note_id'][] = (int)$aMatch['note_id'];
					$aTokens['keyword'][] = (int)$aMatch['note_id'];
				} else if ($aMatch['id']) {
					// Generic Id
					$aTokens['action_id'][] = (int)$aMatch['id'];
					$aTokens['note_id'][] = (int)$aMatch['id'];
					$aTokens['keyword'][] = (int)$aMatch['id'];
				} elseif ($aMatch['keyword']) {
					// Keyword
					$aTokens['keyword'][] = $aMatch['keyword'];
				}
			}
		}
		//throw new Exception(print_r($aTokens, true));
		return $aTokens;
	}
}
<?php

/**
 * JSON_Handler_ActionsAndNotes
 *
 * Handles ajax requests regarding the ActionsAndNotes functionality
 *
 * Handles ajax requests regarding the ActionsAndNotes functionality
 *
 * @class	JSON_Handler_ActionsAndNotes
 */
class JSON_Handler_ActionsAndNotes extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	/**
	 * getActionAndNoteTypes()
	 *
	 * Retreives all ActionTypes and NoteTypes from the database
	 *
	 * @return	Array
	 * @method
	 */
	public static function getActionAndNoteTypes()
	{
		try
		{
			// Retrieve all Note Types
			$arrNoteTypes = Note_Type::getAll();
			$arrNoteTypesAssoc = array();
			foreach ($arrNoteTypes as $intKey=>$objNoteType)
			{
				$arrNoteTypesAssoc[$intKey] = $objNoteType->toArray(true);
			}

			// Retrieve all Action Types
			$arrActionTypes = Action_Type::getAll();
			$arrActionTypesAssoc = array();
			foreach ($arrActionTypes as $intKey=>$objActionType)
			{
				$arrActionTypesAssoc[$intKey] = $objActionType->toArray(true);
				
				// Load the allowable actionAssociationTypes
				$arrAllowableActionAssociationTypes = $objActionType->getAllowableActionAssociationTypes();
				
				// Convert this to an array which is array(actionAssociationType.id=>actionAssociationType.id)
				$arrActionTypesAssoc[$intKey]['allowableActionAssociationTypes'] = ConvertToSimpleArray($arrAllowableActionAssociationTypes, 'id', 'id');
			}
			
			return array(	"success"		=> true,
							"actionTypes"	=> $arrActionTypesAssoc,
							"noteTypes"		=> $arrNoteTypesAssoc
						);
		}
		catch (Exception $e)
		{
			return array(	"success"		=> false,
							"errorMessage"	=> $e->getMessage()
						);
		}
	}
	
	/**
	 * createNote()
	 *
	 * Handles ajax request to create a note
	 * At least 1 of $intAccountId, $intServiceId and $intContactId should be not NULL
	 * 
	 * @param	int		$intNoteTypeId		id of the type of note to create
	 * @param	string	$strContent			content for the note
	 * @param	int		[ $intAccountId ]	Defaults to NULL.  If defined, then it will associate the note with this account
	 * @param	int		[ $intServiceId ]	Defaults to NULL.  If defined, then it will associate the note with this service id, 
	 * 										and also associate the note with the account that this service belongs to
	 * @param	int		[ $intContactId ]	Defaults to NULL.  If defined, then it will associate the note with this contact
	 *
	 * @return	Array	declaring the success of the process
	 * @method
	 */
	public function createNote($intNoteTypeId, $strContent, $intAccountId=NULL, $intServiceId=NULL, $intContactId=NULL)
	{
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		try
		{
			TransactionStart();
			
			// Normalise things
			$intNoteTypeId = intval($intNoteTypeId);
			$intAccountId = ($intAccountId == NULL)? NULL : intval($intAccountId);
			$intServiceId = ($intServiceId == NULL)? NULL : intval($intServiceId);
			$intContactId = ($intContactId == NULL)? NULL : intval($intContactId);

			// Retrieve the Note Type
			$noteType = Note_Type::getForId($intNoteTypeId);
			
			// Make sure the note type, isn't a system note
			if ($noteType->id == Note::SYSTEM_NOTE_TYPE_ID)
			{
				throw new Exception("User cannot manually create system notes");
			}
			
			// Check that there is content
			$strContent = trim($strContent);
			if ($strContent == "")
			{
				throw new Exception("No content has been supplied");
			}
			
			// If $intServiceId has been specified, then set $intAccountId to the account that $intServiceId belongs to
			// (almost all notes associated with a service are also associated with an account)
			if ($intServiceId)
			{
				$objService = Service::getForId($intServiceId);
				$intAccountId = $objService->account;
			}
			
			Note::createNote($noteType->id, $strContent, Flex::getUserId(), $intAccountId, $intServiceId, $intContactId);
			TransactionCommit();
			
			return array(	"success" => true
						);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return array(	"success"		=> false,
							"errorMessage"	=> $e->getMessage()
						);
		}
	}
	
	/**
	 * createAction()
	 *
	 * Handles ajax request to log an Action
	 * At least 1 of $intAccountId, $intServiceId and $intContactId should be not NULL
	 * 
	 * @param	int		$intActionTypeId	id of the type of action to create
	 * @param	string	$strExtraDetails	Extra Details for the action.  This can be NULL
	 * @param	int		[ $intAccountId ]	Defaults to NULL.  If defined, then it will associate the action with this account
	 * @param	int		[ $intServiceId ]	Defaults to NULL.  If defined, then it will associate the action with this service id 
	 * @param	int		[ $intContactId ]	Defaults to NULL.  If defined, then it will associate the action with this contact
	 *
	 * @return	Array	declaring the success of the process
	 * @method
	 */
	public function createAction($intActionTypeId, $strExtraDetails, $intAccountId=NULL, $intServiceId=NULL, $intContactId=NULL)
	{
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		try
		{
			TransactionStart();
			
			// Normalise things
			$intActionTypeId	= intval($intActionTypeId);
			$intAccountId		= ($intAccountId == NULL)? NULL : intval($intAccountId);
			$intServiceId		= ($intServiceId == NULL)? NULL : intval($intServiceId);
			$intContactId		= ($intContactId == NULL)? NULL : intval($intContactId);
			$strExtraDetails	= trim($strExtraDetails);
			$strExtraDetails	= ($strExtraDetails == "")? NULL : $strExtraDetails;
						
			// Retrieve the Action Type
			$actionType = Action_Type::getForId($intActionTypeId);
			
			// Check that each $intAccountId, $intServiceId and $intContactId can be associated with actions of type $actionType, and NULLify them if they can't
			$arrAllowableActionAssociationTypes = $actionType->getAllowableActionAssociationTypes();
			
			if (!array_key_exists(ACTION_ASSOCIATION_TYPE_ACCOUNT, $arrAllowableActionAssociationTypes))
			{
				$intAccountId = NULL;
			}
			if (!array_key_exists(ACTION_ASSOCIATION_TYPE_SERVICE, $arrAllowableActionAssociationTypes))
			{
				$intServiceId = NULL;
			}
			if (!array_key_exists(ACTION_ASSOCIATION_TYPE_CONTACT, $arrAllowableActionAssociationTypes))
			{
				$intContactId = NULL;
			}
			
			$intEmployeeId = Flex::getUserId();
			
			Action::createAction($actionType, $strExtraDetails, $intAccountId, $intServiceId, $intContactId, $intEmployeeId, $intEmployeeId);
			TransactionCommit();
			
			return array(	"success"		=> true
						);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return array(	"success"		=> false,
							"errorMessage"	=> $e->getMessage()
						);
		}
	}
	
	/**
	 * search
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
	 * @return	mixed	On Success - array(	"success"	=> true,
	 *										"items"		=> $arrItems, // The actions and notes
	 *										"search"	=> $arrSearch // The search conditions used
	 *										);
	 *					On Failure - array(	"success"		=> false,
	 *										"errorMessage"	=> $e->getMessage() // The error message
	 *										);
	 * @method
	 */
	public function search($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage, $intPageOffset)
	{
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		try
		{
			// Typecast everything and make sure it's all safe for inserting into queries
			$intAATContextId = intval($intAATContextId);
			$intAATContextReferenceId = intval($intAATContextReferenceId);
			$bolIncludeAllRelatableAATTypes = (bool)$bolIncludeAllRelatableAATTypes;
			$intMaxRecordsPerPage = intval($intMaxRecordsPerPage);
			if ($intMaxRecordsPerPage > 100)
			{
				$intMaxRecordsPerPage = 100;
			}
			
			$intPageOffset = intval($intPageOffset);
			
			$arrResults = ActionsAndNotes::searchFor($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $intMaxRecordsPerPage, $intPageOffset);
		
			// Convert the results (mixed array of Note and Action objects) into a format useful for the javascript component that will render them in the page
			switch ($intAATContextId)
			{
				case ACTION_ASSOCIATION_TYPE_ACCOUNT:
					$arrItems = $this->_processActionsAndNotesForAccountContext($arrResults, $intAATContextReferenceId);
					break;
					
				case ACTION_ASSOCIATION_TYPE_SERVICE:
					$arrItems = $this->_processActionsAndNotesForServiceContext($arrResults, $intAATContextReferenceId);
					break;
					
				case ACTION_ASSOCIATION_TYPE_CONTACT:
					$arrItems = $this->_processActionsAndNotesForContactContext($arrResults, $intAATContextReferenceId);
					break;
			}
			
			// Page details
			$arrPageDetails	= (array)(ActionsAndNotes::getLastSearchPaginationDetails());
			$arrSearch		= array_merge(array("typeConstraint"=>$typeConstraint, "loggedByConstraint"=>$loggedByConstraint), $arrPageDetails);
			
			// You have to return the following structure on success:
			return array(	"success"	=> true,
							"items"		=> $arrItems, // The actions and notes
							"search"	=> $arrSearch // The search conditions used
						);
		}
		catch (Exception $e)
		{
			return array(	"success"		=> false,
							"errorMessage"	=> $e->getMessage()
						);

		}
		
	}
	
	private function _processActionsAndNotesForAccountContext($arrItems, $intAccountId)
	{
		$objAccount = Account::getForId($intAccountId);
		if ($objAccount === NULL)
		{
			throw new Exception("Could not find account with id: $intAccountId");
		}
		
		$arrServices = $objAccount->getAllServiceRecords(TRUE);
		$arrContacts = $objAccount->getContacts(TRUE);
		
		$arrServiceIds = array_keys($arrServices);
		if (count($arrServiceIds))
		{
			// The account has associated service records
			$arrServiceIds = array_combine($arrServiceIds, $arrServiceIds);
		}
		
		$arrContactIds = array_keys($arrContacts);
		if (count($arrContactIds))
		{
			// The account has associated contacts (this should always be the case)
			$arrContactIds = array_combine($arrContactIds, $arrContactIds);
		}
		
		$strViewContactLink = Href()->ViewContact("");
		$strViewServiceLink = Href()->ViewService("", FALSE);
		
		$arrProcessedItems = array();
		
		foreach ($arrItems as $objItem)
		{
			$arrAssociatedContactIds = array();
			$arrAssociatedServiceIds = array();
			
			switch (get_class($objItem))
			{
				case "Note":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_NOTE);
					if ($objItem->service && array_key_exists($objItem->service, $arrServices))
					{
						// Include this service
						$arrAssociatedServiceIds[] = $objItem->service;
					}
					if ($objItem->contact && array_key_exists($objItem->contact, $arrContacts))
					{
						// Include this contact
						$arrAssociatedContactIds[] = $objItem->contact;
					}
					break;
					
				case "Action":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_ACTION);
					$arrAssociatedServiceIds = array_intersect($objItem->getAssociatedServices(true), $arrServiceIds);
					$arrAssociatedContactIds = array_intersect($objItem->getAssociatedContacts(true), $arrContactIds);
					break;
			}
			
			if (count($arrAssociatedContactIds))
			{
				$objProcessedItem->associatedContacts = array();
				foreach ($arrAssociatedContactIds as $intContactId)
				{
					$objProcessedItem->associatedContacts[] = array("name"		=> $arrContacts[$intContactId]->getName(),
																	"link"		=> $strViewContactLink . $intContactId);
				}
			}
			
			if (count($arrAssociatedServiceIds))
			{
				$objProcessedItem->associatedServices = array();
				foreach ($arrAssociatedServiceIds as $intServiceId)
				{
					$objProcessedItem->associatedServices[] = array("name"		=> $arrServices[$intServiceId]->fNN,
																	"link"		=> $strViewServiceLink . $intServiceId);
				}
			}
			$arrProcessedItems[] = (array)$objProcessedItem;
		}
		
		return $arrProcessedItems;
	}
	
	private function _processActionsAndNotesForServiceContext($arrItems, $intServiceId)
	{
		$arrProcessedItems = array();
		
		foreach ($arrItems as $objItem)
		{
			switch (get_class($objItem))
			{
				case "Note":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_NOTE);
					break;
					
				case "Action":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_ACTION);
					break;
			}
			
			$arrProcessedItems[] = (array)$objProcessedItem;
		}
		
		return $arrProcessedItems;
	}
	
	private function _processActionsAndNotesForContactContext($arrItems, $intContactId)
	{
		$arrProcessedItems = array();
		
		foreach ($arrItems as $objItem)
		{
			switch (get_class($objItem))
			{
				case "Note":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_NOTE);
					break;
					
				case "Action":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_ACTION);
					break;
			}
			
			$arrProcessedItems[] = (array)$objProcessedItem;
		}
		
		return $arrProcessedItems;
	}
	
	/*
	 *  This returns the Action or Note in a generic structure, as an object
	 */
	private function _processItemDetails($objActionOrNote, $ActionAndNoteType)
	{
		static $arrEmployees;
		if (!isset($arrEmployees))
		{
			$arrEmployees = array();
			
			// Load the system employee
			$arrEmployees[Employee::SYSTEM_EMPLOYEE_ID] = Employee::getForId(Employee::SYSTEM_EMPLOYEE_ID);
		}
		
		$objItem = new stdClass();
		
		if ($ActionAndNoteType == ActionsAndNotes::TYPE_NOTE)
		{
			// It's a Note
			$objNote = $objActionOrNote;
			
			$objItem->recordType	= ActionsAndNotes::TYPE_NOTE;
			$objItem->typeId		= $objNote->noteType;
			
			
			// CreatedBy and PerformedBy
			if ($objNote->employee == NULL)
			{
				$objNote->employee = Employee::SYSTEM_EMPLOYEE_ID;
			}
			
			if (!array_key_exists($objNote->employee, $arrEmployees))
			{
				// Retrieve the employee
				$arrEmployees[$objNote->employee] = Employee::getForId($objNote->employee);
			}
			
			if ($objNote->noteType == Note::SYSTEM_NOTE_TYPE_ID)
			{
				$objItem->createdBy = "Automatic System";
			}
			else
			{
				$objItem->createdBy = $arrEmployees[$objNote->employee]->getName();
			}
						
			if ($objNote->employee == Employee::SYSTEM_EMPLOYEE_ID)
			{
				$objItem->performedBy = "Automatic System";
				
				// This is just a precautionary measure
				$objItem->createdBy = "Automatic System";
			}
			else
			{
				$objItem->performedBy = $arrEmployees[$objNote->employee]->getName();
			}
			
			// Details
			$strDetails = trim($objNote->note);
			$objItem->details = ($strDetails == "")? NULL : $strDetails;
			
			// CreatedOn
			$objItem->createdOnTimestamp = strtotime($objNote->Datetime);
			
		}
		elseif ($ActionAndNoteType == ActionsAndNotes::TYPE_ACTION)
		{
			// It's an Action
			$objAction = $objActionOrNote;
			
			$objItem->recordType	= ActionsAndNotes::TYPE_ACTION;
			$objItem->typeId		= $objAction->actionTypeId;
			
			// CreatedBy and PerformedBy
			if (!array_key_exists($objAction->createdByEmployeeId, $arrEmployees))
			{
				// Retrieve the employee
				$arrEmployees[$objAction->createdByEmployeeId] = Employee::getForId($objAction->createdByEmployeeId);
			}
			if (!array_key_exists($objAction->performedByEmployeeId, $arrEmployees))
			{
				// Retrieve the employee
				$arrEmployees[$objAction->performedByEmployeeId] = Employee::getForId($objAction->performedByEmployeeId);
			}
			
			$objItem->createdBy		= ($objAction->createdByEmployeeId == Employee::SYSTEM_EMPLOYEE_ID)? "Automatic System" : $arrEmployees[$objAction->createdByEmployeeId]->getName();
			$objItem->performedBy	= ($objAction->performedByEmployeeId == Employee::SYSTEM_EMPLOYEE_ID)? "Automatic System" : $arrEmployees[$objAction->performedByEmployeeId]->getName();
			
			// Details
			$strDetails = trim($objAction->details);
			$objItem->details = ($strDetails == "")? NULL : $strDetails;
			
			// CreatedOn
			$objItem->createdOnTimestamp = strtotime($objAction->createdOn);
		}
		else
		{
			// It's not an Action and it's not a note
			throw new Exception(__METHOD__ ." - Unknown ActionAndNote Type: $ActionAndNoteType");
		}
		
		$objItem->createdOnFormatted = date("l, M j, Y g:i:s A", $objItem->createdOnTimestamp);
		
		if ($objItem->details !== NULL)
		{
			// Protect against html injection, and convert line breaks to br elements
			$objItem->details = nl2br(htmlspecialchars($objItem->details));
		}
		
		return $objItem;
	}
	
	
}
?>
<?php
class JSON_Handler_ActionsAndNotes extends JSON_Handler {
	protected $_JSONDebug = '';
		
	public function __construct() {
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public static function getActionAndNoteTypes() {
		try {
			// Retrieve all Note Types
			$arrNoteTypes = Note_Type::getAll();
			$arrNoteTypesAssoc = array();
			foreach ($arrNoteTypes as $intKey=>$objNoteType) {
				$arrNoteTypesAssoc[$intKey] = $objNoteType->toArray(true);
			}

			// Retrieve all Action Types
			$arrActionTypes = Action_Type::getAll();
			$arrActionTypesAssoc = array();
			foreach ($arrActionTypes as $intKey=>$objActionType) {
				$arrActionTypesAssoc[$intKey] = $objActionType->toArray(true);
				
				// Load the allowable actionAssociationTypes
				$arrAllowableActionAssociationTypes = $objActionType->getAllowableActionAssociationTypes();
				
				// Convert this to an array which is array(actionAssociationType.id=>actionAssociationType.id)
				$arrActionTypesAssoc[$intKey]['allowableActionAssociationTypes'] = ConvertToSimpleArray($arrAllowableActionAssociationTypes, 'id', 'id');
			}
			
			return array(
				"success" => true,
				"actionTypes" => $arrActionTypesAssoc,
				"noteTypes" => $arrNoteTypesAssoc
			);
		} catch (Exception $e) {
			return array(
				"success" => false,
				"errorMessage" => $e->getMessage()
			);
		}
	}
	
	public function createNote($intNoteTypeId, $strContent, $intAccountId=null, $intServiceId=null, $intContactId=null) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));

		try {
			TransactionStart();
			
			// Normalise things
			$intNoteTypeId = (int)$intNoteTypeId;
			$intAccountId = ($intAccountId == null) ? null : (int)$intAccountId;
			$intServiceId = ($intServiceId == null) ? null : (int)$intServiceId;
			$intContactId = ($intContactId == null) ? null : (int)$intContactId;

			// Retrieve the Note Type
			$noteType = Note_Type::getForId($intNoteTypeId);
			
			// Make sure the note type, isn't a system note
			if ($noteType->id == Note::SYSTEM_NOTE_TYPE_ID) {
				throw new Exception("User cannot manually create system notes");
			}
			
			// Check that there is content
			$strContent = trim($strContent);
			if ($strContent == "") {
				throw new Exception("No content has been supplied");
			}
			
			// If $intServiceId has been specified, then set $intAccountId to the account that $intServiceId belongs to
			// (almost all notes associated with a service are also associated with an account)
			if ($intServiceId) {
				$objService = Service::getForId($intServiceId);
				$intAccountId = $objService->account;
			}
			
			$oNote = Note::createNote($noteType->id, $strContent, Flex::getUserId(), $intAccountId, $intServiceId, $intContactId);
			TransactionCommit();
			
			return array(
				"success" => true,
				"iNoteId" => $oNote->Id
			);
		} catch (Exception $oEx) {
			TransactionRollback();
			return array(
				"success" => false,
				"errorMessage" => $oEx->getMessage(),
				'sExceptionClass' => get_class($oEx)
			);
		}
	}
	
	public function createAction($intActionTypeId, $strExtraDetails, $intAccountId=null, $intServiceId=null, $intContactId=null) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));

		try {
			TransactionStart();
			
			// Normalise things
			$intActionTypeId = (int)$intActionTypeId;
			$intAccountId = ($intAccountId == null) ? null : (int)$intAccountId;
			$intServiceId = ($intServiceId == null) ? null : (int)$intServiceId;
			$intContactId = ($intContactId == null) ? null : (int)$intContactId;
			$strExtraDetails = trim($strExtraDetails);
			$strExtraDetails = ($strExtraDetails == "") ? null : $strExtraDetails;
						
			// Retrieve the Action Type
			$actionType = Action_Type::getForId($intActionTypeId);
			
			// Check that each $intAccountId, $intServiceId and $intContactId can be associated with actions of type $actionType, and NULLify them if they can't
			$arrAllowableActionAssociationTypes = $actionType->getAllowableActionAssociationTypes();
			
			if (!array_key_exists(ACTION_ASSOCIATION_TYPE_ACCOUNT, $arrAllowableActionAssociationTypes)) {
				$intAccountId = null;
			}
			if (!array_key_exists(ACTION_ASSOCIATION_TYPE_SERVICE, $arrAllowableActionAssociationTypes)) {
				$intServiceId = null;
			}
			if (!array_key_exists(ACTION_ASSOCIATION_TYPE_CONTACT, $arrAllowableActionAssociationTypes)) {
				$intContactId = null;
			}
			
			$intEmployeeId = Flex::getUserId();
			
			$oAction = Action::createAction($actionType, $strExtraDetails, $intAccountId, $intServiceId, $intContactId, $intEmployeeId, $intEmployeeId);
			TransactionCommit();
			
			return array(
				"success" => true,
				"iActionId" => $oAction->id
			);
		} catch (Exception $e) {
			TransactionRollback();
			return array(
				"success" => false,
				"errorMessage" => $e->getMessage(),
				'sExceptionClass' => get_class($e)
			);
		}
	}
	
	public function getNoteDetails($iId) {
		try {
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL))) {
				throw new JSON_Handler_ActionsAndNotes_Exception('You do not have permission to view Notes.');
			}
			
			$oNote = Note::getForId($iId);
			$oDetails = $this->_processItemDetails($oNote, ActionsAndNotes::TYPE_NOTE);
			
			return array(	
				"Success" => true,
				"oDetails" => $oDetails
			);
		} catch(JSON_Handler_ActionsAndNotes_Exception $oException) {
			return array(
				"Success" => false,
				"Message" => $oException->getMessage()
			);
		} catch(Exception $e) {
			return array(
				"Success" => false,
				"Message" => (Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'An error occurred getting the note details')
			);
		}
	}
	
	public function getActionDetails($iId) {
		try {
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL))) {
				throw new JSON_Handler_ActionsAndNotes_Exception('You do not have permission to view Actions.');
			}
			
			$oAction = Action::getForId($iId);
			$oDetails = $this->_processItemDetails($oAction, ActionsAndNotes::TYPE_ACTION);
			
			return array(	
				"Success" => true,
				"oDetails" => $oDetails
			);
		} catch(JSON_Handler_ActionsAndNotes_Exception $oException) {
			return array(
				"Success" => false,
				"Message" => $oException->getMessage()
			);
		} catch(Exception $e) {
			return array(
				"Success" => false,
				"Message" => (Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'An error occurred getting the action details')
			);
		}
	}
	
	public function search($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $sSearchString, $intMaxRecordsPerPage, $intPageOffset) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));
		
		try {
			// Typecast everything and make sure it's all safe for inserting into queries
			$intAATContextId = (int)$intAATContextId;
			$intAATContextReferenceId = (int)$intAATContextReferenceId;
			$bolIncludeAllRelatableAATTypes = (bool)$bolIncludeAllRelatableAATTypes;
			$intMaxRecordsPerPage = (int)$intMaxRecordsPerPage;
			if ($intMaxRecordsPerPage > 100) {
				$intMaxRecordsPerPage = 100;
			}
			$sSearchString = (string)$sSearchString;
			
			$intPageOffset = (int)$intPageOffset;
			
			$arrResults = ActionsAndNotes::searchFor($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $typeConstraint, $loggedByConstraint, $sSearchString, $intMaxRecordsPerPage, $intPageOffset);
		
			// Convert the results (mixed array of Note and Action objects) into a format useful for the javascript component that will render them in the page
			switch ($intAATContextId) {
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
			$arrPageDetails = (array)(ActionsAndNotes::getLastSearchPaginationDetails());
			$arrSearch = array_merge(array("typeConstraint"=>$typeConstraint, "loggedByConstraint"=>$loggedByConstraint, "searchString"=>$sSearchString), $arrPageDetails);
			
			// You have to return the following structure on success:
			return array(
				"success" => true,
				"items" => $arrItems, // The actions and notes
				"search" => $arrSearch // The search conditions used
			);
		} catch (Exception $e) {
			return array(
				"success" => false,
				"errorMessage" => $e->getMessage()
			);
		}
	}
	
	private function _processActionsAndNotesForAccountContext($arrItems, $intAccountId) {
		$objAccount = Account::getForId($intAccountId);
		if ($objAccount === null) {
			throw new Exception("Could not find account with id: {$intAccountId}");
		}
		
		$arrServices = $objAccount->getAllServiceRecords(true);
		$arrContacts = $objAccount->getContacts(true);
		
		$arrServiceIds = array_keys($arrServices);
		if (count($arrServiceIds)) {
			// The account has associated service records
			$arrServiceIds = array_combine($arrServiceIds, $arrServiceIds);
		}
		
		$arrContactIds = array_keys($arrContacts);
		if (count($arrContactIds)) {
			// The account has associated contacts (this should always be the case)
			$arrContactIds = array_combine($arrContactIds, $arrContactIds);
		}
		
		$strViewContactLink = Href()->ViewContact("");
		$strViewServiceLink = Href()->ViewService("", false);
		
		$arrProcessedItems = array();
		
		foreach ($arrItems as $objItem) {
			$arrAssociatedContactIds = array();
			$arrAssociatedServiceIds = array();
			
			switch (get_class($objItem)) {
				case "Note":
					$objProcessedItem = $this->_processItemDetails($objItem, ActionsAndNotes::TYPE_NOTE);
					if ($objItem->service && array_key_exists($objItem->service, $arrServices)) {
						// Include this service
						$arrAssociatedServiceIds[] = $objItem->service;
					}
					if ($objItem->contact && array_key_exists($objItem->contact, $arrContacts)) {
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
			
			if (count($arrAssociatedContactIds)) {
				$objProcessedItem->associatedContacts = array();
				foreach ($arrAssociatedContactIds as $intContactId) {
					$objProcessedItem->associatedContacts[] = array(
						"name" => $arrContacts[$intContactId]->getName(),
						"link" => $strViewContactLink . $intContactId
					);
				}
			}
			
			if (count($arrAssociatedServiceIds)) {
				$objProcessedItem->associatedServices = array();
				foreach ($arrAssociatedServiceIds as $intServiceId) {
					$objProcessedItem->associatedServices[] = array(
						"name" => $arrServices[$intServiceId]->fNN,
						"link" => $strViewServiceLink . $intServiceId
					);
				}
			}
			$arrProcessedItems[] = (array)$objProcessedItem;
		}
		
		return $arrProcessedItems;
	}
	
	private function _processActionsAndNotesForServiceContext($arrItems, $intServiceId) {
		$arrProcessedItems = array();
		
		foreach ($arrItems as $objItem) {
			switch (get_class($objItem)) {
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
	
	private function _processActionsAndNotesForContactContext($arrItems, $intContactId) {
		$arrProcessedItems = array();
		
		foreach ($arrItems as $objItem) {
			switch (get_class($objItem)) {
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
	
	private function _processItemDetails($objActionOrNote, $ActionAndNoteType) {
		static $arrEmployees;
		if (!isset($arrEmployees)) {
			$arrEmployees = array();
			
			// Load the system employee
			$arrEmployees[Employee::SYSTEM_EMPLOYEE_ID] = Employee::getForId(Employee::SYSTEM_EMPLOYEE_ID);
		}
		
		$objItem = new stdClass();
		
		if ($ActionAndNoteType == ActionsAndNotes::TYPE_NOTE) {
			// It's a Note
			$objNote = $objActionOrNote;
			
			$objItem->id = $objNote->Id;
			$objItem->recordType = ActionsAndNotes::TYPE_NOTE;
			$objItem->typeId = $objNote->noteType;
			
			
			// CreatedBy and PerformedBy
			if ($objNote->employee == null) {
				$objNote->employee = Employee::SYSTEM_EMPLOYEE_ID;
			}
			
			if (!array_key_exists($objNote->employee, $arrEmployees)) {
				// Retrieve the employee
				$arrEmployees[$objNote->employee] = Employee::getForId($objNote->employee);
			}
			
			if ($objNote->noteType == Note::SYSTEM_NOTE_TYPE_ID) {
				$objItem->createdBy = "Automatic System";
			} else {
				$objItem->createdBy = $arrEmployees[$objNote->employee]->getName();
			}
						
			if ($objNote->employee == Employee::SYSTEM_EMPLOYEE_ID) {
				$objItem->performedBy = "Automatic System";
				
				// This is just a precautionary measure
				$objItem->createdBy = "Automatic System";
			} else {
				$objItem->performedBy = $arrEmployees[$objNote->employee]->getName();
			}
			
			// Details
			$strDetails = trim($objNote->note);
			$objItem->details = ($strDetails == "") ? null : $strDetails;
			
			// CreatedOn
			$objItem->createdOnTimestamp = strtotime($objNote->Datetime);
		} elseif ($ActionAndNoteType == ActionsAndNotes::TYPE_ACTION) {
			// It's an Action
			$objAction = $objActionOrNote;
			
			$objItem->id = $objAction->id;
			$objItem->recordType = ActionsAndNotes::TYPE_ACTION;
			$objItem->typeId = $objAction->actionTypeId;
			
			// CreatedBy and PerformedBy
			if (!array_key_exists($objAction->createdByEmployeeId, $arrEmployees)) {
				// Retrieve the employee
				$arrEmployees[$objAction->createdByEmployeeId] = Employee::getForId($objAction->createdByEmployeeId);
			}
			if (!array_key_exists($objAction->performedByEmployeeId, $arrEmployees)) {
				// Retrieve the employee
				$arrEmployees[$objAction->performedByEmployeeId] = Employee::getForId($objAction->performedByEmployeeId);
			}
			
			$objItem->createdBy = ($objAction->createdByEmployeeId == Employee::SYSTEM_EMPLOYEE_ID)? "Automatic System" : $arrEmployees[$objAction->createdByEmployeeId]->getName();
			$objItem->performedBy = ($objAction->performedByEmployeeId == Employee::SYSTEM_EMPLOYEE_ID)? "Automatic System" : $arrEmployees[$objAction->performedByEmployeeId]->getName();
			
			// Details
			$strDetails = trim($objAction->details);
			$objItem->details = ($strDetails == "") ? null : $strDetails;
			
			// CreatedOn
			$objItem->createdOnTimestamp = strtotime($objAction->createdOn);
		} else {
			// It's not an Action and it's not a note
			throw new Exception(__METHOD__ ." - Unknown ActionAndNote Type: {$ActionAndNoteType}");
		}
		
		$objItem->createdOnFormatted = date("l, M j, Y g:i:s A", $objItem->createdOnTimestamp);
		
		if ($objItem->details !== null) {
			// Protect against html injection, and convert line breaks to br elements
			$objItem->details = nl2br(htmlspecialchars($objItem->details));
		}
		
		return $objItem;
	}
}

class JSON_Handler_ActionsAndNotes_Exception extends Exception {}

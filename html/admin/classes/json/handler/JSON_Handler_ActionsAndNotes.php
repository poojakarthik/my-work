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
	public function getActionAndNoteTypes()
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
	
}
?>
<?php
//----------------------------------------------------------------------------//
// HtmlTemplateActionsAndNotesList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateActionsAndNotesList
 *
 * HTML Template class for the ActionsAndNotesList HTML object
 *
 * HTML Template class for the ActionsAndNotesList HTML object
 * displays the ActionsAndNotesList embedded component
 *
 * @package	ui_app
 * @class	HtmlTemplateActionsAndNotesList
 * @extends	HtmlTemplate
 */
class HtmlTemplateActionsAndNotesList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("actions_and_notes");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		$bolUserHasPermissionToAddNotesAndActions = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		
		$strErrorMsg	= NULL;

		try
		{
			// Retrieve all ActionTypes and NoteTypes (While running in javascript: ActionsAndNotes.load(...) will retrieve these from the server, retrieving
			// them now will mean one less request to the server)
			$arrTypes = JSON_Handler_ActionsAndNotes::getActionAndNoteTypes();
			
			if ($arrTypes['success'])
			{
				// Successfully retrieved them
				$jsonNoteTypes		= JSON_Services::encode($arrTypes['noteTypes']);
				$jsonActionTypes	= JSON_Services::encode($arrTypes['actionTypes']);
			}
			else
			{
				// Couldn't successfully retrieve them
				throw new Exception($arrTypes['errorMessage']);
			}
		}
		catch (Exception $e)
		{
			$strErrorMsg = $e->getMessage();
		}

		// Set up things for the ActionsAndNotes list
		try 
		{
			if (DBO()->ActionList->AATContextId->IsSet)
			{
				$intAATContextId = DBO()->ActionList->AATContextId->Value;
			}
			else
			{
				throw new Exception("Context has not been defined");
			}
			
			if (DBO()->ActionList->AATContextReferenceId->IsSet)
			{
				$intAATContextReferenceId = DBO()->ActionList->AATContextReferenceId->Value;
			}
			else
			{
				throw new Exception("{$objAATContext->name} id has not been specified");
			}
			
			$bolIncludeAllRelatableAATTypes	= (DBO()->ActionList->IncludeAllRelatableAATTypes->IsSet)? DBO()->ActionList->IncludeAllRelatableAATTypes->Value : true;
			$intMaxRecordsPerPage			= (DBO()->ActionList->MaxRecordsPerPage->IsSet)? DBO()->ActionList->MaxRecordsPerPage->Value : 5;
			
			// Build the popup title
			$strPopupTitle = "[INSERT TITLE HERE]";
			switch ($intAATContextId)
			{
				case ACTION_ASSOCIATION_TYPE_ACCOUNT:
					$objAccount		= Account::getForId($intAATContextReferenceId);
					$strPopupTitle	= $intAATContextReferenceId ." - ". $objAccount->getName();
					break;
				case ACTION_ASSOCIATION_TYPE_SERVICE:
					$objService		= Service::getForId($intAATContextReferenceId);
					$strPopupTitle	= GetConstantDescription($objService->serviceType, "service_type") ." - ". $objService->fNN;
					break;
				case ACTION_ASSOCIATION_TYPE_CONTACT:
					$objContact		= Contact::getForId($intAATContextReferenceId);
					$strPopupTitle	= $objContact->getName();
					break;
				default:
					throw new Exception("Invalid Context for Actions And Notes (Context: $intAATContextId)");
				
			}
			
			// Encode everything as JSON
			$jsonAATContextId					= JSON_Services::encode($intAATContextId);
			$jsonAATContextReferenceId			= JSON_Services::encode($intAATContextReferenceId);
			$jsonIncludeAllRelatableAATTypes	= JSON_Services::encode($bolIncludeAllRelatableAATTypes);
			$jsonMaxRecordsPerPage				= JSON_Services::encode($intMaxRecordsPerPage);
			$jsonPopupTitle						= htmlspecialchars(JSON_Services::encode($strPopupTitle), ENT_QUOTES);
		}
		catch (Exception $e)
		{
			$strErrorMsg = $e->getMessage();
		}

		// Set up things for the ActionsAndNotes Creator component
		$intAccountId	= (DBO()->ActionCreator->AccountId->IsSet)? DBO()->ActionCreator->AccountId->Value : null;
		$intServiceId	= (DBO()->ActionCreator->ServiceId->IsSet)? DBO()->ActionCreator->ServiceId->Value : null;
		$intContactId	= (DBO()->ActionCreator->ContactId->IsSet)? DBO()->ActionCreator->ContactId->Value : null;
		$jsonAccountId	= JSON_Services::encode($intAccountId);
		$jsonServiceId	= JSON_Services::encode($intServiceId);
		$jsonContactId	= JSON_Services::encode($intContactId);
		
		// Only include the Actions and Notes Creator component, if an accountId, serviceId or contactId has been specified, and now error has been raised
		$bolIncludeCreatorComponent = ($bolUserHasPermissionToAddNotesAndActions && ($intAccountId || $intServiceId || $intContactId) && $strErrorMsg === NULL)? TRUE : FALSE;

		if ($bolIncludeCreatorComponent)
		{
			// Include the creator component
			echo "
<div id='ActionsAndNotesHeader'>
	<h2 class='Actions' style='float:left'>Actions / Notes</h2>
	<input type='button' onclick='document.getElementById(\"ActionsAndNotesCreatorContainer\").style.display = \"block\"; this.parentNode.removeChild(this);' style='float:right' value='New Action'></input>
	<div style='clear:both;float:none'></div>
</div>
<div id='ActionsAndNotesCreatorContainer' style='display:none;margin-bottom:0.5em'></div>\n";

			$strJsToInitialiseTheCreatorComponent = "Flex.EmbeddedActionsAndNotesCreator = ActionsAndNotes.Creator.createEmbeddedComponent(document.getElementById('ActionsAndNotesCreatorContainer'), $jsonAccountId, $jsonServiceId, $jsonContactId);
													Flex.EmbeddedActionsAndNotesCreator.display();";
		}
		else
		{
			// Don't include the creator component
			echo "<h2 class='Actions'>Actions / Notes</h2>\n";
		}
		
		if ($strErrorMsg !== NULL)
		{
			// An error occurred
			echo "
<div class='GroupedContent'>
	<div class='warning'>
		The following error occurred when compiling this component -
		<br />
		<em>". htmlspecialchars($strErrorMsg) ."</em>
		<br />
		Please notify your system administrators.
	</div>
</div>
<div class='SmallSeparator' style='clear:both'></div>\n";
		}
		else
		{
			// Do what you gotta do
			
			echo "
<div id='ActionsAndNotesListContainer'></div>
<div style='clear:both;margin:0.25em 0em 0.5em 0em'>
	<input type='button' value='View All' 
		onclick=\"Flex.ActionsAndNotesListPopup = ActionsAndNotes.List.createPopup($jsonPopupTitle, $jsonAATContextId, $jsonAATContextReferenceId, $jsonIncludeAllRelatableAATTypes, 99999);
				Flex.ActionsAndNotesListPopup.display();\" style='float:right'/>
	<div style='clear:both;float:none'></div>
</div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			ActionsAndNotes.setActionTypes($jsonActionTypes);
			ActionsAndNotes.setNoteTypes($jsonNoteTypes);
			ActionsAndNotes.load(	function()
									{
										". ($bolIncludeCreatorComponent ? $strJsToInitialiseTheCreatorComponent : "") ."
										Flex.EmbeddedActionsAndNotesList = ActionsAndNotes.List.createEmbeddedComponent(document.getElementById('ActionsAndNotesListContainer'), $jsonAATContextId, $jsonAATContextReferenceId, $jsonIncludeAllRelatableAATTypes, $jsonMaxRecordsPerPage);
										Flex.EmbeddedActionsAndNotesList.display();
									});

		}, false)
</script>\n";


		}
		
	}
}

?>

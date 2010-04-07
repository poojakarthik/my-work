<?php
//----------------------------------------------------------------------------//
// HtmlTemplate_ActionsAndNotesList
//----------------------------------------------------------------------------//
class HtmlTemplate_ActionsAndNotesList extends FlexHtmlTemplate
{
	public function __construct($iContext=NULL, $sId=NULL, $mDataToRender=NULL)
	{
		parent::__construct($iContext, $sId, $mDataToRender);
		$this->LoadJavascript("actions_and_notes");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	function Render()
	{
		// Not used currently... only static methods are used		
	}
	
	public static function renderActionsAndNotesList($iAccountId=null, $iServiceId=null, $iContactId=null, $bIncludeAllRelatableAATTypes=true, $iMaxRecordsPerPage=5, $iAATContextId, $iAATContextReferenceId)
	{
		$bPermissionToAdd 	= (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR) || AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR_EXTERNAL));
		$sErrorMsg			= NULL;

		try
		{
			// Retrieve all ActionTypes and NoteTypes (While running in javascript: ActionsAndNotes.load(...) will retrieve these from the server, retrieving
			// them now will mean one less request to the server)
			$aTypes = JSON_Handler_ActionsAndNotes::getActionAndNoteTypes();
			
			if ($aTypes['success'])
			{
				// Successfully retrieved them
				$oNoteTypes		= JSON_Services::encode($aTypes['noteTypes']);
				$oActionTypes	= JSON_Services::encode($aTypes['actionTypes']);
			}
			else
			{
				// Couldn't successfully retrieve them
				throw new Exception($aTypes['errorMessage']);
			}
		}
		catch (Exception $e)
		{
			$sErrorMsg = $e->getMessage();
		}

		// Set up things for the ActionsAndNotes list
		try 
		{
			$sHtml	= '';
			
			// Build the popup title
			$sPopupTitle = '';
			switch ($iAATContextId)
			{
				case ACTION_ASSOCIATION_TYPE_ACCOUNT:
					$oAccount		= Account::getForId($iAATContextReferenceId);
					$sPopupTitle	= $iAATContextReferenceId ." - ". $oAccount->getName();
					break;
				case ACTION_ASSOCIATION_TYPE_SERVICE:
					$oService		= Service::getForId($iAATContextReferenceId);
					$sPopupTitle	= GetConstantDescription($oService->serviceType, "service_type") ." - ". $oService->fNN;
					break;
				case ACTION_ASSOCIATION_TYPE_CONTACT:
					$oContact		= Contact::getForId($iAATContextReferenceId);
					$sPopupTitle	= $oContact->getName();
					break;
				default:
					throw new Exception("Invalid Context for Actions And Notes (Context: $iAATContextId)");
			}
			
			// Encode everything as JSON
			$sAATContextId					= JSON_Services::encode($iAATContextId);
			$sAATContextReferenceId			= JSON_Services::encode($iAATContextReferenceId);
			$sIncludeAllRelatableAATTypes	= JSON_Services::encode($bIncludeAllRelatableAATTypes);
			$sMaxRecordsPerPage				= JSON_Services::encode($iMaxRecordsPerPage);
			$sPopupTitle					= htmlspecialchars(JSON_Services::encode($sPopupTitle), ENT_QUOTES);
		}
		catch (Exception $e)
		{
			$sErrorMsg = $e->getMessage();
		}

		// Set up things for the ActionsAndNotes Creator component
		$sAccountId	= JSON_Services::encode($iAccountId);
		$sServiceId	= JSON_Services::encode($iServiceId);
		$sContactId	= JSON_Services::encode($iContactId);
		
		// Only include the Actions and Notes Creator component, if an accountId, serviceId or contactId has been specified, and now error has been raised
		$bIncludeCreatorComponent = ($bPermissionToAdd && ($iAccountId || $iServiceId || $iContactId) && $sErrorMsg === NULL)? TRUE : FALSE;
		
		$sHtml	= "	<div class='section'>" .
				"		<div class='section-header'>" .
				"			<div class='section-header-title'>" .
				"				<img src='../admin/img/template/action.png'/>" .
				"				<h2>Actions / Notes</h2>" .
				"			</div>" .
				"			<div class='section-header-options'>" .
				"				<button class='icon-button' onclick='document.getElementById(\"ActionsAndNotesCreatorContainer\").style.display = \"block\"; this.parentNode.removeChild(this);'>" .
				"					<img src='../admin/img/template/action_add.png'/>" .
				"					<span>New Action</span>" .
				" 				</button>" .
				"			</div>" .
				"		</div>";
		
		if ($bIncludeCreatorComponent)
		{
			// Include the creator component
			$sHtml	.= "<div class='section-content'>
							<div id='ActionsAndNotesCreatorContainer' style='display:none;margin-bottom:0.5em'></div>\n";
			
			$sJsToInitialiseTheCreatorComponent = "	Flex.EmbeddedActionsAndNotesCreator = ActionsAndNotes.Creator.createEmbeddedComponent(document.getElementById('ActionsAndNotesCreatorContainer'), $sAccountId, $sServiceId, $sContactId);
													Flex.EmbeddedActionsAndNotesCreator.display();";
		}
		else
		{
			$sHtml	.= "<div class='section-content'>";
		}
		
		if ($sErrorMsg !== NULL)
		{
			// An error occurred
			$sHtml	.= "	<div class='GroupedContent'>
								<div class='warning'>
									The following error occurred when compiling this component -
									<br />
									<em>". htmlspecialchars($sErrorMsg) ."</em>
									<br />
									Please notify your systeam administrators.
								</div>
							</div>
							<div class='SmallSeparator' style='clear:both'></div>
						</div>\n";
		}
		else
		{
			// Create list container and javascript content
			$sHtml	.= "	<div id='ActionsAndNotesListContainer'></div>" .
					"	</div>" .
					"	<div class='section-footer'>
							<button class='icon-button' onclick=\"	Flex.ActionsAndNotesListPopup = ActionsAndNotes.List.createPopup($sPopupTitle, $sAATContextId, $sAATContextReferenceId, $sIncludeAllRelatableAATTypes, 99999); Flex.ActionsAndNotesListPopup.display();\">
								<img src='../admin/img/template/magnifier.png'/>" .
								"<span>View All</span>
							</button>
						</div>
						<script type='text/javascript'>
							Event.observe(window, 'load', 
								function()
								{
									ActionsAndNotes.setActionTypes($sActionTypes);
									ActionsAndNotes.setNoteTypes($sNoteTypes);
									ActionsAndNotes.load(	function()
															{
																". ($bIncludeCreatorComponent ? $sJsToInitialiseTheCreatorComponent : "") ."
																Flex.EmbeddedActionsAndNotesList = ActionsAndNotes.List.createEmbeddedComponent(document.getElementById('ActionsAndNotesListContainer'), $sAATContextId, $sAATContextReferenceId, $sIncludeAllRelatableAATTypes, $sMaxRecordsPerPage);
																Flex.EmbeddedActionsAndNotesList.display();
															});
						
								}, false)
						</script>
					</div>
				</div>\n";
		}
		
		return $sHtml;
	}
}

?>

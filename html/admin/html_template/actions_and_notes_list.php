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
		$strErrorMsg = NULL;
		
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
			$jsonPopupTitle						= JSON_Services::encode($strPopupTitle);
		}
		catch (Exception $e)
		{
			$strErrorMsg = $e->getMessage();
		}
		
		echo "<h2 class='Notes'>Actions / Notes</h2>\n";
		
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
		onclick='Flex.ActionsAndNotesListPopup = ActionsAndNotes.List.createPopup($jsonPopupTitle, $jsonAATContextId, $jsonAATContextReferenceId, $jsonIncludeAllRelatableAATTypes, 99999);
				Flex.ActionsAndNotesListPopup.display();' style='float:right'/>
	<div style='clear:both;float:none'></div>
</div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			ActionsAndNotes.load(	function()
									{
										Flex.EmbeddedActionsAndNotesList = ActionsAndNotes.List.createEmbeddedComponent(document.getElementById('ActionsAndNotesListContainer'), $jsonAATContextId, $jsonAATContextReferenceId, $jsonIncludeAllRelatableAATTypes, $jsonMaxRecordsPerPage);
										Flex.EmbeddedActionsAndNotesList.display();
									});

		}, false)
</script>\n";


		}
		
	}
}

?>

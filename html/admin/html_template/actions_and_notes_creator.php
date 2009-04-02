<?php
//----------------------------------------------------------------------------//
// HtmlTemplateActionsAndNotesCreator
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateActionsAndNotesCreator
 *
 * HTML Template class for the ActionsAndNotesCreator HTML object
 *
 * HTML Template class for the ActionsAndNotesCreator HTML object
 * displays the form used to add an action or note
 *
 * @package	ui_app
 * @class	HtmlTemplateActionsAndNotesCreator
 * @extends	HtmlTemplate
 */
class HtmlTemplateActionsAndNotesCreator extends HtmlTemplate
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
		
		$intAccountId = (DBO()->Action->AccountId->IsSet)? DBO()->Action->AccountId->Value : null;
		$intServiceId = (DBO()->Action->ServiceId->IsSet)? DBO()->Action->ServiceId->Value : null;
		$intContactId = (DBO()->Action->ContactId->IsSet)? DBO()->Action->ContactId->Value : null;
		
		$jsonAccountId = JSON_Services::encode($intAccountId);
		$jsonServiceId = JSON_Services::encode($intServiceId);
		$jsonContactId = JSON_Services::encode($intContactId);
		
		try 
		{
			// Retrieve all Note Types
			$arrNoteTypes = Note_Type::getAll();
			$arrNoteTypesAssoc = array();
			foreach ($arrNoteTypes as $intKey=>$objNoteType)
			{
				$arrNoteTypesAssoc[$intKey] = $objNoteType->toArray(true);
			}
			$jsonNoteTypes = JSON_Services::encode($arrNoteTypesAssoc);

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
			$jsonActionTypes = JSON_Services::encode($arrActionTypesAssoc);
		}
		catch (Exception $e)
		{
			$strErrorMsg = $e->getMessage();
		}
		
		echo "<h2 class='Notes'>Create Actions / Notes</h2>\n";
		
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
<div id='AddActionContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			ActionsAndNotes.setActionTypes($jsonActionTypes);
			ActionsAndNotes.setNoteTypes($jsonNoteTypes);
			Flex.EmbeddedActionsAndNotesCreator = ActionsAndNotes.createActionsAndNotesCreatorEmbeddedComponent(document.getElementById('AddActionContainer'), $jsonAccountId, $jsonServiceId, $jsonContactId);
			Flex.EmbeddedActionsAndNotesCreator.display();
		}, false)
</script>\n";
		}
		
	}
}

?>

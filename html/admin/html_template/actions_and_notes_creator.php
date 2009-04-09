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
		
		$intAccountId = (DBO()->ActionCreator->AccountId->IsSet)? DBO()->ActionCreator->AccountId->Value : null;
		$intServiceId = (DBO()->ActionCreator->ServiceId->IsSet)? DBO()->ActionCreator->ServiceId->Value : null;
		$intContactId = (DBO()->ActionCreator->ContactId->IsSet)? DBO()->ActionCreator->ContactId->Value : null;
		
		$jsonAccountId = JSON_Services::encode($intAccountId);
		$jsonServiceId = JSON_Services::encode($intServiceId);
		$jsonContactId = JSON_Services::encode($intContactId);
		
		try 
		{
			// Retrieve all ActionTypes and NoteTypes
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
<div id='ActionsAndNotesCreatorContainer'></div>
<script type='text/javascript'>
	Event.observe(window, 'load', 
		function()
		{
			ActionsAndNotes.setActionTypes($jsonActionTypes);
			ActionsAndNotes.setNoteTypes($jsonNoteTypes);
			ActionsAndNotes.load(	function()
									{
										Flex.EmbeddedActionsAndNotesCreator = ActionsAndNotes.Creator.createEmbeddedComponent(document.getElementById('ActionsAndNotesCreatorContainer'), $jsonAccountId, $jsonServiceId, $jsonContactId);
										Flex.EmbeddedActionsAndNotesCreator.display();
									});
		}, false)
</script>\n";
		}
		
	}
}

?>

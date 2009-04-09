<?php

//----------------------------------------------------------------------------//
// AjaxFramework
//----------------------------------------------------------------------------//
/**
 * AjaxFramework
 *
 * Ajax container
 *
 * Ajax container. Manages the construction of a JSON object to send as a reply for an AJAX request
 *
 * @prefix	ajax
 *
 * @package	ui_app
 * @class	AjaxFramework
 */
class AjaxFramework
{
	//------------------------------------------------------------------------//
	// _arrCommands
	//------------------------------------------------------------------------//
	/**
	 * _arrCommands
	 *
	 * List of commands which will be handled by the Vixen.Ajax.HandleReply
	 *
	 * List of commands which will be handled by the Vixen.Ajax.HandleReply
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrCommands = Array();

	//------------------------------------------------------------------------//
	// instance
	//------------------------------------------------------------------------//
	/**
	 * instance()
	 *
	 * Returns a singleton instance of this class
	 *
	 * Returns a singleton instance of this class
	 *
	 * @return	__CLASS__
	 *
	 * @method
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$instance = new self();
		}
		return $instance;
	}
	
	//------------------------------------------------------------------------//
	// Reply
	//------------------------------------------------------------------------//
	/**
	 * Reply()
	 *
	 * Sends the list of commands as an AjaxReply
	 *
	 * Sends the list of commands as an AjaxReply
	 * 
	 * @return	void		
	 *
	 * @method
	 */
	function Reply()
	{
		// Convert the commands to a json object
		$strReply = Json()->encode($this->_arrCommands);
		
		// Append "//JSON" to the front of the json object so that the reply handler knows it is a json object and not anything else (like html code)
		$strReply = "//JSON". $strReply;
		
		// Send the reply
		echo $strReply;
	}
	
	//------------------------------------------------------------------------//
	// AddCommand
	//------------------------------------------------------------------------//
	/**
	 * AddCommand()
	 *
	 * Adds a javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply
	 *
	 * Adds a javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply
	 * 
	 * @param	string		$strType	command type
	 * @param	mixed		$mixData	command data
	 *
	 * @return	void
	 * @method
	 */
	function AddCommand($strType, $mixData=NULL)
	{
		$arrCommand['Type'] = $strType;
		$arrCommand['Data'] = $mixData;
		$this->_arrCommands[] = $arrCommand;
	}
	
	//------------------------------------------------------------------------//
	// HasCommands
	//------------------------------------------------------------------------//
	/**
	 * HasCommands()
	 *
	 * Returns TRUE if any commands have been added to this object, else returns FALSE
	 *
	 * Returns TRUE if any commands have been added to this object, else returns FALSE
	 * 
	 * @return	void
	 * @method
	 */
	function HasCommands()
	{
		return (bool)count($this->_arrCommands);
	}
	
	//------------------------------------------------------------------------//
	// RenderHtmlTemplate
	//------------------------------------------------------------------------//
	/**
	 * RenderHtmlTemplate()
	 *
	 * Adds a command to the list of commands, that is handled by the AjaxReplyHandler, which renders the Html Template
	 *
	 * Adds a command to the list of commands, that is handled by the AjaxReplyHandler, which renders the Html Template
	 * The rendered Html code will be placed in the div defined by $intContainerDivId.  The existing contents of the div will be destroyed.
	 * This command will actually destroy the div identified by $intContainerDivId, and create a new one.  Therefore any attributes declared
	 * for the div will be lost.
	 * 
	 * @param	string		$strHtmlTemplate	Full name of the HtmlTemplate class, to be rendered (ie HtmlTemplateContactEdit)
	 * @param	integer		$intContext			Context with which to render the Html Template (ie HTML_CONTEXT_CONTACT_EDIT)
	 * @param	string		$strContainerDivId	The id of the Div that the HtmlTemplate will be rendered in.
	 *											Anything currently in this div will be destroyed.
	 * @param	obj			$objAjax			optional, Ajax object
	 * @param	integer		$intMode			optional, The mode number to set
	 *											ie AJAX_MODE, HTML_MODE
	 *
	 * @return	void
	 * @method
	 */
	function RenderHtmlTemplate($strHtmlTemplate, $intContext, $strContainerDivId, $objAjax=NULL, $intTemplateMode=HTML_MODE)
	{
		// Start output buffering as we want to be able to capture rendered Html code
		ob_start();
		
		// Create the Html Template object
		$strClassName = "HtmlTemplate$strHtmlTemplate";
		$objHtmlTemplate = new $strClassName($intContext, $strContainerDivId);
		$objHtmlTemplate->SetMode($intTemplateMode, $objAjax);

		// Capture the rendered html code
		$objHtmlTemplate->Render();
		$strHtmlCode = ob_get_contents();
		
		// Set up the command object
		$arrCommand['Type'] = "ReplaceDivContents";
		$arrCommand['ContainerDivId'] = $strContainerDivId;
		$arrCommand['Data'] = $strHtmlCode;
		
		// Clean the output buffer
		ob_end_clean();
		
		$this->_arrCommands[] = $arrCommand;
	}
	
	//------------------------------------------------------------------------//
	// ReplaceDivContents
	//------------------------------------------------------------------------//
	/**
	 * ReplaceDivContents()
	 *
	 * Adds a command to the list of commands, that is handled by the AjaxReplyHandler, which replaces the contents of a div
	 *
	 * Adds a command to the list of commands, that is handled by the AjaxReplyHandler, which replaces the contents of a div
	 * The Html code will be placed in the div defined by $intContainerDivId.  The existing contents of the div will be destroyed.
	 * This command will actually destroy the div identified by $intContainerDivId, and create a new one.  Therefore any attributes declared
	 * for the div will be lost.
	 * 
	 * @param	string		$strHtmlCode		The html code to place in the div
	 * @param	integer		$intContainerDivId	The id of the Div who's innerHTML will be set to $strHtmlCode.
	 *											Anything currently in this div will be destroyed.
	 *
	 * @return	void
	 * @method
	 */
	function ReplaceDivContents($strHtmlCode, $intContainerDivId)
	{
		$arrCommand['Type'] = "ReplaceDivContents";
		$arrCommand['ContainerDivId'] = $intContainerDivId;
		$arrCommand['Data'] = $strHtmlCode;
		
		$this->_arrCommand[] = $arrCommand;
	}
	
	//------------------------------------------------------------------------//
	// AppendHtmlToElement
	//------------------------------------------------------------------------//
	/**
	 * AppendHtmlToElement()
	 *
	 * Adds a command to the list of commands, that is handled by the AjaxReplyHandler, which appends html code to the specified element
	 *
	 * Adds a command to the list of commands, that is handled by the AjaxReplyHandler, which appends html code to the specified element
	 * The Html code will be appended to the element's innerHTML.
	 * Note that this might not execute any javascript defined in $strHtmlCode.
	 * 
	 * @param	string		$strHtmlCode		html code to append to the element
	 * @param	integer		$intElementId		id of the element with which the html code will be appended to
	 *
	 * @return	void
	 * @method
	 */
	function AppendHtmlToElement($strHtmlCode, $intElementId)
	{
		$arrCommand['Type'] = "AppendHtmlToElement";
		$arrCommand['ElementId'] = $intElementId;
		$arrCommand['Data'] = $strHtmlCode;
		
		$this->_arrCommand[] = $arrCommand;
	}
	
	//------------------------------------------------------------------------//
	// FireEvent
	//------------------------------------------------------------------------//
	/**
	 * FireEvent()
	 *
	 * Adds a FireEvent javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply
	 *
	 * Adds a FireEvent javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply
	 * 
	 * @param	string		$strEventType	Name of the Event
	 * @param	mixed		$mixData		The Event's specific data
	 *
	 * @return	void
	 * @method
	 */
	function FireEvent($strEventType, $mixData=NULL)
	{
		$this->AddCommand("FireEvent", Array("Event"=>$strEventType, "EventData"=>$mixData));
	}
	
	//------------------------------------------------------------------------//
	// FireOnNewNoteEvent
	//------------------------------------------------------------------------//
	/**
	 * FireOnNewNoteEvent()
	 *
	 * Adds a FireEvent javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply, specifically for the OnNewNote Event
	 *
	 * Adds a FireEvent javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply, specifically for the OnNewNote Event.
	 * This Event has been wrapped in its own function because of how often it is used
	 * 
	 * ALL PARAMETERS HAVE BEEN DEPRECATED
	 * @param	integer		$intAccountId	The account that the note is associated with.  Specifiy as NULL, if the note is note associated with an account\
	 *										(Note that this is not optional.  You will have to explicitly declare it as null if the note is not
	 *										associated with an account)
	 * @param 	integer		$intServiceId	optional, The Id of the service that the note is associated with (defaults to NULL)
	 * @param 	integer		$intContactId	optional, The Id of the contact that the note is associated with (defaults to NULL)
	 * @param	integer 	$intNoteType	optional, The NoteType of the note.  This is useful if the new note is a system note, 
	 *										as some listeners should not do anything if the NoteType is SYSTEM_NOTE_TYPE. Defaults to SYSTEM_NOTE_TYPE
	 *
	 * @return	void
	 * @method
	 */
	function FireOnNewNoteEvent()
	{
		$this->AddCommand("ExecuteJavascript", Array("if (window.ActionsAndNotes) {ActionsAndNotes.fireEvent('NewNote');}"));
	}

	function FireOnNewActionEvent()
	{
		$this->AddCommand("ExecuteJavascript", Array("if (window.ActionsAndNotes) {ActionsAndNotes.fireEvent('NewAction');}"));
	}
	
}

?>

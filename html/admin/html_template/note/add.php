<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add.php
//----------------------------------------------------------------------------//
/**
 * add
 *
 * HTML Template for the Add Note HTML object
 *
 * HTML Template for the Add Note HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a note.
 *
 * @file		add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateNoteAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateNoteAdd
 *
 * HTML Template class for the Add Note HTML object
 *
 * HTML Template class for the Add Note HTML object
 * displays the form used to add a note
 *
 * @package	ui_app
 * @class	HtmlTemplateNoteAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateNoteAdd extends HtmlTemplate
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
		
		$this->LoadJavascript("notes");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_PAGE:
				$this->_RenderInPage();
				break;
			case HTML_CONTEXT_POPUP:
				$this->_RenderAsPopup();
				break;
			default:
				$this->_RenderForm();
		}
	}

	//------------------------------------------------------------------------//
	// _RenderForm
	//------------------------------------------------------------------------//
	/**
	 * _RenderForm()
	 *
	 * Renders the form elements for the Add Note Html Template
	 *
	 * Renders the form elements for the Add Note Html Template
	 * The part that is common for when rendered in a page or as a popup
	 *
	 * @method
	 */
	function _RenderForm()
	{
		// Render Hidden Values
		DBO()->NoteDetails->AccountNotes->RenderHidden();
		DBO()->NoteDetails->ServiceNotes->RenderHidden();
		DBO()->NoteDetails->ContactNotes->RenderHidden();
		
		if (DBO()->NoteDetails->AccountNotes->Value)
		{
			// We are dealing with Account Notes
			// Do Account Specific notes stuff
			DBO()->Account->Id->RenderHidden();
		}
		elseif (DBO()->NoteDetails->ServiceNotes->Value)
		{
			// We are dealing with Service Notes
			// Do Service Specific notes stuff
			DBO()->Service->Id->RenderHidden();
		}
		elseif (DBO()->NoteDetails->ContactNotes->Value)
		{
			// We are dealing with Contact Notes
			// Do Contact Specific notes stuff
			DBO()->Contact->Id->RenderHidden();
		}
		
		// Draw the Note TextArea
		echo "<textarea id='Note.Note' name='Note.Note' rows='6' class='DefaultInputTextArea' style='overflow:auto;left:0px;width:100%;border:solid 1px #D1D1D1'></textarea>\n";
		
		// Draw the Note Type combobox
		DBL()->AvailableNoteTypes->SetTable("NoteType");
		DBL()->AvailableNoteTypes->OrderBy("TypeLabel");
		DBL()->AvailableNoteTypes->Load();
		
		echo "<div style='height:25px'>\n";
		echo "   <div class='Left'>\n";
		echo "      <span>&nbsp;&nbsp;Note Type</span>\n";
		echo "      <span>\n";
		echo "         <select id='NoteTypeCombo' name='Note.NoteType'style='border:solid 1px #D1D1D1'>\n";
		// add each Note Type
		foreach (DBL()->AvailableNoteTypes as $dboNoteType)
		{
			$strNoteTypeLabel	= $dboNoteType->TypeLabel->Value;
			$intNoteTypeId		= $dboNoteType->Id->Value;
			
			if ($intNoteTypeId == SYSTEM_NOTE_TYPE)
			{
				// The user cannot choose the System note type
				continue;
			}
			
			$strNoteTypeStyle	= "border: solid 1px #{$dboNoteType->BorderColor->Value};";
			$strNoteTypeStyle	.= " background-color: #{$dboNoteType->BackgroundColor->Value};";
			$strNoteTypeStyle	.= " color: #{$dboNoteType->TextColor->Value};";
			
			// check if the row that you are adding is the currently selected row
			$strSelected = ($intNoteTypeId == DBO()->Note->NoteType->Value) ? "selected='selected'" : "";
			
			echo "            <option id='NoteType.$intNoteTypeId' value='$intNoteTypeId' $strSelected style='$strNoteTypeStyle'>$strNoteTypeLabel</option>\n";
		}
		echo "         </select>\n";
		echo "      </span>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Add the Account Checkbox, if this is a Service or Contact note
		if (DBO()->NoteDetails->ServiceNotes->Value || DBO()->NoteDetails->ContactNotes->Value)
		{
			// This checkbox should default to being ticked
			DBO()->Note->IsAccountNote = TRUE;
			DBO()->Note->IsAccountNote->RenderInput();
		}
	}

	//------------------------------------------------------------------------//
	// _RenderInPage
	//------------------------------------------------------------------------//
	/**
	 * _RenderInPage()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderInPage()
	{
		$this->FormStart("AddNote", "Note", "SaveNewNote");
		
		echo "<h2 class='Notes'>Add Note</h2>\n";
		
		echo "<div class='NarrowContent'>";

		// Set the default NoteType
		DBO()->Note->NoteType = GENERAL_NOTE_TYPE;

		// Render the form
		$this->_RenderForm();
		
		echo "</div>\n";  // NarrowContent
		
		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->AjaxSubmit("Add Note");
		echo "</div></div>\n";
		
		$this->FormEnd();
		
		echo "<script type='text/javascript'>VixenCreateNoteAddObject(); Vixen.NoteAdd.Initialise();</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderAsPopup
	//------------------------------------------------------------------------//
	/**
	 * _RenderAsPopup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderAsPopup()
	{
		$this->FormStart("AddNote", "Note", "SaveNewNote");
		
		echo "<div class='NarrowContent'>";
		
		// Render details about the note
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		
		if (DBO()->NoteDetails->ServiceNotes->Value)
		{	
			// The note is a service note
			DBO()->Service->FNN->RenderOutput();
		}
		if (DBO()->NoteDetails->ContactNotes->Value)
		{
			// The Note is a contact note
			$strFullName = DBO()->Contact->Title->Value . " " . DBO()->Contact->FirstName->Value . " " . DBO()->Contact->LastName->Value;
			DBO()->Contact->FullName->RenderArbitrary($strFullName, RENDER_OUTPUT);
		}

		// Set the default NoteType
		DBO()->Note->NoteType = GENERAL_NOTE_TYPE;

		// Render the form
		$this->_RenderForm();
		
		echo "</div>\n";  // NarrowContent
		
		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		$this->AjaxSubmit("Add Note");
		echo "</div></div>\n";
		
		$this->FormEnd();
		
		echo "<script type='text/javascript'>VixenCreateNoteAddObject(); Vixen.NoteAdd.Initialise('{$this->_objAjax->strId}');</script>\n";
	}
	
}

?>

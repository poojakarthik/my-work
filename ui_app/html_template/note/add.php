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
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

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
		$this->FormStart("AddNote", "Note", "Add");
		
		// Include all the properties necessary to add the record, which shouldn't have controls visible on the form
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_CONTACT_NOTE:
				echo "<div class='WideForm'>\n";
				DBO()->Contact->Id->RenderHidden();
				DBO()->Note->Contact = DBO()->Contact->Id->Value;
				DBO()->Note->Contact->RenderHidden();
				
				$strFullName = DBO()->Contact->FirstName->Value . " " . DBO()->Contact->LastName->Value;
				DBO()->Contact->FullName->RenderArbitrary($strFullName, RENDER_OUTPUT);
				DBO()->Contact->Id->RenderOutput();
				break;
				
			case HTML_CONTEXT_SERVICE_NOTE:
				echo "<div class='WideForm'>\n";
				DBO()->Service->Id->RenderHidden();
				DBO()->Note->Service = DBO()->Service->Id->Value;
				DBO()->Note->Service->RenderHidden();

				DBO()->Service->FNN->RenderOutput();
				DBO()->Service->Id->RenderOutput();
				break;
				
			case HTML_CONTEXT_ACCOUNT_NOTE:
			default:
				echo "<div class='WideForm'>\n";
				DBO()->Account->Id->RenderHidden();
				break;
		}
		
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Note->Note->RenderInput(CONTEXT_DEFAULT, TRUE);
		
		// create a combobox containing all Note Types
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Note Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='NoteTypeCombo' name='Note.NoteType'>\n";
		
		// add each Note Type
		foreach (DBL()->AvailableNoteTypes as $dboNoteType)
		{
			$strNoteTypeLabel	= $dboNoteType->TypeLabel->Value;
			$intNoteTypeId		= $dboNoteType->Id->Value;
			$strNoteTypeStyle	= "border: solid 1px #{$dboNoteType->BorderColor->Value};"; 
			$strNoteTypeStyle	.= " background-color: #{$dboNoteType->BackgroundColor->Value};";
			$strNoteTypeStyle	.= " color: #{$dboNoteType->TextColor->Value};";
			
			// check if the row that you are adding is the currently selected row
			$strSelected = ((DBO()->Note->NoteType->Value) && ($intNoteTypeId == DBO()->Note->NoteType->Value)) ? "selected='selected'" : "";
			
			echo "         <option id='NoteType.$intNoteTypeId' value='$intNoteTypeId' $strSelected style='$strNoteTypeStyle'>$strNoteTypeLabel</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Output the "Show this note in Account Notes" checkbox
		if (($this->_intContext == HTML_CONTEXT_CONTACT_NOTE) || ($this->_intContext == HTML_CONTEXT_SERVICE_NOTE))
		{
			DBO()->Note->IsAccountNote->RenderInput();
		}
		
		// output the manditory field message
		echo "<div class='DefaultElement'><span class='RequiredInput'>*</span>&nbsp;Required Field</div>\n";
		
		echo "</div>\n";  // WideForm
		
		// create the submit button
		echo "<div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		$this->AjaxSubmit("Add Note");
		echo "</div>\n";
		
		// give the Note text area initial focus
		echo "<script type='text/javascript'>document.getElementById('Note.Note').focus();</script>\n";
		
		$this->FormEnd();
	}
}

?>

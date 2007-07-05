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
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
		//$this->LoadJavascript("note_popup");
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
			case HTML_CONTEXT_ACCOUNT_NOTE:
				$this->_RenderAccountNote();
				break;
			default:
				$this->_RenderAccountNote();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// RenderAccountNote
	//------------------------------------------------------------------------//
	/**
	 * RenderAccountNote()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	private function _RenderAccountNote()
	{	
		echo "<div class='PopupMedium'>\n";
		echo "<h2 class='Note'>Add Account Note</h2>\n";
		
		$this->FormStart("AddNote", "Note", "Add");
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
		DBO()->Account->Id->RenderHidden();
		
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Note->Note->RenderInput();
		
		// check if the popup is being opened or being redrawn
		// DBO()->NoteType->Id is set in the SubmittedForm block of code, in the application template
		/*if (!DBO()->NoteType->Id->Value)
		{
			// The popup has just been opened and nothing has been sent yet, so set the Note Type to the first one in the list
			DBL()->AvailableNoteTypes->rewind();
			//HACK HACK HACK (This makes it default to the NoteType with Id == 1)
			DBO()->Note->NoteType = DBL()->AvailableNoteTypes->current()->Id->Value;
		}*/
		//DBO()->NoteType->Id->RenderHidden();
		
		// create a combobox containing all Note Types
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>Note Type:</div>\n";
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
			if ((DBO()->Note->NoteType->Value) && ($intNoteTypeId == DBO()->Note->NoteType->Value))
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}
			
			echo "         <option id='NoteType.$intNoteTypeId' value='$intNoteTypeId' $strSelected style='$strNoteTypeStyle'>$strNoteTypeLabel</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Render the status message, if there is one
		DBO()->Status->Message->RenderOutput();
		
		// create the submit button
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		echo "<input type='button' value='Close' class='InputSubmit' onclick=\"Vixen.Popup.Close('AddNotePopupId');\"></input>\n";
		$this->AjaxSubmit("Add Note");
		echo "</div>\n";
		
		$this->FormEnd();
		echo "</div>\n";
	}
}

?>

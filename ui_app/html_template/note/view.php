<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// view.php
//----------------------------------------------------------------------------//
/**
 * view
 *
 * HTML Template for the View Notes HTML object
 *
 * HTML Template for the View Notes HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all notes relating to either an account, contact or service and can be embedded in
 * various Page Templates or popup windows
 *
 * @file		view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateNoteView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateNoteView
 *
 * HTML Template class for the View Notes HTML object
 *
 * HTML Template class for the View Notes HTML object
 * Lists all Notes related to an account, contact or service
 *
 * @package	ui_app
 * @class	HtmlTemplateNoteView
 * @extends	HtmlTemplate
 */
class HtmlTemplateNoteView extends HtmlTemplate
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
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("debug");  // Tools for debugging, only use when js-ing
	}
	
	function Render()
	{
		switch ($this->_intContext)
		{
			case NOTE_CLASS_SERVICE_NOTES:
				$this->NoteRender();
				break;
			default:
				$this->RenderForm();
				break;
		}
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
	function RenderForm()
	{
		$this->FormStart("NoteTypeForm", "Note", "View");
		DBO()->Note->NoteGroupId->RenderHidden();
		DBO()->Note->NoteClass->RenderHidden();
		
		$strAll = 'checked';

		switch (DBO()->Note->NoteType->Value)
		{
			case "All":
				$strAll = 'checked';
				break;
			case "System":
				$strSystem = 'checked';	
				break;
			case "User":
				$strUser = 'checked';
				break;				
		}

		echo "<input type='radio' name='Note.NoteType' value='All' $strAll onClick='Vixen.Ajax.SendForm(\"VixenForm_NoteTypeForm\", \"\", \"Note\", \"View\", \"Popup\", \"ViewNotesPopupId\");'>All Notes</input>";
		echo "<input type='radio' name='Note.NoteType' value='System' $strSystem onClick='Vixen.Ajax.SendForm(\"VixenForm_NoteTypeForm\", \"\", \"Note\", \"View\", \"Popup\", \"ViewNotesPopupId\");'>System Notes Only</input>";
		echo "<input type='radio' name='Note.NoteType' value='User' $strUser onClick='Vixen.Ajax.SendForm(\"VixenForm_NoteTypeForm\", \"\", \"Note\", \"View\", \"Popup\", \"ViewNotesPopupId\");'>User Notes Only</input>";
		//echo "<input type='checkbox' name='Note.SystemOnly' value=1 $strChecked onClick='Vixen.Ajax.SendForm(\"VixenForm_SystemNotesOnlyForm\", \"\", \"Note\", \"View\", \"Popup\", \"ViewNotesPopupId\");'>Show System Notes Only</input>";
		$this->FormEnd();
		$this->NoteRender();
		
	}
	
	function NoteRender()
	{
		//echo "<div id='NoteListContainer' style='overflow:auto; height:500px; border-left: 1px solid #D1D1D1; border-top: 1px solid #D1D1D1; border-bottom: 1px solid #D1D1D1'>\n";
		
		echo "<h2>Recent Notes</h2><div class='DefaultRegularOutput'>The 5 most recent notes are listed below:</div>";
		
		if (DBL()->Note->RecordCount() == 0)
		{
			echo "<div class='DefaultRegularOutput'>There are no viewable Notes.</div>";
		}
		
		// not limitting to 5 records!!!

		// Display each note
		foreach (DBL()->Note as $dboNote)
		{
			// Find what NoteType this note is and render it accordingly
			foreach (DBL()->NoteType as $dboNoteType)
			{
				if ($dboNoteType->Id->Value == $dboNote->NoteType->Value)
				{
					// Use this NoteType 
					$strBorderColor 	= $dboNoteType->BorderColor->Value;
					$strBackgroundColor = $dboNoteType->BackgroundColor->Value;
					$strTextColor 		= $dboNoteType->TextColor->Value;
					break;
				}
			}
			
			// setup the div to reflect the Note Type
			echo "<div style='border: solid 1px #{$strBorderColor}; background-color: #{$strBackgroundColor}; color: #{$strTextColor}; padding: 3px'>\n";
			
			// Note details
			$strDetailsHtml = "Created on ";
			$strDetailsHtml .= $dboNote->Datetime->FormattedValue();
			$strDetailsHtml .= " by ";
			if ($dboNote->Employee->Value)
			{
				$strDetailsHtml .= GetEmployeeName($dboNote->Employee->Value) . ".";
			}
			else
			{
				$strDetailsHtml .= "Automated System.";
			}
			
			// Output the note details
			echo "<span style='font-size: 9pt'>$strDetailsHtml</span>\n";
			echo "<div class='TinySeperator'></div>\n";
			
			// Output the actual note
			$dboNote->Note->RenderValue();
			echo "</div>\n";
			
			// Include a separator
			echo "<div class='SmallSeperator'></div>\n";
		}
		//echo "</div>\n";  //NoteListContainer
	}
}

?>

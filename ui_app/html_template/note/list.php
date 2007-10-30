<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// list.php
//----------------------------------------------------------------------------//
/**
 * list
 *
 * HTML Template for the View Notes HTML object
 *
 * HTML Template for the View Notes HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all notes relating to either an account, contact or service and can be embedded in
 * various Page Templates or popup windows
 *
 * @file		list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateNoteList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateNoteList
 *
 * HTML Template class for the View Notes HTML object
 *
 * HTML Template class for the View Notes HTML object
 * Lists all Notes related to an account, contact or service
 *
 * @package	ui_app
 * @class	HtmlTemplateNoteList
 * @extends	HtmlTemplate
 */
class HtmlTemplateNoteList extends HtmlTemplate
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
		
		$this->LoadJavascript("notes");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	function Render()
	{
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_POPUP:
				$this->_RenderAsPopup();		
				break;
			case HTML_CONTEXT_PAGE:
				$this->_RenderInPage();
				break;
			default:
				$this->_RenderInPage();	
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderInPage()
	//------------------------------------------------------------------------//
	/**
	 * _RenderInPage()
	 *
	 * Render the Form element
	 *
	 * Render the Form element on the Note HTML element
	 * And sets the checkbox status
	 *
	 * @method
	 */
	private function _RenderInPage()
	{
		if (DBO()->NoteDetails->AccountNotes->Value)
		{
			// We are showing Account Notes
			$strListTitle = "Account Notes";
			
			// Store details for the button to view all account notes
			$strViewAllNotesLink	= Href()->ViewAccountNotes(DBO()->Account->Id->Value);
			$strViewAllNotesLabel	= "View All";
			
			// Store details for the button to add an account note
			$strAddNoteLink		= Href()->AddAccountNote(DBO()->Account->Id->Value);
			$strAddNoteLabel	= "Add Note";
		}
		elseif (DBO()->NoteDetails->ServiceNotes->Value)
		{
			// We are showing Service Notes
			$strListTitle = "Service Notes";
			
			// Store details for the button to view all service notes
			$strViewAllNotesLink	= Href()->ViewServiceNotes(DBO()->Service->Id->Value);
			$strViewAllNotesLabel	= "View All";
			
			// Store details for the button to add a service note
			$strAddNoteLink		= Href()->AddServiceNote(DBO()->Service->Id->Value);
			$strAddNoteLabel	= "Add Note";
		}
		echo "<h2 class='Notes'>$strListTitle</h2>\n";
		
		// Render filtering controls
		echo "<div class='NarrowContent'>";
		
		// Render the NoteType Filter
		$arrFilterOptions = Array();
		$arrFilterOptions[NOTE_FILTER_ALL]		= "All Notes Types";
		$arrFilterOptions[NOTE_FILTER_USER]		= "User Notes";
		$arrFilterOptions[NOTE_FILTER_SYSTEM]	= "System Notes";
		
		// Create a combobox containing all the filter options
		echo "<div style='height:25px'>";
		echo "<div class='Left'>";
		echo "   <span>Filter</span>\n";
		echo "   <span>\n";
		echo "      <select id='NoteFilterCombo' onChange='Vixen.NoteList.intNoteFilter = this.value; Vixen.NoteList.ApplyFilter();' style='width:100%'>\n";
		foreach ($arrFilterOptions as $intFilterOption=>$strFilterOption)
		{
			$strSelected = (DBO()->NoteDetails->FilterOption->Value == $intFilterOption) ? "selected='selected'" : "";
			echo "         <option $strSelected value='$intFilterOption'><span>$strFilterOption</span></option>\n";
		}
		echo "      </select>\n";
		echo "   </span>\n";
		
		// currently the filter is applied when the value of the combobox changes
		//$this->Button("Filter", "Vixen.NoteList.ApplyFilter();");
		echo "</div>\n"; //Left
		// Create button for adding a new note
		echo "<div class='Right'>\n";
		$this->Button($strAddNoteLabel, $strAddNoteLink);
		echo "</div>\n"; //Right
		echo "</div>\n"; //height=40px
		echo "</div>\n"; // NarrowContent
		echo "<div class='TinySeperator'></div>\n";
		
	
		// Render the notes
		echo "<div id='NotesContainer'>";
		$this->_RenderNotes();
		echo "</div>";
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button($strViewAllNotesLabel, $strViewAllNotesLink);
		echo "</div></div>\n";
		
		// Initialise the javascript object
		$intAccountId	= (DBO()->NoteDetails->AccountNotes->Value) ? DBO()->Account->Id->Value : "null";
		$intServiceId	= (DBO()->NoteDetails->ServiceNotes->Value) ? DBO()->Service->Id->Value : "null";
		$intContactId	= (DBO()->NoteDetails->ContactNotes->Value) ? DBO()->Contact->Id->Value : "null";
		$intNoteFilter	= DBO()->NoteDetails->FilterOption->Value;
		$strJavascript	= "VixenCreateNoteListObject(); Vixen.NoteList.Initialise($intAccountId, $intServiceId, $intContactId, $intNoteFilter);";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}

	private function _RenderFilter()
	{
		//TODO remove the filter control code from _RenderInPage and stick it here
		// So that _RenderInPage and _RenderAsPopup can use it
	}
	
	private function _RenderHeader()
	{
		//TODO remove the header code (and the creation/initialisation of the Vixen.NoteList object) and stick it here
		// So that _RenderInPage and _RenderAsPopup can use it
	}

	
	//------------------------------------------------------------------------//
	// _RenderNotes()
	//------------------------------------------------------------------------//
	/**
	 * _RenderNotes()
	 *
	 * Render the Form element
	 *
	 * Render the Form element on the Note HTML element
	 * And sets the checkbox status
	 *
	 * @method
	 */
	private function _RenderNotes()
	{
		DBL()->NoteType->Load();
		
		if (DBL()->Note->RecordCount() == 0)
		{
			echo "<div class='NarrowContent'>\n";
			echo "<div class='DefaultOutputSpan'>There are no notes to display</div>\n";
			echo "</div>\n";
		}
		
		// Set up the DBObject used to retrieve FNNs for Service Notes
		DBO()->NoteService->SetTable("Service");
		
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
			$strDetailsHtml .= "<br />by ";
			if ($dboNote->Employee->Value)
			{
				$strDetailsHtml .= GetEmployeeName($dboNote->Employee->Value) . ".";
			}
			else
			{
				$strDetailsHtml .= "Automated System.";
			}
			
			if ($dboNote->Service->Value && DBO()->NoteDetails->AccountNotes->Value)
			{
				// The note references a service and we are displaying Account Notes
				// Create a link to the service
				$strServiceLink = Href()->ViewServiceDetails($dboNote->Service->Value);
				
				// Retrieve the FNN of the Service
				DBO()->NoteService->Id = $dboNote->Service->Value;
				if (DBO()->NoteService->Load())
				{
					$strDetailsHtml .= "<br />FNN: <a href='$strServiceLink'>". DBO()->NoteService->FNN->Value ."</a>";
				}
			}
			
			// Output the note details
			echo "<span style='font-size: 9pt'>$strDetailsHtml</span>\n";
			echo "<div class='TinySeperator'></div>\n";
			
			// Output the actual note
			$dboNote->Note->RenderValue();
			echo "</div>\n";
			
			// Include a separator
			echo "<div class='TinySeperator'></div>\n";
		}
	}
	
	//------------------------------------------------------------------------//
	// _RenderAsPopup()
	//------------------------------------------------------------------------//
	/**
	 * _RenderAsPopup()
	 *
	 * Render the Form element
	 *
	 * Render the Form element on the Note HTML element
	 * And sets the checkbox status
	 *
	 * @method
	 */
	private function _RenderAsPopup()
	{
		//TODO Finish this some time
		// Currently the View Notes Popup uses an older HtmlTemplate to show the list
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
		$this->FormEnd();
	}
}

?>

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
 * Pages or popup windows
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
				$this->_RenderNotes();	
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
		}
		elseif (DBO()->NoteDetails->ServiceNotes->Value)
		{
			// We are showing Service Notes
			$strListTitle = "Service Notes";
			
			// Store details for the button to view all service notes
			$strViewAllNotesLink	= Href()->ViewServiceNotes(DBO()->Service->Id->Value);
			$strViewAllNotesLabel	= "View All";
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
		echo "      <select id='NoteFilterCombo' onChange='Vixen.NoteList.intNoteFilter = this.value; Vixen.NoteList.ApplyFilter(true);' style='width:100%'>\n";
		foreach ($arrFilterOptions as $intFilterOption=>$strFilterOption)
		{
			$strSelected = (DBO()->NoteDetails->FilterOption->Value == $intFilterOption) ? "selected='selected'" : "";
			echo "         <option $strSelected value='$intFilterOption'><span>$strFilterOption</span></option>\n";
		}
		echo "      </select>\n";
		echo "   </span>\n";
		
		$intMaxNotes = DBO()->NoteDetails->MaxNotes->Value;
		echo "<span>Limit</span><input type='text' class='DefaultInputText' style='left:0px;width:50px;margin-left:5px' id='NoteDetails.MaxNotes' value='$intMaxNotes' onChange='Vixen.NoteList.intMaxNotes = this.value; Vixen.NoteList.ApplyFilter(true);'></input>\n";

		// currently the filter is applied when the value of the combobox changes
		//$this->Button("Filter", "Vixen.NoteList.ApplyFilter();");

		echo "</div>\n"; //Left
		echo "</div>\n"; //height=40px
		echo "</div>\n"; // NarrowContent
		echo "<div class='TinySeperator'></div>\n";
		
	
		// Render the notes
		$strNotesContainerDivId = "NotesContainer";
		echo "<div id='$strNotesContainerDivId'>";
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

		$strJavascript	= "VixenCreateNoteListObject(); Vixen.NoteList.Initialise($intAccountId, $intServiceId, $intContactId, $intNoteFilter, $intMaxNotes, '$strNotesContainerDivId', true); Vixen.NoteList.RegisterListeners();";
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
		echo "<div class='NarrowContent'>";
		
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
		
		// Render filtering controls
		// Render the NoteType Filter
		$arrFilterOptions = Array();
		$arrFilterOptions[NOTE_FILTER_ALL]		= "All Notes Types";
		$arrFilterOptions[NOTE_FILTER_USER]		= "User Notes";
		$arrFilterOptions[NOTE_FILTER_SYSTEM]	= "System Notes";
		
		// Create a combobox containing all the filter options
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div style='height:25px'>";
		echo "<div class='Left'>";
		echo "   <span>&nbsp;&nbsp;Filter</span>\n";
		echo "   <span>\n";
		echo "      <select id='NoteFilterCombo' onChange='Vixen.NoteListPopup.intNoteFilter = this.value;' style='width:100%'>\n";
		foreach ($arrFilterOptions as $intFilterOption=>$strFilterOption)
		{
			$strSelected = (DBO()->NoteDetails->FilterOption->Value == $intFilterOption) ? "selected='selected'" : "";
			echo "         <option $strSelected value='$intFilterOption'><span>$strFilterOption</span></option>\n";
		}
		echo "      </select>\n";
		echo "   </span>\n";
		
		$intMaxNotes = DBO()->NoteDetails->MaxNotes->Value;
		echo "   <span style='margin-left:20px;'>Limit</span><input type='text' class='DefaultInputText' style='left:0px;width:50px;margin-left:5px' id='NoteDetails.MaxNotes' value='$intMaxNotes' onChange='Vixen.NoteListPopup.intMaxNotes = this.value;'></input>\n";

		echo "</div>\n"; //Left
		echo "<div class='Right'>\n";
		$this->Button("Filter", "Vixen.NoteListPopup.ApplyFilter(true);");
		echo "</div>\n"; //Right
		echo "</div>\n"; //height=25px
		
		echo "<div class='TinySeperator'></div>\n";
	
		// Render the notes
		$strNotesContainerDivId = "NotesContainerForPopup";
		echo "<div id='ContainerDiv_ScrollableDiv_Notes' style='border: solid 1px #606060; padding: 5px 5px 5px 5px'>\n";
		echo "<div id='ScrollableDiv_Notes' style='overflow:auto; height:400px; width:auto; padding: 0px 3px 0px 3px'>\n";
		echo "<div id='$strNotesContainerDivId'>\n";
		$this->_RenderNotes();
		echo "</div>\n";
		echo "</div>\n"; //ScrollableDiv_Notes
		echo "</div>\n"; //ContainerDiv_ScrollableDiv_Notes
		
		echo "</div>\n"; // NarrowContent
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
		
		// Initialise the javascript object
		$intAccountId	= (DBO()->NoteDetails->AccountNotes->Value) ? DBO()->Account->Id->Value : "null";
		$intServiceId	= (DBO()->NoteDetails->ServiceNotes->Value) ? DBO()->Service->Id->Value : "null";
		$intContactId	= (DBO()->NoteDetails->ContactNotes->Value) ? DBO()->Contact->Id->Value : "null";
		$intNoteFilter	= DBO()->NoteDetails->FilterOption->Value;
		$strJavascript	= "	if (Vixen.NoteListPopup == undefined)
							{
								Vixen.NoteListPopup = new VixenNoteListClass;
							}
							Vixen.NoteListPopup.Initialise($intAccountId, $intServiceId, $intContactId, $intNoteFilter, $intMaxNotes, '$strNotesContainerDivId', false);
						";
							
		echo "<script type='text/javascript'>$strJavascript</script>\n";
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
					$strServiceType = GetConstantDescription(DBO()->NoteService->ServiceType->Value, "ServiceType");
					$strDetailsHtml .= "<br />$strServiceType: <a href='$strServiceLink'>". DBO()->NoteService->FNN->Value ."</a>";
				}
			}
			
			// Output the note details
			echo "<span style='font-size: 9pt'>$strDetailsHtml</span>\n";
			echo "<div class='TinySeperator'></div>\n";
			
			// Escape any special html chars
			$dboNote->Note = htmlspecialchars($dboNote->Note->Value, ENT_QUOTES);
			
			// Output the actual note
			$dboNote->Note->RenderValue();
			echo "</div>\n";
			
			// Include a separator
			echo "<div class='TinySeperator'></div>\n";
		}
	}
}

?>

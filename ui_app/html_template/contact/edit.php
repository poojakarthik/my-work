<?php
//----------------------------------------------------------------------------//
// HtmlTemplateContactDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateContactDetails
 *
 * A specific HTML Template object
 *
 * An Contact Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateContactDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateContactEdit extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		echo "<h2 class='Contact'>Contact Details</h2>\n";
		echo "<div class='Narrow-Form'>\n";

		// Start the form
		$this->FormStart("EditContact", "Contact", "Edit");

		if ($this->_intContext == HTML_CONTEXT_CONTACT_ADD)
		{
			// Set up the form for adding a new user
			$strButton = "Add Contact";
		}
		else
		{
			// Set Up the form for editting an existing user
			$strButton = "Apply Changes";
			DBO()->Contact->Id->RenderHidden();
		}
		
		if (DBO()->Contact->IsInvalid())
		{
			$bolApplyOutputMask = FALSE;
		}
		else
		{
			$bolApplyOutputMask = TRUE;
		}
			
		DBO()->Contact->Title->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Contact->FirstName->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Contact->LastName->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Contact->JobTitle->RenderInput(CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);
		DBO()->Contact->DOB->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Contact->Email->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Contact->Phone->RenderInput(CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);
		DBO()->Contact->Mobile->RenderInput(CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);
		DBO()->Contact->Fax->RenderInput(CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);
		DBO()->Contact->UserName->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Contact->PassWord->RenderArbitrary("", RENDER_INPUT, CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);

		echo "<table border='0' cellpadding='0' cellspacing='0'>\n";
		echo "	<tr>";
		echo "		<td width='54%' valign='top' rowspan='2'>";
		echo "			<div class='DefaultElement DefaultLabel'>&nbsp;&nbsp;Account Access :</div>";
		echo "		</td>";
		echo "		<td>";
		DBO()->Contact->CustomerContact->RenderInput();
		echo "		</td>";
		echo "	</tr>\n";
		echo "	<tr>";
		echo "		<td>";
		DBO()->Contact->Archived->RenderInput();
		echo "		</td>";
		echo "	</tr>";
		echo "</table>\n";
		
		
		// Render the status message, if there is one
		DBO()->Status->Message->RenderOutput();

		// create the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->Submit($strButton);
		echo "</div>\n";
		$this->FormEnd();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>";
	}
}

?>

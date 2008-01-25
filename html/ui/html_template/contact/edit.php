<?php
//----------------------------------------------------------------------------//
// HtmlTemplateContactEdit
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateContactEdit
 *
 * A specific HTML Template object
 *
 * An Contact Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateContactEdit
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_CONTACT_ADD:
				$this->_RenderContactAdd();
				break;
			case HTML_CONTEXT_CONTACT_EDIT:
				$this->_RenderContactEdit();
				break;
			default:
				echo "ERROR: There is no default render context for HtmlTemplateContactEdit";
				break;
		}
	}
	
	//------------------------------------------------------------------------//
	// _RenderContactEdit
	//------------------------------------------------------------------------//
	/**
	 * _RenderContactEdit()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderContactEdit()
	{
		echo "<h2 class='Contact'>Contact Details</h2>\n";
		echo "<div class='NarrowForm'>\n";

		// Set Up the form for editting an existing user
		$this->FormStart("EditContact", "Contact", "Edit");
		DBO()->Contact->Id->RenderHidden();
		
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

		// create the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";
		$this->FormEnd();
		
		echo "<script type='text/javascript'>document.getElementById('Contact.Title').focus();</script>";		
		echo "<div class='Seperator'></div>\n";
		echo "</div>";
	}
	
	//------------------------------------------------------------------------//
	// _RenderContactAdd
	//------------------------------------------------------------------------//
	/**
	 * _RenderContactAdd()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderContactAdd()
	{
		echo "<h2 class='Contact'>Contact Details</h2>\n";
		echo "<div class='NarrowForm'>\n";

		// Set up the form for adding a new user
		$this->FormStart("AddContact", "Contact", "Add");
		DBO()->Account->Id->RenderHidden();
		
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
		DBO()->Contact->PassWord->RenderArbitrary("", RENDER_INPUT, CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);

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

		// create the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Add Contact");
		$this->FormEnd();
		echo "</div>\n";
		echo "<script type='text/javascript'>document.getElementById('Contact.Title').focus();</script>";		
		echo "<div class='Seperator'></div>\n";
		echo "</div>";
	}
}

?>

<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// email.php
//----------------------------------------------------------------------------//
/**
 * email
 *
 * HTML Template for the Email HTML object
 *
 * HTML Template for the Email HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to email an invoice.
 *
 * @file		email.php
 * @language	PHP
 * @package		ui_app
 * @author		Nathan Abussi
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateInvoiceEmail
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateInvoiceEmail
 *
 * HTML Template class for the InvoiceEmail HTML object
 *
 * HTML Template class for the InvoiceEmail HTML object
 * displays the form used to email an invoice
 *
 * @package	ui_app
 * @class	HtmlTemplateInvoiceEmail
 * @extends	HtmlTemplate
 */
class HtmlTemplateInvoiceEmail extends HtmlTemplate
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
		
		$this->FormStart("EmailPDFInvoice", "Invoice", "EmailPDFInvoice");
		
		echo "<div class='GroupedContent'>\n";
		
		DBO()->Account->Id->RenderHidden();
		DBO()->Invoice->Id->RenderHidden();
		DBO()->Invoice->InvoiceRun->RenderHidden();
		DBO()->Invoice->Year->RenderHidden();
		DBO()->Invoice->Month->RenderHidden();
		
		foreach (DBL()->Contact as $dboContact)
		{
			$strName = "Email." . $dboContact->Id->Value;
			$strLabel = "{$dboContact->FirstName->Value} {$dboContact->LastName->Value} - {$dboContact->Email->Value}";
			$strLabel = substr($strLabel, 0, 60);
			// Checkboxes are the format 'FirstName LastName - Email', have id and name of "Email." + Id
			echo "<input type='checkbox' id='$strName' name='$strName'></input>";
			echo "<label for='$strName' title='{$dboContact->Email->Value}'>$strLabel</label>";
			echo "<div class='SmallSeperator'></div>\n";
		}
		
		
		// Insert Other Email Address box
		echo "Other Email Address : <input type='text' id='ExtraEmail' name='Email.Extra' class='DefaultInputText' style='left:20px;width:270px'></input>";
		
		echo "</div>\n"; // GroupedContent
		
		// Create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		$this->AjaxSubmit("Email Invoice");
		echo "</div></div>\n";
		
		$this->FormEnd();
		
	}
}

?>

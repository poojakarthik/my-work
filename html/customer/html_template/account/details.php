<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountDetails
 *
 * HTML Template object for the Account Details
 *
 * HTML Template object for the Account Details
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountDetails extends HtmlTemplate
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
		echo "<div class='NarrowContent'>\n";
		
		// Display the details of their primary account
		echo "<h2 class='Account'>Account Details</h2>\n";
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		if (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		if (trim(DBO()->Account->ABN->Value))
		{
			DBO()->Account->ABN->RenderOutput();
		}
		if (trim(DBO()->Account->ACN->Value))
		{
			DBO()->Account->ACN->RenderOutput();
		}
		
		DBO()->Account->CustomerBalance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		
		DBO()->Account->UnbilledAdjustments->RenderOutput();
		DBO()->Account->UnbilledCDRs->RenderOutput();
		
		// Display the details of their primary address
		echo "<br/><h2 class='Account'>Address Details</h2>\n";
		if (trim(DBO()->Account->Address1->Value))
		{
			DBO()->Account->Address1->RenderOutput();
		}
		if (trim(DBO()->Account->Address2->Value))
		{
			DBO()->Account->Address2->RenderOutput();
		}
		if (trim(DBO()->Account->Suburb->Value))
		{
			DBO()->Account->Suburb->RenderOutput();
		}
		if (trim(DBO()->Account->State->Value))
		{
			DBO()->Account->State->RenderOutput();
		}
		if (trim(DBO()->Account->Postcode->Value))
		{
			DBO()->Account->Postcode->RenderOutput();
		}
		if (trim(DBO()->Account->Country->Value))
		{
			DBO()->Account->Country->RenderOutput();
		}
		
		// Display the contact details
		echo "<br/><h2 class='Account'>Contact Details</h2>\n";
		if (trim(DBO()->Contact->Title->Value))
		{
			DBO()->Contact->Title->RenderOutput();
		}
		if (trim(DBO()->Contact->FirstName->Value))
		{
			DBO()->Contact->FirstName->RenderOutput();
		}
		if (trim(DBO()->Contact->LastName->Value))
		{
			DBO()->Contact->LastName->RenderOutput();
		}
		if (trim(DBO()->Contact->JobTitle->Value))
		{
			DBO()->Contact->JobTitle->RenderOutput();
		}
		if (trim(DBO()->Contact->Email->Value))
		{
			DBO()->Contact->Email->RenderOutput();
		}
		if (trim(DBO()->Contact->Phone->Value))
		{
			DBO()->Contact->Phone->RenderOutput();
		}
		if (trim(DBO()->Contact->Mobile->Value))
		{
			DBO()->Contact->Mobile->RenderOutput();
		}
		if (trim(DBO()->Contact->Fax->Value))
		{
			DBO()->Contact->Fax->RenderOutput();
		}
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>

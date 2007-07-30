<?php
//----------------------------------------------------------------------------//
// HtmlTemplateConsole
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateConsole
 *
 * HTML Template object for the client app console 
 *
 * HTML Template object for the client app console
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateConsole
 * @extends	HtmlTemplate
 */
class HtmlTemplateConsole extends HtmlTemplate
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
		$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
		
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
		echo "<div class='WideContent'>\n";
		
		
		// For the console page, we need display some of the contact's details
		// We need to display account details for each account that the contact belongs to.
		// This will usually just be the one account, but we have to make provisions for multiple accounts
		// You may want to display the account details differently if they have more than one account
		// each account listed should have a link to a page listing all the details for that account
		
		
		//TODO! INSERT A LOG OUT BUTTON
		
		echo "<h2 class='Console'>Console</h2>\n";
		
		echo "Welcome " . DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value .". You are currently logged into your account\n";
		
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
		DBO()->Account->ABN->RenderOutput();
		
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		
		
		echo "<div class='Seperator'></div>\n";
		
		//TODO! INSERT BUTTONS HERE
				
		
		// display all accounts that the user can view, if there is more than one
		if ((DBO()->Contact->CustomerContact->Value) && (DBL()->Account->RecordCount()))
		{
			Table()->Accounts->SetHeader("Account#", "BusinessName", "Trading Name");
			Table()->Accounts->SetAlignment("Left", "Left", "Left");
			Table()->Accounts->SetWidth("15%", "45%", "40%");
			foreach (DBL()->Account as $dboAccount)
			{
				Table()->Accounts->AddRow($dboAccount->Id->AsValue(), $dboAccount->BusinessName->AsValue(), $dboAccount->TradingName->AsValue());
				
				$strAccountHref = Href()->LoadAccountInConsole($dboAccount->Id->Value);
				Table()->Accounts->SetOnClick("window.location='$strAccountHref'");
			}
			
			// Currently there are some javascript errors that occur if you don't turn on Row Highlighting
			Table()->Accounts->RowHighlighting = TRUE;
			
			Table()->Accounts->Render();
			
		}
		echo "</div>\n";
	}
}

?>

<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountList
 *
 * HTML Template object for the Account List
 *
 * HTML Template object for the Account List
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountList extends HtmlTemplate
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
		// TODO: AU - make sure this works
		// display all accounts that the user can view, if there is more than one
		if (DBL()->Account->RecordCount())
		{
			echo "<H2 class='Accounts'>Available Accounts</H2>";
		
			Table()->Accounts->SetHeader("Account #", "Business Name", "Trading Name");
			Table()->Accounts->SetAlignment("Left", "Left", "Left");
			Table()->Accounts->SetWidth("15%", "45%", "40%");
			foreach (DBL()->Account as $dboAccount)
			{
				Table()->Accounts->AddRow($dboAccount->Id->AsValue(), $dboAccount->BusinessName->AsValue(), $dboAccount->TradingName->AsValue());
				
				//TODO! Make the mouse turn into a hand when it is hovering over rows of the table
				
				$strAccountHref = Href()->LoadAccountInConsole($dboAccount->Id->Value);
				Table()->Accounts->SetOnClick("Vixen.SetLocation('$strAccountHref');");
			}
			
			// Currently there are some javascript errors that occur if you don't turn on Row Highlighting
			Table()->Accounts->RowHighlighting = TRUE;
			
			Table()->Accounts->Render();
		}
		echo "</div>\n";
	}
}

?>

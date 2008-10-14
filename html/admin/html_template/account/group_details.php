<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// group_details.php
//----------------------------------------------------------------------------//
/**
 * group_details
 *
 * HTML Template for the AccountGroup details HTML component
 *
 * HTML Template for the AccountGroup details HTML component
 *
 * @file		group_details.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		8.10
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountGroupDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountGroupDetails
 *
 * HTML Template class for the AccountGroup details HTML component
 *
 * HTML Template class for the AccountGroup details HTML component
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountGroupDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountGroupDetails extends HtmlTemplate
{
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
		$objAccountGroup = DBO()->Account->AccountGroupObject->Value;
		
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
	
		echo "<h2 class='Account'>Associated Accounts</h2>\n";
		
		Table()->AssociatedAccounts->SetHeader("Account #", "Name");
		Table()->AssociatedAccounts->SetAlignment("Left", "Left");

		// Retrieve the Accounts belonging to the account group (as objects)
		$arrAccounts = $objAccountGroup->getAccounts(TRUE);

		foreach ($arrAccounts as $objAccount)
		{
			$strAccountOverviewLink	= Href()->AccountOverview($objAccount->id);
			$strAccountName			= htmlspecialchars($objAccount->getName());
			$strAccountIdCell		= "<a href='$strAccountOverviewLink' title='Account Overview'>{$objAccount->id}</a>";
			$strAccountNameCell		= $strAccountName;
			
			Table()->AssociatedAccounts->AddRow($strAccountIdCell, $strAccountNameCell);
		}
		
		Table()->AssociatedAccounts->Render();

		echo "<div class='SmallSeperator'></div>\n";
	}
}

?>

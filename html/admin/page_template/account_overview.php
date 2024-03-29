<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_overview.php
//----------------------------------------------------------------------------//
/**
 * account_overview
 *
 * Page Template for the account_overview webpage
 *
 * Page Template for the account_overview webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		account_overview.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title (Note this won't update if the name changes)
$strPageName = "Account";
if (DBO()->Account->BusinessName->Value != "")
{
	$strPageName .= " - ". DBO()->Account->BusinessName->Value;
}
elseif (DBO()->Account->TradingName->Value != "")
{
	$strPageName .= " - ". DBO()->Account->TradingName->Value;
}

$this->Page->SetName($strPageName);

// set the layout template for the page
$this->Page->SetLayout('2Column_65_35');

// add the Html Objects to their respective columns
if (Flex_Module::isActive(FLEX_MODULE_CUSTOMER_STATUS))
{
	//$this->Page->AddObject(AccountCustomerStatusHistory, COLUMN_ONE);
	//$this->Page->AddObject(AccountCustomerStatusHistory, COLUMN_TWO);
}

$objAccountGroup = DBO()->Account->AccountGroupObject->Value;
if (count($objAccountGroup->getAccounts()) > 1)
{
	// There are multiple accounts in this account group.  Display the AccountGroup component
	$this->Page->AddObject('AccountGroupDetails', COLUMN_TWO);
}


if (Flex_Module::isActive(FLEX_MODULE_TICKETING) && Ticketing_User::currentUserIsTicketingUser())
{
	// I'm passing the account id as the "context"
	$this->Page->AddObject(AccountTicketList, COLUMN_TWO, DBO()->Account->Id->Value);
}


$this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_VIEW, "AccountDetailsDiv");
$this->Page->AddObject('AccountContactsList', COLUMN_ONE, HTML_CONTEXT_PAGE);
$this->Page->AddObject('InvoiceList', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('AccountServicesList', COLUMN_ONE, HTML_CONTEXT_PAGE, "AccountServicesDiv");

// The ActionsAndNotesList Includes the embedded creator control
$this->Page->AddObject('ActionsAndNotesList', COLUMN_TWO, HTML_CONTEXT_PAGE, "ActionsAndNotesListDiv");

//$this->Page->AddObject('AccountServicesList', COLUMN_THREE, HTML_CONTEXT_PAGE, "AccountServicesDiv");
?>
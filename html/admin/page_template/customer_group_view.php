<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_group_view.php
//----------------------------------------------------------------------------//
/**
 * customer_group_view
 *
 * Page Template for the "View Customer Group" webpage
 *
 * Page Template for the "View Customer Group" webpage
 *
 * @file		customer_group_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("Customer Group");

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('CustomerGroupDetails', COLUMN_ONE, HTML_CONTEXT_VIEW, "CustomerGroupDetailsContainerDiv");

$this->Page->AddObject('CustomerGroupPaymentTermsLink', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

$this->Page->AddObject('CustomerGroupDocumentTemplates', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

if (defined('FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS') && FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
{
	$this->Page->AddObject('CustomerGroupCreditCardConfigLink', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
}

?>

<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$strTitle = "Account Services - ". DBO()->Account->Id->Value;
if (DBO()->Account->BusinessName->IsSet)
{
	$strTitle .= " - ". DBO()->Account->BusinessName->Value;
}
$this->Page->SetName($strTitle);

//$this->Page->SetLayout('popup_layout');  // If we want to display it in a popup
$this->Page->SetLayout('1Column');

// Add each html object to the appropriate column
//$this->Page->AddObject('AccountServicesList', COLUMN_ONE, HTML_CONTEXT_POPUP); // If we want to display it in a popup
$this->Page->AddObject('AccountServicesList', COLUMN_ONE, HTML_CONTEXT_PAGE, "AccountServicesDiv");
?>

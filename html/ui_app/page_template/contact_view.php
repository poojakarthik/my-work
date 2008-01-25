<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('View Contact Details');

$strLayout = '3Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//EXAMPLE:
$id = $this->Page->AddObject('ContactDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ContactOptions', COLUMN_TWO, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ContactAccounts', COLUMN_THREE, HTML_CONTEXT_DEFAULT);

?>

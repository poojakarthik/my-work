<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Add Contact');

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//EXAMPLE:
$id = $this->Page->AddObject('ContactEdit', COLUMN_ONE, HTML_CONTEXT_CONTACT_ADD, "ContactAddDiv");

?>

<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Rates Search Results');

$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
//EXAMPLE:
$this->Page->AddObject('RateList', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
?>

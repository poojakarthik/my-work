<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Account Services');

$this->Page->SetLayout('popup_layout');

// Add each html object to the appropriate column
$this->Page->AddObject('AccountServices', COLUMN_ONE, HTML_CONTEXT_POPUP);
?>

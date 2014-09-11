<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Add Service');

$this->Page->SetLayout('popup_layout');
$this->Page->AddObject('ServiceAdd', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "ServiceAddDiv");
?>

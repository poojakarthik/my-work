<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Rate Search Results');

$this->Page->SetLayout('popup_layout');

$this->Page->AddObject('RateList', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
?>

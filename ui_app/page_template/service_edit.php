<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Edit Service Details');

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);
$this->Page->AddObject('ServiceEdit', COLUMN_ONE, HTML_CONTEXT_SERVICE_EDIT, "ServiceEditDiv");
?>

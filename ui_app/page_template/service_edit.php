<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('Edit Service Details');

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//EXAMPLE:
//$id = $this->Page->AddObject('ServiceAccount', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ServiceEdit', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
//$this->Page->AddObject('ServiceAdjustment', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
//$this->Page->AddObject('ServiceRecAdjustment', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
//$this->Page->AddObject('ServiceOptions', COLUMN_TWO, HTML_CONTEXT_DEFAULT);
?>

<?php
//DEPRICATED
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('View Service Plan');

$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//EXAMPLE:
$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_BARE_DETAIL);
$this->Page->AddObject('PlanDetails', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL);
$this->Page->AddObject('RateGroupList', COLUMN_ONE, HTML_CONTEXT_NORMAL_DETAIL);
?>

<?php
//DEPRICATED
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$this->Page->SetName('View Rate Plan Details');

$strLayout = 'popup_layout';
$this->Page->SetLayout($strLayout);

// Add each html object to the appropriate column
//EXAMPLE:
$this->Page->AddObject('PlanDetails', COLUMN_ONE, HTML_CONTEXT_RATE_DETAIL);
$this->Page->AddObject('PlanList', COLUMN_ONE, HTML_CONTEXT_LIST_DETAIL);
?>

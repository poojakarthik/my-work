<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$strServiceType	= GetConstantDescription(DBO()->Service->ServiceType->Value, "service_type");
$strFnn			= DBO()->Service->FNN->FormattedValue();
$this->Page->SetName("Service Plan: $strServiceType - $strFnn");

// Work out which layout to use
if (DBO()->CurrentRatePlan->Id->Value && DBO()->FutureRatePlan->Id->Value)
{
	//  The service has both a Current plan and a plan scheduled to start in the next billing period
	$this->Page->SetLayout("4Column_50_50");
	
	// Add each html object to the appropriate column
	$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_BARE_DETAIL);
	$this->Page->AddObject('ServicePlanDetails', COLUMN_TWO, HTML_CONTEXT_CURRENT_PLAN);
	$this->Page->AddObject('ServicePlanDetails', COLUMN_THREE, HTML_CONTEXT_FUTURE_PLAN);
	$this->Page->AddObject('ServiceRateGroupList', COLUMN_FOUR, HTML_CONTEXT_NORMAL_DETAIL, "ServiceRateGroupListDiv");
}
elseif (DBO()->FutureRatePlan->Id->Value)
{
	// The service does not have a current plan, but does have a future one
	$this->Page->SetLayout("1Column");
	
	// Add each html object to the appropriate column
	$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_BARE_DETAIL);
	$this->Page->AddObject('ServicePlanDetails', COLUMN_ONE, HTML_CONTEXT_FUTURE_PLAN);
	$this->Page->AddObject('ServiceRateGroupList', COLUMN_ONE, HTML_CONTEXT_NORMAL_DETAIL, "ServiceRateGroupListDiv");
}
else
{
	// The service doesn't have a future scheduled plan
	$this->Page->SetLayout("1Column");
	
	// Add each html object to the appropriate column
	$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_BARE_DETAIL);
	$this->Page->AddObject('ServicePlanDetails', COLUMN_ONE, HTML_CONTEXT_CURRENT_PLAN);
	$this->Page->AddObject('ServiceRateGroupList', COLUMN_ONE, HTML_CONTEXT_NORMAL_DETAIL, "ServiceRateGroupListDiv");
}


?>

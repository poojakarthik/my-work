<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$strServiceType	= GetConstantDescription(DBO()->Service->ServiceType->Value, "service_type");
$strFnn			= DBO()->Service->FNN->Value;
$strIndial		= (DBO()->Service->Indial100->Value)? " (Indial100)" : "";
$this->Page->SetName("Service Details: $strServiceType - $strFnn{$strIndial}");

$this->Page->SetLayout('3Column');

$this->Page->AddObject('ServiceAccount', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

$this->Page->AddObject('ActionsAndNotesList', COLUMN_TWO, HTML_CONTEXT_PAGE, "ActionsAndNotesListDiv");

$this->Page->AddObject('ProvisioningHistoryList', COLUMN_THREE, HTML_CONTEXT_PAGE, "ProvHistoryListDiv");

?>

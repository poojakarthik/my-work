<?php
// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title
$strServiceType	= GetConstantDescription(DBO()->Service->ServiceType->Value, "ServiceType");
$strFnn			= DBO()->Service->FNN->FormattedValue();
$this->Page->SetName("Service Details: $strServiceType - $strFnn");

$this->Page->SetLayout('2Column');

$id = $this->Page->AddObject('ServiceAccount', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ServiceDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT);
if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
{
	$this->Page->AddObject('NoteAdd', COLUMN_TWO, HTML_CONTEXT_PAGE, "NoteAddDiv");
}
$this->Page->AddObject('NoteList', COLUMN_TWO, HTML_CONTEXT_PAGE, "NoteListDiv");

?>

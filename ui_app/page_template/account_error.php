<?php
// page template
// this file specifies the layout to use and the objects to put into each column on the page

// The page object already exists

// set the page title
$this->Page->SetName('Account Error');

//$this->Page->SetLayout(CONSTANT_LAYOUT_TYPE|$strLayoutType??);
$strLayout = '3Column';
$this->Page->SetLayout($strLayout);

// many other functions which arent needed, but could be included like destroy, getObjByID
$id = $this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>

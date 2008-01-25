<?php
//DEPRICATED DEPRICATED DEPRICATED DEPRICATED DEPRICATED DEPRICATED DEPRICATED DEPRICATED DEPRICATED
// page template
// this file specifies the layout to use and the objects to put into each column on the page

// The page object already exists

// set the page title
$this->Page->SetName("Account Details");

//$this->Page->SetLayout(CONSTANT_LAYOUT_TYPE|$strLayoutType??);
$strLayout = 'popup_layout';
$this->Page->SetLayout($strLayout);

// many other functions which arent needed, but could be included like destroy, getObjByID
$id = $this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_FULL_DETAIL, "AccountDetailDiv");

?>

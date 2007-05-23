<?php
// page template
// this file specifies the layout to use and the objects to put into each column on the page

// The page object already exists

$this->Page->SetName('Account View');

//$this->Page->SetLayout(CONSTANT_LAYOUT_TYPE|$strLayoutType??);
$this->Page->SetLayout('1');

// many other functions which arent needed, but could be included like destroy, getObjByID
$id = $this->Page->AddObject('Account.Details',1,'blkAccountDetails');



?>

<?php

// The page object already exists

$this->Page->SetName('Account View');
$this->Page->SetLayout('1');
// many other functions which arent needed, but could be included like destroy, getObjByID
$id = $this->Page->AddObject('Account.Details',1,'blkAccountDetails');



?>

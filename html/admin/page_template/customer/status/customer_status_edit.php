<?php

$objStatus = $this->Page->mxdDataToRender['CustomerStatus'];

$this->Page->SetName("Edit Customer Status - ". $objStatus->name);
$this->Page->SetLayout('full_area');
$this->Page->AddObject('Customer_Status_Edit');

?>

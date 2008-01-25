<?php
// Error page template

// set the page title
$this->Page->SetName('Error');

// set the page layout
$this->Page->SetLayout('1Column');

// display the error object
$this->Page->AddObject('Error', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>

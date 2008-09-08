<?php
// Error page template
// Note that this only works with Framework 2
// Framework 3's error page is called error_page.php

// set the page title
$this->Page->SetName('Error');

// set the page layout
$this->Page->SetLayout('1Column');

// display the error object
$this->Page->AddObject('Error', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>

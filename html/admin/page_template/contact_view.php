<?php

// Page Template
// Specifies the layout to use for the page and the html objects
// to put into each column on the page

// Set the page title & Layout
$this->Page->SetName('View Contact Details');
$this->Page->SetLayout('full_area');

// Add HtmlTemplate_Contact_View
$this->Page->AddObject('Contact_View');

?>

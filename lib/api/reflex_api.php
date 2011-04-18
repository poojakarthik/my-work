<?php

// Get the Flex class...
require_once dirname(__FILE__).'/../../lib/classes/Flex.php';
// Load the Flex framework and application
Flex::load();

//this is just for debug purposes, to download the result into the browser window.
API::processRequest();
?>

<?php
define('FLEX_SESSION_NAME', 'flex_cust_sess_id');


// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// load framework
require_once('require.php');

// instanciate application
$Application = Application::instance();

// call ajax_load
$Application->AjaxLoad();


?>

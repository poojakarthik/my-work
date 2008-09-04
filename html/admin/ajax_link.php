<?php
define('FLEX_SESSION_NAME', 'flex_admin_sess_id');


// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// I added this, so that the new autoloading functionality is available, (which includes the old)
Flex::load();

// load framework
require_once('require.php');

// instanciate application
$Application = Application::instance();

// call ajax_load
$Application->AjaxLoad();


?>

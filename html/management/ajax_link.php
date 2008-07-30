<?php
define('FLEX_SESSION_NAME', 'flex_admin_sess_id');

// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// load framework
require_once('../ui/require.php');

// instanciate application
$Application = Singleton::Instance('Application');

// call ajax_load
$Application->AjaxLoad();
?>

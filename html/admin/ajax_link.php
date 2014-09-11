<?php
define('FLEX_SESSION_NAME', 'flex_admin_sess_id');


// Get the Flex class...
require_once '../../lib/classes/Flex.php';

// I added this, so that the new autoloading functionality is available, (which includes the old)
Flex::load();

// load framework
require_once('require.php');

// We never want to cache AJAX
// FIXME: We should probably look into a better solution for this...
header( 'Expires: Mon, 20 Oct 1985 10:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Cache-Control: max-age=0', false );
header( 'Pragma: no-cache' );

// instanciate application
$Application = Application::instance();

// call ajax_load
$Application->AjaxLoad();


?>

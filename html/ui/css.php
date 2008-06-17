<?php
//----------------------------------------------------------------------------//
// css
//----------------------------------------------------------------------------//
/**
 * css
 *
 * Defines what css file to use for the management app website
 *
 * Defines what css file to use for the management app website
 *
 * @file		css.php
 * @language	PHP
 * @package		web_app
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// The CSS file URI contains a unique 'version number' (the md5 of the CSS content).
// If the browser is checking to see if has changed, the copy it has MUST be the latest version.
// No point sending it again, so send a 304 (not changed) header instead.
if (array_key_exists('v', $_GET) && array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
{
	header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
	exit;
}

header('Content-type: text/css');
header('Cache-Control: public'); // Set both to confuse browser (causes clash with PHP's own headers) forcing browser to decide
header('Pragma: public');		 // (see above)
header('Last-Modified: '.date('r', time()-10000)); // Some time in the past	
header('Expires: '.date('r', time()+(365*24*60*60))); // About a year from now
require_once('style_template/default.css');

?>

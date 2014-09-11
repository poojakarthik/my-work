<?php
// Get the Flex class...
require_once dirname(__FILE__).'/../../lib/classes/Flex.php';
// Load the Flex framework and application
Flex::load();

if ($_SERVER['PHP_AUTH_USER'] == "" || $_SERVER['PHP_AUTH_USER'] == NULL) {
	// No auth details detected
	header('WWW-Authenticate: Basic realm="Flex API"');
	header("HTTP/1.0 401 Unauthorized");
	print "Authorization Required.";
	exit;
} else if($GLOBALS['**API']['user'] !== $_SERVER['PHP_AUTH_USER'] || $GLOBALS['**API']['pass'] !== $_SERVER['PHP_AUTH_PW']) {
	// Username and password do not match.
	header('WWW-Authenticate: Basic realm="Flex API"');
	header("HTTP/1.0 401 Unauthorized");
	print "Authorization Required.";
	exit;
} else {
	// Authenticated
	API_Server::process();
}

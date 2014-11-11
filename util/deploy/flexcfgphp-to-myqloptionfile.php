<?php

if ($argc < 3 || !is_readable($argv[1])) {
	throw new Exception('Missing or unreadable flex.cfg.php file provided');
}

$properties = array_slice($argv, 2);

require_once $argv[1];

echo "[client]\n";

if (array_search('host', $properties)) {
	if (empty($GLOBALS['**arrDatabase']['admin']['URL'])) {
		throw new Exception('No URL defined for "admin" connection');
	}
	printf("host=%s\n", str_replace('\\', '\\\\', $GLOBALS['**arrDatabase']['admin']['URL']));
}

if (array_search('port', $properties)) {
	printf("port=%s\n", str_replace('\\', '\\\\', empty($GLOBALS['**arrDatabase']['admin']['Port']) ? 3306 : $GLOBALS['**arrDatabase']['admin']['Port']));
}

if (array_search('user', $properties)) {
	if (empty($GLOBALS['**arrDatabase']['admin']['User'])) {
		throw new Exception('No User defined for "admin" connection');
	}
	printf("user=%s\n", str_replace('\\', '\\\\', $GLOBALS['**arrDatabase']['admin']['User']));
}

if (array_search('password', $properties)) {
	if (empty($GLOBALS['**arrDatabase']['admin']['Password'])) {
		throw new Exception('No Password defined for "admin" connection');
	}
	printf("password=%s\n", str_replace('\\', '\\\\', $GLOBALS['**arrDatabase']['admin']['Password']));
}

if (array_search('database', $properties)) {
	if (empty($GLOBALS['**arrDatabase']['admin']['Database'])) {
		throw new Exception('No Database defined for "admin" connection');
	}
	printf("database=%s\n", str_replace('\\', '\\\\', $GLOBALS['**arrDatabase']['admin']['Database']));
}
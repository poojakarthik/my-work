<?php

//----------------------------------------------------------------------------//
// Database Framework
//----------------------------------------------------------------------------//
/**
 * Database
 *
 *
 * @package	ui_app
 * @class	Database
 *  
 * 
 * 
 */



abstract class DB_Database_Data_Source {
	static $connection;

	public static function get_database_connection($type, & $handler) {
		$mode = $handler->mode;
		$engine = 'Postgres';

		switch ($type) {
			case 'cms':
				$db_host = ('production' == $mode) ? 'syd-cmsbe-01.brennanit.net.au' : 'localhost';
				$db_name = ('testing' == $mode) ? 'cmstest' : 'cms';
				$db_user = 'sqlacc';
				$db_pass = 'f0rgt3N!';
				break;

			case 'vpdn':
				$db_host = 'syd-cmspoll-01.brennanit.net.au';
				$db_name = 'auth-traffic';
				$db_user = 'poller';
				$db_pass = 'P0leP0$!tioN?';
				break;

			case 'traffic':
				$db_host = "syd-cmspoll-01.brennanit.net.au";
				$db_name = "nonauth-traffic";
				$db_user = "poller";
				$db_pass = 'P0leP0$!tioN?';
				break;

			case 'usage':
				$db_host = ('production' == $mode) ? 'syd-cmspoll-01.brennanit.net.au' : 'localhost';
				$db_name = 'link-usage';
				$db_user = 'poller';
				$db_pass = 'P0leP0$!tioN?';
				break;

			case 'monitor':
				$db_host = ('production' == $mode) ? 'syd-cmsbe-01.brennanit.net.au' : 'bne-cmsdev-01.brennanit.net.au';
				$db_name = 'link_monitoring';
				$db_user = 'sqlacc';
				$db_pass = 'f0rgt3N!';
				break;

			case 'radius':
				$db_host = "syd-cmsbe-01.brennanit.net.au";
				$db_name = "radius";
				$db_user = "iexecradius";
				$db_pass = "C0keisit";
				break;

			case 'monitoring':
//				$db_host = "syd-cmsmonitor-01.brennanit.net.au";
//				$db_name = "iexec-monitoring";
//				$db_user = "poller";
//				$db_pass = "P0leP0$!tioN?";
				$db_host = "cyclops.iexec.net.au";
				$db_name = "iexec-monitoring";
				$db_user = "sqlacc";
				$db_pass = "f0rgt3N!";
				break;

			case 'mail':
				$db_host = ('production' == $mode) ? "vmail.brennanit.net.au" : "vmail.brennanit.net.au";
				$db_name = "vmail";
				$db_user = "vmail";
				$db_pass = "P0stF!X";
				break;

			case 'dns':
				$db_host = ('production' == $mode) ? "zeus.iexec.net.au" : "zeus.iexec.net.au";
				$db_name = "powerdns";
				$db_user = "dns";
				$db_pass = "D3eNe5?";
				break;

			case 'proftpd':
				$db_host = ('production' == $mode) ? '210.18.210.233' : 'localhost';
				$db_name = "proftpd";
				$db_user = "sqlacc";
				$db_pass = "f0rgt3N!";
				break;

			case 'mysql_setup':
				$engine = 'MySQL';
				$db_host = ('production' == $mode) ? '210.18.210.233' : 'localhost';
				$db_user = 'sqlacc';
				$db_pass = 'f0rgt3N!';
				$db_name = 'mysql';
				break;

			case 'solarwinds':
				$engine = 'MSSQL';
				$db_host = 'BRENSYDSQL';
				$db_user = 'CMS';
				$db_pass = 'whateverittakes';
				$db_name = 'NetPerfMon';
				break;

			default:
				throw new Exception("$type is not a valid type of database!");
				break;
		}

		$class = $engine . 'Database';
		return new $class ($db_host, $db_name, $db_user, $db_pass, $handler);
	}
}

?>
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



class DB_MySQL_Database extends DB_Database {

	function __construct($db_host, $db_name, $db_user, $db_pass, & $handler) {
		parent::__construct($handler, $db_host, $db_name);
		$this->function_prefix = 'mysql';
		if ($this->handle = mysql_connect($db_host, $db_user, $db_pass)) {
			if(!mysql_select_db($db_name, $this->handle)) {
				return false;
			}
		} else {
			return false;
		}
	}

	public function get_last_error() {
		return mysql_error($this->handle);
	}

	public function flush_privileges() {
		return $this->query("FLUSH PRIVILEGES");
	}
}

?>
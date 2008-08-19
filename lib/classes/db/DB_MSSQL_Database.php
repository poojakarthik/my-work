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



class DB_MSSQL_Database extends DB_Database {

	function __construct($db_host, $db_name, $db_user, $db_pass, & $handler) {
		parent::__construct($handler, $db_host, $db_name);
		$this->function_prefix = 'mssql';
		if ($this->handle = mssql_connect($db_host, $db_user, $db_pass)) {
			if(!mssql_select_db($db_name, $this->handle)) {
				return false;
			}
		} else {
			return false;
		}
	}

	public function get_last_error() {
		return mssql_get_last_message();
	}
}

?>
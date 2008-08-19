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



class DB_Postgres_Database extends DB_Database {

	function __construct($db_host, $db_name, $db_user, $db_pass, & $handler) {
		parent::__construct($handler, $db_host, $db_name);
		$this->function_prefix = 'pg';
		if (!$this->handle = pg_connect("host=$db_host port=5432 dbname=$db_name user=$db_user password=$db_pass")) {
		 return false;
		}
	}

	/**
	 * Arguments to pg_query are backwards to every other bloody implementation...
	 */
	public function execute($sql) {
		return pg_query($this->handle, $sql);
	}

	public function get_last_error() {
		return pg_last_error($this->handle);
	}

	public function escape_blob($string) {
		$data = pg_escape_bytea($string);
		return $data;
	}

	public function unescape_blob($string) {
		$data = pg_unescape_bytea($string);
		return $data;
	}

	public function table_exists($sql) {
		$sql = $this->escape($sql);
		return $this->numrows("select table_name from information_schema.tables where table_schema='public' and table_type='BASE TABLE' and table_name = '$sql'");
	}

	public function num_fields(){
		return pg_num_fields($this->result);
	}

	public function field_name($pos) {
		return pg_field_name($this->result, $pos);
	}
}

?>
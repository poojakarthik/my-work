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
 * MySQLDatabase($db_host, $db_name, $db_user, $db_pass, $db_handler);
 * 
 * 
 */



class SOAPDataSource {

}

abstract class Database {
	protected $function_prefix;
	protected $handle;
	protected $handler;
	protected $host;
	protected $db;
	protected $sent_feedback = false;

	function __construct($handler, $host, $db) {
		$this->handler = $handler;
		$this->host = $host;
		$this->db = $db;
	}

	function is_connected() {
		return $this->handle ? true : false;
	}

	/**
	 * Executes the supplied $sql query.
	 *
	 * Given a query, $sql, executes it and returns a resource to the $result.
	 * @param string $sql The query to execute.
	 * @return resource The database resource associated with the results.
	 */
	public function query($sql) {
		global $cms_meta;
		static $error_notified = false;

		if (!$this->handle) {
			return false;
		}

		if (!$result = @ $this->execute($sql)) {
			$error = $this->get_last_error();
			ob_start();
			debug_print_backtrace();
			$backtrace = ob_get_clean();

			error_log("Invalid query: $sql $error");

			if (!$error_notified && !$cms_meta->ajax) {
				$cms_meta->display_message('Sorry, an error occurred while processing this page, which may affect the results you see below. The Service Centre team at Brennan have been notified of this problem and will take action to correct it.', 'error');
				$error_notified = true;
			}

			$cms_meta->last_error = $error;

			if ($cms_meta->is_developer) {
				$this->handler->errors[] = <<<ERROR
<pre style="background: #fcc; border: 1px solid red;">Query Was: $sql</pre><br />
<pre style="background: #fcc; border: 1px solid red;">SQL Error Was: $error</pre><br />
<pre style="background: #fcc; border: 1px solid red;">Function Call Backtrace:<br />$backtrace</pre>
ERROR;
			} elseif (!$this->handler->sent_feedback) {
				global $realname, $username;
				$now = date('d/m/Y H:i:s');
				$posted_data = print_r($_POST, true);
				$getted_data = print_r($_GET, true);
				$subject = "CMS Error Notification";
				$message = "Automatic Database Error Report generated at $now from user $realname ($username).\n\nGETTED DATA:\n\n$getted_data\n\nPOSTED DATA:\n\n$posted_data\n\nDATABASE ERROR:\n\n$error\n\nHOST:\n\n$this->host\n\nDATABASE:$this->db\n\nSQL:\n\n$sql\n\nBACKTRACE:\n\n$backtrace";

				// Increment the number of DB query errors in a static log file.
				$filename = 'cms_errors-' . date('Y-m') . '.log';
				$log_path = dirname(dirname(__FILE__)) . "/log/$filename";
				$monthly_count = ($count = file_get_contents($log_path)) ? $count + 1 : 1;
				file_put_contents($log_path, $monthly_count);

				@mail(DEV_EMAIL, $subject, $message, FROM_CMS_EMAIL);
				$this->handler->sent_feedback = true;
			}
		}
		if ($cms_meta->is_developer) {
			$this->handler->requests[] =  "\n$this->function_prefix\n$sql";
		}
		$this->result = $result;
		return $result;
	}

	public function execute($sql) {
		$function = $this->function_prefix . '_query';
		return $function($sql, $this->handle);
	}

	public function fetch($sql, $array = false) {
		$data = array();
		if (!$result = $this->query($sql)) return false;
		$fetch_function = $this->function_prefix . '_fetch_object';
		while($row = $fetch_function($result)) {
			if ($array) {
				$row = (array) $row;
			}
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Fetches a set of data as an array of pairs, keyed on the first field specified.
	 */
	public function fetchPairs($sql) {
		$data = array();
		if (!$result = $this->query($sql)) {
			return array();
		}
		$fetch_function = $this->function_prefix . '_fetch_row';
		while($row = $fetch_function($result)) {
			$data[$row[0]] = $row[1];
		}
		return $data;
	}

	public function fetchone($sql, $array = false) {
		if (!$result = $this->query($sql)) return false;
		$fetch_function = ($array) ? $this->function_prefix . '_fetch_assoc' : $this->function_prefix . '_fetch_object';
		return $fetch_function($result);
	}

	public function get($sql) {
		$data = array();
		if (!$result = $this->query($sql)) return false;
		$num_rows_function = $this->function_prefix . '_num_rows';
		$count = $num_rows_function($result);
		$result_function = $this->function_prefix . '_result';
		for ($i = 0; $i < $count; $i++) {
			$data[] = $result_function($result, $i, 0);
		}
		return $data;
	}

	public function getone($sql) {
		if (!$result = $this->query($sql)) return false;
		$num_rows_function = $this->function_prefix . '_num_rows';
		if ($num_rows_function($result) == 0) {
			$data = NULL;
		} else {
			$result_function = $this->function_prefix . '_result';
			$data = $result_function($result, 0);
		}
		return $data;
	}

	public function numrows($sql) {
		$num_rows_function = $this->function_prefix . '_num_rows';
		if ($sql) {
			$result = $this->query($sql);
			$count = $num_rows_function($result);
		} else {
			$count = isset($this->result) ? $num_rows_function($this->result) : 0;
		}
		return $count;
	}

	public function escape($string) {
		$escape_string_function = $this->function_prefix . '_escape_string';
		$data = $escape_string_function($string);
		return $data;
	}
}


class PostgresDatabase extends Database {

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

class MSSQLDatabase extends Database {

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

class MySQLDatabase extends Database {

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

abstract class DatabaseDataSource {
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

class DataSourceHandler {
	private $protocol;
	private $source;
	public $sent_feedback = false;
	public $requests = array();
	public $errors = array();
	public $mode;
	private static $_instances = array();

	public static function getInstance($type, $protocol = 'database') {
		if (!isset(self::$_instances[$protocol][$type])) {
			self::$_instances[$protocol][$type] = new self($type, $protocol);
		}
		return self::$_instances[$protocol][$type];
	}

	public function __construct($type, $protocol = 'database') {
		global $devserver, $testserver, $prodserver, $currentuser;
		static $requests, $errors, $sent_feedback;

		if ($prodserver) {
			$this->mode = 'production';
		} elseif ($testserver) {
			$this->mode = 'testing';
		} elseif ($devserver) {
			$this->mode = 'development';
		} else {
			throw new Brennan_Exception('Server type not set - unable to continue!');
		}

		$this->requests = & $requests;
		$this->errors = & $errors;
		$this->sent_feedback = & $sent_feedback;

		$this->protocol = $protocol;
		switch ($protocol) {
			case 'database':
				$this->source = & DatabaseDataSource::get_database_connection($type, $this);
				break;

			case 'soap':
				$this->source = & new SOAPDataSource($type, $this);
				break;

			default:
				debug_print_backtrace();
				throw new Exception("A source handler is not defined for $protocol");
				break;
		}

		if (!$this->source->is_connected()) {
			// We failed to get a connection to the database.
			Meta::display_message('A connection to the database could not be established, which may affect the results you see below.  Please wait and try again. (' . $type . ' database)', 'error');
			return false;
		}
	}

	public function debug_print_all_errors() {
		if ($this->errors) {
			$requests = implode ("\n\n", $this->errors);
			$count = count($this->errors);
			echo <<<ERRORS
<strong>Data Errors:</strong>
<pre class="error">

$requests

<strong>TOTAL ERRORS: $count</strong>
</pre>
ERRORS;
		}
	}

	public function debug_print_all_requests() {
		if ($this->requests) {
			$requests = implode ("\n\n", $this->requests);
			$count = count($this->requests);
			echo <<<REQUESTS
<strong>Data Requests:</strong>
<pre class="info">

$requests

<strong>TOTAL REQUESTS: $count</strong>
</pre>
REQUESTS;
		}
	}

	private function __call($function, $args) {
		return call_user_func_array(array(& $this->source, $function), $args);
	}
}

?>

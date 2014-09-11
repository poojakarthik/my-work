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



abstract class DB_Database {
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
				//$cms_meta->display_message('Sorry, an error occurred while processing this page, which may affect the results you see below. The Service Centre team at Brennan have been notified of this problem and will take action to correct it.', 'error');
				throw new Exception ("<div style=\"color: red; height: 100; width: 100%; text-align: left; border: 1px solid #dedede;\">$sql: $error</div>");

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
				/*
				$filename = 'cms_errors-' . date('Y-m') . '.log';
				$log_path = dirname(dirname(__FILE__)) . "/log/$filename";
				$monthly_count = ($count = file_get_contents($log_path)) ? $count + 1 : 1;
				file_put_contents($log_path, $monthly_count);
				*/
				@mail("rforrester@yellowbilling.com.au", $subject, $message, FROM_CMS_EMAIL);
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

	public function fetch_array($sql) {
		$function = $this->function_prefix . '_fetch_array';
		return $function($sql);
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

?>
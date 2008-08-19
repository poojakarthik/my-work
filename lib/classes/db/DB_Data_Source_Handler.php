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



class DB_Data_Source_Handler {
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
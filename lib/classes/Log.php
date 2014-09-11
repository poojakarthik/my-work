<?php
// Just in case there is no autoloader
require_once(dirname(__FILE__).'/Callback.php');

class Log {
	const LOG_TYPE_STRING = 'string';
	const LOG_TYPE_FILE = 'file';
	const LOG_TYPE_FUNCTION = 'function';

	const SYSTEM_ALIAS = '**default';

	static protected $_strSystemAlias = self::SYSTEM_ALIAS;
	static protected $_strSystemLogType = self::LOG_TYPE_FUNCTION;
	static protected $_arrSystemConfig = array(
		'Class' => 'Log',
		'Function' => 'echoMessage'
	);

	static protected $_strDefaultAlias = self::SYSTEM_ALIAS;

	static protected $_arrLogs = array();

	protected $_strLogType;
	protected $_arrConfig = array();

	private function __construct($strLogType, $arrConfig) {
		$this->_strLogType = $strLogType;
		$this->_arrConfig = $arrConfig;
	}

	public function log($strMessage, $bolAddNewLine=true) {
		if ($this->_strLogType !== self::LOG_TYPE_FUNCTION && $bolAddNewLine) {
			$strMessage .= "\n";
		}

		// Output the Message
		switch ($this->_strLogType) {
			case self::LOG_TYPE_STRING:
				$this->_arrConfig['Reference'] .= $strMessage;
				break;

			case self::LOG_TYPE_FILE:
				fwrite($this->_arrConfig['Reference'], $strMessage);
				break;

			case self::LOG_TYPE_FUNCTION:
				$arrFuncArgs = func_get_args();
				$this->_arrConfig['Callback']->invokeArray($arrFuncArgs);
				break;

			default:
				throw new Exception("'{$this->_strLogType}' is not a valid Log Type");
		}
	}

	public function logIf($mExpression, $strMessage, $bolAddNewLine=true) {
		if ($mExpression) {
			$this->log($strMessage, $bolAddNewLine);
		}
	}

	public function formatLog($sFormat) {
		$aArgs = func_get_args();
		$aFormatArgs = array_slice($aArgs, 1);
		if (is_bool(end($aArgs))) {
			$aFormatArgs = array_slice($aFormatArgs, 0, -1);
			$bAddNewLine = end($aArgs);
			return $this->log(vsprintf($sFormat, $aFormatArgs), $bAddNewLine);
		}

		return $this->log(vsprintf($sFormat, $aFormatArgs));
	}

	public function formatLogIf($mExpression) {
		if ($mExpression) {
			$aArgs = func_get_args();
			return call_user_func_array(array($this, 'formatLog'), array_slice(1, $aArgs));
		}
	}

	public function getReference() {
		return isset($this->_arrConfig['Reference']) ? $this->_arrConfig['Reference'] : null;
	}

	public function getLogType() {
		return $this->_strLogType;
	}

	public static function getLog($strLogAlias=null) {
		// Select the Default Log if no alias is provided
		if (!$strLogAlias) {
			$strLogAlias = self::$_strDefaultAlias;

			if (!self::logExists($strLogAlias)) {
				if (self::$_strSystemLogType === self::LOG_TYPE_FUNCTION) {
					// Function-based Log
					$strClass = (self::$_arrSystemConfig['Class']) ? self::$_arrSystemConfig['Class'] : null;
					self::registerFunctionLog($strLogAlias, self::$_arrSystemConfig['Function'], $strClass);
				} else {
					// Reference-based Log
					self::registerLog($strLogAlias, self::$_strSystemLogType, self::$_arrSystemConfig['Reference']);
				}
			}
		}

		// Does this Log exist?
		if (self::logExists($strLogAlias)) {
			return self::$_arrLogs[$strLogAlias];
		} else {
			throw new Exception("Log {$strLogAlias} has not been defined");
		}
	}

	public static function get($sLogAlias=null) {
		return self::getLog($sLogAlias);
	}

	public static function logExists($strLogAlias) {
		return (isset(self::$_arrLogs[$strLogAlias]) && self::$_arrLogs[$strLogAlias]);
	}

	public static function registerLog($strLogAlias, $strLogType, &$refReference) {
		if (self::logExists($strLogAlias)) {
			throw new Exception("A Log with alias '{$strLogAlias}' already exists");
		}

		// Config
		$arrConfig = array();
		$arrConfig['Reference'] = &$refReference;

		// Create Instance
		$objLog = new Log($strLogType, $arrConfig);
		self::$_arrLogs[$strLogAlias] = $objLog;
		return $objLog;
	}

	public static function registerFunctionLog($strLogAlias, $strFunction, $strClass=null) {
		if (self::logExists($strLogAlias)) {
			throw new Exception("A log with alias '{$strLogAlias}' already exists");
		}

		// Config
		$arrConfig = array();

		if ($strFunction instanceof Callback) {
			$arrConfig['Callback'] = $strFunction;
		} else {
			$arrConfig['Callback'] = Callback::create($strFunction, $strClass);
		}

		// Create Instance
		$objLog = new Log(self::LOG_TYPE_FUNCTION, $arrConfig);
		self::$_arrLogs[$strLogAlias] = $objLog;
		return $objLog;
	}

	public static function setDefaultLog($mLog=null) {
		// Allow supply of an alias or Log instance
		$strLogAlias = ($mLog instanceof self) ? array_search($mLog, self::$_arrLogs, true) : $mLog;
		if ($strLogAlias && !self::logExists($strLogAlias)) {
			throw new Exception("The log alias '{$strLogAlias}' does not exist");
		}

		// Create Instance
		self::$_strDefaultAlias = ($strLogAlias) ? $strLogAlias : self::SYSTEM_ALIAS;
		return self::get();
	}

	public static function echoMessage($strMessage, $bolAddNewLine=true) {
		$strMessage .= ($bolAddNewLine ? "\n" : '');
		if ($resSTDOUT = fopen('php://stdout','w')) {
			fwrite($resSTDOUT, $strMessage);
			fclose($resSTDOUT);
		} else {
			echo $strMessage;
			flush();
		}
	}
}
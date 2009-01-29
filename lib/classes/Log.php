<?php
/**
 * Log
 *
 * Handles logging to various locations
 *
 * @class	Log
 */
class Log
{
	const	LOG_TYPE_STRING		= 'string';
	const	LOG_TYPE_FILE		= 'file';
	const	LOG_TYPE_FUNCTION	= 'function';
	
	const	SYSTEM_ALIAS		= '**default';
	
	static protected	$_strSystemAlias	= SYSTEM_ALIAS;
	static protected	$_strSystemLogType	= LOG_TYPE_FUNCTION;
	static protected	$_strSystemConfig	=	array
												(
													'Class'		=> 'echoMessage',
													'Function'	=> 'Log'
												);
	
	static protected	$_strDefaultAlias	= SYSTEM_ALIAS;
	
	static protected	$_arrLogs		= array();
	
	protected	$_strLogType;
	protected	$_arrConfig	= array();
	
	/**
	 * __construct()
	 *
	 * Private Constructor
	 *
	 * @param	string	$strLogType			Type of the new Log
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	private function __construct($strLogType, $arrConfig)
	{
		$this->_strLogType	= $strLogType;
		$this->_arrConfig	= $arrConfig;
	}
	
	/**
	 * log()
	 *
	 * Echo wrapper
	 *
	 * @param	string	$strMessage				Message to output
	 *
	 * @return	void
	 *
	 * @method
	 */
	public function log($strMessage)
	{
		// Output the Message
		switch ($this->_strLogType)
		{
			case self::LOG_TYPE_STRING:
				$this->_arrConfig['Reference']	.= $strMessage;
				break;
				
			case self::LOG_TYPE_FILE:
				fwrite($this->_arrConfig['Reference'], $strMessage);
				break;
				
			case self::LOG_TYPE_FUNCTION:
				$strFunction	= (($this->_arrConfig['Class']) ? $this->_arrConfig['Class'].'::' : '') . $this->_arrConfig['Function'];
				$arrFuncArgs	= func_get_args();
				call_user_func_array($strFunction, $arrFuncArgs);
				break;
			
			default:
				throw new Exception("'{$this->_strLogType}' is not a valid Log Type");
		}
	}
	
	/**
	 * getReference()
	 *
	 * If this is a Reference-based Log, returns the Reference that this Log uses, else null
	 *
	 * @return	mixed							Reference or null
	 *
	 * @method
	 */
	public function getReference()
	{
		return isset($this->_arrConfig['Reference']) ? $this->_arrConfig['Reference'] : null;
	}
	
	/**
	 * getLogType()
	 *
	 * Returns the type of log
	 *
	 * @return	string							Type of Log
	 *
	 * @method
	 */
	public function getLogType()
	{
		return $this->_strLogType;
	}
	
	/**
	 * getLog()
	 *
	 * Gets a Log instance
	 *
	 * @param	[string	$strLogAlias]		Alias of the log to return
	 *
	 * @return	void
	 *
	 * @method
	 */
	public static function getLog($strLogAlias=null)
	{
		// Select the Default Log if no alias is provided
		if (!$strLogAlias)
		{
			$strLogAlias	= self::$_strDefaultAlias;
			
			if (!self::logExists($strLogAlias))
			{
				if (self::$_strDefaultLogType === self::LOG_TYPE_FUNCTION)
				{
					// Function-based Log
					$strClass	= (self::$_strDefaultConfig['Class']) ? $_strDefaultConfig['Class'] : null;
					self::registerFunctionLog($strLogAlias, self::$_strDefaultConfig['Function'], $strClass);
				}
				else
				{
					// Reference-based Log
					self::registerLog($strLogAlias, self::$_strDefaultLogType, &self::$_strDefaultConfig['Reference']);
				}
			}
		}
		
		// Does this Log exist?
		if (self::logExists($strLogAlias))
		{
			return self::$_arrLogs[$strLogAlias];
		}
		else
		{
			throw new Exception("Log {$strLogAlias} has not been defined");
		}
	}
	
	/**
	 * logExists()
	 *
	 * Determines whether a log with the given alias exists
	 *
	 * @param	string		$strLogAlias			Callback Alias of the Log
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	public static function logExists($strLogAlias)
	{
		return (isset(self::$_arrLogs[$strLogAlias]) && self::$_arrLogs[$strLogAlias]);
	}
	
	/**
	 * registerLog()
	 *
	 * Registers a Reference-based Log
	 *
	 * @param	string		$strLogAlias			Callback Alias of the Log
	 * @param	string		$strLogType				Type of the new Log
	 * @param	reference	$refReference			Reference to a variable to log to
	 *
	 * @return	void
	 *
	 * @method
	 */
	public static function registerLog($strLogAlias, $strLogType, &$refReference)
	{
		if (self::logExists($strLogAlias))
		{
			throw new Exception("A Log with alias '{$strLogAlias}' already exists");
		}
		
		// Config
		$arrConfig				= array();
		$arrConfig['Reference']	= &$refReference;
		
		// Create Instance
		$objLog	= new Log($strLogType, $arrConfig);
		self::$_arrLogs[$strLogAlias]	= $objLog;
	}
	
	/**
	 * registerFunctionLog()
	 *
	 * Registers a Function-based Log
	 *
	 * @param	string		$strLogAlias			Callback Alias of the Log
	 * @param	string		$strFunction			Function to call
	 * @param	[string		$strClass]				Class that owns the static function
	 *
	 * @return	void
	 *
	 * @method
	 */
	public static function registerFunctionLog($strLogAlias, $strFunction, $strClass=null)
	{
		if (self::logExists($strLogAlias))
		{
			throw new Exception("A log with alias '{$strLogAlias}' already exists");
		}
		
		// Config
		$arrConfig				= array();
		$arrConfig['Function']	= $strFunction;
		$arrConfig['Class']		= $strClass;
		
		// Create Instance
		$objLog	= new Log(self::LOG_TYPE_FUNCTION, $arrConfig);
		self::$_arrLogs[$strLogAlias]	= $objLog;
	}
	
	/**
	 * setDefaultLog()
	 *
	 * Sets the default Log retrieved from Log::getLog()
	 *
	 * @param	[string	$strLogAlias]			Alias of the Log to make default.  If null, then reverts to system default
	 *
	 * @return	void
	 *
	 * @method
	 */
	public static function setDefaultLog($strLogAlias=null)
	{
		if ($strLogAlias && !self::logExists($strLogAlias))
		{
			throw new Exception("The log alias '{$strLogAlias}' does not exist");
		}
		
		// Create Instance
		self::$_strDefaultAlias	= ($strLogAlias) ? $strLogAlias : self::SYSTEM_ALIAS;
	}
	
	/**
	 * echoMessage()
	 *
	 * Echo wrapper, because PHP's echo can't be used as a function
	 *
	 * @param	string	$strMessage				Message to output
	 *
	 * @return	void
	 *
	 * @method
	 */
	private static function echoMessage($strMessage, $bolAddNewLine=true)
	{
		echo ($strMessage . ($bolAddNewLine ? "\n" : ''));
	}
}
?>
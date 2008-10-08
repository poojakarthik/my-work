<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../email/Email_Notification.php";

/*
 * Base class for command line applications
 * 
 */

abstract class Cli
{
	const ARG_LABEL = 0;
	const ARG_REQUIRED = 1;
	const ARG_DESCRIPTION = 2;
	const ARG_DEFAULT = 3;
	const ARG_VALIDATION = 4;

	private $_arrCommandLineArguments = NULL;
	private $_arrValidatedArguments = NULL;
	private $_strApplicationFile = NULL;

	const SWITCH_LOG = "l";
	const SWITCH_VERBOSE = "v";
	const SWITCH_SILENT = "s";
	const SWITCH_HELP = "?";

	// Depricated! Use Email_Notification class for emailing!!
	const EMAIL_ATTACHMENT_NAME = 'content_type';
	const EMAIL_ATTACHMENT_MIME_TYPE = 'dfilename';
	const EMAIL_ATTACHMENT_CONTENT = 'CONTENT';

	private $logFile = NULL;
	private $logSilent = FALSE;
	private $logVerbose = FALSE;

	private $bolCachingErrors = FALSE;

	protected final function __construct()
	{
		$this->_arrCommandLineArguments = $this->getCommandLineArguments();

		if (array_key_exists(self::SWITCH_LOG, $this->_arrCommandLineArguments) 
		 || array_key_exists(self::SWITCH_VERBOSE, $this->_arrCommandLineArguments) 
		 || array_key_exists(self::SWITCH_SILENT, $this->_arrCommandLineArguments))
		{
			echo "Invalid implementation. The following command line switches are reserved: " . self::SWITCH_LOG . ", " . self::SWITCH_VERBOSE . " and " . self::SWITCH_SILENT;
			exit(1);
		}

		$this->_arrCommandLineArguments[self::SWITCH_LOG] = array(
			self::ARG_LABEL			=> "LOG_FILE", 
			self::ARG_REQUIRED		=> FALSE,
			self::ARG_DESCRIPTION	=> "is a writable file location to write log messages to [optional, default is no logging]",
			self::ARG_DEFAULT		=> FALSE,
			self::ARG_VALIDATION	=> 'Cli::_validFile("%1$s", FALSE)'
		);

		$this->_arrCommandLineArguments[self::SWITCH_VERBOSE] = array(
			self::ARG_REQUIRED		=> FALSE,
			self::ARG_DESCRIPTION	=> "for verbose messages [optional, default is to output errors only]",
			self::ARG_DEFAULT		=> FALSE,
			self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
		);

		$this->_arrCommandLineArguments[self::SWITCH_SILENT] = array(
			self::ARG_REQUIRED		=> FALSE,
			self::ARG_DESCRIPTION	=> "do not output messages to console [optional, default is to output messages]",
			self::ARG_DEFAULT		=> FALSE,
			self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
		);

		$this->_arrCommandLineArguments[self::SWITCH_HELP] = array(
			self::ARG_REQUIRED		=> FALSE,
			self::ARG_DESCRIPTION	=> "to view this usage information",
			self::ARG_DEFAULT		=> FALSE,
			self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
		);
	}

	public static final function execute($class)
	{
		$classFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . $class . ".php";
		try
		{
			if (file_exists($classFile))
			{
				require_once $classFile;
				$app = new $class();

				$logSwitches = array(self::SWITCH_LOG, self::SWITCH_VERBOSE, self::SWITCH_SILENT, self::SWITCH_HELP);
				
				$logArgs = $app->_getValidatedArguments($logSwitches);

				if ($logArgs[self::SWITCH_HELP])
				{
					$app->showUsage();
				}

				$app->startLog($logArgs[self::SWITCH_LOG], $logArgs[self::SWITCH_SILENT], $logArgs[self::SWITCH_VERBOSE]);

				$exitCode = $app->run();

				$app->endLog();

				if ($exitCode)
				{
					exit($exitCode);
				}
			}
			else
			{
				echo "\nError: Client application '$class' not found in application directory.\n\n";
				exit(1);
			}
		}
		catch(Exception $e)
		{
			echo "\nError: Unable to run client application: $class\n\n";
			exit(1);
		}
	}

	protected function getCommandLineArguments()
	{
		$commandLineArguments = array(
		);
		return $commandLineArguments;
	}

	abstract protected function run(); 

	protected function showUsage($error="", $supressNewLine=FALSE)
	{
		if ($error)
		{
			$this->log("\nError: $error\n", TRUE, $supressNewLine, TRUE);
		}

		$this->endLog();

		$sp = "\n	";
		$pad = str_repeat(" ", 30);
		
		echo "\nUsage:{$sp}php " . $this->_strApplicationFile;
		$where = "\nwhere:";
		$switches = "\nwith switches:";
		$switched = FALSE;
		foreach ($this->_arrCommandLineArguments as $switch => $param)
		{
			$labelled = array_key_exists(self::ARG_LABEL, $param);
			$label = array_key_exists(self::ARG_LABEL, $param) ? ' ' . $param[self::ARG_LABEL] : '';
			echo " " . ($param[self::ARG_REQUIRED] ? "" : "[") . "-" . $switch . $label . ($param[self::ARG_REQUIRED] ? "" : "]");
			if ($labelled)
			{
				$where .= $sp . $label . substr($pad, strlen($label)) . $param[self::ARG_DESCRIPTION];
			}
			else
			{
				$label = "-$switch";
				$switches .= $sp . $label . substr($pad, strlen($label)) . $param[self::ARG_DESCRIPTION];
				$switched = TRUE;
			}
		}

		echo "$where";
		echo $switched ? "$switches\n\n" : "\n";
		
		exit($error ? 1 : 0);
	}
	
	protected function _getValidatedArguments($arrArgs=NULL)
	{
		$this->startErrorCatching();
		global $argv;
		$arrArgv = array_values($argv);
		if (!isset($arrArgv) || !is_array($arrArgv))
		{
			// Prevent execution by any means other than the command line!
			// (prevents access via a browser)
			exit(1);
		}
		$this->_strApplicationFile = array_shift($arrArgv);

		$validArgs = array();
		$i = 0;
		$requiredSwitches = 0;
		$swiches = "";
		$switched = 0;
		foreach ($this->_arrCommandLineArguments as $switch => $param)
		{
			$req = pow(2, $i);
			if ($param[Cli::ARG_REQUIRED])
			{
				$requiredSwitches = $requiredSwitches | $req;
			}
			else
			{
				$validArgs[$switch] = $param[Cli::ARG_DEFAULT];
				$switched = $switched | $req;
			}
			$i++;
			$swiches .= $switch;
			$this->_arrCommandLineArguments[$switch]["BIN_SWITCH"] = $req;
		}

		for ($i = 0, $l = count($arrArgv); $i < $l; $i++)
		{
			// If the arg is only a parameter switch, 
			// add it to the next value and continue to that value
			if (strlen($arrArgv[$i]) <= 2 && $arrArgv[$i][0] == "-" && $i < $l - 1)
			{
				// But only if the next parameter is not a switch too!
				if ($arrArgv[$i+1][0] != '-')
				{
					$arrArgv[$i+1] = $arrArgv[$i] . $arrArgv[$i+1];
					continue; 
				}
			}
		
			// If the value does not start with a switch, show the usage message 
			if (strlen($arrArgv[$i]) < 2 || $arrArgv[$i][0] != "-")
			{
				$this->showUsage("Invalid arguments passed.");
			}
			
			// We have a switch with a value
			$switch = $arrArgv[$i][1];
			$value = substr($arrArgv[$i], 2);
			
			if (!array_key_exists($switch, $this->_arrCommandLineArguments))
			{
				$this->showUsage("Argument '-$switch' not supported.");
			}

			// If we are only getting a subset of the switches (as when setting up logging)
			if (!is_array($arrArgs) || array_search($switch, $arrArgs) !== FALSE)
			{
				// Escape the string to make it safer for eval'ing
				$evalValue = addcslashes($value, "\$\"\\");
				$validation = sprintf($this->_arrCommandLineArguments[$switch][Cli::ARG_VALIDATION], $evalValue);
				try
				{
					eval('$validArgs[$switch] = ' . $validation . ";");
				}
				catch (Exception $e)
				{
					$this->showUsage($e->getMessage());
				}
			}
			
			$switched = $switched | $this->_arrCommandLineArguments[$switch]["BIN_SWITCH"];
		}

		if (!is_array($arrArgs) && $requiredSwitches ^ ($requiredSwitches & $switched))
		{
			$this->showUsage("Please provide all required arguments.");
		}

		$this->dieIfErred();

		return $validArgs;
	}
	
	protected function getValidatedArguments()
	{
		if ($this->_arrValidatedArguments === NULL)
		{
			$this->_arrValidatedArguments = $this->_getValidatedArguments();
		}
		return $this->_arrValidatedArguments;
	}
	
	protected function requireOnce($strFilePath)
	{
		$this->startErrorCatching();
		require_once $this->getFlexBasePath() . $strFilePath;
		$this->dieIfErred();
	}

	protected function getFlexBasePath()
	{
		static $strFlexBasePath;
		if (!isset($strFlexBasePath))
		{
			$strFlexBasePath = realpath(dirname(__FILE__) . "/../../") . DIRECTORY_SEPARATOR;
		}
		return $strFlexBasePath;
	}
	
	public function startErrorCatching()
	{
		// Declare a global error string for error handling
		global $cli_error;
		$cli_error = "";
		if (!$this->bolCachingErrors)
		{
			set_error_handler("Cli_Error_Handler", E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_WARNING | E_NOTICE);
			$this->bolCachingErrors = TRUE;
		}
	}
	
	public function dieIfErred()
	{
		if ($this->bolCachingErrors)
		{
			restore_error_handler();
			$this->bolCachingErrors = FALSE;
		}
		// Access the global error string to check for errors
		global $cli_error;
		if ($cli_error !== "")
		{
			$this->showUsage($cli_error);
		}
	}


	public function getCachedError()
	{
		if ($this->bolCachingErrors)
		{
			restore_error_handler();
			$this->bolCachingErrors = FALSE;
		}
		// Access the global error string to check for errors
		global $cli_error;
		return $cli_error;
	}
	


	protected function startLog($logFile, $logSilent=FALSE, $logVerbose=FALSE)
	{
		$this->logSilent = $logSilent;
		$this->logVerbose = $logVerbose;
		if ($logFile && $this->logFile == NULL)
		{
			$this->logFile = fopen($logFile, "a+");
			$this->log("\n::START::");
		}
	}

	protected function log($message, $isError=FALSE, $suppressNewLine=FALSE, $alwaysEcho=FALSE)
	{
		if (!$alwaysEcho && !$this->logVerbose && !$isError) return;
		if (!$this->logSilent || $alwaysEcho) 
		{
			echo $message . ($suppressNewLine ? "" : "\n");
			flush();
		}
		if (!$this->logVerbose && !$isError) return;
		if ($this->logFile == NULL) return;
		fwrite($this->logFile, date("Y-m-d H-i-s.u :: ") . trim(str_replace(chr(8), '', $message)) . "\n");
		if ($message === "::END::")
		{
			fwrite($this->logFile, "\n\n\n");
		}
	}

	protected function endLog()
	{
		if ($this->logFile == NULL) return;
		$this->log("::END::");
		fclose($this->logFile);
	}


	/**
	 * This function can be invoked by the subclass to interact with a user at the command line.
	 */
	protected function getUserResponse($strPrompt)
	{
		set_time_limit(0);
		if ($fh = fopen('php://stdout','w'))
		{
			fwrite($fh, $strPrompt . " ");
			fclose($fh);
		}
		if ($fh = fopen('php://stdin','rb'))
		{
			$strResponse = fread($fh,1024);
			fclose($fh);
		}
		set_time_limit(600);
		return trim($strResponse);
	}

	/**
	 * @deprecated IMMEDIATELY - Use Email_Notifiction instances to send emails!
	 */
	protected function sendEmailNotification($intEmailNotification, $intCustomerGroupId, $strToEmail, $strSubject, $strHTMLMessage, $strTextMessage=NULL, $arrAttachments=NULL)
	{
		$outcome = Email_Notification::sendEmailNotification($intEmailNotification, $intCustomerGroupId, $strToEmail, $strSubject, $strHTMLMessage, $strTextMessage, $arrAttachments);
		return $outcome === TRUE;
	}


	/**
	 * Validation functions used for command line arg validation
	 */

	public static function _validDate($date)
	{
		if (preg_match("/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}(| ([01]{1,1}[0-9]{1,1}|2[0-3]{1,1}):[0-5]{1,1}[0-9]{1,1}:[0-5]{1,1}[0-9]{1,1})$/", $date))
		{
			if (checkdate(intval(substr($date, 5, 2)), intval(substr($date, 8, 2)), intval(substr($date, 0, 4))))
			{
				$hasTime = strlen($date) > 10;
				$Y = intval(substr($date, 0, 4));
				$m = intval(substr($date, 5, 2));
				$d = intval(substr($date, 8, 2));
				$H = $hasTime ? intval(substr($date, 11, 2)) : 0;
				$i = $hasTime ? intval(substr($date, 14, 2)) : 0;
				$s = $hasTime ? intval(substr($date, 17, 2)) : 0;
				return mktime($H, $i, $s, $m, $d, $Y, FALSE);
			}
			else
			{
				throw new Exception("Invalid date specified: '$date'");
			}
		}
		if (preg_match("/^[0-9]+$/", $date))
		{
			return intval($date);
		}
		throw new Exception("Invalid date specified: '$date'");
	}
	
	public static function _validIsSet()
	{
		return TRUE;
	}
	
	public static function _validInArray($value, $array)
	{
		if (array_search($value, $array, TRUE) !== FALSE)
		{
			return $value;
		}
		throw new Exception("Invalid value specified: '$value'");
	}
	
	public static function _validInteger($int)
	{
		if (preg_match("/^[0-9]+$/", $int))
		{
			return intval($int);
		}
		throw new Exception("Invalid integer specified: '$int'");
	}
	
	public static function _validFileName($filename)
	{
		$misc = "(){}[]<>&)-+*:_ ";
		if (preg_match("/[^a-zA-Z0-9" . preg_quote($misc) ." ]+/", $filename))
		{
			throw new Exception("Filename contains invalid characters (Allowed: a-zA-Z0-9$misc): $filename");
		}
		return $filename;
	}

	public static function _validFileNameWithOptionalExtension($filename)
	{
		$misc = "(){}[]<>&)-+*:_. ";
		if (preg_match("/[^a-zA-Z0-9" . preg_quote($misc) ." ]+/", $filename))
		{
			throw new Exception("Filename contains invalid characters (Allowed: a-zA-Z0-9$misc): $filename");
		}
		return $filename;
	}
	
	public static function _validReadableFileOrDirectory($file)
	{
		if (file_exists($file))
		{
			if (is_readable($file))
			{
				return $file;
			}
			
			if (is_file($file))
			{		
				throw new Exception("Unreadable file specified: '$file'");
			}
			else
			{
				throw new Exception("Unreadable directory specified: '$file'");
			}
		}
		throw new Exception("File or directory not found: '$file'");
	}
	
	public static function _validWritableFile($file)
	{
		if (file_exists($file))
		{
			if (is_dir($file))
			{
				throw new Exception("Directory specified but file required: $file");
			}
			if (!is_writable($file))
			{
				throw new Exception("Unwritable file specified: $file");
			}
			return $file;
		}
		$path = dirname($file);
		$lastPath = NULL;
		while ($path !== $lastPath && !file_exists($path))
		{
			$lastPath = $path;
			$path = dirname($path);
		}
		if (!is_writable($path))
		{
			throw new Exception("Unable to create file in unwritable directory: $file");
		}
		return $file;
	}
	
	public static function _validWritableFileOrDirectory($file)
	{
		// If it writable, it's good no matter what it is!
		if (is_writable($file))
		{
			return $file;
		}

		// If the file/dir does not exist, we must check that we can create the dir
		if (!file_exists($file))
		{
			$first = TRUE;
			$path = $file;
			do
			{
				$base = basename($path);
				$path = dirname($path);
				// If this is the base part of the path and it's a file, ignore it 
				// as we want to know if the directory is writable.
				if ($first && strpos($base, '.') !== FALSE)
				{
					// The base part is a file, so skip this bit.
					$first = FALSE;
					continue;
				}
				// If the directory exists but is not writable
				if (file_exists($path) && (!is_dir($path) || !is_writable($path)))
				{
					throw new Exception("Unwritable directory specified: $path");
				}
				$first = FALSE;
				// If the directory exists it is writable
				if (file_exists($path))
				{
					return $file;
				}
			}
			while (!file_exists($path));
		}

		// If it's a file (exiting or not, or a non-existing directory)
		if (!is_dir($file))
		{
			// If it exists then it isn't writable
			if (file_exists($file))
			{
				throw new Exception("Unwritable file specified: '$file'.");
			}
			// Check to see if it can be created in the directory
			$dir = dirname($file);
			if (is_writable($dir))
			{
				return $file;
			}
			throw new Exception("Unable to create file '" . basename($file) . "' in unwritable directory '$dir'");
		}
		else if (file_exists($file))
		{
			throw new Exception("Unwritable directory specified: '$file'");
		}
		throw new Exception("Directory not found: '$file'");
	}

	public static function _validFile($file, $checkReadable=TRUE)
	{
		if (file_exists($file))
		{
			if (!is_file($file))
			{
				throw new Exception("'$file' is not a file.");
			}

			if ($checkReadable)
			{
				if (is_readable($file))
				{
					return $file;
				}
				throw new Exception("Unreadable file specified: '$file'");
			}
			
			else
			{
				if (is_writable($file))
				{
					return $file;
				}
				throw new Exception("Unwritable file specified: '$file'");
			}
		}
		if ($checkReadable)
		{
			throw new Exception("File not found: '$file'");
		}
		try
		{
			$dir = dirname($file);
			$name = basename($file);
			self::_validDir($dir);
			return $file;
		}
		catch (Exception $e)
		{
			throw new Exception("Unable to create file '$name' in unwritable directory '$dir'");
		}

		throw new Exception("Invalid file specified: '$file'");
	}
	
	public static function _validDir($dir)
	{
		if (file_exists($dir) && is_dir($dir))
		{
			if (is_writable($dir))
			{
				return $dir;
			}
			throw new Exception("Unwritable directory specified: '$file'");
		}
		throw new Exception("Invalid directory specified: '$dir'");
	}
	
	public static function _validConstant($name, $prefix="", $suffix="")
	{
		$contsantName = $prefix.$name.$suffix;
		if (!defined($contsantName))
		{
			throw new Exception("Undefined constant specified: '$name'" . ($name == $contsantName ? "" : " (i.e. $contsantName)"));
		}
		return constant($contsantName);
	}
}

//********************************************************************************************
// Helper functions...
//********************************************************************************************


function Cli_Error_Handler($intErrno, $strError, $strErrfile=NULL, $intErrline=NULL, $arrErrcontext=NULL)
{
	global $cli_error;
	$cli_error .= ($cli_error ? "\n" : "") . "$strError (Code: $intErrno) [$strErrfile @line $intErrline]";
	return TRUE;
}

?>

<?php

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
	
	protected final function __construct()
	{
		$this->_arrCommandLineArguments = $this->getCommandLineArguments();
	}
	
	public static final function execute($class)
	{
		$classFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . "apps" . DIRECTORY_SEPARATOR . $class . ".php";
		try
		{
			if (file_exists($classFile))
			{
				require_once $classFile;
				$app = new $class();
				$app->run();
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
	
	abstract protected function getCommandLineArguments(); 

	abstract protected function run(); 
	
	protected function showUsage($error="")
	{
		if ($error)
		{
			echo "\nError: $error\n";
		}
	
	    $sp = "\n    ";
	    $pad = str_repeat(" ", 30);
	    
		echo "\nUsage:{$sp}php " . $this->_strApplicationFile;
		$where = "\nwhere:";
		$switches = "\nwith switches:";
	    $switched = FALSE;
	    foreach ($this->_arrCommandLineArguments as $switch => $param)
	    {
	    	$labelled = array_key_exists(self::ARG_LABEL, $param);
	    	$label = array_key_exists(self::ARG_LABEL, $param) ? $param[self::ARG_LABEL] : '';
	    	echo " " . ($param[self::ARG_REQUIRED] ? "" : "[") . "-" . $switch . " " . $label . ($param[self::ARG_REQUIRED] ? "" : "]");
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
	
	protected function getValidatedArguments()
	{
		if ($this->_arrValidatedArguments === NULL)
		{
			$this->startErrorCatching();
			global $argv;
			if (!isset($argv) || !is_array($argv))
			{
				// Prevent execution by any means other than the command line!
				// (prevents access via a browser)
				exit(1);
			}
			$this->_strApplicationFile = array_shift($argv);

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
			
			for ($i = 0, $l = count($argv); $i < $l; $i++)
			{
				// If the arg is only a parameter switch, 
				// add it to the next value and continue to that value
				if (strlen($argv[$i]) <= 2 && $argv[$i][0] == "-" && $i < $l - 1)
				{
					// But only if the next parameter is not a switch too!
					if ($argv[$i+1][0] != '-')
					{
						$argv[$i+1] = $argv[$i] . $argv[$i+1];
						continue; 
					}
				}
			
				// If the value does not start with a switch, show the usage message 
				if (strlen($argv[$i]) < 2 || $argv[$i][0] != "-")
				{
					$this->showUsage("Invalid arguments passed.");
				}
				
				// We have a switch with a value
				$switch = $argv[$i][1];
				$value = substr($argv[$i], 2);
				
				if (!array_key_exists($switch, $this->_arrCommandLineArguments))
				{
					$this->showUsage("Argument '-$switch' not supported.");
				}
				
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
				
				$switched = $switched | $this->_arrCommandLineArguments[$switch]["BIN_SWITCH"];
			}

			if ($requiredSwitches ^ ($requiredSwitches & $switched))
			{
				$this->showUsage("Please provide all required arguments.");
			}
			$this->_arrValidatedArguments = $validArgs;
			$this->dieIfErred();
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
		set_error_handler("Cli_Error_Handler", E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_WARNING | E_NOTICE);
	}
	
	public function dieIfErred()
	{
		restore_error_handler();
		// Access the global error string to check for errors
		global $cli_error;
		if ($cli_error !== "")
		{
			$this->showUsage($cli_error);
		}
	}
	
	public static function _validDate($date)
	{
		if (preg_match("/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}(| ([01]{1,1}[0-9]{1,1}|2[0-3]{1,1}):[0-5]{1,1}[0-9]{1,1}:[0-5]{1,1}[0-9]{1,1})$/", $date))
		{
			if (checkdate(intval(substr($date, 5, 2)), intval(substr($date, 8)), intval(substr($date, 0, 4))))
			{
				return $date;
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
	
	public static function _validWritableFileOrDirectory($file)
	{
		// If it writable, it's good no matter what it is!
		if (is_writable($file))
		{
			return $file;
		}
		
		// If it's a file (exiting or not)
		if (!is_dir($file))
		{		
			// If it exists then it isn't writable
			if (file_exists($file))
			{
				throw new Exception("Unwritable file specified: '$file'");
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

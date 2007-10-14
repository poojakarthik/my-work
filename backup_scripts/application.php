<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		Backup
 * @author		Jared 'flame' Herbohn
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 

//----------------------------------------------------------------------------//
// ApplicationBackup
//----------------------------------------------------------------------------//
/**
 * ApplicationBackup
 *
 * Backup Application
 *
 * Backup Application
 *
 *
 * @prefix		app
 *
 * @package		backup_app
 * @class		ApplicationBackup
 */
 class ApplicationBackup extends ApplicationBaseClass
 {
 
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Initialises backup information
	 *
	 * Initialises backup information
	 *
	 *
	 * @method
	 */
 	function __Construct()
	{
		$this->strMysqlUser			= 'vixenbackup';
		$this->strMysqlPassword		= 'v1x3n';
		$this->strMysqlDatabase		= 'vixen';
		$this->strDow 				= strtolower(date("D"));
		$this->arrDrives			= array();
		$this->arrTarget			= array();
		$this->arrErrors			= array();
		$this->arrErrorsByMethod 	= array();
		
		$this->arrDrives['sdc1']	= TRUE;
		$this->arrDrives['sdd1']	= FALSE;
		$this->arrDrives['sde1']	= FALSE;
		
		$this->arrTarget['mon']			= "sdc1";
		$this->arrTarget['tue']			= "sdc1";
		$this->arrTarget['wed']			= "sdc1";
		$this->arrTarget['thu']			= "sdc1";
		$this->arrTarget['fri']			= "sdc1";
		$this->arrTarget['sat']			= "sdc1";
		$this->arrTarget['sun']			= "sdc1";
		
		
	}
	
	//------------------------------------------------------------------------//
	// MountDrives
	//------------------------------------------------------------------------//
	/**
	 * MountDrives()
	 *
	 * Attempts to mount all the drives in order
	 *
	 * Attempts to mount all the drives in order. Returns number of drives
	 * mounted.
	 *
	 * @return	Integer	
	 *
	 * @method
	 */
	function MountDrives()
	{
		$intMounted = 0;

		// for each drive
		foreach ($this->arrDrives as $strDrive=>$bolMount)
		{
			// if drive is set to be mounted
			if ($bolMount)
			{
				// mount the drive
				$strResult = shell_exec("mount /dev/$strDrive /mnt/$strDrive 2>&1");
				
				// check if mounting failed
				$strMtab = shell_exec("grep \"/dev/$strDrive\" /etc/mtab");
				if (strpos($strMtab, "/mnt/$strDrive") === FALSE)
				{
					// drive not mounted
					$this->Error("$strDrive failed to mount - $strResult", 'Mount');
				}
				else
				{
					// drive mounted
					$intMounted++;
					$this->arrDrives[$strDrive] = "/mnt/$strDrive";
				}
			}
		}

		return $intMounted;
	}
	
	//------------------------------------------------------------------------//
	// UnmountDrives
	//------------------------------------------------------------------------//
	/**
	 * UnmountDrives()
	 *
	 * Unmounts all drives that have been mounted with MountDrives
	 *
	 * Unmounts all drives that have been mounted with MountDrives. Returns
	 * Returns false if any drives are still mounted
	 *
	 * @return	Boolean	
	 *
	 * @method
	 */
	function UnmountDrives()
	{
		$bolreturn = TRUE;
		
		// for each drive
		foreach ($this->arrDrives as $strDrive=>$bolMount)
		{
			// if drive is mounted (or should have been mounted)
			if ($bolMount)
			{
				// unmount the drive
				$strResult = shell_exec("umount /dev/$strDrive");
				
				// check if drive unmounted
				$strMtab = shell_exec("grep \"/dev/$strDrive\" /etc/mtab");
				if (strpos($strMtab, "/mnt/$strDrive") === FALSE)
				{
					// unmounted
					$this->arrDrives[$strDrive] = TRUE;
				}
				else
				{
					// still mounted
					$this->Error("$strDrive failed to unmount - $strResult", 'Unmount');
					$bolreturn = FALSE;
				}
			}
		}
		
		return $bolreturn;
	}
	
	function DrivesMounted()
	{
		$intMounted = 0;
		
		// for each drive
		foreach ($this->arrDrives as $strDrive=>$bolMount)
		{
			// if drive is mounted
			if ($bolMount && $bolMount !== TRUE)
			{
				$intMounted++;
			}
		}
		
		return $intMounted;
	}
	
	//------------------------------------------------------------------------//
	// SelectTarget
	//------------------------------------------------------------------------//
	/**
	 * SelectTarget()
	 *
	 * Selects the target drive to backup to.
	 *
	 * Selects the target drive to backup to. Sets an error if the wrong drive
	 * is selected, and returns false if no drive is selected
	 *
	 * @return	Boolean
	 *
	 * @method
	 */
	function SelectTarget()
	{
		// get DOW
		$strDow = $this->strDow;
		
		// select drive for today
		$strDrive = $this->arrTarget[$strDow];
		
		// check if todays drive is mounted
		if ($this->arrDrives[$strDrive] && $this->arrDrives[$strDrive] !== TRUE)
		{
			// todays drive is mounted
			return $this->arrDrives[$strDrive];
		}
		
		// if we got to here then todays drive is not mounted, so set an error
		$this->Error("$strDrive is set as the Drive for $strDow, but $strDrive is not mounted", 'Target');
		
		// try to find another mounted drive
		foreach ($this->arrDrives as $strDrive=>$mixMount)
		{
			if ($mixMount && $mixMount !== TRUE)
			{
				// got one
				$this->Error("$strDrive is mounted, using $strDrive as substitute", 'Target');
				return $mixMount;
			}
		}
		
		// no mounted drives, this is BAD!
		$this->Error("Can't find a mounted backup drive", 'Target');
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// PrepareTarget
	//------------------------------------------------------------------------//
	/**
	 * PrepareTarget()
	 *
	 * Prepares (cleans) the target
	 *
	 * Prepares (cleans) the target
	 *
	 * @param	String		$strTarget	The directory to clean
	 * @return	Boolean
	 *
	 * @method
	 */
	// prepare (clean) the target
	function PrepareTarget($strTarget)
	{
		$strTarget = trim($strTarget, '/ \t\n\r\0\x0B');
		if (!$strTarget)
		{
			// target can not be empty or root
			return FALSE;
		}
		
		// get DOW
		$strDow = $this->strDow;
		
		// make sure the directory exists
		@mkdir("/$strTarget/viXenBackup");
		@mkdir("/$strTarget/viXenBackup/$strDow");
		
		// clean the dirty little bitch
		shell_exec("rm -Rf /$strTarget/viXenBackup/$strDow/* 2>&1");
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// CopyToTarget
	//------------------------------------------------------------------------//
	/**
	 * CopyToTarget()
	 *
	 * Copys all files recursively from the source path to the target path
	 *
	 * Copys all files recursively from the source path to the target path
	 *
	 * @param	String		$strSource	Source file or directory
	 * @param	String		$strTarget	Target directory to copy to
	 * @return	Boolean
	 *
	 * @method
	 */
	function CopyToTarget($strSource, $strTarget)
	{

		if (!trim($strSource, '/ \t\n\r\0\x0B') || !trim($strTarget, '/ \t\n\r\0\x0B'))
		{
			// source can not be empty or root
			$this->Error("Copy faied - invalid source or target");
			return FALSE;
		}
		
		// check for a valid source
		if (is_file($strSource))
		{
			$strSourcePath = dirname($strSource);
		}
		elseif(is_dir($strSource))
		{
			$strSourcePath = $strSource;
		}
		else
		{
			// source must exist
			$this->Error("Copy faied - invalid source : $strSource");
			return FALSE;
		}
		
		// remove slashes and spaces from paths
		$strSoure	 	= trim($strSource, '/ \t\n\r\0\x0B');
		$strSourePath 	= trim($strSourcePath, '/ \t\n\r\0\x0B');
		$strTarget 		= trim($strTarget, '/ \t\n\r\0\x0B');
		
		// get DOW
		$strDow = $this->strDow;
		
		// make directory path
		$strTargetPath = "/$strTarget/viXenBackup/$strDow/$strSourcePath/";
		$strReturn = shell_exec("mkdir -p $strTargetPath 2>&1");
		
		// copy
		$strReturn = shell_exec("cp -Rp /$strSource $strTargetPath 2>&1");
		
		// check for copy errors
		if ($strReturn != '')
		{
			$this->Error("Failed to copy /$strSource - $strReturn");
			return FALSE;
		}
		
		// return
		return TRUE;
	}
	
	function DumpToTarget($strTarget, $bolRemove=FALSE)
	{
		$strTarget = trim($strTarget, '/ \t\n\r\0\x0B');
		if (!$strTarget)
		{
			// target can not be empty or root
			$this->Error("Failed to dump database : bad target");
			return FALSE;
		}
		
		// get DOW
		$strDow = $this->strDow;
		
		// setup target 
		$strTargetPath 	= "/$strTarget/viXenBackup/$strDow/mysqldump/";
		$strTargetFile 	= $strTargetPath.$this->strMysqlDatabase.".sql";
		$strErrorFile 	= $strTargetPath.$this->strMysqlDatabase.".err";
		
		// make directory path
		$strReturn = shell_exec("mkdir -p $strTargetPath 2>&1");
		
		// clean error file
		shell_exec("echo \"\" > $strErrorFile 2> /dev/null");
		
		// setup dump command
		$strCommand = "mysqldump --user={$this->strMysqlUser} --password={$this->strMysqlPassword} --master-data=2 --single-transaction {$this->strMysqlDatabase} > $strTargetFile  2> $strErrorFile";
		
		// dump the bitch
		$strReturn = shell_exec($strCommand);
		
		// check for dump errors
		if ($strReturn = shell_exec("cat $strErrorFile 2>&1"))
		{
			$this->Error("Failed to dump database to $strTargetFile : $strReturn");
			return FALSE;
		}
		
		// clean error file
		shell_exec("echo \"\" > $strErrorFile 2> /dev/null");
		
		// setup gzip command
		$strCommand = "gzip -c $strTargetFile > $strTargetFile.gz 2> $strErrorFile";
		
		// gzip the bitch
		$strReturn = shell_exec($strCommand);
		
		// check for gzip errors
		if ($strReturn = shell_exec("cat $strErrorFile 2>&1"))
		{
			$this->Error("Failed to gzip database at $strTargetFile : $strReturn");
			return FALSE;
		}
		
		// split the backup into tables
		//$strCommand = "csplit --prefix=charge $strTargetFile /DROP TABLE IF EXISTS/ {*}";
		
		if ($bolRemove === TRUE)
		{
			// setup remove command
			$strCommand = "rm -f $strTargetFile";
			
			// remove the bitch
			$strReturn = shell_exec($strCommand);
		}
		
		// all good
		return TRUE;
	}
	
	function StopServer($strServer)
	{
		shell_exec("/etc/init.d/$strServer stop");
	}
	
	function StartServer($strServer)
	{
		shell_exec("/etc/init.d/$strServer start");
	}
	
	// returns $strTarget or FALSE
	function PrepareBackup()
	{
		// Check if we have mounted drives
		if ($intMounted = $this->DrivesMounted())
		{
			$this->Error("$intMounted drives are already mounted");
			//return $intMounted;
		}
		
		// try to mount drives
		if (!$this->MountDrives())
		{
			return FALSE;
		}
		
		// Select target
		if (!$strTarget = $this->SelectTarget())
		{
			return FALSE;
		}
		
		// prepare target
		if (!$this->PrepareTarget($strTarget))
		{
			return FALSE;
		}
		
		// return the target
		return $strTarget;
	}
	
	//------------------------------------------------------------------------//
	// Error
	//------------------------------------------------------------------------//
	/**
	 * Error()
	 *
	 * Records the passed error into an array
	 *
	 * Records the passed error into an array
	 *
	 * @param	String		$strError	The error message
	 * @param	String		$strMethod	The method where the error occured
	 * @return	void
	 *
	 * @method
	 */
	function Error($strError, $strMethod=NULL)
	{
		$this->arrErrors[] = trim($strError);
		$this->arrErrorsByMethod[$strMethod][] = trim($strError);
	}
	
	
	//------------------------------------------------------------------------//
	// GetErrorMessage
	//------------------------------------------------------------------------//
	/**
	 * GetErrorMessage()
	 *
	 * Returns all error messages, or all error messages for a particular method
	 *
	 * Returns all error messages, or all error messages for a particular method
	 *
	 * @param	String		$strMethod	Optional method to return errors for
	 * @return	String
	 *
	 * @method
	 */
	function GetErrorMessage($strMethod=NULL)
	{
		if ($strMethod != NULL)
		{
			$strErrors = implode("\n", $this->arrErrorsByMethod[$strMethod]);
		}
		else
		{
			$strErrors = implode("\n", $this->arrErrors);
		}
		
		return "$strErrors\n";
	}
	
	//------------------------------------------------------------------------//
	// CheckError
	//------------------------------------------------------------------------//
	/**
	 * CheckError()
	 *
	 * Checks to see if there are any errors
	 *
	 * Checks to see if there are any errors. If passed a method name, it only
	 * checks for errors from that method.
	 *
	 * @param	String		$strMethod	Optional method to check for errors
	 * @return	int			number of errors
	 *
	 * @method
	 */
	function CheckError($strMethod=NULL)
	{
		if ($strMethod != NULL)
		{
			$intErrors = count($this->arrErrorsByMethod[$strMethod]);
		}
		else 
		{
			$intErrors = count($this->arrErrors);
		}

		return $intErrors;
	}
	
	//------------------------------------------------------------------------//
	// ClearErrorMessage
	//------------------------------------------------------------------------//
	/**
	 * ClearErrorMessage()
	 *
	 * Clears all error messages, or all error messages for the specified method
	 *
	 * Clears all error messages, or all error messages for the specified method
	 *
	 * @param	String		$strMethod	Optional method to clear errors for
 	 * @return	Boolean
	 *
	 * @method
	 */
	function ClearErrorMessage($strMethod=NULL)
	{
		if ($strMethod != NULL)
		{
			$this->arrErrorsByMethod[$strMethod] = array();
		}
		else 
		{
			$this->arrErrors 			= array();
			$this->arrErrorsByMethod 	= array();
		}
		return true;
	}
}

?>

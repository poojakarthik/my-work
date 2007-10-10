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
		$this->strLiveDatbase 	= "vixen";
		$this->strDow 			= strtolower(date("D"));
		$this->arrDrives		= array();
		$this->arrErrors		= array();
		$this->arrErrorsByMethod = array('MysqlHotCopy'=>array(), 		'MysqlColdBackup'=>array(), 
										 'MysqlBinlogBackup'=>array(), 	'MountDrives'=>array(), 
										 'UnmountDrives'=>array(), 		'SelectTarget'=>array(), 
										 'PrepareTarget'=>array(), 		'CopyToTarget'=>array());
		$this->bolError			= FALSE;
		
	}
	
	//------------------------------------------------------------------------//
	// MysqlHotCopy
	//------------------------------------------------------------------------//
	/**
	 * MysqlHotCopy()
	 *
	 * Backs up a database while it is running
	 *
	 * Backs up a database while it is running
	 *
	 * @param	Array		arrSkipTables	List of tables to skip backing up
	 * @return	Boolean
	 *
	 * @method
	 */
 	function MysqlHotCopy($arrSkipTables=NULL)
	{
		// check that we are connected to the correct database
		if (strtolower($GLOBALS["**arrDatabase"]['Database']) == "vixen")
		{
			// can't hot copy the live database onto itself
			return FALSE;
		}
		
		// make sure skip tables is an array
		if (!is_array($arrSkipTables))
		{
			$arrSkipTables = Array();
		}
		
		// set up list tables object
		$qltCopyTable = new QueryListTables();
		
		// get tables from vixen
		$arrTables = $qltCopyTable->Execute($this->strLiveDatbase);
		
		// set up copy table object
		$qctCopyTable = new QueryCopyTable();
		
		// clean tables list
		foreach($arrTables AS $mixKey=>$strTable)
		{
			if (strpos($strTable, '_') !== FALSE)
			{
				// tables with an '_' are temporary backups
				echo "skip table : $strTable\n";
			}
			elseif ($arrSkipTables[$strTable])
			{
				echo "skip table : $strTable\n";
			}
			else
			{
				echo "copy table : $strTable\n";
				
				// copy a table
				$qctCopyTable->Execute($strTable, "{$this->strLiveDatbase}.$strTable");
			}
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// MysqlColdBackup
	//------------------------------------------------------------------------//
	/**
	 * MysqlColdBackup()
	 *
	 * Shuts down MySQL, backs up all the data, then starts it again
	 *
	 * Shuts down MySQL, backs up all the data, then starts it again
	 *
	 * @return	Boolean
	 *
	 * @method
	 */
	function MysqlColdBackup()
	{
		$bolReturn = TRUE;
		// Backup a MySQL InnoDB Database
		//
		// built from instructions found at : http://mysql.org/doc/refman/5.0/en/innodb-backup.html
		
		// -----------------------------------------------------------------------------
		// CONFIG
		// -----------------------------------------------------------------------------
		
		// BACKUP_DIR
		// 	Full path to backup directory (do NOT include trailing slash)
		$BACKUP_DIR='/home/backup';
		
		// MYSQL_DIR
		// 	Full path to MySQL directory (do NOT include trailing slash)
		$MYSQL_DIR='/var/lib/mysql';
		
		// LOG_DIR
		// 	Full path to MySQL LOG directory (do NOT include trailing slash)
		$LOG_DIR='/var/log/mysql';
		
		// OLD_LOG_DIR
		// 	Full path to MySQL OLD LOG directory (do NOT include trailing slash)
		$OLD_LOG_DIR='/var/log/mysql_old';
		
		// INNODB_DIR
		// 	Full path to InnoDB directory (do NOT include trailing slash)
		$INNODB_DIR='/var/lib/mysql';
		
		// DATABASE_NAME
		// 	Full path to InnoDB directory (do NOT include trailing slash)
		$DATABASE_NAME='vixen';
		
		// -----------------------------------------------------------------------------
		// SCRIPT
		// -----------------------------------------------------------------------------
		
		// Set Backup Name
		$BACKUP_NAME= date("Y-m-d_H:i:s");
		
		// Make backup dir for logs
		$strReturn = shell_exec("mkdir -pm 700 $OLD_LOG_DIR/$BACKUP_NAME/");
		
		// Make Directory for this backup
		$strReturn = shell_exec("mkdir -pm 700 $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/log/");
		
		$strReturn = '';
		
		// Shut down your MySQL server and make sure that it shuts down without errors.
		$strReturn = shell_exec("/etc/init.d/mysql stop");
		
		if ($strReturn != '')
		{
			$this->Error("MySQL failed to shut down - $strReturn", 'MysqlColdBackup');
			return FALSE;
		}
		$strReturn = '';
		
		// Copy all your data files (ibdata files and .ibd files) into a safe place.
		$strReturn = shell_exec("cp -ip $INNODB_DIR/ibdata* $BACKUP_DIR/$BACKUP_NAME/");
		
		if ($strReturn != '')
		{
			$this->Error("ibdata files failed to copy - $strReturn", 'MysqlColdBackup');
			$bolReturn = FALSE;
		} 
		$strReturn = '';
		
		// Copy all your ib_logfile files to a safe place.
		$strReturn = shell_exec("cp -ip $INNODB_DIR/ib_logfile* $BACKUP_DIR/$BACKUP_NAME/");
		
		if ($strReturn != '')
		{
			$this->Error("ib_logfiles failed to copy - $strReturn", 'MysqlColdBackup');
			$bolReturn = FALSE;
		} 
		$strReturn = '';
		
		// Copy your my.cnf configuration file or files to a safe place.
		$strReturn = shell_exec("cp -ip /etc/mysql/my.cnf $BACKUP_DIR/$BACKUP_NAME/");
		
		if ($strReturn != '')
		{
			$this->Error("my.cnf configuration file failed to copy - $strReturn", 'MysqlColdBackup');
			$bolReturn = FALSE;
		} 
		$strReturn = '';
		
		// Copy all the .frm & .idb files for your InnoDB tables to a safe place.
		$strReturn = shell_exec("cp -Rip $MYSQL_DIR/$DATABASE_NAME/* $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/");
		
		if ($strReturn != '')
		{
			$this->Error(".frm & .idb files faile to copy - $strReturn", 'MysqlColdBackup');
			$bolReturn = FALSE;
		} 
		$strReturn = '';
		
		// Copy the MySQL database
		$strReturn = shell_exec("cp -Rip $MYSQL_DIR/mysql $BACKUP_DIR/$BACKUP_NAME/");
		
		if ($strReturn != '')
		{
			$this->Error("MySQL database failed to copy - $strReturn", 'MysqlColdBackup');
			$bolReturn = FALSE;
		} 
		$strReturn = '';
		
		// Restart the MySQL server
		$strReturn = shell_exec("/etc/init.d/mysql start");
		
		if ($strReturn != '')
		{
			$this->Error("MySQL database failed to restart - $strReturn", 'MysqlColdBackup');
			$bolReturn = FALSE;
		} 
		// Send a message
		//TODO!!!!
		
		return $bolReturn;
	}
	
	//------------------------------------------------------------------------//
	// MysqlBinlogBackup
	//------------------------------------------------------------------------//
	/**
	 * MysqlBinlogBackup()
	 *
	 * Backs up the MySQL binary logs
	 *
	 * Backs up the MySQL binary logs
	 *
	 * @return	Boolean
	 *
	 * @method
	 */
	function MysqlBinlogBackup()
	{
		//Set directories
		$strLogFileDir = "/var/log/mysql";
		$strBackupDir = "/home/backup/mysql_bin_logs";
		
		//Make Backup Dir
		mkdir($strBackupDir, 0700, TRUE);
		
		//Get file lists
		$arrLogFiles = scandir($strLogFileDir);
		$arrBackupFiles = scandir($strBackupDir);
		
		//read index file
		$arrIndexFile = file("$strLogFileDir/mysql-bin.index");
		$strCurrentLog = trim(end(explode("/", end($arrIndexFile))));
		
		
		//remove index and current log file from file list
		$arrRemove = array($strCurrentLog, "mysql-bin.index");
		$arrLogFiles = array_diff($arrLogFiles, $arrRemove);
		
		//copy each file that doesnt exist in the backup directory
		foreach ($arrLogFiles AS $strLogFile)
		{
			If(!file_exists("$strBackupDir/$strLogFile"))
			{
				// echo "Copying $strLogFile\n";
				if (!copy("$strLogFileDir/$strLogFile", "$strBackupDir/$strLogFile")) 
				{
					$this->Error("Failed to copy $strLogFile", 'MysqlBinlogBackup');
				}
			}
		}
		return TRUE;
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
			if (!$bolMount)
			{
				// mount the drive
				$strResult = shell_exec("mount $strDrive /media/$strDrive");
				
				// check if mounting failed
				if (file_exists("/media/$strDrive/vixen.nodisk"))
				{
					// drive not mounted
					$this->Error("$strDrive failed to mount - $strResult", 'MountDrives');	
				}
				else
				{
					// drive mounted
					$intMounted++;
					$this->arrDrives[$strDrive] = "/media/$strDrive";
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
				$strResult = shell_exec("umount $strDrive");
				
				// check if drive unmounted
				if (file_exists("/media/$strDrive/vixen.nodisk"))
				{
					// unmounted
					$this->arrDrives[$strDrive] = TRUE;
				}
				else
				{
					// still mounted
					$this->Error("$strDrive failed to unmount - $strResult", 'UnmountDrives');
					$bolreturn = FALSE;
				}
			}
		}
		
		return $bolreturn;
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
		$this->Error("$strDrive is set as the Drive for $strDow, but $strDrive is not mounted", 'SelectTarget');
		
		// try to find another mounted drive
		foreach ($this->arrDrives as $strDrive=>$mixMount)
		{
			if ($mixMount && $mixMount !== TRUE)
			{
				// got one
				$this->Error("$strDrive is mounted, using $strDrive as substitute", 'SelectTarget');
				return $mixMount;
			}
		}
		
		// no mounted drives, this is BAD!
		$this->Error("Can't find a mounted backup drive", 'SelectTarget');
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
		$strTarget = trim($strTarget);
		if (!$strTarget || $strTarget == '/')
		{
			// target can not be empty or root
			return FALSE;
		}
		
		// get DOW
		$strDow = $this->strDow;
		
		// make sure the directory exists
		mkdir("$strTarget/viXenBackup");
		mkdir("$strTarget/viXenBackup/$strDOW");
		
		// clean the dirty little bitch
		shell_exec("rm -Rf $strTarget/viXenBackup/$strDOW/*");
		
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
		if (!$strSource || $strSource == '/')
		{
			// source can not be empty or root
			return FALSE;
		}
		
		$bolIsFile = is_file($strSource);
		$strSourcePath = "";
		if ($bolIsFile)
		{
			$strSourcePath = dirname($strSource);
		}
		else
		{
			$strSourcePath = $strSource;
		}
		$strSourePath = trim($strSourePath, '/');
		
		$strTarget = trim($strTarget);
		if (!$strTarget || $strTarget == '/')
		{
			// target can not be empty or root
			return FALSE;
		}
		
		// get DOW
		$strDow = $this->strDow;
		
		// make directory path
		$strTargetPath = "$strTarget/viXenBackup/$strDow/$strSourcePath/";
		$strReturn = shell_exec("mkdir -p $strTargetPath");
		// mkdir cannot error, running as root, so no need to check
		$strReturn = '';
		
		// copy
		$strReturn = shell_exec("cp -Rp $strSource $strTargetPath");
		
		// check for copy errors
		if ($strReturn != '')
		{
			$this->Error("Failed to copy $strSource - $strReturn", 'CopyToTarget');
			return FALSE;
		}
		
		// return
		return TRUE;
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
	function Error($strError, $strMethod)
	{
		$this->bolError = TRUE;
		$this->arrErrors[] = "$strMethod - $strError";
		$this->arrErrorsByMethod[$strMethod][] = $strError;
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
			$strErrors = "$strMethod Errors \n";
			if (!(empty($this->arrErrorsByMethod[$strMethod])))
			{
				foreach($this->arrErrorsByMethod[$strMethod] as $strMessage)
				{
					$strErrors .= "$strMessage \n";
				}
			}
			elseif ((empty($this->arrErrorsByMethod[$strMethod])) && array_key_exists($strMethod, $this->arrErrorsByMethod))
			{
				$strErrors = 'No errors found';
			}
			else 
			{
				$strErrors = 'Invalid Method Name';
			}
			
		}
		else
		{
			if ($this->bolError)
			{
				$strErrors = implode("\n", $this->arrErrors);
			}
			else 
			{
				$strErrors = 'No errors found';
			}
		}
		
		return $strErrors;
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
	 * @return	String
	 *
	 * @method
	 */
	function CheckError($strMethod=NULL)
	{
		if ($strMethod != NULL)
		{
			if (!(empty($this->arrErrorsByMethod[$strMethod])))
			{
				$intErrors = count($this->arrErrorsByMethod[$strMethod]);
			}
			elseif ((empty($this->arrErrorsByMethod[$strMethod])) && array_key_exists($strMethod, $this->arrErrorsByMethod))
			{
				$intErrors = 0;
			}
			else 
			{
				$intErrors = -1;
			}
		}
		else 
		{
			if ($this->bolError)
			{
				$intErrors = count($this->arrErrors);
			}
			else 
			{
				$intErrors = 0;
			}
		}
		
		switch ($intErrors)
			{
				case -1:
					$strMessage = 'Invalid Method Name';
					break;
				case 1:
					$strMessage = '1 Error Found';
					break;
				default:
					$strMessage = "$intErrors Errors Found";
					break;
			}
		return $strMessage;
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
			$this->arrErrors = array();
			$this->arrErrorsByMethod = array('MysqlHotCopy'=>array(), 		'MysqlColdBackup'=>array(), 
										 	 'MysqlBinlogBackup'=>array(), 	'MountDrives'=>array(), 
										 	 'UnmountDrives'=>array(), 		'SelectTarget'=>array(), 
										 	 'PrepareTarget'=>array(), 		'CopyToTarget'=>array());
			$this->bolError = TRUE;
		}
		return true;
	}

 }
?>

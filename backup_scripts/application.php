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
 	function __Construct()
	{
		$this->strLiveDatbase 	= "vixen";
		$this->strDow 			= strtolower(date("D"));
	}
	
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
	
	function MysqlColdBackup()
	{
	
	}
	
	function MysqlBinlogBackup()
	{
	
	}
	
	// return the number of drives mounted
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
					$this->arrErrors['Drive'][$strDrive] = "$strDrive failed to mount - $strResult";	
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
	
	// return FALSE if any drives are still mounted
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
					$this->arrErrors['Unmount'][$strDrive] = "$strDrive failed to unmount - $strResult";
					$bolreturn = FALSE;
				}
			}
		}
		
		return $bolreturn;
	}
	
	// select target
	// sets an error if the wrong drive is selected
	// returns FALSE if no drive is selected
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
		$this->Error("$strDrive is set as the Drive for $strDow, but $strDrive is not mounted";
		
		// try to find another mounted drive
		foreach ($this->arrDrives as $strDrive=>$mixMount)
		{
			if ($mixMount && $mixMount !== TRUE)
			{
				// got one
				$this->Error("$strDrive is mounted, using $strDrive as substitute";
				return $mixMount;
			}
		}
		
		// no mounted drives, this is BAD!
		$this->Error("Can't find a mounted backup drive";
		return FALSE;
	}
	
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
	
	function CopyToTarget($strSource, $strTarget)
	{
		$strTarget = trim($strTarget);
		if (!$strTarget || $strTarget == '/')
		{
			// target can not be empty or root
			return FALSE;
		}
		if (!$strSubDir)
		{
			$strSubDir = "";
		}
		
		// get DOW
		$strDow = $this->strDow;
		
		// copy
		$return = shell_exec("cp -Rip $strSource $strTarget/viXenBackup/$strDOW/");
		
		// errors ??
		//TODO!!!!
		
		// return
		return TRUE;
	}
}


?>

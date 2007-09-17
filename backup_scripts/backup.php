<?php

// CONFIG SECTION

$arrConfig = array();

// Removable or ???
$arrConfig['Type'] = "???";

// Server name for use in email message
$arrConfig['Server'] = "Test";

// Directories to backup
$arrConfig['Directory'][0] = "testarea/tysonsucksv2";
$arrConfig['Directory'][1] = "testarea/fgfds";

// Backup directory for non-removable mode
$arrConfig['BackupPath'] = "testarea/backup/";

// Emails to send confirmation / log to
$arrConfig['Email'][0] = "test@lol.com";

// Required only if type is set to removable.
$arrConfig['Drive'][0] = "SDC";
$arrConfig['Drive'][1] = "SDD";
$arrConfig['Drive'][2] = "SDE";

// MySQL Path, if empty no MySQL backup will be performed
$arrConfig['MySQL'] = '/somewhere/over/the/rainbow?';



// SCRIPT

class Backup
{
	
	function __Construct($arrConfig)
	{ 
		// save config options
		
		$this->strType = $arrConfig['Type'];
		$this->strServer = $arrConfig['Server'];
				
		$this->arrDirectories = array();
		foreach ($arrConfig['Directory'] as $strPath)
		{
			$this->arrDirectories[] = $strPath;		
		}
		
		$this->arrEmails = array();
		foreach ($arrConfig['Email'] as $strEmail)
		{
			// email checking?
			$this->arrEmails[] = $strEmail;		
		}
		
		$this->arrErrors = array();
		
		if ($this->strType == 'Removable')
		{
			$this->arrDrives = array();
			foreach ($arrConfig['Drive'] as $strDrive)
			{
				$this->arrDrives[] = $strDrive;		
			}
			$this->arrStatistics = array();
		}
		else
		{
			$this->strBackupPath = $arrConfig['BackupPath'];
		}
		
		if ($this->strMySQLPath = $arrConfig['MySQL'])
		{
			$this->bolMySQL = TRUE;
		}
	}

	function OutputConfig()
	{
		echo "<p> Directories to be backed up </p>";
		print_r($this->arrDirectories);
		echo "<p> Drives to mount </p>";
		print_r($this->arrDrives);
		echo "<p> Email Addresses to notify </p>";
		print_r($this->arrEmails);
	
	}

	function MountDrives()
	{
		$bolDriveMounted = FALSE;

		foreach ($this->arrDrives as $strDrive)
		{
			$strResult = "";
			$strResult = shell_exec("mount $strDrive /media/$strDrive");
			// is it possible to tell whether the drive was mounted this way??
			if ($strResult)
			{
				$this->arrErrors['Drive'][$strDrive] = "$strDrive failed to mount - $strResult";
			}
			else
			{
				$bolDriveMounted = TRUE;
				$this->arrStatistics[$strDrive] = "/media/$strDrive";
			}
		}
		if ($bolDriveMounted == FALSE)
		{
			return FALSE;
		}
		
		return TRUE;
	}

	function SelectDrive()
	{
		$strDoW = date("D"); // or for full name l
		if ($strDow = "Mon" or $strDow = "Wed")
		{
			$strDrive = 	"SDC";
			$strSecond = 	"SDD";
			$strThird = 	"SDE";			
		}
		elseif ($strDow = "Tue" or $strDow = "Thu")
		{
			$strDrive = 	"SDD";
			$strSecond = 	"SDE";
			$strThird = 	"SDC";
		}
		else 
		{
			$strDrive = 	"SDE";
			$strSecond = 	"SDC";
			$strThird = 	"SDD";
		}
		
		if ($this->arrErrors['Drive'][$strDrive])
		{
			$this->arrErrors['Drive']['Correct'] = "Correct drive $strDrive could not be mounted, ";
			
			if($this->arrErrors['Drive'][$strSecond])
			{
				$this->arrErrors['Drive']['Correct'] .= "Drive $strThird used instead";
				return "$strThird/Other/";
			}
			else
			{
				$this->arrErrors['Drive']['Correct'] .= "Drive $strSecond used instead";
				return "$strSecond/Other/";
			}
			
		}
		return "$strDrive/$strDoW/";
	}


	function EmptyDirectory($strTarget, $bolDeleteDirectory)
	{
		if (!$strHandle = @opendir($strTarget))
		{
			return;
		}
		
		while (($strName = readdir($strHandle)) == TRUE)
		{
			if ($strName == '.' || $strName == '..')
			{
				continue;
			}
			
			if (!@unlink($strTarget.'/'.$strName))
			{
				$this->EmptyDirectory($strTarget.'/'.$strName, TRUE);
			}	
		}
		
		if ($bolDeleteDirectory)
		{
			closedir($strHandle);
			@rmdir($strTarget);
		}
	}

	
	function CopyDirectory($strSource, $strDestination)
	{
		if (!is_dir($strDestination))
		{
			mkdir($strDestination);
		}
		
		if (!$strHandle = @opendir($strSource))
		{
			return;
		}
		
		while (($strName = readdir($strHandle)) == TRUE)
		{
			if ($strName == '.' || $strName == '..')
			{
				continue;
			}
			
			if (is_dir($strSource.'/'.$strName))
			{ 
				$this->CopyDirectory("$strSource/$strName", "$strDestination/$strName");
			}
			else 
			{			
				copy("$strSource/$strName", "$strDestination/$strName");
			}	
		}
	}

	function MySQLShutdown()
	{
		// shell_exec("/etc/init.d/mysql stop")
	}

	function MySQLCopy()
	{
		// ??
	}
	
	function MySQLRestart()
	{
		// shell_exec("/etc/init.d/mysql start")
	}

	function CollectStats()
	{
		foreach ($this->arrStatistics as $strDrive=>$strPath)
		{
			$intFree = disk_free_space($strPath);
			$intTotal = disk_total_space($strPath);
			$intUsed = $intTotal - $intFree;
			$this->arrStatistics[$strDrive]['Free'] = $this->ConvertSize($intFree);
			$this->arrStatistics[$strDrive]['Used'] = $this->ConvertSize($intUsed);
		}
	}
	
	function ConvertSize($intSize)
	{
		switch (true)
		{
			case ($intSize > 1099511627776):
				$intSize /= 1099511627776;
				$suffix = 'TB';
			break;
			
			case ($intSize > 1073741824):
				$intSize /= 1073741824;
				$suffix = 'GB';
			break;
			
			case ($intSize > 1048576):
				$intSize /= 1048576;
				$suffix = 'MB';   
			break;
			
			case ($intSize > 1024):
				$intSize /= 1024;
				$suffix = 'KB';
				break;
				
			default:
				$suffix = 'B';
    	}
    	return round($intSize, 2).$suffix;
	}
	
	function UnmountDrives()
	{
		// become root??
		foreach ($this->arrStatistics as $strDrive=>$strStats)
		{
			$strResult = "";
			$strResult = shell_exec("umount $strDrive");
			// is it possible to tell whether the drive was unmounted this way??
			if ($strResult)
			{
				$this->arrErrors['Unmount'][$strDrive] = "$strDrive failed to unmount - $strResult";
			}
		}
	}
	
	function WriteEmail()
	{
		$bolErrors = FALSE;
		$strMessage = "Backup Report for $this->strServer - ";
		$strMessage .= date('H:i:s d-m-Y');
		$strMessage .= "\n";
		$strMessage .= "\n";
		
		// ERRORS
		$strMessage .= "Errors - \n";
		
		if ($this->strType == "Removable")
		{
			if ($this->arrErrors['Drive']['Correct'])
			{
				$strMessage .= "$this->arrErrors['Drive']['Correct'] \n";
				$bolErrors = TRUE;
			}
			
			// if multiple drives 
	
			if ($this->arrErrors['Unmount'])
			{
				$bolErrors = TRUE;
				foreach ($this->arrErrors['Unmount'] as $strDrive=>$strMessage)
				{
					$strMessage .= "$strMessage \n";
				}
			}
		}
		if ($bolErrors == FALSE)
		{
			$strMessage .= "None encountered \n";
		}
		
		
		// STATS
		if ($this->strType == "Removable")
		{
			$strMessage .= "Usage Statistics - \n";
			foreach ($this->arrStatistics as $strDrive=>$strData)
			{
				$strMessage .= "Drive $strDrive \n";
				$strMessage .= "Used Space - $this->arrStatistics[$strDrive]['Used'] \n";
				$strMessage .= "Free Space - $this->arrStatistics[$strDrive]['Free'] \n \n";
			}
		}
		return $strMessage;
	}
	
	function SendEmail()
	{
		// ??
	}

	function Backup()
	{
		if ($this->strType == 'Removable')
		{
			$this->MountDrives();
			$strBackupPath = $this->SelectDrive();
		}
		else
		{
			// addition of DoW or DoM?
			$strBackupPath = $this->strBackupPath;
		}
		
		$this->EmptyDirectory($strBackupPath, FALSE);
		
		foreach ($this->arrDirectories as $strDataPath)
		{
			$this->CopyDirectory($strDataPath, $strBackupPath);	
		}		
		
		if ($this->bolMySQL)
		{
			$this->MySQLShutdown();
			$this->MySQLCopy();
			$this->MySQLRestart();
		}
		
		
		
		if ($this->strType == 'Removable')
		{
			$this->CollectStats();
			$this->UnmountDrives();
		}
		
		echo $this->WriteEmail();
		//$this->SendEmail($this->WriteEmail());
		
	}

}

$TestBackup = new Backup($arrConfig);
//$TestBackup->OutputConfig();
echo '<pre>';
// echo $TestBackup->WriteEmail();
// $TestBackup->CopyDirectory('testarea/tysonsucksv2', 'testarea/tysonsucksv3');
$TestBackup->Backup();
echo '</pre>';

?>

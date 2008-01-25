<?php
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
		echo "Copying $strLogFile\n";
		if (!copy("$strLogFileDir/$strLogFile", "$strBackupDir/$strLogFile")) 
		{
   			echo "\tFailed to copy $strLogFile...\n";
		}
	}
}
echo("\n Done.\n\n");
?>

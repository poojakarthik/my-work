<?php
//Set directories
$LogFileDir = "/var/log/mysql";
$BackupDir = "/home/tyson/LogFileBackup";

//Get file lists
$LogFiles = scandir($LogFileDir);
$BackupFiles = scandir($BackupDir);

//read index file
$IndexFile = file("$LogFileDir/mysql-bin.index");
$CurrentLog = trim(end(explode("/", end($IndexFile))));


//remove index and current log file from file list
$Remove = array($CurrentLog, "mysql-bin.index");
$LogFiles = array_diff($LogFiles, $Remove);

//copy each file that doesnt exist in the backup directory
foreach ($LogFiles AS $LogFile)
{
	If(!file_exists("$BackupDir/$LogFile"))
	{
		echo "Copying $LogFile\n";
		if (!copy("$LogFileDir/$LogFile", "$BackupDir/$LogFile")) 
		{
   			echo "\tFailed to copy $LogFile...\n";
		}
	}
}
echo("\n Done.\n\n");
?>

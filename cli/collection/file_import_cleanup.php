<?php

// Framework & Archive_Tar
require_once("../../flex.require.php");
require_once("Archive/Tar.php");

// Statements
$arrStatuses		= Array(FILE_IMPORTED, FILE_NORMALISED, FILE_NOT_UNIQUE);
$selCompletedFiles	= new StatementSelect("FileImport", "Id, Location", "(Status IN (".implode(', ', $arrStatuses).") AND archived_on IS NULL) OR ADD_DATE(CAST(ImportedOn AS DATE), INTERVAL 30 DAY) < CURDATE()");
$ubiCompletedFile	= new StatementUpdateById("FileImport", Array('archive_location' => NULL, 'archived_on' => new MySQLFunction("NOW()")));

// Init Paths
$strImportDir		= FILES_BASE_PATH."import/";
$strArchiveDir		= $strImportDir."archived/";
$strArchivePath		= $strArchiveDir.date("Ymdhis").".tar.bz2";

// Retrieve files to be Archived (which are files that are completely imported, duplicates, or older than 30 days)
if ($selCompletedFiles->Execute() === FALSE)
{
	throw new Exception($selCompletedFiles->Error());
}
else
{
	// Create the Archive
	$tbzArchive	= new Archive_Tar($strArchivePath, 'bz2');
	if ($tbzArchive)
	{
		// Populate the Archive
		while ($arrFileImport = $selCompletedFiles->Fetch())
		{
			if (is_file($arrFileImport['Location']))
			{
				// Add to the archive
				if (!$tbzArchive->add(Array($arrFileImport['Location'])))
				{
					// Error adding to Archive
					throw new Exception("There was an error adding '{$arrFileImport['Location']}' to archive '{$strArchivePath}'!");
				}
				else
				{
					// Clean up the existing file
					if (!unlink($arrFileImport['Location']))
					{
						throw new Exception("There was an error deleting '{$arrFileImport['Location']}'!");
					}
				}
			}
			
			// Set the DB record as Archived, even if the file wasn't found
			$arrFileImport['archived_on']		= new MySQLFunction("NOW()");
			$arrFileImport['archive_location']	= $strArchivePath;
			if ($ubiCompletedFile->Execute($arrFileImport) === FALSE)
			{
				throw new Exception($ubiCompletedFile->Error());
			}
		}
	}
	else
	{
		throw new Exception("There was an error creating archive '{$strArchivePath}'!");
	}
	
	
}
?>
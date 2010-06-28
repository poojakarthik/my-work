<?php

require_once("../../../../../flex.require.php");

// File Types
$aFileTypes	= array();

// -- Data Usage
$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]						= array();
$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]['Regex']				= "/^tap_isk4_\d{14}_\d{8}_\d{6}_a_s\.dat$/i";
$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]['Uniqueness']			= "FileName = <FileName> AND SHA1 = <SHA1>";
$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]['DownloadUnique']		= true;

// Directory Structure
$aDirectories	=	array
					(
						'home'	=>	array
									(
										'arrSubdirectories'	=>	array
																(
																	'TB'	=>	array
																				(
																					'arrSubdirectories'	=>	array
																											(
																												'speedi'	=>	array
																																(
																																	'arrFileTypes'	=>	array
																																						(
																																							RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD	=> &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]
																																						)
																																)
																											)
																				)
																)
									)
					);
// PROPOSED FORMAT
$aPaths	=	array
			(
				'/home/TB/speedi/{/^tap_isk4_\d{14}_\d{8}_\d{6}_a_s\.dat$/i}'	=> &$arrFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD]
			);

// Output
$strOutputFile	= basename(__FILE__, '.cfg.php').'.serialised';
@unlink($strOutputFile);

if (file_put_contents($strOutputFile, serialize($aDirectories)))
{
	echo "\nSerialised Data successfully dumped to '$strOutputFile'.\n\n";
}
else
{
	echo "\nUnable to dump serialised data to '$strOutputFile'.\n\n";
}
?>
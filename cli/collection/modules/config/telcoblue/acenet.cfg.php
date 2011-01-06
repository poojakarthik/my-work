<?php

require_once(dirname(__FILE__)."/../../../../../flex.require.php");

// File Types
$aFileTypes	= array(
	// CDRs
	RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP				=> array(
		'Regex'				=> '/\-cdrs\-\.csv$/i',
		'Uniqueness'		=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'	=> true
	)
);

// Directory Structure
$aDirectories	=	array(
	'cdrs'	=>	array(
		'arrFileTypes'	=>	array(
			RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET				=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET]
		)
	)
);

// PROPOSED FORMAT
$aPaths	=	array
			(
				'/cdrs/{/\-cdrs\-\.csv$/i}'	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET]
			);

// Config
$aModuleConfig['Host']			['Value']		= 'acecentral.acenet.net.au';
$aModuleConfig['Username']		['Value']		= 'tblue';
$aModuleConfig['Password']		['Value']		= 'tblue0987';
$aModuleConfig['FileDefine']		['Value']		= $aDirectories;

// Output
$strOutputFile	= basename(__FILE__, '.cfg.php').'.serialised';
@unlink($strOutputFile);

if (file_put_contents(dirname(__FILE__).'/'.$strOutputFile, serialize($aDirectories)))
{
	echo "\nSerialised Data successfully dumped to '$strOutputFile'.\n\n";
}
else
{
	echo "\nUnable to dump serialised data to '$strOutputFile'.\n\n";
}
?>
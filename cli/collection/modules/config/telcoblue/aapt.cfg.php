<?php

require_once(dirname(__FILE__)."/../../../../../flex.require.php");

// File Types
$aFileTypes	= array(
	// CDRs
	/* Old, uncompressed location
	RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP				=> array(
		'Regex'				=> '/^CTOP\_[a-z0-9]+\_\d+\_\d{14}\.txt$/i',
		'Uniqueness'		=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'	=> true
	),*/
	RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP				=> array(
		'Regex'					=> '/^CTOP\_[a-z0-9]+\_\d+\_\d{14}\.txt\.gz$/i',
		'Uniqueness'			=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'		=> true,
		'Compression'			=> array(
			'StreamWrapper'		=> 'compress.zlib',
			'FileExtensions'	=> array('gz'=>'')	// Remove .gz (if it's the last extension)
		)
	),
	RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE				=> array(
		'Regex'					=> '/^COCE\_[a-z0-9]+\_\d+\_\d{14}\.txt\.gz$/i',
		'Uniqueness'			=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'		=> true,
		'Compression'			=> array(
			'StreamWrapper'		=> 'compress.zlib',
			'FileExtensions'	=> array('gz'=>'')	// Remove .gz (if it's the last extension)
		)
	),
	// Provisioning Responses
	RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT	=> array(
		'Regex'				=> '/^(D)([a-z0-9]{3})([a-z0-9]{3})(E)(\d{14})$/i',
		'Uniqueness'		=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'	=> true
	)
);

// Directory Structure
$aDirectories	=	array(
	'download'	=>	array(
		'arrFileTypes'	=>	array(
			RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP				=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP],
			RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE				=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE],
			RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT]
		)
	)
);

// PROPOSED FORMAT
$aPaths	=	array
			(
				//'/download/{/^CTOP\_[a-z0-9]+\_\d+\_\d{14}\.txt$/i}'	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP],
				'/download/{/^CTOP\_[a-z0-9]+\_\d+\_\d{14}\.txt\.gz$/i}'	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP],
				'/download/{/^(D)([a-z0-9]{3})([a-z0-9]{3})(E)(\d{14})$/i}'	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT]
			);

// Config
$aModuleConfig['Host']			['Value']		= 'ftp.powertel.com.au';
$aModuleConfig['Username']		['Value']		= 'telcoxfr';
$aModuleConfig['Password']		['Value']		= 'T3LCO408';
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
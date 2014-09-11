<?php

require_once("../../../../../flex.require.php");

// File Types
$aFileTypes	= array(
	// CDRs
	RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP				=> array(
		'Regex'				=> '/^CTOP\_[a-z0-9]+\_\d+\_\d{14}\.txt$/i',
		'Uniqueness'		=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'	=> true
	),
	// Provisioning Responses
	RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT	=> array(
		'Regex'				=> '/^(D)([a-z0-9]{3})(E)(\d{14})$/i',
		'Uniqueness'		=> "FileName = <FileName> AND SHA1 = <SHA1>",
		'DownloadUnique'	=> true
	)
);

// Directory Structure
$aDirectories	= array(
	'home'	=> array(
		'arrSubdirectories'	=> array(
			'telcoblue'	=> array(
				'arrSubdirectories'	=> array(
					'Incoming'	=> array(
						'arrSubdirectories'	=> array(
							'cdr'	=> array(
								'arrSubdirectories'	=> array(
									'aapt'	=> array(
										'arrFileTypes'	=>	array(
											RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP				=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP]
										)
									)
								)
							),
							'provisioning'	=> array(
								'arrSubdirectories'	=> array(
									'aapt'	=> array(
										'arrFileTypes'	=>	array(
											RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT]
										)
									)
								)
							)
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
				'/home/telcoblue/Incoming/cdr/aapt/{/^CTOP\_[a-z0-9]+\_\d+\_\d{14}\.txt$/i}'	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP],
				'/home/telcoblue/Incoming/provisioning/aapt/{/^(D)([a-z0-9]{3})(E)(\d{14})$/i}'	=> &$aFileTypes[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT],
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
<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
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
 * @package		collection
 * @author		Rich Davis
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationCollection
//----------------------------------------------------------------------------//
/**
 * ApplicationCollection
 *
 * Collection Application
 *
 * Collection Application
 *
 *
 * @prefix		app
 *
 * @package		vixen
 * @class		ApplicationCollection
 */
class ApplicationCollection extends ApplicationBaseClass
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Collection Application
	 *
	 * Constructor for the Collection Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 */
	function __construct($arrConfig)
	{
		parent::__construct();
		
		// Store local copy of configuration
		$this->_arrConfig	= $arrConfig;
		
 		// Load Collection CarrierModules
 		CliEcho(" * COLLECTION MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_COLLECTION));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$this->_arrModules[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
 			CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'Carrier')." : ".$this->_arrModules[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
 		}
	}
	
	//------------------------------------------------------------------------//
	// Collect
	//------------------------------------------------------------------------//
	/**
	 * Collect()
	 *
	 * Downloads new files from various provider locations
	 *
	 * Downloads new files from various provider locations
	 *
	 * @method
	 */
	function Collect()
	{
		// Statements
		$arrCols				= Array();
		$arrCols['Status']		= NULL;
		$arrCols['ImportedOn']	= NULL;
		$ubiFileDownload		= new StatementUpdateById("FileDownload");
		
		$insFileDownload		= new StatementInsert("FileDownload");
		
		CliEcho("\n[ COLLECTION ]\n");
		
		// For each module
		foreach ($this->_arrModules as $intCarrier=>&$arrFileTypes)
		{
			CliEcho("\t * Provider: ".GetConstantDescription($intCarrier, 'Carrier'));
			foreach ($arrFileTypes as $intResourceType=>&$modModule)
			{
				CliEcho("\t\t * Resource: ".GetConstantDescription($intResourceType, 'FileResource'));
				
				// Download paths
				$strDownloadDirectory	= FILES_BASE_PATH."download/current/".GetConstantDescription($intCarrier, 'Carrier').'/'.GetConstantName($intResourceType, 'FileResource').'/';
				@mkdir($strDownloadDirectory, 0777, TRUE);
				
				// Connect to the Source
				CliEcho("\t\t\t * Connecting to Repository...\t\t\t", FALSE);
				$mixResult	= $modModule->Connect();
				if ($mixResult === TRUE)
				{
					CliEcho("[   OK   ]");
					
					// Download all new files
					$intTotal			= 0;
					$arrDownloadedFiles	= Array();
					CliEcho("\t\t\t * Downloading new files...\n");
					while ($mixDownloadFile	= $modModule->Download($strDownloadDirectory))
					{
						if (is_string($mixDownloadFile))
						{
							CliEcho("\t\t\t\t ERROR: $mixDownloadFile");
							continue;
						}
						else
						{
							$strDownloadPath	= $mixDownloadFile['LocalPath'];
							$strFileName		= basename($strDownloadPath);
							$intSize			= ceil(filesize($strDownloadPath) / 1024);
							
							CliEcho("\t\t\t\t + $strFileName ({$intSize}KB)\t\t", FALSE);
							
							// Insert into FileDownload table
							$arrFileDownload	= Array();
							$arrFileDownload['FileName']	= basename($strDownloadPath);
							$arrFileDownload['Location']	= $strDownloadPath;
							$arrFileDownload['Carrier']		= $intCarrier;
							$arrFileDownload['CollectedOn']	= date("Y-m-d H:i:s");
							$arrFileDownload['Status']		= FILE_COLLECTED;
							if (!defined('COLLECTION_DEBUG_MODE') || !COLLECTION_DEBUG_MODE)
							{
								$arrFileDownload['Id']	= $insFileDownload->Execute($arrFileDownload);
							}
							else
							{
								$arrFileDownload['Id']	= TRUE;
							}
							
							if ($arrFileDownload['Id'] !== FALSE)
							{
								// Add this file to the Import queue, and any files that may be archived within it
								$arrDownloadedFiles[]	= $mixDownloadFile;
								while (current($arrDownloadedFiles))
								{
									$arrFile	= &$arrDownloadedFiles[key($arrDownloadedFiles)];
									next($arrDownloadedFiles);
									
									// If this file is an archive, unpack it
									if ($arrFile['FileType']['ArchiveType'])
									{
										CliEcho("\n\t\t\t\t\t * Unpacking Archive... ", FALSE);
										$strPassword	= $arrFile['FileType']['ArchivePassword'];
										$strUnzipPath	= $strDownloadPath.'_files/';
										$arrResult		= UnpackArchive($strDownloadPath, $strUnzipPath, FALSE, $strPassword, $arrFile['ArchiveType']);
										if (is_string($arrResult))
										{
											// Error
											CliEcho("\t\t\t[ FAILED ]");
											CliEcho("\t\t\t\t\t -- $arrResult");
											continue;
										}
										elseif ($arrResult['Processed'])
										{
											foreach ($arrResult['Files'] as $strArchivedFile)
											{
												$arrArchivedFile					= Array();
												$arrArchivedFile['LocalPath']		= $strArchivedFile;
												$arrArchivedFile['RemotePath']		= $arrFile['RemotePath'];
												$arrArchivedFile['ArchiveParent']	= &$arrFile;
												$arrArchivedFile['ExtractionDir']	= $strUnzipPath;
												$arrArchivedFile['FileType']		= $modModule->GetFileType($arrArchivedFile);
												
												$arrDownloadedFiles[]	= $arrArchivedFile;
											}
											CliEcho(count($arrResult['Files'])." file(s) extracted.\t", FALSE);
										}
										else
										{
											CliEcho("\t\t\t[  SKIP  ]");
										}
									}
								}
								CliEcho("[   OK   ]");
							}
							else
							{
								// MySQL Error
								CliEcho("[ FAILED ]");
								CliEcho("\t\t\t\t\t\t -- ".$insFileDownload->Error());
							}
						}
					}
					
					// Import Files
					CliEcho("\t\t\t * Importing downloaded files...\n");
					foreach ($arrDownloadedFiles as $arrDownloadedFile)
					{
						$strRelativePath	= substr($arrDownloadedFile['LocalPath'], (strlen($strDownloadDirectory)));
						CliEcho("\t\t\t\t + $strRelativePath\t\t\t", FALSE);
						
						// If this is not a Download-only file, them Import
						if (!$arrDownloadedFile['FileType']['DownloadOnly'])
						{
							$mixImportResult	= $this->ImportModuleFile($arrDownloadedFile['LocalPath'], $modModule);
							if (is_int($mixImportResult) || $mixImportResult === TRUE)
							{
								CliEcho("[   OK   ]");
							}
							else
							{
								CliEcho("[ FAILED ]");
								CliEcho("\t\t\t\t\t\t -- $mixImportResult");
							}
						}
						else
						{
							CliEcho("[  SKIP  ]");
						}
					}
				}
				elseif (is_string($mixResult))
				{
					// Connection Failed
					CliEcho("[ FAILED ]");
					CliEcho("\t\t\t\t -- $mixResult");
				}
				else
				{
					// Connection Failed -- no reason given
					CliEcho("[ FAILED ]");
				}
			}
		}
		
		// TAR-BZ2 all downloaded files
		$strDownloadDir		= FILES_BASE_PATH."download/";
		$strTARDir			= $strDownloadDir."archived/";
		$strExclusionFile	= $strDownloadDir."tar.exclude";
		$strDownloadDir		= $strDownloadDir."current/";
		$strTARFile			= $strTARDir.date("Ymdhis").".tar";
		$strTARBZ2File		= $strTARDir.date("Ymdhis").".tar.bz2";
		@mkdir($strTARDir, 0777, TRUE);
		
		// Create Archive Exclusion File
		@unlink($strExclusionFile);
		$arrExclude	= Array();
		$arrExclude[]	= $strExclusionFile;
		$arrExclude[]	= $strTARDir;
		$arrExclude[]	= $strTARFile;
		file_put_contents($strExclusionFile, implode("\n", $arrExclude));
		
		$strTARCommand		= "tar -X $strExclusionFile -cf $strTARFile $strDownloadDir >/dev/null 2>&1";
		$strBZ2Command		= "bzip2 $strTARFile";
		
		CliEcho("\n * Archiving Downloaded Files to '".basename($strTARFile)."'...\t\t", FALSE);
		$intTARReturn	= NULL;
		$intBZ2Return	= NULL;
		$arrOutput		= Array();
		exec($strTARCommand, $arrOutput, $intTARReturn);
		if (!$intTARReturn && file_exists($strTARFile))
		{
			CliEcho("[   OK   ]");
			
			// TAR succeeded, so BZ2 it
			CliEcho(" * Compressing Archive to '".basename($strTARBZ2File)."'...\t\t\t", FALSE);
			exec($strBZ2Command, $arrOutput, $intBZ2Return);
			if (!$intBZ2Return && file_exists($strTARBZ2File))
			{
				// BZ2 succeeded
				CliEcho("[   OK   ]");
			}
			else
			{
				CliEcho("[ FAILED ]");
				CliEcho("\t -- Code '$intBZ2Return' returned");
			}
			
			// If we have successfully Archived (even if not compressed), then remove the raw files
			CliEcho(" * Removing Raw Downloaded Files...\t\t\t\t\t", FALSE);
			$arrDirectories			= glob($strDownloadDir.'*', GLOB_ONLYDIR);
			foreach ($arrDirectories as $strDirectory)
			{
				exec("rm -R $strDirectory");
			}
			CliEcho("[   OK   ]");
		}
		else
		{
			CliEcho("[ FAILED ]");
			CliEcho("\t -- Code '$intTARReturn' returned");
		}
		CliEcho();
	}
	
	//------------------------------------------------------------------------//
	// ImportModuleFile
	//------------------------------------------------------------------------//
	/**
	 * ImportModuleFile()
	 *
	 * Imports a file into Flex using information from a passed CarrierModule
	 *
	 * Imports a file into Flex using information from a passed CarrierModule
	 * 
	 * 
	 *
	 * @return	mixed								integer: Insert Id; string: Error message
	 *
	 * @method
	 */
	function ImportModuleFile($arrDownloadFile, &$modCarrierModule)
	{
		return ApplicationCollection::ImportFile($arrDownloadFile['LocalPath'], $arrDownloadFile['FileImportType'], $modCarrierModule->GetCarrier(), $arrDownloadFile['Uniqueness']);
	}
	
	//------------------------------------------------------------------------//
	// ImportFile
	//------------------------------------------------------------------------//
	/**
	 * ImportFile()
	 *
	 * Imports a file into Flex
	 *
	 * Imports a file into Flex
	 * 
	 * @param	string	$strFilePath					Full Path to the file to be imported
	 * @param	integer	$intFileType					The FileImport type for the file
	 * @param	integer	$intCarrier						The Carrier from where this file originated
	 * @param	string	$strUniqueness		[optional]	SQL WHERE Clause on the FileImport table, where a positive match means that the file already exists in Flex; default: FileName or SHA1 must be unique
	 *
	 * @return	mixed									integer: Insert Id; string: Error message
	 *
	 * @method
	 */
	static function ImportFile($strFilePath, $intFileType, $intCarrier, $strUniqueness = "FileName = <FileName> AND SHA1 = <SHA1>")
	{
		// Set initial File Status
		$arrFileImport	= Array();
		$arrFileImport['Status']		= FILE_IMPORTED;
		
		// Init Statements
 		$insFileImport	= new StatementInsert("FileImport");
		
		// Determine File Type
		if (GetConstantName($intFileType, 'FileImport') === FALSE)
		{
			// Unknown File Type
			$arrFileImport['Status']	= FILE_UNKNOWN_TYPE;
		}
		else
		{
			// Copy to final location
			$strDestination	= FILES_BASE_PATH."import/".GetConstantDescription($intCarrier, 'Carrier').'/'.GetConstantName($intFileType['FileImportType'], 'FileImport').'/';
			$strNewFileName	= basename($strFilePath);
			$strDestination	.= $strFilePath;
			if (!copy($strFilePath, $strDestination))
			{
				// Unable to copy
				$arrFileImport['Status']	= FILE_MOVE_FAILED;
			}
			else
			{
				// Check uniqueness
				$arrWhere				= Array();
				$arrWhere['SHA1']		= sha1_file($strFilePath);
				$arrWhere['FileName']	= basename($strFilePath);
				$selFileUnique	= new StatementSelect("FileImport", "Id", $strUniqueness);
				if ($selFileUnique->Execute($arrWhere))
				{
					// Not Unique
					$arrFileImport['Status']	= FILE_NOT_UNIQUE;
				}
			}
		}
		
		if (!defined('COLLECTION_DEBUG_MODE') || !COLLECTION_DEBUG_MODE)
		{
			// Insert into FileImport
			$arrFileImport['FileName']		= basename($strFilePath);
			$arrFileImport['Location']		= $strFilePath;
			$arrFileImport['Carrier']		= $intCarrier;
			$arrFileImport['ImportedOn']	= date("Y-m-d H:i:s");
			$arrFileImport['FileType']		= $intFileType;
			$arrFileImport['SHA1']			= sha1_file($strFilePath);
			if (($intInsertId = $insFileImport->Execute($arrFileImport)) === FALSE)
			{
				// Unable to Import
				return "Import Failed";
			}
			
			if ($arrFileImport['Status'] === FILE_IMPORTED)
			{
				// Return the Insert Id
				return $intInsertId;
			}
			else
			{
				// Return error message
				return GetConstantDescription($arrFileImport['Status'], 'FileStatus');
			}
		}
		else
		{
			// Debug Mode always returns TRUE
			return TRUE;
		}
	}
}
?>
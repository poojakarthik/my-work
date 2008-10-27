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
		$arrFiles	= array();
		foreach ($this->_arrModules as $intCarrier=>&$arrFileTypes)
		{
			CliEcho("\t * Provider: ".GetConstantDescription($intCarrier, 'Carrier'));
			foreach ($arrFileTypes as $intResourceType=>&$modModule)
			{
				CliEcho("\n\t\t * Resource: ".GetConstantDescription($intResourceType, 'FileResource'));
				
				// Download paths
				$strCarrierName			= preg_replace("/\W/", '_', GetConstantDescription($intCarrier, 'Carrier'));
				$strDownloadDirectory	= FILES_BASE_PATH."download/current/{$strCarrierName}/".GetConstantName($intResourceType, 'FileResource').'/';
				@mkdir($strDownloadDirectory, 0777, TRUE);
				
				// Connect to the Source
				CliEcho("\n\t\t\t * Connecting to Repository...\t\t\t", FALSE);
				$mixResult	= $modModule->Connect();
				if ($mixResult === TRUE)
				{
					CliEcho("[   OK   ]");
					
					// Download all new files
					$intTotal			= 0;
					$arrDownloadedFiles	= Array();
					reset($arrDownloadedFiles);
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
							$arrFiles[]			= $strDownloadPath;
							
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
								$mixDownloadFile['file_download']	= $arrFileDownload['Id'];
								$arrDownloadedFiles[]				= $mixDownloadFile;
								while ($arrFile = current($arrDownloadedFiles))
								{
									$mixIndex	= key($arrDownloadedFiles);
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
												$arrArchivedFile['ArchiveParent']	= &$arrDownloadedFiles[$mixIndex];
												$arrArchivedFile['ExtractionDir']	= $strUnzipPath;
												$arrArchivedFile['FileType']		= $modModule->GetFileType($arrArchivedFile);
												$arrArchivedFile['file_download']	= $arrFileDownload['Id'];
												
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
					CliEcho("\n\t\t\t * Importing downloaded files...\n");
					foreach ($arrDownloadedFiles as $arrDownloadedFile)
					{
						$strRelativePath	= substr($arrDownloadedFile['LocalPath'], (strlen($strDownloadDirectory)));
						CliEcho("\t\t\t\t + $strRelativePath\t\t\t", FALSE);
						
						// If this is not a Download-only file, them Import
						if (!$arrDownloadedFile['FileType']['DownloadOnly'])
						{
							$mixImportResult	= $this->ImportModuleFile($arrDownloadedFile, $modModule);
							if (is_int($mixImportResult) || $mixImportResult === TRUE)
							{
								CliEcho("[   OK   ]");
							}
							else
							{
								CliEcho("[ FAILED ]");
								CliEcho("\t\t\t\t\t -- $mixImportResult");
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
					CliEcho("\t\t\t\t -- {$mixResult}");
				}
				else
				{
					// Connection Failed -- no reason given
					CliEcho("[ FAILED ]");
				}
			}
		}
		
		// TAR-BZ2 all downloaded files
		$strDownloadDir			= FILES_BASE_PATH."download/";
		$strTARDir				= $strDownloadDir."archived/";
		$strDownloadFilesDir	= $strDownloadDir."current/";
		$strTARBZ2File			= $strTARDir.date("Ymdhis").".tar.bz2";
		
		$intVersion	= 0;
		while (file_exists($strTARBZ2File))
		{
			// Come up with a different name
			$intVersion++;
			$strTARBZ2File	= $strTARDir.date("Ymdhis").".{$intVersion}.tar.bz2";
		}
		
		@mkdir($strTARDir, 0777, TRUE);
		
		CliEcho("\n * Archiving and compressing Downloaded Files to '{$strTARBZ2File}'...\t\t\t", FALSE);
		$resTARchive	= new Archive_Tar($strTARBZ2File, 'bz2');
		if ($resTARchive->create($arrFiles) && @filesize($strTARBZ2File))
		{
			CliEcho("[   OK   ]");
			CliEcho(" * Removing Raw Files...\t\t\t\t\t", FALSE);
			
			// The archive appears to have been created properly, so delete the raw copies
			$arrDownloadDirs	= glob($strDownloadFilesDir.'*', GLOB_ONLYDIR);
			foreach ($arrDownloadDirs as $strDownloadDirPath)
			{
				exec("rm -R \"$strDownloadDirPath\"");
			}
			
			CLiEcho("[   OK   ]");
		}
		else
		{
			CLiEcho("[ FAILED ]");
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
		return ApplicationCollection::ImportFile($arrDownloadFile['LocalPath'], $arrDownloadFile['FileType']['FileImportType'], $modCarrierModule->GetCarrier(), $arrDownloadFile['FileType']['Uniqueness'], $arrDownloadFile['file_download']);
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
	 * @param	integer	$intFileDownload	[optional]	Entry in the FileDownload table for this file or the archive from which it was extracted
	 *
	 * @return	mixed									integer: Insert Id; string: Error message
	 *
	 * @method
	 */
	static function ImportFile($strFilePath, $intFileType, $intCarrier, $strUniqueness = "FileName = <FileName> AND SHA1 = <SHA1>", $intFileDownload = NULL)
	{
		// Set initial File Status
		$arrFileImport	= Array();
		$arrFileImport['Status']		= FILE_COLLECTED;
		
		// Init Statements
 		$insFileImport	= new StatementInsert("FileImport");
		
		// Determine File Type
		if (GetConstantName($intFileType, 'resource_type') === FALSE)
		{
			// Unknown File Type
			$arrFileImport['Status']	= FILE_UNKNOWN_TYPE;
		}
		else
		{
			// Copy to final location
			$strDestination	= FILES_BASE_PATH."import/".GetConstantDescription($intCarrier, 'Carrier').'/'.GetConstantName($intFileType, 'FileImport').'/';
			@mkdir($strDestination, 0777, TRUE);
			$strNewFileName	= basename($strFilePath);
			$strNewFileName	.= date("_Ymdhis");
			$strDestination	.= $strNewFileName;
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
				$selFileUnique	= new StatementSelect("FileImport", "*", $strUniqueness);
				if ($selFileUnique->Execute($arrWhere))
				{
					// Not Unique
					$arrFileImport['Status']	= FILE_NOT_UNIQUE;
				}
			}
			
			// Compress the Imported File using the BZ2 algorithm
			if (file_put_contents("compress.bzip2://{$strDestination}.bz2", file_get_contents($strDestination)))
			{
				// Success, remove the uncompressed file
				unlink($strDestination);
				
				$strDestination								.= '.bz2';
				$arrFileImport['compression_algorithm_id']	= COMPRESSION_ALGORITHM_BZIP2;
			}
			else
			{
				// Failure, keep the old file, and continue as if nothing went wrong
				$arrFileImport['compression_algorithm_id']	= COMPRESSION_ALGORITHM_NONE;
			}
		}
		
		if (!defined('COLLECTION_DEBUG_MODE') || !COLLECTION_DEBUG_MODE)
		{
			// Insert into FileImport
			$arrFileImport['FileName']		= basename($strFilePath);
			$arrFileImport['Location']		= ($strDestination) ? $strDestination : $strFilePath;
			$arrFileImport['Carrier']		= $intCarrier;
			$arrFileImport['ImportedOn']	= date("Y-m-d H:i:s");
			$arrFileImport['FileType']		= $intFileType;
			$arrFileImport['SHA1']			= sha1_file($strFilePath);
			$arrFileImport['file_download']	= $intFileDownload;
			if (($intInsertId = $insFileImport->Execute($arrFileImport)) === FALSE)
			{
				// Unable to Import
				return "Import Failed";
			}
			
			if ($arrFileImport['Status'] === FILE_COLLECTED)
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
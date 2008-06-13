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
		
		CliEcho("[ COLLECTION ]");
		
		// For each module
		foreach ($this->_arrModules as $intCarrier=>&$arrFileTypes)
		{
			CliEcho("\t * Provider: ".GetConstantDescription($intCarrier, 'Carrier'));
			foreach ($arrFileTypes as $intResourceType=>&$modModule)
			{
				CliEcho("\t\t * Resource: ".GetConstantDescription($intResourceType, 'FileResourceType'));
				
				// Download paths
				$strDownloadPath	= FILES_BASE_PATH."download/".GetConstantDescription($intCarrier, 'Carrier').'/'.GetConstantName($intResourceType, 'FileResourceType').'/';
				
				// Connect to the Source
				CliEcho("\t\t\t * Connecting to Repository...\t\t\t", FALSE);
				$mixResult	= $modModule->Connect();
				if ($mixResult === TRUE)
				{
					CliEcho("[   OK   ]");
					
					// Download all new files
					$intTotal	= 0;
					CliEcho("\t\t\t * Downloading new files...\n");
					while ($strDownloadPath	= $modModule->Download())
					{
						$strFileName	= basename($strDownloadPath);
						$intSize		= filesize($strDownloadPath) / 1024;
						
						CliEcho("\t\t\t\t + $strFileName ({$intSize}KB)");
						
						// Unpack this file
						CliEcho("\t\t\t\t * Unpacking Archive...", FALSE);
						$strPassword	= $modModule->GetConfigField('ArchivePassword');
						$strUnzipPath	= $strDownloadPath.basename($strDownloadPath).'_temp';
						$arrResult		= UnpackArchive($strDownloadPath, $strUnzipPath, TRUE, $strPassword);
						if (is_string($arrResult))
						{
							// Error
							CliEcho("[ FAILED ]");
							CliEcho("\t\t\t\t\t -- $arrResult");
							continue;
						}
						elseif ($arrResult['Processed'])
						{
							CliEcho("[   OK   ]");
						}
						else
						{
							CliEcho("[  SKIP  ]");
						}
						
						CliEcho("\t\t\t\t\t > Importing ".basename($strDownloadPath)."...", FALSE);
						
						// Insert into FileDownload table
						$arrFileDownload	= Array();
						$arrFileDownload['FileName']	= basename($strDownloadPath);
						$arrFileDownload['Location']	= $strDownloadPath;
						$arrFileDownload['Carrier']		= $intCarrier;
						$arrFileDownload['CollectedOn']	= date("Y-m-d H:i:s");
						$arrFileDownload['Status']		= FILE_COLLECTED;
						if (($arrFileDownload['Id'] = $insFileDownload->Execute($arrFileDownload)) !== FALSE)
						{
							// Process each file
							foreach ($arrResult['Files'] as $strFilePath)
							{
								// Import into Flex
								$mixImportResult	= $this->ImportFile($modModule, $strFilePath);
								if (is_int($mixImportResult))
								{
									CliEcho("[   OK   ]");
								}
								else
								{
									CliEcho("[ FAILED ]");
									CliEcho("\t\t\t\t\t\t -- $mixImportResult");
								}
							}
						}
						else
						{
							// MySQL Error
							CliEcho("[ FAILED ]");
							CliEcho("\t\t\t\t\t\t -- ".$insFileDownload->Error());
						}
						
						// Cleanup Archive directory
						@rmdir($strUnzipPath);
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
		$strDownloadDir	= FILES_BASE_PATH."download/";
		$strTARDir		= $strDownloadDir."archived/";
		$strTARFile		= $strTARDir.date("Ymdhis")."tar";
		$strTARBZ2File	= $strTARDir.date("Ymdhis")."tar.bz2";
		$strTARCommand	= "tar -cvf $strTARFile $strDownloadDir";
		$strBZ2Command	= "bzip2 $strTARFile";
		
		CliEcho("\n * Archiving Downloaded Files to '".basename($strTARFile)."'...\t\t\t", FALSE);
		$intTARReturn	= NULL;
		$intBZ2Return	= NULL;
		exec($strTARCommand, NULL, $intTARReturn);
		if (!$intTARReturn && file_exists($strTARFile))
		{
			CliEcho("[   OK   ]");
			
			// TAR succeeded, so BZ2 it
			CliEcho("\n * Compressing Archive to '".basename($strTARBZ2File)."'...\t\t\t", FALSE);
			exec($strBZ2Command, NULL, $intBZ2Return);
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
			CliEcho("\n * Removing Raw Downloaded Files...\t\t\t\t", FALSE);
			$arrIgnoredDirectories	= Array("archived");
			$arrDirectories			= glob($strDownloadDir, GLOB_ONLYDIR);
			foreach ($arrDirectories as $strDirectory)
			{
				if (!in_array($strDirectory, $arrIgnoredDirectories))
				{
					exec("rm -R $strDirectory");
				}
			}
			CliEcho("[   OK   ]");
		}
		else
		{
			CliEcho("[ FAILED ]");
			CliEcho("\t -- Code '$intTARReturn' returned");
		}
		CliEcho('');
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
	 * 
	 *
	 * @return	mixed								integer: Insert Id; string: Error message
	 *
	 * @method
	 */
	function ImportFile(&$modCarrierModule, $strFilePath)
	{
		// Set initial File Status
		$arrFileImport	= Array();
		$arrFileImport['Status']		= FILE_IMPORTED;
		
		// Determine File Type
		if (($arrFileType = $modCarrierModule->GetFileType($strFilePath)) === FALSE)
		{
			// Unknown File Type
			$arrFileImport['Status']	= FILE_UNKNOWN_TYPE;
		}
		else
		{
			// Copy to final location
			$intCarrier		= $modCarrierModule->intCarrier;
			$strDestination	= FILES_BASE_PATH."import/".GetConstantDescription($intCarrier, 'Carrier').'/'.GetConstantName($arrFileType['FileImportType'], 'FileImport').'/';
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
				$selFileUnique	= new StatementSelect("FileImport", "Id", $arrFileType['Uniqueness']);
				if ($selFileUnique->Execute($arrWhere))
				{
					// Not Unique
					$arrFileImport['Status']	= FILE_NOT_UNIQUE;
				}
			}
		}		
		
		// Insert into FileImport
		$arrFileImport['FileName']		= $arrWhere['FileName'];
		$arrFileImport['Location']		= $strFilePath;
		$arrFileImport['Carrier']		= $modCarrierModule->intCarrier;
		$arrFileImport['ImportedOn']	= date("Y-m-d H:i:s");
		$arrFileImport['FileType']		= $arrFileType['FileImportType'];
		$arrFileImport['SHA1']			= $arrWhere['SHA1'];
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
}
?>
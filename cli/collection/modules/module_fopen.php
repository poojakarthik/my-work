<?php
/**
 * CollectionModuleFOpen
 *
 * fopen() File Wrapper Collection Base Module
 *
 * @class	CollectionModuleFOpen
 */
 abstract class CollectionModuleFOpen extends CollectionModuleBase
 {
	protected	$_resConnection;
	protected	$_strWrapper		= '';
	
	//public $intBaseCarrier			= CARRIER_UNITEL;
	//public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_LOCAL;
	
	const	DIRECTORY_NAME_REGEX_PREFIX	= 'regex:';
	const	SKIP_IS_DIR_AFTER_REGEX		= true;
	
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleFOpen
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 	}
 	
	/**
	 * Connect()
	 *
	 * Connects to the Resource
	 *
	 * @return	mixed									TRUE: Pass; string: Error
	 *
	 * @method
	 */
	//abstract function Connect();
	
	/**
	 * Disconnect()
	 *
	 * Disconnects from the Resource
	 *
	 * @method
	 */
	function Disconnect()
	{
		unset($this->_resConnection);
		return isset($this->_resConnection);
	}
	
	/**
	 * Download()
	 *
	 * Downloads the next file from the FTP server to the specified directory.
	 * If there is no next file, then FALSE is returned
	 *
	 * @return		mixed		String of Filename or FALSE if there is no next file
	 *
	 * @method
	 */
 	function Download($strDestination)
 	{
		// Get the Current path element
		if (!($arrCurrentFile = current($this->_arrDownloadPaths)))
		{
			// No files left, return FALSE
			return false;
		}
		else
		{
			// Advance the arrDownloadPaths internal pointer
			next($this->_arrDownloadPaths);
			
			// Calculate Local Download Path
			$arrCurrentFile['LocalPath']	= $strDestination.ltrim(basename($arrCurrentFile['RemotePath']), '/');
			
			// Attempt to download this file
			if ($strFileContents = @file_get_contents($this->_strWrapper.$arrCurrentFile['RemotePath']))
			{
				if (@file_put_contents($arrCurrentFile['LocalPath'], $strFileContents))
				{
					return $arrCurrentFile;
				}
				else
				{
					return "Error saving to local path '{$arrCurrentFile['LocalPath']}': ".print_r(error_get_last(), true);
				}
			}
			else
			{
				return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}': ".print_r(error_get_last(), true);
			}
		}
 	}
 	
	/**
	 * _getDownloadPaths()
	 *
	 * Gets a full list of all files to download
	 *
	 * @return		array							Array of files to download
	 *
	 * @method
	 */
	protected function _getDownloadPaths()
	{
		//CliEcho("\nGetting Download Paths...");
		
		// Get Path Definitions
		$arrDefinitions		= $this->GetConfigField('FileDefine');
		
		$arrDownloadPaths	= array();
		try
		{
			$arrDownloadPaths	= $this->_getDownloadPathsForDirectories($arrDefinitions);
		}
		catch (Exception $eException)
		{
			CliEcho("Error retrieving download paths: ".$eException->getMessage());
		}
		
		return $arrDownloadPaths;
	}
	
	protected function _getDownloadPathsForDirectories(&$arrDirectories, $strCurrentPath='')
	{
		$arrDownloadPaths	= array();
		
		while (list($strDirectory, $arrDefinition) = each($arrDirectories))
		{
			CliEcho("Currently ".(count($arrDirectories))." subdirectories for path '{$strCurrentPath}'");
			
			// Is this a Regex/Variable Directory?
			if (stripos($strDirectory, self::DIRECTORY_NAME_REGEX_PREFIX) === 0)
			{
				CliEcho("'{$strDirectory}' is a Regex/Variable Directory");
				
				// Regex -- get list of subdirectories that match this criteria
				$strRegex	= substr($strDirectory, strlen(self::DIRECTORY_NAME_REGEX_PREFIX));
				CliEcho("Checking for Subdirectory matches against '{$strRegex}'");
				
				$sWrappedPath			= $this->_strWrapper.$strCurrentPath.'/';
				$arrDirectoryContents	= @scandir($sWrappedPath);
				
				if (is_array($arrDirectoryContents))
				{
					CliEcho("Found ".count($arrDirectoryContents)." remote objects...");
					foreach ($arrDirectoryContents as $intIndex=>$strSubItem)
					{
						CliEcho("Subitem {$intIndex}: {$strSubItem}");
						
						$strSubItemFullPath	= $strCurrentPath.'/'.$strSubItem;
						if (preg_match($strRegex, $strSubItem) && is_dir($this->_strWrapper.$strSubItemFullPath))
						{
							// We have a matching subdirectory -- add it to our list of directories to download from
							if (!array_key_exists($strSubItemFullPath, $arrDirectories))
							{
								CliEcho("Physical Subdirectory '{$strSubItem}' matches regex of '{$strRegex}'");
								$arrDirectories[$strSubItem]	= $arrDefinition;
							}
						}
					}
				}
				else
				{
					// Error
					throw new Exception("Error retrieving contents of '{$sWrappedPath}': ".implode('; ', error_get_last()));
				}
			}
			else
			{
				CliEcho("'{$strDirectory}' is a Normal Directory");
				
				// Normal Directory
				$strDirectoryFullPath	= $strCurrentPath.'/'.$strDirectory;
				
				// Browse Subdirectories
				if (array_key_exists('arrSubdirectories', $arrDirectories[$strDirectory]) && is_array($arrDirectories[$strDirectory]['arrSubdirectories']) && count($arrDirectories[$strDirectory]['arrSubdirectories']))
				{
					CliEcho("Traversing subdirectories for '{$strDirectory}'");
					
					$arrSubdirectoryDownloadPaths	= $this->_getDownloadPathsForDirectories($arrDirectories[$strDirectory]['arrSubdirectories'], $strDirectoryFullPath);
					foreach ($arrSubdirectoryDownloadPaths as $arrSubdirectoryDownloadPath)
					{
						$arrDownloadPaths[]	= $arrSubdirectoryDownloadPath;
					}
				}
				else
				{
					CliEcho("'{$strDirectory}' has no Subdirectory definitions");
				}
				
				// Get any Files in this Directory
				if (array_key_exists('arrFileTypes', $arrDirectories[$strDirectory]) && is_array($arrDirectories[$strDirectory]['arrFileTypes']) && count($arrDirectories[$strDirectory]['arrFileTypes']))
				{
					$sWrappedPath			= $this->_strWrapper.$strDirectoryFullPath.'/';
					$arrDirectoryContents	= @scandir($sWrappedPath);
					
					$intFileCount	= count($arrDirectoryContents);
					
					CliEcho("{$intFileCount} files (including '.' and '..')");
					
					CliEcho("\033[s");
					
					if (is_array($arrDirectoryContents))
					{
						$intProgress	= 0;
						$intMatches		= 0;
						foreach ($arrDirectoryContents as $strSubItem)
						{
							$intProgress++;
							CliEcho("\033[2K\033[uProcessing File {$intProgress}/{$intFileCount}; Matches: {$intMatches}", false);
							
							$strSubItemFullPath	= $strDirectoryFullPath.'/'.$strSubItem;
							
							foreach ($arrDirectories[$strDirectory]['arrFileTypes'] as $intResourceTypeId=>$arrFileType)
							{
								if (preg_match($arrFileType['Regex'], $strSubItem) && !is_dir($this->_strWrapper.$strSubItemFullPath))
								{
									// Does this File Name exist in the database?
									if ($arrFileType['DownloadUnique'] && !$this->isDownloadUnique($strSubItem))
									{
										// Yes, so we should skip this file
										break;
									}
									
									// Regex matches -- is this a directory?
									if (self::SKIP_IS_DIR_AFTER_REGEX || !is_dir($this->_strWrapper.$strSubItemFullPath))
									{
										// It's a File --matched a File Type definition
										$arrFileType['FileImportType']	= $intResourceTypeId;
										$arrDownloadPaths[]	= array('RemotePath' => trim($strSubItemFullPath), 'FileType' => $arrFileType);
										$intMatches++;
										break;
									}
								}
							}
						}
						CliEcho();
					}
					else
					{
						// Error
						throw new Exception("Error retrieving contents of '{$strCurrentPath}': ".error_get_last());
					}
				}
				else
				{
					CliEcho("'{$strDirectory}' has no File Type definitions");
				}
			}
		}
		
		unset($arrDefinition);
		
		return $arrDownloadPaths;
	}
	
	/**
	 * GetFileType()
	 *
	 * Determines the FileImport type for a given file
	 *
	 * @param	array	$arrDownloadFile				FileDownload properties
	 *
	 * @return	mixed									array: FileImport Type; NULL: Unrecognised type
	 *
	 * @method
	 */
	public function GetFileType($arrDownloadFile)
	{
		// Has this file been extracted from a downloaded archive?
		if ($arrDownloadFile['ArchiveParent'])
		{
			// FIXME: FOpen Collection Modules don't support Archives yet
			return NULL;
		}
		else
		{
			// The File Type for this file is already defined
			return $arrDownloadFile['FileType'];
		}
	}
}
?>
<?php
/**
 * CollectionModuleFOpenFTP
 *
 * New FTP Collection Module
 *
 * @class	CollectionModuleFTPNew
 */
 class CollectionModuleFTPNew extends CollectionModuleBase
 {
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_RESOURCE_FTP;
	
	protected	$_resConnection;
	protected	$_strWrapper;
	
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_FTP;
	
	const	DIRECTORY_NAME_REGEX_PREFIX	= 'regex:';
	const	SKIP_IS_DIR_AFTER_REGEX		= true;
	
	const	ENABLE_DEBUG_OUTPUT	= false;
	
	protected function _cliEcho($sMessage='', $bNewLine=true)
	{
		if (self::ENABLE_DEBUG_OUTPUT)
		{
			$this->_cliEcho($sMessage, $bNewLine);
		}
	}
	
	public static function getConfigDefinition()
	{
		// Values defined in here are DEFAULT values
		return	array
				(
					'Host'	=>		array
									(
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'FTP Server to connect to'
									),
					'Username'		=>	array
									(
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'FTP Username'
									),
					'Password'		=>	array
									(
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'FTP Password'
									),
					'FileDefine'	=>	array
									(
										'Value'			=> array(),
										'Type'			=> DATA_TYPE_ARRAY,
										'Description'	=> 'Definitions for where to download files from'
									)
				);
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
	function Connect()
	{
		$strHost				= $this->_oConfig->Host;
		$strUsername			= $this->_oConfig->Username;
		$strPassword			= $this->_oConfig->Password;
		
		// Init wrapper
		if ($this->_resConnection = ftp_connect($strHost))
		{
			if (ftp_login($this->_resConnection, $strUsername, $strPassword))
			{
				// Get list of files to download
				$this->_arrDownloadPaths	= $this->_getDownloadPaths();
				reset($this->_arrDownloadPaths);
				
				return true;
			}
			else
			{
			return "Unable to log in to host {$strHost}";
			}
		}
		else
		{
			return "Unable to connect to host {$strHost}";
		}
	}
	
 	function Disconnect()
 	{
		if ($this->_resConnection)
		{
			ftp_close($this->_resConnection);
		}
		return true;
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
			if (!@ftp_get($this->_resConnection, $arrCurrentFile['LocalPath'], $arrCurrentFile['RemotePath'], FTP_BINARY))
			{
				return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}' to '{$arrCurrentFile['LocalPath']}': ".$php_errormsg;
			}
			
			return $arrCurrentFile;
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
		$this->_cliEcho("\nGetting Download Paths...");
		
		// Get Path Definitions
		$arrDefinitions		= $this->_oConfig->FileDefine;
		
		$arrDownloadPaths	= array();
		try
		{
			$arrDownloadPaths	= $this->_getDownloadPathsForDirectories($arrDefinitions);
		}
		catch (Exception $eException)
		{
			$this->_cliEcho("Error retrieving download paths: ".$eException->getMessage());
		}
		
		return $arrDownloadPaths;
	}
	
	protected function _getDownloadPathsForDirectories(&$arrDirectories, $strCurrentPath='')
	{
		$arrDownloadPaths	= array();
		
		while (list($strDirectory, $arrDefinition) = each($arrDirectories))
		{
			$this->_cliEcho("Currently ".(count($arrDirectories))." subdirectories for path '{$strCurrentPath}'");
			
			// Is this a Regex/Variable Directory?
			if (stripos($strDirectory, self::DIRECTORY_NAME_REGEX_PREFIX) === 0)
			{
				$this->_cliEcho("'{$strDirectory}' is a Regex/Variable Directory");
				
				// Regex -- get list of subdirectories that match this criteria
				$strRegex	= substr($strDirectory, strlen(self::DIRECTORY_NAME_REGEX_PREFIX));
				$this->_cliEcho("Checking for Subdirectory matches against '{$strRegex}'");
				
				$arrDirectoryContents	= ftp_nlist($this->_resConnection, $strCurrentPath);
				
				if (is_array($arrDirectoryContents))
				{
					$this->_cliEcho("Found ".count($arrDirectoryContents)." remote subdirectories...");
					foreach ($arrDirectoryContents as $intIndex=>$strSubItem)
					{
						$this->_cliEcho("Subitem {$intIndex}: {$strSubItem}");
						
						$strSubItemFullPath	= $strCurrentPath.'/'.$strSubItem;
						if (preg_match($strRegex, $strSubItem) && $this->_isDir($strSubItemFullPath))
						{
							// We have a matching subdirectory -- add it to our list of directories to download from
							if (!array_key_exists($strSubItemFullPath, $arrDirectories))
							{
								$this->_cliEcho("Physical Subdirectory '{$strSubItem}' matches regex of '{$strRegex}'");
								$arrDirectories[$strSubItem]	= $arrDefinition;
							}
						}
					}
				}
				else
				{
					// Error
					throw new Exception("Error retrieving contents of '{$sWrappedPath}': ".$php_errormsg);
				}
			}
			else
			{
				$this->_cliEcho("'{$strDirectory}' is a Normal Directory");
				
				// Normal Directory
				$strDirectoryFullPath	= $strCurrentPath.'/'.$strDirectory;
				
				// Browse Subdirectories
				if (array_key_exists('arrSubdirectories', $arrDirectories[$strDirectory]) && is_array($arrDirectories[$strDirectory]['arrSubdirectories']) && count($arrDirectories[$strDirectory]['arrSubdirectories']))
				{
					$this->_cliEcho("Traversing subdirectories for '{$strDirectory}'");
					
					$arrSubdirectoryDownloadPaths	= $this->_getDownloadPathsForDirectories($arrDirectories[$strDirectory]['arrSubdirectories'], $strDirectoryFullPath);
					foreach ($arrSubdirectoryDownloadPaths as $arrSubdirectoryDownloadPath)
					{
						$arrDownloadPaths[]	= $arrSubdirectoryDownloadPath;
					}
				}
				else
				{
					$this->_cliEcho("'{$strDirectory}' has no Subdirectory definitions");
				}
				
				// Get any Files in this Directory
				if (array_key_exists('arrFileTypes', $arrDirectories[$strDirectory]) && is_array($arrDirectories[$strDirectory]['arrFileTypes']) && count($arrDirectories[$strDirectory]['arrFileTypes']))
				{
					$arrDirectoryContents	= ftp_nlist($this->_resConnection, $strDirectoryFullPath);
					
					$intFileCount	= count($arrDirectoryContents);
					
					$this->_cliEcho("{$intFileCount} files (including '.' and '..')");
					
					$this->_cliEcho("\033[s");
					
					if (is_array($arrDirectoryContents))
					{
						$intProgress	= 0;
						$intMatches		= 0;
						foreach ($arrDirectoryContents as $strSubItem)
						{
							$intProgress++;
							
							$strSubItem	= basename($strSubItem);
							$this->_cliEcho("\033[2K\033[uProcessing File '{$strSubItem}' {$intProgress}/{$intFileCount}; Matches: {$intMatches}", false);
							
							$strSubItemFullPath	= $strDirectoryFullPath.'/'.$strSubItem;
							
							foreach ($arrDirectories[$strDirectory]['arrFileTypes'] as $intResourceTypeId=>$arrFileType)
							{
								if (preg_match($arrFileType['Regex'], $strSubItem) && !$this->_isDir($strSubItemFullPath))
								{
									// Does this File Name exist in the database?
									if ($arrFileType['DownloadUnique'] && !$this->isDownloadUnique($strSubItem))
									{
										// Yes, so we should skip this file
										break;
									}
									
									// Regex matches -- is this a directory?
									if (self::SKIP_IS_DIR_AFTER_REGEX || !$this->_isDir($strSubItemFullPath))
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
						$this->_cliEcho();
					}
					else
					{
						// Error
						throw new Exception("Error retrieving contents of '{$strCurrentPath}': ".$php_errormsg());
					}
				}
				else
				{
					$this->_cliEcho("'{$strDirectory}' has no File Type definitions");
				}
			}
		}
		
		unset($arrDefinition);
		
		return $arrDownloadPaths;
	}
	
	protected function _isDir($sPath)
	{
		$sPWD	= ftp_pwd($this->_resConnection);
		
		if (@ftp_chdir($this->_resConnection, $sPath))
		{
			ftp_chdir($this->_resConnection, $sPWD);
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>
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
					return "Error saving to local path '{$arrCurrentFile['LocalPath']}': ".error_get_last();
				}
			}
			else
			{
				return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}': ".error_get_last();
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
		//Debug($this->_strWrapper." in ".__FILE__." @ ".__LINE__);
		
		// Get Path Definitions
		$arrDefinitions		= $this->GetConfigField('FileDefine');
		
		$arrDownloadPaths	= array();
		foreach ($arrDefinitions as $intFileType=>&$arrFileType)
		{
			while (list($mixPathKey, $strPath) = each($arrFileType['Paths']))
			{
				// Get the directory listing for this
				$arrFiles	= scandir($this->_strWrapper.$strPath);
				
				// Filter file names that we don't want
				if (is_array($arrFiles))
				{
					foreach ($arrFiles as $strFilePath)
					{
						// Ignore '.' and '..'
						if (in_array($strFilePath, array('.', '..')))
						{
							
						}
						
						if (is_dir($strFilePath))
						{
							// This is a directory
							if ($arrFileType['Recursive'])
							{
								$arrFileType['Paths'][]	= $strPath.'/'.$strFilePath;
							}
							else
							{
								continue;
							}
						}
						
						// Does this file match our REGEX?
						if (!preg_match($arrFileType['Regex'], trim(basename($strFilePath))))
						{
							// No match
							continue;
						}
						
						// Does this FileType have download uniqueness?
						if ($arrFileType['DownloadUnique'])
						{
							// Does this File Name exist in the database?
							if ($this->_selFileDownloaded->Execute(array('FileName' => basename($strFilePath))))
							{
								// Yes, so we should skip this file
								continue;
							}
						}
						
						// Add the FileImport Type to our element
						$arrFileType['FileImportType']	= $intFileType;
						
						CliEcho("Adding '{$strFilePath}'...");
						
						// As far as we can tell, this file is valid
						$arrDownloadPaths[]	= array('RemotePath' => trim($strFilePath), 'FileType' => $arrFileType);
					}
				}
			}
		}
		return $arrDownloadPaths;
	}
}
?>
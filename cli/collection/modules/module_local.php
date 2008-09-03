<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_local
//----------------------------------------------------------------------------//
/**
 * module_local
 *
 * Local File Collection Module
 *
 * Local File Collection Module
 *
 * @file		module_local.php
 * @language	PHP
 * @package		collection
 * @author		Rich Davis
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleLocal
//----------------------------------------------------------------------------//
/**
 * CollectionModuleLocal
 *
 * Local File Collection Module
 *
 * Local File Collection Module
 *
 *
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleLocal
 */
 class CollectionModuleLocal extends CollectionModuleBase
 {
	private $_resConnection;
	
	//public $intBaseCarrier			= CARRIER_UNITEL;
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_LOCAL;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleLocal
	 *
	 * Constructor for CollectionModuleLocal
	 *
	 * @return		CollectionModuleLocal
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
 		
 		$this->_arrModuleConfig['FileDefine']	['Default']		= Array();
 		$this->_arrModuleConfig['FileDefine']	['Type']		= DATA_TYPE_ARRAY;
 		$this->_arrModuleConfig['FileDefine']	['Description']	= "Definitions for where to download files from";
 	}
 	
 	//------------------------------------------------------------------------//
	// Connect
	//------------------------------------------------------------------------//
	/**
	 * Connect()
	 *
	 * Connects to the Local File Server (unused)
	 *
	 * Connects to the Local File Server (unused)
	 *
	 * @return	mixed									TRUE: Pass; string: Error
	 *
	 * @method
	 */
 	function Connect()
 	{
		// Server is the localhost - no need to connect
		$this->_arrDownloadPaths	= $this->_GetDownloadPaths();
		
		// Prepare list of files to download
		return TRUE;
 	}
 	
  	//------------------------------------------------------------------------//
	// Disconnect
	//------------------------------------------------------------------------//
	/**
	 * Disconnect()
	 *
	 * Disconnects from the Local File Server (unused)
	 *
	 * Disconnects from the Local File Server (unused)
	 *
	 * @method
	 */
 	function Disconnect()
 	{
		// Server is the localhost - no need to disconnect
		return TRUE;
 	}
 	
  	//------------------------------------------------------------------------//
	// Download
	//------------------------------------------------------------------------//
	/**
	 * Download()
	 *
	 * Downloads next file from FTP
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
			return FALSE;
		}
		else
		{
			// Advance the arrDownloadPaths internal pointer
			next($this->_arrDownloadPaths);
			
			// Calculate Local Download Path
			$arrCurrentFile['LocalPath']	= $strDestination.basename($arrCurrentFile['RemotePath']);
			
			// Attempt to download this file
			if (copy($arrCurrentFile['RemotePath'], $arrCurrentFile['LocalPath']))
			{
				return $arrCurrentFile;
			}
			else
			{
				return "Error downloading from the remote path '{$arrCurrentFile['RemotePath']}'";
			}
		}
 	}
 	
  	//------------------------------------------------------------------------//
	// _GetDownloadPaths
	//------------------------------------------------------------------------//
	/**
	 * _GetDownloadPaths()
	 *
	 * Gets a full list of all files to download
	 *
	 * Gets a full list of all files to download
	 * 
	 * @return		array							Array of files to download
	 *
	 * @method
	 */
	protected function _GetDownloadPaths()
	{
		// Get Path Definitions
		$arrDefinitions		= $this->GetConfigField('FileDefine');
		
		$arrDownloadPaths	= Array();
		foreach ($arrDefinitions as $intFileType=>&$arrFileType)
		{
			foreach ($arrFileType['Paths'] as $strPath)
			{
				// Get the directory listing for this
				$arrFiles	= glob(rtrim($strPath, '/').'/*');
				
				// Filter file names that we don't want
				if (is_array($arrFiles))
				{
					foreach ($arrFiles as $strFilePath)
					{
						if (is_dir($strFilePath))
						{
							// This is a directory, ignore
							continue;
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
							if ($this->_selFileDownloaded->Execute(Array('FileName' => basename($strFilePath))))
							{
								// Yes, so we should skip this file
								continue;
							}
						}
						
						// Add the FileImport Type to our element
						$arrFileType['FileImportType']	= $intFileType;
						
						// As far as we can tell, this file is valid
						$arrDownloadPaths[]	= Array('RemotePath' => trim($strFilePath), 'FileType' => $arrFileType);
					}
				}
			}
		}
		return $arrDownloadPaths;
	}
}

?>
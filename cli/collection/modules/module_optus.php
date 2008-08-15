<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_optus
//----------------------------------------------------------------------------//
/**
 * module_optus
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 * @file		module_optus.php
 * @language	PHP
 * @package		collection
 * @author		Rich Davis
 * @version		8.07
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleOptus
//----------------------------------------------------------------------------//
/**
 * CollectionModuleOptus
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 * Optus Collection Module (because they're special (see: retards))
 *
 *
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleOptus
 */
 class CollectionModuleOptus extends CollectionModuleBase
 {
	private $_resConnection;
 	
	//public $intBaseCarrier			= CARRIER_OPTUS;
	public $intBaseFileType			= RESOURCE_TYPE_FILE_REOURCE_OPTUS;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleOptus
	 *
	 * Constructor for CollectionModuleOptus
	 *
	 * @return		CollectionModuleOptus
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['URL']			['Default']		= "https://www.optus.com.au/wholesalenet/catalog/catalog.taf?custid=<Config::CustomerId>&check=<Config::CheckId>&_function=catalog&_filetype=Speedi";
 		$this->_arrModuleConfig['URL']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['URL']			['Description']	= "URL to retrieve XML from";
 		
 		$this->_arrModuleConfig['CustomerId']	['Default']		= '';
 		$this->_arrModuleConfig['CustomerId']	['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['CustomerId']	['Description']	= "Customer Id";
 		
 		$this->_arrModuleConfig['CheckId']		['Default']		= '';
 		$this->_arrModuleConfig['CheckId']		['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['CheckId']		['Description']	= "6-digit Check Id";
 		
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
	 * Connects to HTTP server and gets catalogue file
	 *
	 * Connects to HTTP server and gets catalogue file
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function Connect()
 	{
 		// Init CURL session
 		$this->_ptrSession = curl_init();
 		
 		if ($this->_ptrSession)
 		{
			$this->_arrDownloadPaths	= $this->_GetDownloadPaths();
			
			if (is_string($this->_arrDownloadPaths))
			{
				// Error
				return $this->_arrDownloadPaths;
			}
			else
			{
				// Success
				reset($this->_arrDownloadPaths);
				return TRUE;
			}
 		}
 		else
 		{
 			return "Unable to initiate CURL session";
 		}
 	}
 	
  	//------------------------------------------------------------------------//
	// Disconnect
	//------------------------------------------------------------------------//
	/**
	 * Disconnect()
	 *
	 * Disconnect from FTP server
	 *
	 * Disconnect from FTP server
	 *
	 * @method
	 */
 	function Disconnect()
 	{
		if ($this->_ptrSession)
		{
			curl_close($this->_ptrSession);
		}
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
			$arrCurrentFile['LocalPath']	= $strDestination.$arrCurrentFile['FileName'];
			
			// Attempt to download this file
			curl_setopt($this->_ptrSession, CURLOPT_URL				, trim($arrCurrentFile['RemotePath']));
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
			curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
			$strDownloadedFile = curl_exec($this->_ptrSession);
			
			// Write to download directory
			file_put_contents($arrCurrentFile['LocalPath'], $strDownloadedFile);
			
			return $arrCurrentFile;
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
		// Retrieve file listing
		$intCustId		= $this->GetConfigField('CustomerId');
		$intCheckId		= $this->GetConfigField('CheckId');
		$strURL			= $this->GetConfigField('URL');
		
		curl_setopt($this->_ptrSession, CURLOPT_URL				, $strURL);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
		
		// Get the directory listing for this
		$strCatalogFile	= curl_exec($this->_ptrSession);
		if (!$strCatalogFile)
		{
			// No Response
			return "No Response from the Server";
		}
		elseif (stripos($strCatalogFile, '<html>'))
		{
			// Malformed Catalog
			return "Catalog is malformed";
		}
		
		$arrFiles = Array();
		$arrLines = explode("\r\n", trim($strCatalogFile));
		foreach ($arrLines as $intIndex=>$strLine)
		{
			$arrLine = explode("\t", $strLine);
			
			// Make sure there are no double-ups
			if (!in_array(Array('FileName'=>trim($arrLine[0]), 'URL'=>$arrLine[1]), $arrLines))
			{
				$arrFiles[] = Array('FileName'=>trim($arrLine[0]), 'URL'=>$arrLine[1]);
			}
		}
		
		// Get File Definitions
		$arrDefinitions	= $this->GetConfigField('FileDefine');
		
		$arrDownloadPaths	= Array();
		foreach ($arrDefinitions as $intFileType=>&$arrFileType)
		{
			// Filter file names that we don't want
			if (is_array($arrFiles))
			{
				foreach ($arrFiles as $arrFileDetails)
				{					
					// Does this file match our REGEX?
					if (!preg_match($arrFileType['Regex'], trim($arrFileDetails['FileName'])))
					{
						// No match
						continue;
					}
						
					// Does this FileType have download uniqueness?
					if ($arrFileType['DownloadUnique'])
					{
						// Does this File Name exist in the database?
						if ($this->_selFileDownloaded->Execute(Array('FileName' => trim($arrFileDetails['FileName']))))
						{
							// Yes, so we should skip this file
							continue;
						}
					}
					
					// Add the FileImport Type to our element
					$arrFileType['FileImportType']	= $intFileType;
					
					// As far as we can tell, this file is valid
					$arrDownloadPaths[]	= Array('RemotePath' => trim($arrFileDetails['URL']), 'FileType' => $arrFileType, 'FileName' => trim($arrFileDetails['FileName']));
				}
			}
		}
		return $arrDownloadPaths;
	}
}

?>
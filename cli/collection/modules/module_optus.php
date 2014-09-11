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
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_RESOURCE_OPTUS;
	
	private $_resConnection;
 	
	public $intBaseFileType = RESOURCE_TYPE_FILE_RESOURCE_OPTUS;
	
	public static function getConfigDefinition()
	{
		// Values defined in here are DEFAULT values
		return	array
				(
					'URL'	=>		array
									(
										'Value'			=> "https://www.optus.com.au/wholesalenet/catalog/catalog.taf?custid=<Config::CustomerId>&check=<Config::CheckId>&_function=catalog&_filetype=Speedi",
										'Type'			=> DATA_TYPE_STRING,
										'Description'	=> 'URL to retrieve XML from'
									),
					'CustomerId'	=>	array
									(
										'Type'			=> DATA_TYPE_INTEGER,
										'Description'	=> 'Customer Id'
									),
					'CheckId'		=>	array
									(
										'Type'			=> DATA_TYPE_INTEGER,
										'Description'	=> '6-digit Check Id'
									),
					'FileDefine'	=>	array
									(
										'Value'			=> array(),
										'Type'			=> DATA_TYPE_ARRAY,
										'Description'	=> 'Definitions for where to download files from'
									)
				);
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
			$arrCurrentFile['LocalPath']	= $strDestination.ltrim($arrCurrentFile['FileName'], '/');
			
			// Attempt to download this file
			curl_setopt($this->_ptrSession, CURLOPT_URL				, trim($arrCurrentFile['RemotePath']));
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
			curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_FOLLOWLOCATION	, TRUE);
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
		$intCustId		= $this->_oConfig->CustomerId;
		$intCheckId		= $this->_oConfig->CheckId;
		$strURL			= $this->_oConfig->URL;
		
		//Log::getLog()->log("Connecting to: {$strURL}");
		
		curl_setopt($this->_ptrSession, CURLOPT_URL				, $strURL);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_FOLLOWLOCATION	, TRUE);
		
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
			SendEmail("rdavis@yellowbilling.com.au", "Optus CDR Catalog is Malformed", $strCatalogFile, CUSTOMER_URL_NAME."@yellowbilling.com.au");
			return "Catalog is malformed";
		}
		
		$arrFiles = Array();
		$arrLines = explode("\r\n", trim($strCatalogFile));
		foreach ($arrLines as $intIndex=>$strLine)
		{
			$arrLine	= explode("\t", $strLine);
			$strFileURL	= $arrLine[1];
			
			/*// HACKHACKHACK: Optus are fucking stupid, and are using redirects to link to the actual file, so try to hack the actual address
			$strCatalogDomain	= substr($strURL, 0, stripos($strURL, 'wholesalenet'));
			$strFileURL			= $strCatalogDomain.substr($strFileURL, stripos($strURL, 'wholesalenet'));*/
			
			// Make sure there are no double-ups
			if (!in_array(Array('FileName'=>trim($arrLine[0]), 'URL'=>$strFileURL), $arrLines))
			{
				$arrFiles[] = Array('FileName'=>trim($arrLine[0]), 'URL'=>$strFileURL);
			}
		}
		
		// Get File Definitions
		$arrDefinitions	= $this->_oConfig->FileDefine;
		
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
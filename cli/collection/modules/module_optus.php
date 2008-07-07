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
 class CollectionModuleOptus extends CarrierModueBase
 {
	private $_resConnection;
 	
	//public $intBaseCarrier			= CARRIER_OPTUS;
	public $intBaseFileType			= FILE_RESOURCE_OPTUS;
 	
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
 		
		// Get the Catalogue File
		$intCustId	= $this->GetConfigField('CustomerId');
		$intCheckId	= $this->GetConfigField('CheckId');
		$strURL		= $this->GetConfigField('URL');
		//Debug($strURL);
		curl_setopt($this->_ptrSession, CURLOPT_URL				, $strURL);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
		curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
		curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
		
		$strCatalogFile	= curl_exec($this->_ptrSession);
		if (!$strCatalogFile)
		{
			// No Response
			return "No Response from the Server";
		}
		elseif (stripos($strCatalogFile, '<html>'))
		{
			// Malformed XML
			return "XML is malformed";
		}
		
		// Parse Catalogue File
		$this->_arrFiles = Array();
		$arrLines = explode("\r\n", trim($strCatalogFile));
		foreach ($arrLines as $intIndex=>$strLine)
		{
			$arrLine = explode("\t", $strLine);
			
			// Make sure there are no double-ups
			if (!in_array(Array('FileName'=>trim($arrLine[0]), 'URL'=>$arrLine[1]), $arrLines))
			{
				$this->_arrFiles[] = Array('FileName'=>trim($arrLine[0]), 'URL'=>$arrLine[1]);
			}
		}
		reset($this->_arrFiles);
		
		if ($this->_arrFiles)
		{
			return TRUE;
		}
		else
		{
			return "Unable to retrieve file list";
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
 		if (!$this->_arrFiles)
		{
			// No files to download
			return FALSE;
		}
		
		// Download the next file
		if ($arrCurrent = next($this->_arrFiles))
		{
			$strTestName	= $arrCurrent['FileName'];
			if (stripos($strTestName, '.zip'))
			{
				$strTestName	= substr($arrCurrent['FileName'], 0, -4);
			}
			
			// Do we already have this file?
			if ($this->_selFileImported->Execute(Array('FileName' => $strTestName)))
			{
				// Yes, recursively call until we find a new file (or FALSE)
				return $this->Download($strDestination);
			}
			
			// Download the file
			curl_setopt($this->_ptrSession, CURLOPT_URL				, trim($arrCurrent['URL']));
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYPEER	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_SSL_VERIFYHOST	, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_HEADER			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_RETURNTRANSFER	, TRUE);
			curl_setopt($this->_ptrSession, CURLOPT_POST			, FALSE);
			curl_setopt($this->_ptrSession, CURLOPT_BINARYTRANSFER	, FALSE);
			$strDownloadedFile = curl_exec($this->_ptrSession);
			
			// Write to download directory
			$ptrTempFile	= fopen($strDestination.$arrCurrent['FileName'], 'w');
			fwrite($ptrTempFile, $strDownloadedFile);
			fclose($ptrTempFile);
			
			// Return file path
			return $strDestination.$arrCurrent['FileName'];
		}
		else
		{
			// No more files to download
			return FALSE;
		}
 	}
}

?>

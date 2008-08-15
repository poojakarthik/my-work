<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------/

//----------------------------------------------------------------------------//
// module_aapt
//----------------------------------------------------------------------------//
/**
 * module_aapt
 *
 * AAPT Collection Module
 *
 * AAPT Collection Module
 *
 * @file		module_aapt.php
 * @language	PHP
 * @package		vixen
 * @author		Rich Davis
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// CollectionModuleAAPT
//----------------------------------------------------------------------------//
/**
 * CollectionModuleAAPT
 *
 * AAPT Collection Module
 *
 * AAPT Collection Module
 *
 *
 * @prefix		mod
 *
 * @package		collection
 * @class		CollectionModuleAAPT
 */
 class CollectionModuleAAPT extends CollectionModuleBase
 {
	private $_resConnection;
 	
	//public $intBaseCarrier			= CARRIER_AAPT;
	public $intBaseFileType			= RESOURCE_TYPE_FILE_REOURCE_AAPT;
	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleFTP
	 *
	 * Constructor for CollectionModuleFTP
	 *
	 * @return		CollectionModuleFTP
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
 		$this->_arrModuleConfig['Host']			['Default']		= 'https://wholesalebbs.aapt.com.au/';
 		$this->_arrModuleConfig['Host']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Host']			['Description']	= "AAPT XML Server to connect to";
 		
 		$this->_arrModuleConfig['Username']		['Default']		= '';
 		$this->_arrModuleConfig['Username']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Username']		['Description']	= "AAPT XML Username";
 		
 		$this->_arrModuleConfig['Password']		['Default']		= '';
 		$this->_arrModuleConfig['Password']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']		['Description']	= "AAPT XML Password";
 		
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
	 * Connects to AAPT XML server
	 *
	 * Connects to AAPT XML server
	 *
	 * @return	mixed									TRUE: Pass; string: Error
	 *
	 * @method
	 */
 	function Connect()
 	{
 		// Init CURL session
 		$this->_resConnection = curl_init();
		
		// Connect to the remote server
		$arrParams['username']		= "username=".urlencode($this->GetConfigField('Username'));
		$arrParams['password']		= "password=".urlencode($this->GetConfigField('Password'));
		$arrParams['fileAction']	= "fileAction=".urlencode("allNew");
		curl_setopt($this->_resConnection, CURLOPT_URL				, $this->GetConfigField('Host').'preparedownloads.asp');
		curl_setopt($this->_resConnection, CURLOPT_SSL_VERIFYPEER	, FALSE);
		curl_setopt($this->_resConnection, CURLOPT_SSL_VERIFYHOST	, FALSE);
		curl_setopt($this->_resConnection, CURLOPT_HEADER			, FALSE);
		curl_setopt($this->_resConnection, CURLOPT_RETURNTRANSFER	, TRUE);
		curl_setopt($this->_resConnection, CURLOPT_POSTFIELDS		, implode($arrParams, "&"));
		curl_setopt($this->_resConnection, CURLOPT_POST				, TRUE);
		curl_setopt($this->_resConnection, CURLOPT_BINARYTRANSFER	, FALSE);
		
		// Prepare the download and retrieve token
		if (!$strXML = curl_exec($this->_resConnection))
		{
			// Can't connect (probably no internet)
			Debug($strXML);
			return "Unable to connect to the Server or no Token received";
		}
		$this->_domDocument	= new DOMDocument('1.0', 'iso-8859-1');
		$this->_domDocument->loadXML($strXML);
		$this->_dxpPath		= new DOMXPath($this->_domDocument);
		
		// Find token
		foreach ($this->_dxpPath->query("/PrepareDownloadsResponse/ResultSet") as $xndFileNode)
		{
			$this->_strToken = $this->_dxpPath->query("token", $xndFileNode)->Item(0)->nodeValue;
		}
		
		if((bool)$this->_strToken)
		{
			return TRUE;
		}
		else
		{
			return "No Token received";
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
		if ($this->_resConnection)
		{
			curl_close($this->_resConnection);
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
	 * @return	mixed						string: Filename; FALSE: No more files
	 *
	 * @method
	 */
 	function Download($strDestination)
 	{
 		if (!$this->_strToken)
		{
			// No files to download
			return FALSE;
		}
		
		// Download the ZIP file
		curl_setopt($this->_resConnection, CURLOPT_URL				, $this->GetConfigField('Host')."autodownload.asp?token={$this->_strToken}");
		curl_setopt($this->_resConnection, CURLOPT_BINARYTRANSFER	, TRUE);
		curl_setopt($this->_resConnection, CURLOPT_POST				, FALSE);
		
		// Download file and save
		$strZIPData		= curl_exec($this->_resConnection);
		$strBasename	= "AAPT_NewFiles_".date("Y-m-d_His").".zip";
		$ptrTempFile	= fopen($strDestination.$strBasename, 'w');
		fwrite($ptrTempFile, $strZIPData);
		fclose($ptrTempFile);
		
		$this->_strToken	= NULL;
		
		// Return file name, or FALSE on failure
		$arrFileTypes	= $this->GetConfigField('FileDefine');
		$arrFile		= Array();
		$arrFile['FileType']					= &$arrFileTypes['XML_ARCHIVE'];
		$arrFile['FileType']['FileImportType']	= 'XML_ARCHIVE';
		$arrFile['RemotePath']					= $strBasename;
		$arrFile['LocalPath']					= $strDestination.$strBasename;
		return (@filesize($strDestination.$strBasename)) ? $arrFile : FALSE;
 	}
}

?>
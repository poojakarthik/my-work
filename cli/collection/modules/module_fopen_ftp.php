<?php
/**
 * CollectionModuleFOpenFTP
 *
 * FTP (File Wrapper) Collection Module
 * 
 * @class	CollectionModuleFOpenFTP
 */
 class CollectionModuleFOpenFTP extends CollectionModuleFOpen
 {
	protected	$_resConnection;
	protected	$_strWrapper;
	
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_FTP;
	
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleFOpenFTP
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
 		$this->_arrModuleConfig['Host']						['Default']		= '';
 		$this->_arrModuleConfig['Host']						['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Host']						['Description']	= "FTP Server to connect to";
 		
 		$this->_arrModuleConfig['Username']					['Default']		= '';
 		$this->_arrModuleConfig['Username']					['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Username']					['Description']	= "FTP Username";
 		
 		$this->_arrModuleConfig['Password']					['Default']		= '';
 		$this->_arrModuleConfig['Password']					['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']					['Description']	= "FTP Password";
 		
 		$this->_arrModuleConfig['FileDefine']				['Default']		= array();
 		$this->_arrModuleConfig['FileDefine']				['Type']		= DATA_TYPE_ARRAY;
 		$this->_arrModuleConfig['FileDefine']				['Description']	= "Definitions for where to download files from";
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
		$strHost				= $this->GetConfigField('Host');
		$strUsername			= $this->GetConfigField('Username');
		$strPassword			= $this->GetConfigField('Password');
		
		// Init wrapper
		$this->_strWrapper	= "ftp://{$strUsername}:{$strPassword}@{$strHost}";
		
		// Test if the connection works...
		if ($this->_resConnection = fopen($this->_strWrapper.'/2009/W015657972.K16', 'r'))
		{
			// Get list of files to download
			$this->_arrDownloadPaths	= $this->_getDownloadPaths();
			reset($this->_arrDownloadPaths);
			
			return true;
		}
		else
		{
			// Error
			return "Unable to connect to host {$strHost}";
		}
	}
}
?>
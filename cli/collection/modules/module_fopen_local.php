<?php
/**
 * CollectionModuleFOpenLocal
 *
 * Local (File Wrapper) Collection Module
 * 
 * @class	CollectionModuleFOpenLocal
 */
 class CollectionModuleFOpenLocal extends CollectionModuleFOpen
 {
	protected	$_resConnection;
	protected	$_strWrapper;
	
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_LOCAL;
	
	/**
	 * __construct()
	 *
	 * Constructor for CollectionModuleFOpenSFTP
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
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
		// Init wrapper
		$this->_strWrapper	= "";
		
		// Get list of files to download
		$this->_arrDownloadPaths	= $this->_getDownloadPaths();
		reset($this->_arrDownloadPaths);
		
		return true;
	}
}
?>
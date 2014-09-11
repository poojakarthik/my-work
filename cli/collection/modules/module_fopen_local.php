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
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_RESOURCE_LOCAL;
	
	protected	$_resConnection;
	protected	$_strWrapper;
	
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_LOCAL;
 	
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
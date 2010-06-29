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
	const	RESOURCE_TYPE	= RESOURCE_TYPE_FILE_RESOURCE_FTP;
	
	protected	$_resConnection;
	protected	$_strWrapper;
	
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_FTP;
	
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
		$this->_strWrapper	= "ftp://{$strUsername}:{$strPassword}@{$strHost}";
		$rResource	= fopen($this->_strWrapper.'/2009/W015657972.K16', 'r');
		ftp_pasv($rResource);
		throw new Exception("TEST");
		
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
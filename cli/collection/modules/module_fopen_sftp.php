<?php
/**
 * CollectionModuleFOpenSFTP
 *
 * SFTP (File Wrapper) Collection Module
 * 
 * @class	CollectionModuleFOpenSFTP
 */
 class CollectionModuleFOpenSFTP extends CollectionModuleFOpen
 {
	protected	$_resConnection;
	protected	$_strWrapper;
	
	public $intBaseFileType			= RESOURCE_TYPE_FILE_RESOURCE_SFTP;
	
	const	SSH_KEY_PATH			= '/home/ybs-admin/.ssh/';
	
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
		
		// Mandatory
 		$this->_arrModuleConfig['Host']						['Default']		= '';
 		$this->_arrModuleConfig['Host']						['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Host']						['Description']	= "SFTP Server to connect to";
 		
 		$this->_arrModuleConfig['Username']					['Default']		= '';
 		$this->_arrModuleConfig['Username']					['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Username']					['Description']	= "SFTP Username";
 		
 		$this->_arrModuleConfig['Password']					['Default']		= '';
 		$this->_arrModuleConfig['Password']					['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']					['Description']	= "SFTP Password";
 		
 		$this->_arrModuleConfig['UsePublicKey']				['Default']		= '';
 		$this->_arrModuleConfig['UsePublicKey']				['Type']		= DATA_TYPE_BOOLEAN;
 		$this->_arrModuleConfig['UsePublicKey']				['Description']	= "Use Public/Private Key";
 		
 		$this->_arrModuleConfig['KeyEncryptionAlgorithm']	['Default']		= 'rsa';
 		$this->_arrModuleConfig['KeyEncryptionAlgorithm']	['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['KeyEncryptionAlgorithm']	['Description']	= "Key Encryption Algortihm (rsa/dsa)";
 		
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
		$bolUsePrivateKey		= $this->GetConfigField('UsePublicKey');
		$strEncryptionAlgorithm	= strtolower(trim($this->GetConfigField('KeyEncryptionAlgorithm')));
		$strEncryptionAlgorithm	= (in_array($strEncryptionAlgorithm, array('rsa', 'dsa'))) ? $strEncryptionAlgorithm : $this->_arrModuleConfig['KeyEncryptionAlgorithm']['Default'];
		
		switch (strtolower(trim($this->GetConfigField('KeyEncryptionAlgorithm'))))
		{
			case 'rsa':
				$strEncryptionAlgorithmPHP	= 'rsa';
				break;
			case 'dsa':
				$strEncryptionAlgorithmPHP	= 'dss';
				break;
		}
		
		$arrMethods	= ($bolUsePrivateKey) ? array('hostkey'=>'ssh-'.$strEncryptionAlgorithmPHP) : array();
		if ($this->_resConnection = ssh2_connect($strHost, 22, $arrMethods))
		{
			if ($bolUsePrivateKey)
			{
				// Public/Private Key
				if (!ssh2_auth_pubkey_file($this->_resConnection, $strUsername, self::SSH_KEY_PATH."id_{$strEncryptionAlgorithm}.pub", self::SSH_KEY_PATH."id_{$strEncryptionAlgorithm}", $strPassword))
				{
					// Error
					return "Unable to authenticate with {$strHost} using ".strtoupper($strEncryptionAlgorithm)." with Username '{$strUsername}'";
				}
			}
			else
			{
				// Password Auth
				if (!ssh2_auth_password($this->_resConnection, $strUsername, $strPassword))
				{
					// Error
					return "Unable to authenticate with {$strHost} with Username '{$strUsername}'";
				}
			}
			
			if ($this->_resSFTPConnection = ssh2_sftp($this->_resConnection))
			{
				// Init wrapper
				$this->_strWrapper	= "ssh2.sftp://{$this->_resSFTPConnection}";
				
				// Get list of files to download
				$this->_arrDownloadPaths	= $this->_getDownloadPaths();
				reset($this->_arrDownloadPaths);
				
				return true;
			}
			else
			{
				return "Unable to initialise SFTP subsystem";
			}
		}
		else
		{
			// Error
			return "Unable to connect to host {$strHost}";
		}
	}
}
?>
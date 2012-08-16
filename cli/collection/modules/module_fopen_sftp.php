<?php
 class CollectionModuleFOpenSFTP extends CollectionModuleFOpen {
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_RESOURCE_SFTP;
	
	protected $_resConnection;
	protected $_strWrapper;
	
	public $intBaseFileType = RESOURCE_TYPE_FILE_RESOURCE_SFTP;
	
	const LEGACY_SSH_KEY_PATH = '/home/ybs-collection/.ssh/';
	
	public static function getConfigDefinition() {
		// Values defined in here are DEFAULT values
		return array(
			'Host' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'SFTP Server to connect to'
			),
			'Username' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'SFTP Username'
			),
			'Password' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'SFTP Password'
			),
			'FileDefine' => array(
				'Value' => array(),
				'Type' => DATA_TYPE_ARRAY,
				'Description' => 'Definitions for where to download files from'
			),
			'UsePublicKey' => array(
				'Value' => false,
				'Type' => DATA_TYPE_BOOLEAN,
				'Description' => 'Use Public/Private Key'
			),
			'KeyEncryptionAlgorithm' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Key Encryption Algortihm (rsa/dsa)'
			),
			'PrivateKeyFile' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Path to the SSH Private Key File'
			),
			'PublicKeyFile' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Path to the SSH Public Key File (defaults to <PrivateKeyFile>.pub)'
			)
		);
	}

	function Connect() {
		$strHost = $this->_oConfig->Host;
		$strUsername = $this->_oConfig->Username;
		$strPassword = $this->_oConfig->Password;
		$bolUsePrivateKey = $this->_oConfig->UsePublicKey;
		$sPrivateKeyFile = $this->_oConfig->PrivateKeyFile;
		$sPublicKeyFile = $this->_oConfig->PublicKeyFile;
		$strEncryptionAlgorithm = strtolower(trim($this->_oConfig->KeyEncryptionAlgorithm));
		$strEncryptionAlgorithm = (in_array($strEncryptionAlgorithm, array('rsa', 'dsa'))) ? $strEncryptionAlgorithm : $this->_arrModuleConfig['KeyEncryptionAlgorithm']['Default'];
		
		switch (strtolower(trim($this->_oConfig->KeyEncryptionAlgorithm))) {
			case 'rsa':
				$strEncryptionAlgorithmPHP = 'rsa';
				break;
			case 'dsa':
				$strEncryptionAlgorithmPHP = 'dss';
				break;
		}
		
		$arrMethods = ($bolUsePrivateKey) ? array('hostkey'=>'ssh-'.$strEncryptionAlgorithmPHP) : array();
		if ($this->_resConnection = ssh2_connect($strHost, 22, $arrMethods)) {
			if ($bolUsePrivateKey) {
				// Public/Private Key
				$sPrivateKeyFile = $sPrivateKeyFile ? $sPrivateKeyFile : self::LEGACY_SSH_KEY_PATH."id_{$strEncryptionAlgorithm}";
				$sPublicKeyFile = $sPublicKeyFile ? $sPublicKeyFile : "{$sPrivateKeyFile}.pub";
				$strPassword = $strPassword ? $strPassword : null;
				if (!ssh2_auth_pubkey_file($this->_resConnection, $strUsername, $sPublicKeyFile, $sPrivateKeyFile, $strPassword)) {
					// Error
					return "Unable to authenticate with {$strHost} using ".strtoupper($strEncryptionAlgorithm)." ({$sPublicKeyFile}|{$sPrivateKeyFile}) with Username '{$strUsername}'";
				}
			} else {
				// Password Auth
				if (!ssh2_auth_password($this->_resConnection, $strUsername, $strPassword)) {
					// Error
					return "Unable to authenticate with {$strHost} with Username '{$strUsername}'";
				}
			}
			
			if ($this->_resSFTPConnection = ssh2_sftp($this->_resConnection)) {
				// Init wrapper
				$this->_strWrapper = "ssh2.sftp://{$this->_resSFTPConnection}";
				
				// Get list of files to download
				$this->_arrDownloadPaths = $this->_getDownloadPaths();
				reset($this->_arrDownloadPaths);
				
				return true;
			} else {
				return "Unable to initialise SFTP subsystem";
			}
		} else {
			// Error
			return "Unable to connect to host {$strHost}";
		}
	}
}
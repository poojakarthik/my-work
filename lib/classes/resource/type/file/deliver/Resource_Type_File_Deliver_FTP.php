<?php
class Resource_Type_File_Deliver_FTP extends Resource_Type_File_Deliver
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_DELIVERER_FTP;
	
	protected	$_rConnection	= '';
	
	public function connect()
	{
		$sHost		= $this->getConfig()->Host;
		$sUsername	= $this->getConfig()->Username;
		$sPassword	= $this->getConfig()->Password;
		$iPort		= $this->getConfig()->Port;
		$bPassive	= $this->getConfig()->Passive;
		
		// Connect to the FTP server
		$this->_rConnection	= @ftp_connect($this->getConfig()->Host, $this->getConfig()->Port);
		Flex::assert($this->_rConnection,
			"Unable to connect to FTP host '{$sHost}:{$iPort}'",
			$php_errormsg,
			'File Delivery: Unable to connect to FTP host'
		);
		
		// Authenticate
		Flex::assert(@ftp_login($this->_rConnection, $sUsername, $sPassword),
			"Unable to authenticate with FTP host '{$sHost}' as '{$sUsername}'",
			$php_errormsg,
			'File Delivery: Unable to authenticate with FTP host'
		);
		
		if ($bPassive)
		{
			Flex::assert(@ftp_pasv($this->_rConnection, true),
				"Unable to initiate PASV mode on FTP host '{$sHost}' as '{$sUsername}'",
				$php_errormsg,
				'File Delivery: Unable to initiate PASV mode on FTP host'
			);
		}
		
		return $this;
	}
	
	protected function _deliver($sLocalPath, $mCarrierModule=null)
	{
		$sDeliveryPath	= $this->_getDeliveryPath($sLocalPath);
		
		$aErrorData	=	array
						(
							'sLocalPath'			=> $sLocalPath,
							'sDeliveryPath'			=> $sDeliveryPath,
							'aCarrierModuleConfig'	=> $this->getConfig()->toArray()
						);
		
		Flex::assert($this->getConfig()->AllowOverwrite || (@ftp_mdtm($this->_rConnection, $sDeliveryPath) === -1),
			"File Delivery Path '{$sDeliveryPath}' already exists",
			print_r(array_merge($aErrorData, array('PHP Warning'=>$php_errormsg)), true),
			'File Delivery Path already exists'
		);
		
		Flex::assert(@ftp_put($this->_rConnection, $sDeliveryPath, $sLocalPath, FTP_BINARY),
			"Unable to deliver '{$sLocalPath}' to '{$sDeliveryPath}'",
			print_r(array_merge($aErrorData, array('PHP Warning'=>$php_errormsg)), true),
			'Unable to deliver file'
		);
		
		return $this;
	}
	
	public function disconnect()
	{
		// We probably don't really care if this fails
		@ftp_close($this->_rConnection);
		
		return $this;
	}
	
	protected function _getDeliveryPath($sFilePath)
	{
		return rtrim($this->getConfig()->RemotePath, '/\\').'/'.basename($sFilePath);
	}

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}
	
	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'RemotePath'		=>	array('Description'=>'Remote path to deposit the file to'),
			'AllowOverwrite'	=>	array('Description'=>'Allow overwriting of existing files on the host'),
			'Host'				=>	array('Description'=>'FTP Host'),
			'Username'			=>	array('Description'=>'Username'),
			'Password'			=>	array('Description'=>'Password'),
			'Port'				=>	array('Description'=>'Port','Value'=>21,'Type'=>DATA_TYPE_INTEGER),
			'PassiveMode'		=>	array('Description'=>'0: Active Mode; 1: Passive Mode','Value'=>false,'Type'=>DATA_TYPE_BOOLEAN)
		));
	}
}
?>
<?php
class Resource_Type_File_Deliver_FileSystem extends Resource_Type_File_Deliver
{
	protected	$_sWrapper	= '';
	
	public function connect()
	{
		return $this;
	}
	
	public function deliver($sLocalPath)
	{
		$sDeliveryPath	= $this->_getDeliveryPath();
		
		$aErrorData	=	array
						(
							'sLocalPath'			=> $sLocalPath,
							'sRemotePath'			=> $sRemotePath,
							'sDeliveryPath'			=> $sDeliveryPath,
							'aCarrierModuleConfig'	=> $this->getConfig()->toArray()
						);
		
		Flex::assert(@is_readable($sLocalPath),
			"File Delivery Path '{$sLocalPath}' is not readable",
			print_r($aErrorData, true),
			'File Delivery Path is not readable'
		);
		
		Flex::assert(!$this->_checkRemoteWritable() || @is_writable($sDeliveryPath),
			"File Delivery Path '{$sRemotePath}' is not writable",
			print_r($aErrorData, true),
			'File Delivery Path is not writable'
		);
		
		Flex::assert(!$this->getConfig()->AllowOverwrite && @file_exists($sDeliveryPath),
			"File Delivery Path '{$sRemotePath}' already exists",
			print_r($aErrorData, true),
			'File Delivery Path already exists'
		);
		
		Flex::assert(@copy($sLocalPath, $sDeliveryPath),
			"Unable to deliver '{$sLocalPath}' to '{$sRemotePath}'",
			print_r($aErrorData, true),
			'Unable to deliver file'
		);
		
		return $this;
	}
	
	public function disconnect()
	{
		return $this;
	}
	
	protected function _getDeliveryPath($sFilePath)
	{
		return rtrim($this->_sWrapper, '/\\').'/'.rtrim($this->getConfig()->RemotePath, '/\\').'/';
	}
	
	protected function _checkRemoteWritable()
	{
		return true;
	}
	
	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}
	
	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'RemotePath'	=>	array()
		));
	}
}
?>
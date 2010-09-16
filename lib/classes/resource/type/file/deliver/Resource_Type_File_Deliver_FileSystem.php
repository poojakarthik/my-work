<?php
class Resource_Type_File_Deliver_FileSystem extends Resource_Type_File_Deliver
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM;

	//protected	$_sWrapper = FILES_BASE_PATH;

	public function connect()
	{
		return $this;
	}

	public function deliver($sLocalPath)
	{

		$sDeliveryPath	= $this->_getDeliveryPath($sLocalPath);

		$aErrorData	=	array
						(
							'sLocalPath'			=> $sLocalPath,
							'sDeliveryPath'			=> $sDeliveryPath,
							'aCarrierModuleConfig'	=> $this->getConfig()->toArray()
						);

		$sDirectoryPath = str_replace ( strrchr($sDeliveryPath, '/') , "" ,$sDeliveryPath );
		Flex::assert(!$this->_checkRemoteWritable() || @is_writable($sDirectoryPath),
			"File Delivery Path '{$sDeliveryPath}' is not writable",
			print_r(array_merge($aErrorData, array('PHP Warning'=>$php_errormsg)), true),
			'File Delivery Path is not writable'
		);

		Flex::assert(@file_exists($sDeliveryPath)?($this->getConfig()->AllowOverwrite==1?true:false):true,
			"File Delivery Path '{$sDeliveryPath}' already exists",
			print_r(array_merge($aErrorData, array('PHP Warning'=>$php_errormsg)), true),
			'File Delivery Path already exists'
		);

		Flex::assert(@copy( $sLocalPath , $sDeliveryPath),
			"Unable to deliver '{$sLocalPath}' to '{$sDeliveryPath}'",
			print_r(array_merge($aErrorData, array('PHP Warning'=>$php_errormsg)), true),
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
		return rtrim($this->_sWrapper, '/\\').'/'.trim($this->getConfig()->RemotePath, '/\\').'/'.basename($sFilePath);
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
			'RemotePath'		=>	array('Description'=>'Remote path to deposit the file to'),
			'AllowOverwrite'	=>	array('Description'=>'Allow overwriting of existing files on the host')
		));
	}
}
?>
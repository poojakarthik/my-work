<?php
class Resource_Type_File_Deliver_Filesystem_FTP extends Resource_Type_File_Deliver_FileSystem
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_RETAILDECISIONS_APPLICATIONS;
	
	public function connect()
	{
		// Configure the fopen() wrapper
		$sHost		= $this->getConfig()->Host;
		$sUsername	= $this->getConfig()->Username;
		$sPassword	= $this->getConfig()->Password;
		
		$this->_sWrapper	= "ftp://{$sUsername}:{$sPassword}@{$sHost}/";
		
		return $this;
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
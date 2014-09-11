<?php
abstract class Resource_Type_File_Export extends Resource_Type_Base
{
	protected	$_oFileExport;
	protected	$_oFileDeliver;
	protected	$_sFilePath;

	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);

		// File Export ORM
		$this->_oFileExport	= new File_Export();

		// Get the File Deliverer Class
		$oFileDeliverCarrierModule	= Carrier_Module::getForId($this->getConfig()->FileDeliverCarrierModuleId, true);
		if (!$oFileDeliverCarrierModule)
		{
			throw new Exception("Unable to load 'File Deliver' Carrier Module with Id '".$this->getConfig()->FileDeliverCarrierModuleId."'");
		}
		$sFileDeliverClass		= $oFileDeliverCarrierModule->Module;
		$this->_oFileDeliver	= new $sFileDeliverClass($oFileDeliverCarrierModule);
	}

	public static function getExportPath()
	{
		return FILES_BASE_PATH."export/";
	}

	abstract public function addRecord($mRecord);

	abstract public function render();

	abstract public function deliver();

	public function getFileExport()
	{
		return $this->_oFileExport;
	}

	protected function _logToDatabase()
	{
		// FIXME: Perhaps this can be moved directly into save()
		// Generic FileExport stuff
		$this->_oFileExport->FileName	= basename($this->_sFilePath);
		$this->_oFileExport->Location	= $this->_sFilePath;
		$this->_oFileExport->Carrier	= $this->getCarrier();
		$this->_oFileExport->ExportedOn	= Data_Source_Time::currentTimestamp(null, true);
		$this->_oFileExport->Status		= FILE_DELIVERED;	// FIXME: Is this correct?
		$this->_oFileExport->FileType	= $this->getResourceType();
		$this->_oFileExport->SHA1		= sha1_file($this->_oFileExport->Location);
		$this->_oFileExport->save();


	}

	public function save()
	{
		$this->_logToDatabase();
		return $this;
	}

	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'FileDeliverCarrierModuleId'	=>	array('Description'=>'CarrierModule.Id for the method of delivery','Type'=>DATA_TYPE_INTEGER)
		));
	}
}
?>
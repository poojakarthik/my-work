<?php
abstract class Correspondence_Dispatcher extends Resource_Type_Base
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_CORRESPONDENCE_EXPORT;

	protected $_aRuns;


	protected	$_oFileExport;
	protected	$_oFileDeliver;
	protected	$_sFilePath;

	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);



		// Get the File Deliverer Class
		$oFileDeliverCarrierModule	= Carrier_Module::getForId($this->getConfig()->FileDeliverCarrierModuleId, true);
		if (!$oFileDeliverCarrierModule)
		{
			throw new Exception("Unable to load 'File Deliver' Carrier Module with Id '".$this->getConfig()->FileDeliverCarrierModuleId."'");
		}
		$sFileDeliverClass		= $oFileDeliverCarrierModule->Module;
		$this->_oFileDeliver	= new $sFileDeliverClass($oFileDeliverCarrierModule);
	}




	final public function sendWaitingRuns()
	{
		//retrieve the set of correspondence runs that should be sent
		$this->_aRuns = Correspondence_Run::getWaitingRuns();
		$sFileDirectoryPath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__);
		//create the run_batch record
		$oBatch = new Correspondence_Run_Batch_ORM(array($sTimeStamp));
		$oBatch->save();


		foreach ($aRuns as $oRun)
		{
			$this->export();
			//create the file export record
			$this->_logToDatabase();
			//set the file export id of the run object and timestamp
			$oRun->setDeliveryDetails($this->_oFileExport->id,$sTimeStamp, $oBatch->id);
			//save the run object
			$oRun->save();
		}
	}

	public static function getExportPath()
	{
		return FILES_BASE_PATH."export/";
	}

	protected function _logToDatabase()
	{

		// File Export ORM
		$this->_oFileExport	= new File_Export();
		$this->_oFileExport->FileName	= basename($this->_sFilePath);
		$this->_oFileExport->Location	= $this->_sFilePath;
		$this->_oFileExport->Carrier	= $this->getCarrier();
		$this->_oFileExport->ExportedOn	= Data_Source_Time::currentTimestamp(null, true);
		$this->_oFileExport->Status		= FILE_DELIVERED;	// FIXME: Is this correct?
		$this->_oFileExport->FileType	= $this->getResourceType();
		$this->_oFileExport->SHA1		= sha1_file($this->_oFileExport->Location);
		$this->_oFileExport->save();
	}




	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, self::CARRIER_MODULE_TYPE);
	}

	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'FileDeliverCarrierModuleId'	=>	array('Description'=>'CarrierModule.Id for the method of delivery','Type'=>DATA_TYPE_INTEGER)
		));
	}

	abstract protected function export();

}




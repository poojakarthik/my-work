<?php
abstract class Correspondence_Dispatcher extends Resource_Type_File_Export
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_CORRESPONDENCE_EXPORT;

	protected $_oRun;
	protected $_iBatchId;

	protected $_oTARFileExport;

	protected 	$_aPDFFilenames = array();
	protected $_aTARFilePath = array();
	protected $_bPreprinted;
	protected $_sInvoiceRunPDFBasePath;

	final public function sendRun($oRun, $iBatchId)
	{

		$this->_oRun = $oRun;
		$this->_iBatchId = $iBatchId;
		$this->export();
		$this->render();
		$this->deliver();
		//create the file export record
		$this->_logToDatabase();
		//set the file export id of the run object and timestamp
		$oRun->setDeliveryDetails($this->_oFileExport->id,$this->_oFileExport->ExportedOn, $this->_oTARFileExport->id, $iBatchId);



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



	public static function sendWaitingRuns()
	{
		//retrieve the set of correspondence runs that should be sent
		$aRuns = Correspondence_Run::getWaitingRuns();
		$oBatch = new Correspondence_Run_Batch();
		$oBatch->batch_datetime = Data_Source_Time::currentTimestamp();
		$oBatch->save();
		foreach ($aRuns as $oRun)
		{
			$oDispatcher = $oRun->getCarrierModule();
			$oDispatcher->sendRun($oRun, $oBatch->id);
			$oRun->save();
		}

	}

protected function _logToDatabase()
	{


		parent::_logToDatabase();
		if ($this->_bPreprinted)
		{
			$this->_oTARFileExport->FileName	= basename($this->_aTARFilePath);
			$this->_oTARFileExport->Location	= $this->_aTARFilePath;
			$this->_oTARFileExport->Carrier		= $this->getCarrier();
			$this->_oTARFileExport->ExportedOn	= $this->_oFileExport->ExportedOn;
			$this->_oTARFileExport->Status		= FILE_DELIVERED;	// FIXME: Is this correct?
			$this->_oTARFileExport->FileType	= 10036;
			$this->_oTARFileExport->SHA1		= sha1_file($this->_oTARFileExport->Location);
			$this->_oTARFileExport->save();
		}


	}



}




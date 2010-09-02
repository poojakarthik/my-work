<?php
class Correspondence_Dispatcher_YellowBillingCSV extends Correspondence_Dispatcher
{

	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLINGCSV;


	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		$this->_iTimestamp	= time();
		$this->_oFileExporterCSV	= new File_Exporter_CSV();

	}

	public function render()
	{
		$sFileDirectoryPath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__);
		$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
		$sFilename	= $oRun->getCorrespondenceCode()
				.'_'
				.$sTimeStamp
				.'_'
				.$this->_oRun->id
				.'.csv';

		$this->_sFilePath = $sFileDirectoryPath.$sFilename;

		// Render and write to disk
		$this->_oFileExporterCSV->renderToFile($this->_sFilePath);

		// TODO: Do we need to return anything special?
		return $this;
	}

	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		return $this;
	}



	public function addRecord($mRecord)
	{
		$oRecord	= $this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_DETAIL)->newRecord();
		foreach ($mRecord as $sField=>$mValue)
		{
			$oRecord->$sField = $mValue;
		}
		$this->_oFileExporterCSV->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);
	}

	public function export()
	{
		$this->_configureFileExporter($this->_oRun->getAllColumns());

		foreach ($this->oRun->getCorrespondence() as $oCorrespondence)
		{
			$oFile->addRecord($oCorrespondence->toArray());
		}


	}


	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."$iCarrier/$sClass/";
	}


	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}

		public static function create()
		{
			return new self();
		}

	protected function _configureFileExporter($aColumns)
	{
		$this->_iTimestamp	= time();
		$oRecordType = File_Exporter_RecordType::factory();
		foreach($aColumn as $sColumn)
		{
			$oRecord->addField($sColumn, File_Exporter_Field::factory());
		}

		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_DETAIL, $oRecordType);
	}


}
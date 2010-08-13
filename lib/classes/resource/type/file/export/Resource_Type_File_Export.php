<?php
abstract class Resource_Type_File_Export extends Resource_Type_File
{
	protected	$_oFileExport;
	
	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		
		$this->_oFileExport	= new File_Export();
	}
	
	public static function getExportPath()
	{
		return parent::getExportPath()."export/";
	}
	
	abstract public function addRecord($mRecord);
	
	abstract public function render();
	
	abstract public function deliver();
	
	protected function _logToDatabase()
	{
		// Generic FileExport stuff
		$this->_oFileExport->FileName	= $this->getFileName();
		$this->_oFileExport->Location	= $this->getExportPath().$this->getFileName();
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
	}
}
?>
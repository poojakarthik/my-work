<?php
abstract class File_Exporter
{
	const	RECORD_GROUP_BODY	= 'body';
	
	protected	$_aRecordTypes	= array();
	protected	$_aRecords		= array();
	
	public function addRecord(File_Exporter_Record $oRecord, $sRecordGroup=self::RECORD_GROUP_BODY)
	{
		$this->_aRecords[$sRecordGroup][]	= $oRecord;
	}
	
	public function registerRecordType($mIdentifier, File_Exporter_RecordType $oRecordType)
	{
		if (isset($this->_aRecordTypes[$mIdentifier]))
		{
			throw new Exception("Record Type Identifier '{$mIdentifier}' is already registered!");
		}
		
		$this->_aRecordTypes[$mIdentifier]	= $oRecordType;
	}
	
	public function getRecordType($sIdentifier)
	{
		if ($sIdentifier instanceof File_Exporter_RecordType)
		{
			if (!in_array($sIdentifier, $this->_aRecordTypes))
			{
				throw new Exception("Record Type '{$sIdentifier}' is not registered with this File Exporter");
			}
			return $sIdentifier;
		}
		else
		{
			if (!isset($this->_aRecordTypes[$sIdentifier]))
			{
				throw new Exception("Record Type Identifier '{$sIdentifier}' is not registered with this File Exporter");
			}
			return $this->_aRecordTypes[$sIdentifier];
		}
	}
	
	abstract public function render();
	
	public function renderToFile($sPath)
	{
		return @(bool)file_put_contents($sPath, $this->render());
	}
}
?>
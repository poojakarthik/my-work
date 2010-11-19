<?php
class File_Exporter
{
	const	RECORD_GROUP_BODY	= 'body';
	
	protected	$_aRecordTypes	= array();
	protected	$_aRecords		= array();
	private		$_sNewLine		= null;
	
	public function __construct()
	{
		$this->_sNewLine	= chr(13);
	}
	
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
	
	public function render()
	{
		$aLines	= array();
		
		// Body
		foreach ($this->_aRecords[self::RECORD_GROUP_BODY] as $oRecord)
		{
			$sLine		= implode('', $oRecord->getProcessedRecord());
			$aLines[]	= $sLine;
		}
		
		return implode($this->_sNewLine, $aLines);
	}
	
	public function renderToFile($sPath)
	{
		@mkdir(dirname($sPath), 0777, true);
		return @(bool)file_put_contents($sPath, $this->render());
	}
}
?>
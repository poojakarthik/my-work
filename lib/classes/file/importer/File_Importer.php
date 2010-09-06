<?php
abstract class File_Importer
{
	protected	$_aRecordTypes	= array();
	protected	$_aRecords		= array();
	
	protected	$_sData;
	
	protected	$_oRecordTypeCallback;
	
	public function __construct(){}
	
	abstract protected function _fetch();
	
	public function fetch()
	{
		if (!isset($this->_oRecordTypeCallback))
		{
			throw new Exception("Record Type Callback is not yet defined");
		}
		$aRecord	= $this->_fetch();
		return $this->getRecordType($this->_oRecordTypeCallback->invoke($aRecord))->newRecord($aRecord);
	}
	
	public function setData($sData)
	{
		if (isset($this->_sData))
		{
			throw new Exception("Input data cannot be redefined");
		}
		$this->_sData	= (string)$sData;
	}
	
	public function setDataFile($sDataFilePath)
	{
		if (is_readable($sDataFilePath))
		{
			throw new Exception("Data File '{$sDataFilePath}' is not readable");
		}
		
		$sData	= @file_get_contents($sDataFilePath);
		if ($sData === false)
		{
			throw new Exception("Unable to read Data File '{$sDataFilePath}': {$php_errormsg}");
		}
		
		$this->setData($sData);
	}
	
	public function setRecordTypeCallback(Callback $oCallback)
	{
		$this->_oRecordTypeCallback	= $oCallback;
	}
	
	public function registerRecordType($mIdentifier, File_Importer_RecordType $oRecordType)
	{
		if (isset($this->_aRecordTypes[$mIdentifier]))
		{
			throw new Exception("Record Type Identifier '{$mIdentifier}' is already registered!");
		}
		
		$this->_aRecordTypes[$mIdentifier]	= $oRecordType;
	}
	
	public function getRecordType($sIdentifier)
	{
		if ($sIdentifier instanceof File_Importer_RecordType)
		{
			if (!in_array($sIdentifier, $this->_aRecordTypes))
			{
				throw new Exception("Record Type '{$sIdentifier}' is not registered with this File Importer");
			}
			return $sIdentifier;
		}
		else
		{
			if (!isset($this->_aRecordTypes[$sIdentifier]))
			{
				throw new Exception("Record Type Identifier '{$sIdentifier}' is not registered with this File Importer");
			}
			return $this->_aRecordTypes[$sIdentifier];
		}
	}
}
?>
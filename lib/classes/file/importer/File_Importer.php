<?php
class File_Importer
{
	protected	$_aRecordTypes	= array();
	protected	$_aRecords		= array();
	
	protected	$_sData;
	protected	$_aLines;
	
	protected	$_sNewLine	= "\n";
	
	protected	$_oRecordTypeCallback;
	
	public function __construct(){}
	
	public function getRecordTypeForData($sRecord)
	{
		$mRecordType	= $this->_oRecordTypeCallback->invoke($sRecord);
		return (is_string($mRecordType) || $mRecordType instanceof File_Importer_RecordType) ? $this->getRecordType()->newRecord($sRecord) : null;
	}
	
	public function fetch()
	{
		return $this->_fetch();
	}
	
	public function fetchProcessed()
	{
		if (!isset($this->_oRecordTypeCallback))
		{
			throw new Exception("Record Type Callback is not yet defined");
		}
		$sRecord	= $this->fetch();
		if ($sRecord !== false)
		{
			return $this->getRecordTypeForData($sRecord);
		}
		else
		{
			return false;
		}
	}
	
	protected function _fetch()
	{
		if (!isset($this->_aLines))
		{
			$this->_splitLines();
		}
		$sCurrent	= current($this->_aLines);
		if ($sCurrent !== false)
		{
			next($this->_aLines);
		}
		return $sCurrent;
	}
	
	protected function _splitLines()
	{
		if (!isset($this->_sData))
		{
			throw new Exception("Data has not been provided");
		}
		$this->_aLines	= explode($this->_sNewLine, $this->_sData);
		return $this;
	}
	
	public function setNewLine($sNewLine)
	{
		$this->_sNewLine	= (string)$sNewLine;
		return $this;
	}
	
	public function setData($sData)
	{
		if (isset($this->_sData))
		{
			throw new Exception("Input data cannot be redefined");
		}
		$this->_sData	= (string)$sData;
		return $this;
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
		return $this;
	}
	
	public function setRecordTypeCallback(Callback $oCallback)
	{
		$this->_oRecordTypeCallback	= $oCallback;
		return $this;
	}

	public function createRecordType($mIdentifier, $sClass='File_Importer_RecordType') {
		$oRecordType	= new $sClass($this);
		$this->registerRecordType($mIdentifier, $oRecordType);
		return $oRecordType;
	}
	
	public function registerRecordType($mIdentifier, File_Importer_RecordType $oRecordType)
	{
		if (isset($this->_aRecordTypes[$mIdentifier]))
		{
			throw new Exception("Record Type Identifier '{$mIdentifier}' is already registered!");
		}
		
		$this->_aRecordTypes[$mIdentifier]	= $oRecordType;
		return $this;
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
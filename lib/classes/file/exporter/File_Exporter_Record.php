<?php
class File_Exporter_Record
{
	protected	$_oRecordType;
	protected	$_aRecord;
	
	public function __construct(File_Exporter_RecordType $oRecordType)
	{
		$this->_oRecordType	= $oRecordType;
		
		$this->_setDefaultValues();
	}
	
	protected function _setDefaultValues()
	{
		$aFields	= $this->_oRecordType->getFields();
		foreach ($aFields as $sAlias=>$oField)
		{
			$this->{$sAlias}	= $oField->getDefaultValue();
		}
	}
	
	public function getRecordType()
	{
		return $this->_oRecordType;
	}
	
	public function getRecord()
	{
		return $this->_aRecord;
	}
	
	public function getProcessedRecord()
	{
		return $this->_oRecordType->process($this->_aRecord);
	}
	
	public function __set($sProperty, $mValue)
	{
		try
		{
			$this->_oRecordType->getField($sProperty);
		}
		catch (Exception $oException)
		{
			throw new Exception("Field '{$sProperty}' is not defined on Record Type '".print_r($this->_oRecordType, true)."'");
		}
		
		$this->_aRecord[$sProperty]	= $mValue;
	}
	
	public function __get($sProperty)
	{
		try
		{
			$this->_oRecordType->getField($this);
		}
		catch (Exception $oException)
		{
			throw new Exception("Field '{$sProperty}' is not defined on Record Type '{$oRecordType}'");
		}
		
		return $this->_aRecord[$sProperty];
	}
}
?>
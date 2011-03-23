<?php
class File_Importer_Record
{
	protected	$_oRecordType;
	protected	$_mData;
	protected	$_aRecord;
	
	public function __construct(File_Importer_RecordType $oRecordType, $mData)
	{
		$this->_oRecordType	= $oRecordType;
		
		$this->_mData	= $mData;
		$this->_aRecord	= $this->_oRecordType->parseData($this->_mData);
	}
	
	public function getRecordType()
	{
		return $this->_oRecordType;
	}
	
	public function getRawData()
	{
		return $this->_mData;
	}
	
	public function toArray()
	{
		return $this->_aRecord;
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
			$this->_oRecordType->getField($sProperty);
		}
		catch (Exception $oException)
		{
			throw new Exception("Field '{$sProperty}' is not defined on Record Type '{$oRecordType}'");
		}
		
		return $this->_aRecord[$sProperty];
	}
}
?>
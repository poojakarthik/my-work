<?php
class File_Importer_CSV_Field extends File_Importer_Field
{
	protected	$_iColumn;
	
	public function __construct(File_Importer_CSV_RecordType $oRecordType)
	{
		parent::__construct($oRecordType);
	}
	
	public function setColumn($iColumn)
	{
		if ($iColumn < 0)
		{
			throw new Exception("Column cannot be negative ({$iColumn})");
		}
		$this->_iColumn	= $iColumn;
	}
	
	public function extract($mData)
	{
		$aData	= (is_array($mData)) ? $mData : $this->_oRecordType->parseLine($mData);
		
		if (!isset($aData[$this->_iColumn]))
		{
			throw new Exception("Data provided doesn't have a column with index {$this->_iColumn}");
		}
		return parent::extract($aData[$this->_iColumn]);
	}
}
?>
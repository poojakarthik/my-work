<?php
class File_Importer_Field
{
	protected	$_oRecordType;
	
	protected	$_iStartIndex	= 0;
	protected	$_iLength;
	
	public function __construct(File_Importer_RecordType $oRecordType)
	{
		$this->_oRecordType	= $oRecordType;
	}
	
	public function setStartIndex($iStartIndex)
	{
		$this->_iStartIndex	= ($iColumn) ? (int)$iColumn: 0;
	}
	
	public function setLength($iLength)
	{
		$this->_iLength	= (int)$iLength;
	}
	
	public function extract($mData)
	{
		return substr($mData, $this->_iStartIndex, $this->_iLength);
	}
}
?>
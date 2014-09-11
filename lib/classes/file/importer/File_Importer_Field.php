<?php
class File_Importer_Field
{
	protected	$_oRecordType;
	
	protected	$_iStartIndex	= 0;
	protected	$_iLength		= null;
	
	public function __construct(File_Importer_RecordType $oRecordType)
	{
		$this->_oRecordType	= $oRecordType;
	}
	
	public function setStartIndex($iStartIndex)
	{
		$this->_iStartIndex	= ($iStartIndex) ? (int)$iStartIndex: 0;
		return $this;
	}
	
	public function setLength($iLength)
	{
		$this->_iLength	= $iLength !== null ? (int)$iLength : null;
		return $this;
	}
	
	public function extract($mData)
	{
		//Log::getLog()->log($mData);
		//Log::getLog()->log($this->_iStartIndex);
		//Log::getLog()->log($this->_iLength);
		//Log::getLog()->log(strlen($mData));
		//Log::getLog()->log(coalesce($this->_iLength, strlen($mData)));
		//Log::getLog()->log(max(0, coalesce($this->_iLength, strlen($mData))));
		return substr($mData, $this->_iStartIndex, max(0, coalesce($this->_iLength, strlen($mData))));
	}
	
	public static function factory()
	{
		return new self();
	}
}
?>
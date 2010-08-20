<?php
class File_Exporter_CSV extends File_Exporter
{
	const	RECORD_GROUP_HEADER	= 'header';
	const	RECORD_GROUP_FOOTER	= 'footer';
	
	protected	$_sEscape		= '\\';
	protected	$_sQuote		= '"';
	protected	$_sDelimiter	= ",";
	protected	$_sNewLine		= "\n";
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_oCSVFile	= new File_CSV();
	}
	
	public function setDelimiter($sDelimiter)
	{
		$this->_sDelimiter	= (string)$sDelimiter;
	}
	
	public function setQuote($sQuote)
	{
		$this->_sQuote	= (string)$sQuote;
	}
	
	public function setEscape($sEscape)
	{
		$this->_sEscape	= (string)$sEscape;
	}
	
	public function setNewLine($sNewLine)
	{
		$this->_sNewLine	= (string)$sNewLine;
	}
	
	protected function _getSpecialCharacters()
	{
		return array($this->_sNewLine, $this->_sDelimiter, $this->_sEscape, $this->_sQuote);
	}
	
	protected function _getQuotableCharacters()
	{
		return array($this->_sNewLine, $this->_sDelimiter, $this->_sEscape, $this->_sQuote, ' ');
	}
	
	public function render()
	{
		$aLines	= array();
		
		// Header
		foreach ($this->_aRecords[self::RECORD_GROUP_HEADER] as $oRecord)
		{
			$aLines[]	= File_CSV::buildLine($oRecord->getProcessedRecord(), $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $this->_getQuotableCharacters());
		}
		
		// Body
		foreach ($this->_aRecords[self::RECORD_GROUP_BODY] as $oRecord)
		{
			$aLines[]	= File_CSV::buildLine($oRecord->getProcessedRecord(), $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $this->_getQuotableCharacters());
		}
		
		// Footer
		foreach ($this->_aRecords[self::RECORD_GROUP_FOOTER] as $oRecord)
		{
			$aLines[]	= File_CSV::buildLine($oRecord->getProcessedRecord(), $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $this->_getQuotableCharacters());
		}
		
		return implode($this->_sNewLine, $aLines);
	}
}
?>
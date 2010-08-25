<?php
class File_Exporter_CSV extends File_Exporter
{
	const	RECORD_GROUP_HEADER	= 'header';
	const	RECORD_GROUP_FOOTER	= 'footer';
	
	const	QUOTE_MODE_ALWAYS	= true;
	const	QUOTE_MODE_NEVER	= false;
	const	QUOTE_MODE_REACTIVE	= null;
	
	protected	$_sEscape		= '\\';
	protected	$_sQuote		= '"';
	protected	$_sDelimiter	= ",";
	protected	$_sNewLine		= "\n";
	protected	$_bQuoteMode	= self::QUOTE_MODE_REACTIVE;
	
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
	
	public function setQuoteMode($bQuoteMode)
	{
		$this->_bQuoteMode	= ($bQuoteMode === null) ? null : !!$bQuoteMode;
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
		return array($this->_sDelimiter, $this->_sEscape, $this->_sQuote, ' ');
	}
	
	protected function _buildLine($oRecord)
	{
		return File_CSV::buildLine($oRecord->getProcessedRecord(), $this->_sDelimiter, $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $mQuote);
	}
	
	public function render()
	{
		$mQuote	= ($this->_bQuoteMode === null) ? $this->_getQuotableCharacters() : $this->_bQuoteMode;
		
		$aLines	= array();
		
		// Header
		foreach ($this->_aRecords[self::RECORD_GROUP_HEADER] as $oRecord)
		{
			$aLines[]	= $this->_buildLine($oRecord);
		}
		
		// Body
		foreach ($this->_aRecords[self::RECORD_GROUP_BODY] as $oRecord)
		{
			$aLines[]	= $this->_buildLine($oRecord);
		}
		
		// Footer
		foreach ($this->_aRecords[self::RECORD_GROUP_FOOTER] as $oRecord)
		{
			$aLines[]	= $this->_buildLine($oRecord);
		}
		
		return implode($this->_sNewLine, $aLines);
	}
}
?>
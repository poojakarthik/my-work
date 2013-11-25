<?php
class File_Exporter_CSV extends File_Exporter
{
	const	RECORD_GROUP_HEADER	= 'header';
	const	RECORD_GROUP_FOOTER	= 'footer';

	const	QUOTE_MODE_ALWAYS	= true;
	const	QUOTE_MODE_NEVER	= false;
	const	QUOTE_MODE_REACTIVE	= null;

	const ESCAPE_MODE_RFC4180 = 'RFC4180';
	const ESCAPE_MODE_LEGACY = 'LEGACY';

	protected $_sEscape = '\\';
	protected $_sQuote = '"';
	protected $_sDelimiter = ",";
	protected $_sNewLine = "\n";
	protected $_bQuoteMode = self::QUOTE_MODE_REACTIVE;
	protected $_sEscapeMode = self::ESCAPE_MODE_LEGACY;

	public function setDelimiter($sDelimiter)
	{
		$this->_sDelimiter	= (string)$sDelimiter;
		return $this;
	}

	public function setQuote($sQuote)
	{
		$this->_sQuote	= (string)$sQuote;
		return $this;
	}

	public function setQuoteMode($bQuoteMode)
	{
		$this->_bQuoteMode	= ($bQuoteMode === null) ? null : !!$bQuoteMode;
		return $this;
	}

	public function setEscape($sEscape)
	{
		$this->_sEscape	= (string)$sEscape;
		return $this;
	}

	public function setNewLine($sNewLine)
	{
		$this->_sNewLine	= (string)$sNewLine;
		return $this;
	}

	public function setEscapeMode($sEscapeMode) {
		$this->_sEscapeMode = $sEscapeMode;
	}

	protected function _getEscapableCharacters() {
		if ($this->_sEscapeMode === self::ESCAPE_MODE_RFC4180) {
			return array($this->_sQuote);
		}
		return array($this->_sNewLine, $this->_sDelimiter, $this->_sEscape, $this->_sQuote);
	}

	protected function _getQuotableCharacters()
	{
		return array($this->_sDelimiter, $this->_sEscape, $this->_sQuote, ' ');
	}

	protected function _buildLine($oRecord)
	{
		$mQuote	= ($this->_bQuoteMode === null) ? $this->_getQuotableCharacters() : $this->_bQuoteMode;
		return File_CSV::buildLine($oRecord->getProcessedRecord(), $this->_sDelimiter, $this->_sQuote, $this->_sEscape, $this->_getEscapableCharacters(), $mQuote);
	}

	public function render()
	{
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
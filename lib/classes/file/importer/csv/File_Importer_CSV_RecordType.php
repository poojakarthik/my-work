<?php
class File_Importer_CSV_RecordType extends File_Importer_RecordType
{
	protected	$_sEscape		= '\\';
	protected	$_sQuote		= '"';
	protected	$_sDelimiter	= ',';
	
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
	
	public function setEscape($sEscape)
	{
		$this->_sEscape	= (string)$sEscape;
		return $this;
	}
	
	public function parseLine($sLine)
	{
		//Log::getLog()->log($sLine);
		//Log::getLog()->log(print_r(File_CSV::parseLine($sLine, $this->_sDelimiter, $this->_sQuote, $this->_sEscape), true));
		return File_CSV::parseLine($sLine, $this->_sDelimiter, $this->_sQuote, $this->_sEscape);
	}
	
	public static function factory()
	{
		return new self();
	}
}
?>
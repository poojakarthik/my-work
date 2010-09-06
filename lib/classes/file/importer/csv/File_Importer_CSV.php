<?php
class File_Importer_CSV extends File_Importer
{
	protected	$_sEscape		= '\\';
	protected	$_sQuote		= '"';
	protected	$_sDelimiter	= ",";
	protected	$_sNewLine		= "\n";
	
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
	
	public function setNewLine($sNewLine)
	{
		$this->_sNewLine	= (string)$sNewLine;
		return $this;
	}
}
?>
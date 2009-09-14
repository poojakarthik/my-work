<?php

class File_CSV
{
	protected	$_aColumns	= array();
	protected	$_aRows		= array();
	
	protected	$_sDelimiter;
	protected	$_sQuote;
	protected	$_sEscape;
	
	public function __construct($sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns=null)
	{
		$this->setDelimiter($sDelimiter);
		$this->setQuote($sQuote);
		$this->setEscape($sEscape);
		
		if ($aColumns)
		{
			$this->setColumns($aColumns);
		}
	}
	
	public function setDelimiter($sDelimiter)
	{
		$this->_sDelimiter	= $sDelimiter;
	}
	
	public function setQuote($sQuote)
	{
		$this->_sQuote	= $sQuote;
	}
	
	public function setEscape($sEscape)
	{
		$sEscape		= (string)$sEscape;
		$this->_sEscape	= ($sEscape) ? $sEscape[0] : '';
	}
	
	public function setColumns($aColumns)
	{
		$this->_aColumns	= array();
		$iIndex				= 0;
		foreach ($aColumns as $sColumn)
		{
			if (is_scalar($sColumn))
			{
				$this->_aColumns[]	= $sColumn;
			}
			else
			{
				throw new Exception("Column name for index {$iIndex} is not a scalar value! (Type: ".gettype($sColumn)."; Value: '{$sColumn}')");
			}
		}
	}
	
	public function addRow($aRow)
	{
		if (is_array($aRow))
		{
			$this->_aRows[]	= $aRow;
		}
		else
		{
			throw new Exception("Parameter \$aRow is not an array");
		}
	}
	
	public function save($bIncludeHeaderRow=true)
	{
		$sOutput	= '';
		
		// Add Header Row
		if ($bIncludeHeaderRow)
		{
			$aRowOutput	= array();
			foreach ($this->_aColumns as $sColumn)
			{
				$aRowOutput[]	= $this->_prepare($sColumn);
			}
			$sOutput	.= implode($this->_sDelimiter, $aRowOutput)."\n";
		}
		
		// Add Content
		foreach ($this->_aRows as $iRow=>$aRow)
		{
			$aRowOutput	= array();
			foreach ($this->_aColumns as $sColumn)
			{
				$aRowOutput[]	= $this->_prepare((array_key_exists($sColumn, $aRow)) ? $aRow[$sColumn] : '');
			}
			$sOutput	.= implode($this->_sDelimiter, $aRowOutput)."\n";
		}
		
		return $sOutput;
	}
	
	public function saveToFile($sPath, $bIncludeHeaderRow=true)
	{
		if (!is_writable($sPath))
		{
			throw new Exception("Unable to export to path '{$sPath}': Path is not writable");
		}
		elseif (!@file_put_contents($sPath, $this->save($bIncludeHeaderRow)))
		{
			throw new Exception("Unable to export to path '{$sPath}': There was an unknown error writing to the path");
		}
		
		return true;
	}
	
	public function importArray($aArray)
	{
		if (is_array($aArray))
		{
			foreach ($aArray as $sColumn=>$sValue)
			{
				
			}
		}
		else
		{
			throw new Exception("Parameter \$aArray is not an array! (Type: ".gettype($aArray)."; Value: '{$aArray}')");
		}
	}
	
	protected function _escape($mValue)
	{
		// Escape any instances of the escape character
		$mValue	= str_replace($this->_sEscape, $this->_sEscape.$this->_sEscape, $mValue);
		
		// Escape and instances of the quote string
		$mValue	= str_replace($this->_sQuote, $this->_sEscape.$this->_sQuote, $mValue);
		
		return $mValue;
	}
	
	protected function _quote($mValue)
	{
		return "{$this->_sQuote}{$mValue}{$this->_sQuote}";
	}
	
	protected function _prepare($mValue)
	{
		return $this->_quote($this->_escape($mValue));
	}
}

?>
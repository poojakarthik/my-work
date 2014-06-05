<?php

class File_CSV implements Iterator
{
	protected	$_aColumns	= array();
	protected	$_aRows		= array();

	protected	$_sDelimiter;
	protected	$_sQuote;
	protected	$_sEscape;
	protected	$_sNewLine	= "\n";

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
		$sDelimiter			= (string)$sDelimiter;
		$this->_sDelimiter	= ($sDelimiter) ? $sDelimiter[0] : '';
	}

	public function setQuote($sQuote)
	{
		$sQuote			= (string)$sQuote;
		$this->_sQuote	= ($sQuote) ? $sQuote[0] : '';
	}

	public function setEscape($sEscape)
	{
		$sEscape		= (string)$sEscape;
		$this->_sEscape	= ($sEscape) ? $sEscape[0] : '';
	}

	public function setNewLine($sNewLine)
	{
		$this->_sNewLine	= (string)$sNewLine;
	}

	protected function _getSpecialCharacters()
	{
		return array($this->_sNewLine, $this->_sDelimiter, $this->_sQuote);
	}

	protected function _getQuotableCharacters()
	{
		return array($this->_sNewLine, $this->_sDelimiter, $this->_sEscape, $this->_sQuote, ' ');
	}

	public function setColumns($aColumns)
	{
		// Update the Columns definition
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
				$aRowOutput[]	= self::_prepare($sColumn, $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $this->_getQuotableCharacters());
			}
			$sOutput	.= implode($this->_sDelimiter, $aRowOutput).$this->_sNewLine;
		}

		// Add Content
		foreach ($this->_aRows as $iRow=>$aRow)
		{
			$aRowOutput	= array();
			foreach ($this->_aColumns as $sColumn)
			{
				$aRowOutput[]	= self::_prepare((array_key_exists($sColumn, $aRow)) ? $aRow[$sColumn] : '', $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $this->_getQuotableCharacters());
			}
			$sOutput	.= implode($this->_sDelimiter, $aRowOutput).$this->_sNewLine;
		}

		return $sOutput;
	}

	public function saveToFile($sPath, $bIncludeHeaderRow=true)
	{
		if (!@file_put_contents($sPath, $this->save($bIncludeHeaderRow)))
		{
			throw new Exception("Unable to export to path '{$sPath}': There was an unknown error writing to the path");
		}

		return true;
	}

	public function importArray($aArray)
	{
		if (is_array($aArray))
		{
			if (count($aArray) > 0)
			{
				// Set Columns
				$this->setColumns(array_keys($aArray[0]));

				// Set Data
				foreach ($aArray as $aRow)
				{
					$this->addRow($aRow);
				}
			}
		}
		else
		{
			throw new Exception("Parameter \$aArray is not an array! (Type: ".gettype($aArray)."; Value: '{$aArray}')");
		}
	}

	public function importFile($sPath, $bHasHeader=false, $bImportHeader=false)
	{
		if (!is_readable($sPath))
		{
			throw new Exception("Unable to import from path '{$sPath}': Path is not readable");
		}
		if (($sDataString = @file_get_contents($sPath)) === false)
		{
			throw new Exception("Unable to import from path '{$sPath}': There was an unknown error reading from the path");
		}

		return $this->importFileAsString($sDataString, $bHasHeader, $bImportHeader);
	}

	public function importFileAsString($sDataString, $bHasHeader=false, $bImportHeader=false)
	{
		$aLines	= explode($this->_sNewLine, $sDataString);

		Log::getLog()->log("Importing file: ".count($aLines)." lines");

		// Parse the first row as a Header
		if ($bHasHeader)
		{
			Log::getLog()->log("... has header");

			if ($sHeader = trim(reset($aLines), "\n\r\0\x0B"))
			{
				if ($bImportHeader)
				{
					Log::getLog()->log("... importing header: {$sHeader}");
					$aColumns	= self::parseLine($sHeader, $this->_sDelimiter, $this->_sQuote, $this->_sEscape);
					$this->setColumns($aColumns);
				}
			}

			// Remove the first line
			array_shift($aLines);
		}

		Log::getLog()->log("... importing rows: ".count($aLines)." lines");

		// Import each row
		while (($sRow = current($aLines)) !== false)
		{
			Log::getLog()->log("... ROW: {$sRow}");

			$aRow			= self::parseLine($sRow, $this->_sDelimiter, $this->_sQuote, $this->_sEscape);
			$aFormattedRow	= array();
			foreach ($aRow as $iIndex=>$mValue)
			{
				// Try to match to a known column, otherwise just append to the end of the row
				if (array_key_exists($iIndex, $this->_aColumns))
				{
					$aFormattedRow[$this->_aColumns[$iIndex]]	= $mValue;
				}
				else
				{
					$aFormattedRow[]	= $mValue;
				}
			}

			$this->addRow($aFormattedRow);
			next($aLines);
		}

		return true;
	}

	public function toArray()
	{
		$aOutput	= array();

		foreach ($this->_aRows as $iRow=>$aRow)
		{
			$aRowOutput	= array();
			foreach ($this->_aColumns as $sColumn)
			{
				$aRowOutput[$sColumn]	= self::_prepare((array_key_exists($sColumn, $aRow)) ? $aRow[$sColumn] : '', $this->_sQuote, $this->_sEscape, $this->_getSpecialCharacters(), $this->_getQuotableCharacters());
			}
			$aOutput[]	= $aRowOutput;
		}

		return $aOutput;
	}

	public function current()
	{
		return current($this->_aRows);
	}

	public function key()
	{
		return key($this->_aRows);
	}

	public function next()
	{
		return next($this->_aRows);
	}

	public function rewind()
	{
		return reset($this->_aRows);
	}

	public function valid()
	{
		return (key($this->_aRows) !== null);
	}

	public function count()
	{
		return count($this->_aRows);
	}

	public static function parseLineRFC4180($sLine) {
		$sEncoding = mb_detect_encoding($sLine);
		$iLineLength = mb_strlen($sLine, $sEncoding);

		$aParsed = array();
		$bQuoted = false;
		$bEscaped = false;
		$sField = '';
		for ($i = 0; $i < $iLineLength; $i++) {
			$sCharacter = mb_substr($sLine, $i, 1, $sEncoding);
			if ($sCharacter === self::RFC4180_DELIMITER) {
				if (!$bQuoted || $bEscaped) {
					$aParsed[] = $sField;
					$sField = '';
					$bQuoted = false;
					continue;
				}
			}
			if ($sCharacter === self::RFC4180_QUOTE) {
				if (!$sField) {
					$bQuoted = true;
					continue;
				}
				if (!$bQuoted) {
					// NOTE: RFC4180 says that quotes as data can only appear in quoted fields, and as long as they're escaped
					// It also says to be liberal in what we accept, so we could choose to relax this rule
					throw new DomainException('Illegal quote data character in an unquoted CSV field');
				}
				if (!$bEscaped) {
					$bEscaped = true;
					continue;
				}
				$bEscaped = false;
			}
			$sField .= $sCharacter;
		}
		// Clean up last field
		$aParsed[] = $sField;
		return $aParsed;
	}

	// Mimic the fgetcsv() function from PHP 5.3
	public static function parseLine($sLine, $sDelimiter=',', $sQuote='"', $sEscape='\\')
	{
		$sDelimiter	= ($sDelimiter)	? $sDelimiter[0]	: ',';
		$sQuote		= ($sQuote)		? $sQuote[0]		: '';
		$sEscape	= ($sEscape)	? $sEscape[0]		: '';

		// Parse the Line character-by-character
		$bEscaped	= false;
		$bQuoted	= false;
		$aLine		= array();
		$sField		= '';
		for ($i=0, $iLineLength=strlen($sLine); $i < $iLineLength; $i++)
		{
			$sCharacter	= $sLine[$i];
			switch($sCharacter)
			{
				case $sEscape:
					if ($bEscaped)
					{
						// Escape Character is Escaped
						$sField	.= $sEscape;
					}
					$bEscaped	= !$bEscaped;
					break;

				case $sQuote:
					if ($bEscaped)
					{
						// Quote Character is Escaped
						$sField		.= $sQuote;
						$bEscaped	= false;
					}
					else
					{
						$bQuoted	= !$bQuoted;
					}
					break;

				case $sDelimiter:
					if ($bEscaped)
					{
						// Delimiter Character is Escaped
						$sField		.= $sDelimiter;
						$bEscaped	= false;
					}
					elseif ($bQuoted)
					{
						// Delimiter Character is Quoted
						$sField		.= $sDelimiter;
					}
					else
					{
						// End of Field
						$aLine[]	= $sField;
						$sField		= '';
					}
					break;

				default:
					// Not a special character
					$sField		.= $sCharacter;
					$bEscaped	= false;
					break;
			}
		}

		// Finish the current Field
		$aLine[]	= $sField;

		// Return the Array representing this Line
		return $aLine;
	}

	const RFC4180_DELIMITER = ',';
	const RFC4180_QUOTE = '"';
	public static function buildLineRFC4180($aRecord) {
		return self::buildLine($aRecord, self::RFC4180_DELIMITER, self::RFC4180_QUOTE, self::RFC4180_QUOTE, array(self::RFC4180_QUOTE), array(self::RFC4180_DELIMITER, self::RFC4180_QUOTE));
	}

	public static function buildLine($aRecord, $sDelimiter=',', $sQuote='"', $sEscape='\\', $aSpecialCharacters=array(), $mQuote=false)
	{
		$aPreparedRecords	= array();
		foreach ($aRecord as $mValue)
		{
			$aPreparedRecords[]	= self::_prepare($mValue, $sQuote, $sEscape, $aSpecialCharacters, $mQuote);
		}

		return implode($sDelimiter, $aPreparedRecords);
	}

	protected static function _prepare($mValue, $sQuote='"', $sEscape='\\', $aSpecialCharacters=array(), $mQuote=false)
	{
		return self::_quote(self::_escape($mValue, $sEscape, $aSpecialCharacters), $sQuote, $mQuote);
	}

	protected static function _escape($mValue, $sEscape='\\', $aSpecicalCharacters=array()) {
		// Escape the escape character
		$mValue	= str_replace($sEscape, $sEscape.$sEscape, $mValue);

		// Escape all other special characters
		foreach ($aSpecicalCharacters as $sSpecialCharacter) {
			if ($sSpecialCharacter !== $sEscape) {
				// Escape special characters that are not the Escape character (we've already escaped it)
				$mValue	= str_replace($sSpecialCharacter, $sEscape.$sSpecialCharacter, $mValue);
			}
		}

		return $mValue;
	}

	protected static function _quote($mValue, $sQuote, $mQuote=false)
	{
		$bQuote	= (is_bool($mQuote) && $mQuote);
		if (is_array($mQuote))
		{
			// Only quote if one of these characters is in the string
			foreach ($mQuote as $sQuotableCharacter)
			{
				$bQuote	= (stripos($mValue, $sQuotableCharacter) === false) ? $bQuote : true;
			}
		}

		if ($bQuote)
		{
			$mValue	= $sQuote.$mValue.$sQuote;
		}

		return $mValue;
	}
}

?>
<?php
class Correspondence_Logic_Source_Csv extends Correspondence_Logic_Source
{

	protected $_aCsv;



	public function __construct($sCsv = null)
	{
		parent::__construct(CORRESPONDENCE_SOURCE_TYPE_CSV);
		$this->_aCsv = trim($sCsv)==null?null:explode("\n",trim($sCsv));
	}

	function getData($bPreprinted, $aAdditionalColumns = array(), $bNoDataOk = false)
	{
		if (count($this->_aCsv)>0)
		{
			$this->_bPreprinted = $bPreprinted;
			$this->_aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);
			$this->_aAdditionalColumns = $aAdditionalColumns;
			$this->iLineNumber = 1;
			foreach($this->_aCsv as $sLine)
			{
				$aLine = self::parseLineHashed(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\');
				$this->processCorrespondenceRecord($aLine);
				$this->iLineNumber++;
			}
			if ($this->_bValidationFailed)
			{
				$this->processValidationErrors();
			}

		}
		else if (!$bNoDataOk)
		{
			throw new Correspondence_DataValidation_Exception(null, null, true);
		}
		return $this->_aCorrespondence;
	}


	public function parseLineHashed($sLine, $sDelimiter=',', $sQuote='"', $sEscape='\\')
	{
		$sDelimiter	= ($sDelimiter)	? $sDelimiter[0]	: ',';
		$sQuote		= ($sQuote)		? $sQuote[0]		: '';
		$sEscape	= ($sEscape)	? $sEscape[0]		: '';

		// Parse the Line character-by-character
		$bEscaped	= false;
		$bQuoted	= false;
		$aLine = array('standard_fields'=>array(), 'additional_fields'=>array());
		$sField		= '';

		$iFieldIndex = 0;
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
						if ($iFieldIndex>=count($this->_aInputColumns))
						{
							$sFieldName = $iFieldIndex<$this->getColumnCount()?$this->_aAdditionalColumns[$iFieldIndex]:$iFieldIndex;
							$aLine['additional_fields'][$sFieldName] = $sField;
						}
						else
						{
							$sFieldName = $iFieldIndex<count($this->_aInputColumns)?$this->_aInputColumns[$iFieldIndex]:$this->_aColumns[$iFieldIndex];
							$aLine['standard_fields'][$sFieldName]	= $sField;
						}
						$iFieldIndex++;
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
		if ($iFieldIndex>=count($this->_aInputColumns))
		{
			$sFieldName = $iFieldIndex<$this->getColumnCount()?$this->_aAdditionalColumns[$iFieldIndex]:$iFieldIndex;
			$aLine['additional_fields'][$sFieldName]= $sField;
		}
		else
		{
			$sFieldName = $iFieldIndex<count($this->_aInputColumns)?$this->_aInputColumns[$iFieldIndex]:$this->_aColumns[$iFieldIndex];
			$aLine['standard_fields'][$sFieldName]	= $sField;
		}

		// Return the Array representing this Line
		return $aLine;
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}





}
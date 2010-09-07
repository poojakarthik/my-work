<?php
class Correspondence_Logic_Source_Csv extends Correspondence_Logic_Source
{

	protected $_aCsv;

public function __construct($sCsv = null)
{
	parent::__construct(Correspondence_Logic_Source::CSV);
	$this->_aCsv = explode("\n",$sCsv);

}


function getData($bPreprinted, $aAdditionalColumns = array())
{

	$aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);
	$aCorrespondence = array();
	foreach($this->_aCsv as $sLine)
	{
		$aLine = self::parseLineHashed(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns, $aAdditionalColumns);

		$aCorrespondence[] = new Correspondence_Logic($aLine);
	}
	return $aCorrespondence;
}

public function __get($sField)
{
	return $this->_oDO->$sField;
}

// Mimic the fgetcsv() function from PHP 5.3
	public function parseLineHashed($sLine, $sDelimiter=',', $sQuote='"', $sEscape='\\', $aFieldNames, $aAdditionalFieldNames)
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
						if ($iFieldIndex>=count($aFieldNames))
						{
								$aLine['additional_fields'][$aAdditionalFieldNames[$iFieldIndex]] = $sField;
						}
						else
						{
							$sFieldName = $iFieldIndex<count($this->_aInputColumns)?$this->_aInputColumns[$iFieldIndex]:$aFieldNames[$iFieldIndex];
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
		if ($iFieldIndex>=count($aFieldNames))
		{
				$aLine['additional_fields'][$aAdditionalFieldNames[$iFieldIndex]]= $sField;
		}
		else
		{
			$sFieldName = $iFieldIndex<count($this->_aInputColumns)?$this->_aInputColumns[$iFieldIndex]:$aFieldNames[$iFieldIndex];
			$aLine['standard_fields'][$sFieldName]	= $sField;
		}

		// Return the Array representing this Line
		return $aLine;
	}





}
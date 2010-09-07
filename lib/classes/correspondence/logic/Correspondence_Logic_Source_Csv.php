<?php
class Correspondence_Logic_Source_Csv extends Correspondence_Logic_Source
{

	protected $_aCsv;
	protected $_aLines = array();


	public function __construct($sCsv = null)
	{
		parent::__construct(Correspondence_Logic_Source::CSV);
		$this->_aCsv = explode("\n",trim($sCsv));
	}


	function getData($bPreprinted, $aAdditionalColumns = array())
	{
		$aCorrespondence = array();
		if (count($this->_aCsv)>0)
		{
			$aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);


			if (count($aColums) + count($aAdditionalColumns)!= count($this->_aCsv[0]))
			{
				$this->_bValidationFailed = true;
				return 'The number of columns of the supplied data and the correspondence template do not match.';
			}

			foreach($this->_aCsv as $sLine)
			{
				$aLine = self::parseLineHashed(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns, $aAdditionalColumns);
				$bValid = $this->validate($aLine);
				if (!$bValid)
					$this->_bValidationFailed = true;
				if (!$this->_bValidationFailed)
				{
					$aCorrespondence[] = new Correspondence_Logic($aLine);
				}
				$this->_aLines[]=$aLine;

			}
			if ($this->_bValidationFailed)
			{
				//create data file with error messages
				//generate email
				//return a summary error message and url for the error file

			}

		}

			return $aCorrespondence;

	}



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

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}





}
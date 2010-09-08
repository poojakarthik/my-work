<?php
class Correspondence_Logic_Source_Csv extends Correspondence_Logic_Source
{

	protected $_aCsv;
	protected $_aLines = array();
	protected $_errorReport;
	protected $_oTemplate;


	public function __construct($sCsv = null)
	{
		parent::__construct(CORRESPONDENCE_SOURCE_TYPE_CSV);
		$this->_aCsv = explode("\n",trim($sCsv));
		$this->_errorReport = new File_Exporter_CSV();
	}

	public function setTemplate($oTemplate)
	{
		$this->_oTemplate = $oTemplate;
	}


	function getData($bPreprinted, $aAdditionalColumns = array())
	{
		$aCorrespondence = array();
		if (count($this->_aCsv)>0)
		{
			$aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);


			if (count($aColumns) + count($aAdditionalColumns)!= $this->columnCount($this->_aCsv[0]))
			{
				$this->_bValidationFailed = true;
			}
			$this->_aReport['column_count']['required']	= count($aColumns) + count($aAdditionalColumns);
			$this->_aReport['column_count']['supplied']	= $this->columnCount($this->_aCsv[0]);
			$iLineNumber = 1;
			foreach($this->_aCsv as $sLine)
			{
				$aLine = self::parseLineHashed(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\', $aColumns, $aAdditionalColumns);
				$bValid = $this->validateDataRecord($aLine);
				if (!$bValid)
					$this->_bValidationFailed = true;
				if (!$this->_bValidationFailed)
				{
					$aCorrespondence[] = new Correspondence_Logic($aLine);
				}

				$this->_aLines[]=$aLine;
				foreach ($aLine['validation_errors'] as $sErrorType=>$sMessage)
				{
					$this->_aReport[$sErrorType][]=$iLineNumber;
				}
				if (count($aLine['validation_errors'])==0)
					$this->_aReport['success'][]= $iLineNumber;
				$iLineNumber++;
			}
			if ($this->_bValidationFailed)
			{
				//create data file with error messages

				$oRecordType = File_Exporter_RecordType::factory();

				foreach($aLine as $key =>$aLinePart)
				{
					if ($key == 'validation_errors')
					{
						$oRecordType->addField($key, File_Exporter_Field::factory());
					}
					else
					{
						foreach($aLinePart as $key2=>$value)
							$oRecordType->addField($key2, File_Exporter_Field::factory());
					}
				}

				$this->_errorReport->registerRecordType('detail', $oRecordType);

				foreach($this->_aLines as $aLine)
				{
					$this->addErrorRecord($aLine);
				}
				$sPath = FILES_BASE_PATH.'temp/';
				$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());


				$sFilename	= $this->_oTemplate->template_code
				.'.'
				.$sTimeStamp
				.'.'
				.'error_report'
				.'.csv'
				;
				$this->_errorReport->renderToFile($sPath.$sFilename);
				throw new Correspondence_DataValidation_Exception($this->_aReport, $sPath.$sFilename);
				//generate email
				//return a summary error message and url for the error file


			}

		}

			return $aCorrespondence;

	}

	public function columnCount($sLine)
	{
		return count(File_CSV::parseLine(rtrim($sLine,"\r\n"), $sDelimiter=',', $sQuote='"', $sEscape='\\'));
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


	public function addErrorRecord($aLine)
	{
		$oRecord	= $this->_errorReport->getRecordType('detail')->newRecord();

		foreach ($aLine as $sField=>$aValue)
		{

			if ($sField == 'validation_errors')
			{
				$oRecord->$sField = implode(';', $aValue);

			}
			else
			{
				foreach ($aValue as $key=>$mValue)
				{
					$oRecord->$key = $mValue;
				}
			}
		}
		$this->_errorReport->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);

	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}





}
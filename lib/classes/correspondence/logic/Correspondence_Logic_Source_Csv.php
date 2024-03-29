<?php
class Correspondence_Logic_Source_CSV extends Correspondence_Logic_Source
{

	protected $_aCsv;
	protected $_sFileName;
	protected $_sTmpPath;
	protected $_oDO;
	protected $_oFileImport;


	public function __construct($oTemplate)
	{
		parent::__construct(Correspondence_Source::getForTemplateId($oTemplate->id),$oTemplate);
	}

	function setData($aFileInfo)
	{
		$this->_sTmpPath =$aFileInfo['tmp_name'];
		$this->_sFileName = $aFileInfo['name'];
		/*$aFileImport = File_Import::getForFileName($this->_sFileName);
		foreach ($aFileImport as $oFileImport)
		{
			if ($oFileImport->FileName == $this->_sFileName &&
				$oFileImport->FileType == RESOURCE_TYPE_FILE_IMPORT_CORRESPONDENCE_YELLOWBILLING_CSV)
			{
				throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::DUPLICATE_FILE);
			}
		}*/
		$sCsv = file_get_contents($this->_sTmpPath);
		$this->_aCsv = trim($sCsv)==null?null:explode("\n",trim($sCsv));
		return $this->import();
	}

	protected function _getCorrespondence()
	{
		if (count($this->_aCsv)>0)
		{
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
		else
		{
			throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::NODATA);
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

	public function import()
	{
		$oFileImport = File_Import::import($this->_sTmpPath, RESOURCE_TYPE_FILE_IMPORT_CORRESPONDENCE_YELLOWBILLING_CSV, Carrier_Config::get()->vendor_carrier_id, $this->_sFileName);
		return $oFileImport->id;
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField!=null?$this->_oDO->$sField:parent::__get($sField);
	}

	public static function getFilePath()
	{
		return FILES_BASE_PATH."import/".$intCarrier.'/'.GetConstantName(RESOURCE_TYPE_FILE_IMPORT_CORRESPONDENCE_YELLOWBILLING_CSV, 'resource_type').'/';
	}
}
?>
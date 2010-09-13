<?php

class Correspondence_Logic_Source_System extends Correspondence_Logic_Source
{

	protected $_aData;

	public function __construct($aData)
	{
		parent::__construct(CORRESPONDENCE_SOURCE_TYPE_SYSTEM);
		$this->_aData = $aData;
	}

	public function getData($bPreprinted, $aAdditionalColumns = array(), $bNoDataOk = false)
	{
		if (count($this->_aData)>0)
		{
			$this->_bPreprinted = $bPreprinted;
			$this->_aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);
			$this->_aAdditionalColumns = $aAdditionalColumns;
	 		$this->iLineNumber = 1;
			foreach ($this->_aData as $aDataRecord)
			{
	 			$aRecord = array('standard_fields'=>array(), 'additional_fields'=>array());
	 			foreach ($aDataRecord as $sField => $mValue)
				{
					if (in_array($sField,$this->_aColumns))
					{
						$aRecord['standard_fields'][$sField]= $mValue;
					}
					else
					{
						$aRecord['additional_fields'][$sField] = $mValue;
					}
				}
				$this->processCorrespondenceRecord($aRecord);
				$this->iLineNumber++;
	 		}

			if ($this->_bValidationFailed)
			{
				$this->processValidationErrors();
			}
		}
		else if (!$bNoDataOk)
		{
			throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::NODATA);
		}


		return $this->_aCorrespondence;
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}


}



?>
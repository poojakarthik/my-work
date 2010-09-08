<?php

class Correspondence_Logic_Source_System extends Correspondence_Logic_Source
{

	protected $_aData;

	public function __construct($aData)
	{
		parent::__construct(CORRESPONDENCE_SOURCE_TYPE_SYSTEM);
		$this->_aData = $aData;
	}

	public function getData($bPreprinted, $aAdditionalColumns = array())
	{
		$this->_aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);

 		$this->iLineNumber = 1;
		foreach ($this->_aData as $aDataRecord)
		{
 			if ($this->iLineNumber == 1)
 				$this->columnCountValidation($aAdditionalColumns, $aDataRecord);

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


		return $this->_aCorrespondence;
	}

	public function columnCount($mDataRecord)
	{
		return count($mDataRecord);
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}


}



?>
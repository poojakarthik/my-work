<?php

class Correspondence_Logic_Source_System extends Correspondence_Logic_Source
{

	protected $_aData;

	public function __construct($oTemplate)
	{
		parent::__construct(Correspondence_Source::getForTemplateId($oTemplate->id),$oTemplate);
	}

	public function setData($aData)
	{
		$this->_aData = $aData;
		return null;
	}

	public function _getCorrespondence()
	{
		if (count($this->_aData)>0)
		{
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
		}
		else
		{
			throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::NODATA);
		}


	}

	public function __get($sField)
	{
		return $this->_oDO->$sField!=null?$this->_oDO->$sField:parent::__get($sField);
	}


}



?>
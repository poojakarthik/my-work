<?php
class Correspondence_Logic_Source_Sql extends Correspondence_Logic_Source
{

	protected $_oDO;

	public function __construct($oTemplate)
	{
		parent::__construct(Correspondence_Source::getForTemplateId($oTemplate->id),$oTemplate);
		$this->_oDO = Correspondence_Source_Sql::getForCorrespondenceSourceId(parent::__get('id'));
	}

	public function setData($mData)
	{
		return false;
	}

	public function _getCorrespondence()
	{
		$this->db = DataAccess::getDataAccess();
		$result = $this->db->refMysqliConnection->query($this->sql_syntax);
		if (!$result)
		{
			throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::SQLERROR);
		}
		else if ($result->num_rows == 0)
		{
			throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::NODATA);
		}

		while($row = $result->fetch_array(MYSQLI_ASSOC))
 		{
 			$aRecord = array('standard_fields'=>array(), 'additional_fields'=>array());
			$iFieldIndex = 0;
 			foreach ($row as $sField => $mValue)
			{
				if ($iFieldIndex<count($this->_aColumns))
				{
					$sFieldName = $iFieldIndex<count($this->_aInputColumns)?$this->_aInputColumns[$iFieldIndex]:$aFieldNames[$iFieldIndex];

					$aRecord['standard_fields'][$sFieldName]= $mValue;
				}
				else
				{
					$sFieldName = $iFieldIndex<$this->getColumnCount()?$this->_aAdditionalColumns[$iFieldIndex]:$iFieldIndex;
					$aRecord['additional_fields'][$sFieldName] = $mValue;
				}
				$iFieldIndex++;
			}
			$this->processCorrespondenceRecord($aRecord);
			$this->iLineNumber++;
 		}

		if ($this->_bValidationFailed)
		{
			$this->processValidationErrors();
		}


	}



/*	public static function getForCorrespondenceSourceId($iId)
	{
		$oORM = Correspondence_Source_Sql::getForCorrespondenceSourceId($iId);
		return new self ($oORM);
	}*/

	public function __get($sField)
	{
		return $this->_oDO->$sField!=null?$this->_oDO->$sField:parent::__get($sField);
	}


}


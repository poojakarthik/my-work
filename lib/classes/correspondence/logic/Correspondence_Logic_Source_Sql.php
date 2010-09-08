<?php
class Correspondence_Logic_Source_Sql extends Correspondence_Logic_Source
{

	protected $_oDO;

	public function __construct($mDefinition)
	{
		parent::__construct($mDefinition->correspondence_source_id);
		$this->_oDO = $mDefinition;
	}

	public function getData($bPreprinted, $aAdditionalColumns = array())
	{
		$this->_aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);

		$this->db = DataAccess::getDataAccess();
		$result = $this->db->refMysqliConnection->query($this->sql_syntax);
		if (!$result)
		{
			throw new Correspondence_DataValidation_Exception(null, null, true, true);
		}
		else if ($result->num_rows == 0)
		{
			throw new Correspondence_DataValidation_Exception(null, null, true);
		}

 		$this->iLineNumber = 1;
		while($row = $result->fetch_array(MYSQLI_ASSOC))
 		{
 			if ($this->iLineNumber == 1)
 				$this->columnCountValidation($aAdditionalColumns, $row);
 			$aRecord = array('standard_fields'=>array(), 'additional_fields'=>array());
			$iFieldIndex = 0;
 			foreach ($row as $sField => $mValue)
			{
				if ($iFieldIndex<= count($this->_aColumns))
				{
					$sFieldName = $iFieldIndex<count($this->_aInputColumns)?$this->_aInputColumns[$iFieldIndex]:$aFieldNames[$iFieldIndex];

					$aRecord['standard_fields'][$sFieldName]= $mValue;
				}
				else
				{
					$aLine['additional_fields'][$aAdditionalFieldNames[$iFieldIndex]] = $sField;
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

		return $this->_aCorrespondence;
	}

	public function columnCount($mDataRecord)
	{
		return count($mDataRecord);
	}

	public static function getForCorrespondenceSourceId($iId)
	{
		$oORM = Correspondence_Source_Sql::getForCorrespondenceSourceId($iId);
		return new self ($oORM);
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}


}


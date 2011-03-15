<?php
class Correspondence_Logic_Source_SQLAccounts extends Correspondence_Logic_Source
{

	protected $_oDO;

        protected $_aAccounts;

	public function __construct($oTemplate)
	{
		parent::__construct(Correspondence_Source::getForTemplateId($oTemplate->id),$oTemplate);
		$this->_oDO = Correspondence_Source_SqlAccounts::getForCorrespondenceSourceId(parent::__get('id'));
	}

	public function setData($mData)
	{
		$this->_aAccounts = $mData;
                return null;
	}

	public function _getCorrespondence()
	{
		$this->db 	= DataAccess::getDataAccess();

                 $sSql = str_replace("<ACCOUNTS>", implode(",", $this->_aAccounts), $this->sql_syntax);

		$result 	= $this->db->refMysqliConnection->query($sSql);
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
				if ($iFieldIndex < count($this->_aColumns))
				{
					$sFieldName	= ($iFieldIndex < count($this->_aInputColumns) ? $this->_aInputColumns[$iFieldIndex] : $iFieldIndex);
					$aRecord['standard_fields'][$sFieldName]	= $mValue;
				}
				else
				{
					$sFieldName	= ($iFieldIndex < $this->getColumnCount() ? $this->_aAdditionalColumns[$iFieldIndex] : $iFieldIndex);
					$aRecord['additional_fields'][$sFieldName]	= $mValue;
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

                if ($this->enforce_account_set)
                {
                    $aCorrespondenceAccounts = array();
                    foreach ($this->_aCorrespondence as $aCorrespondence)
                    {
                        foreach($aCorrespondence as $oCorrespondence)
                        {
                            $aCorrespondenceAccounts[] = $oCorrespondence->account_id;
                        }
                    }

                    if (count(array_diff($this->_aAccounts,$aCorrespondenceAccounts )) > 0)
                        throw new Correspondence_DataValidation_Exception(Correspondence_DataValidation_Exception::DATAMISMATCH);
                } 

                return $this->_aCorrespondence;
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField!==null?$this->_oDO->$sField:parent::__get($sField);
	}
}
?>
<?php
class Correspondence_Logic_Source_Sql extends Correspondence_Source
{

	protected $_oDO;

	private function __construct($mDefinition)
	{
		$this->_oDO = $mDefinition;
	}

	public function getData($bPreprinted, $aAdditionalColumns = array())
	{
		$aColumns = Correspondence_Logic::getStandardColumns($bPreprinted);
		if ($this->_validateQuery())
		{
			$aCorrespondence = array();
			$this->db = DataAccess::getDataAccess();
			if($result = $this->db->refMysqliConnection->query($this->sql_syntax))
			{
		 		while($row = $result->fetch_array(MYSQLI_ASSOC))
		 		{

		 			$aRecord = array('standard_fields'=>array(), 'additional_fields'=>array());
					foreach ($row as $sField => $mValue)
					{
						if (in_array($sField,$aColumns ))
						{

							$aRecord['standard_fields'][$sField]	= $mValue;
						}
						else
						{
							$aRecord['additional_fields'][$sField] = $mValue;
						}
					}
					$aCorrespondence[] = new Correspondence_Logic($aLine);
		 		}
			}


			return $aCorrespondence;
		}
		else
		{
			throw new Exception('invalid query supplied for correspondence source');
		}
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

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}

}


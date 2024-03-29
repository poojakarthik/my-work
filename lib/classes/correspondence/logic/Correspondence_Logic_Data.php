<?php


class Correspondence_Logic_Data
{
	protected $_oDO;
	protected $_oColumn;

	public function __construct($mDetails)
	{
		$this->_oDO = is_array($mDetails)?new Correspondence_Data($mDetails):$mDetails;
		$this->_oColumn = Correspondence_Logic_Template_Column::getForData($this);

	}

	public function getColumnName()
	{
		return $this->_oColumn->name;
	}

	public function save()
	{
		$this->_oDO->save();
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}


	public static function getForCorrespondence($oCorrespondence)
	{
		$aORM = Correspondence_Data::getForCorrespondenceId($oCorrespondence->id);
		$aCorrespondenceData = array();
		foreach ($aORM as $oORM)
		{
			$x = new Correspondence_Logic_Data($oORM);
			$fieldName = $x->getColumnName();
			$aCorrespondenceData[$fieldName] = $x;
		}
		return $aCorrespondenceData;
	}
}
?>
<?php
class Correspondence_Logic_Carrier
{

	protected $_oDO;

	public function __construct($mDefinition, $aColumns = array())
	{
		is_numeric($mDefinition)?$this->_oDO = Correspondence_Carrier::getForId($mDefinition):$this->_oDO = $mDefinition;
		$this->_oDO->setSaved();
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
?>
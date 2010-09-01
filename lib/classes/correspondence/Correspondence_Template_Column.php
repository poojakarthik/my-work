<?php

class Correspondence_Template_Column
{

	protected $_oDO;
	protected $_oTemplate;

	public function __construct($mDefinition, $oTemplate = null)
	{
		$this->_oTemplate = $oTemplate;
		$this->_oDO =is_array($mDefinition)?new Correspondence_Template_Column_ORM($mDefinition):$mDefinition;

	}

	public function save()
	{
		$this->correspondence_template_id = $this->_oTemplate->id;
		$this->_oDO->save();

	}



	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}

	public static function getForData($oData)
	{
		return new self(Correspondence_Template_Column_ORM::getForId($oData->correspondence_template_column_id));
	}


}



?>
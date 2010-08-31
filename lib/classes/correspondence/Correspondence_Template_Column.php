<?php

class Correspondence_Template_Column
{

	protected $_oDO;
	protected $_oTemplate;

	public function __construct($mDefinition, $oTemplate)
	{
		$this->_oTemplate = $oTemplate;
		if (is_array($mDefinition))
		{
			$this->_oDO = new Correspondence_Template_Column_ORM($mDefinition);
		}


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


}



?>
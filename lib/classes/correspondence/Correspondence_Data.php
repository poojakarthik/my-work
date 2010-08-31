<?php


class Correspondence_Data
{
	protected $_oDO;

	public function __construct($mDetails)
	{
		if (is_array($mDetails))
		{
			$this->_oDO = new Correspondence_Data_ORM($mDetails);
		}


	}

	public function save()
	{
		$this->_oDO->save();
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField = $mValue;
	}





}
<?php

abstract class Correspondence_Source
{
protected $_oDO;

const CSV = 1;
const SQL = 2;
const SCRIPT = 3;

public function __construct( $iSourceType = null, $iId = null)
{
		$this->_oDO = $iId ==null?new Correspondence_Source_ORM():Correspondence_Source_ORM::getForId($iId);
		if ($iSourceType!=null)
			$this->_oOD->correspondence_source_type_id = $iSourceType;
}


/*
 * to be implemented by each child class
 * every implementation of this method must return data in the same format
  */
abstract public function getData();

public function save()
{
	$this->$_oDO->save();
}

}


?>
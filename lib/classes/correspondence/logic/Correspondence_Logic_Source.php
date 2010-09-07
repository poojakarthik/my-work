<?php

abstract class Correspondence_Logic_Source
{
protected $_oDO;

const CSV = 1;
const SQL = 2;
const SCRIPT = 3;

protected $_aInputColumns =  array(	'customer_group_id',
									'account_id',
									'account_name',
									'title',
									'first_name',
									'last_name',
									'address_line_1',
									'address_line2',
									'suburb',
									'postcode',
									'state',
									'email',
									'mobile',
									'landline',
									'correspondence_delivery_method'
								);

public function __construct( $iSourceType, $iId = null)
{
		$this->_oDO = $iId ==null?new Correspondence_Source(array('correspondence_source_type_id'=>$iSourceType)):Correspondence_Source::getForId($iId);
		if ($iSourceType!=null)
			$this->_oOD->correspondence_source_type_id = $iSourceType;
}


/*
 * to be implemented by each child class
 * every implementation of this method must return data in the same format
  */
abstract public function getData($aColumns);

public function save()
{
	if (isset($this->_oDO))
		$this->_oDO->save();
}

public function validateDataRecord($aRecord)
{

}

}


?>
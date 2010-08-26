<?php
class Correspondence_Source_Sql extends Correspondence_Source
{

protected $_sSql; //or should this be the actual orm object representing the sql? probably depends on whether we want to make the sql editable

public function getData()
{

	if ($this->_validateQuery())
	{
	$aCorrespondence = array();

	$this->db = DataAccess::getDataAccess();

	if($result = $this->db->refMysqliConnection->query('select * FROM  account_history'))
	{
 		while($row = $result->fetch_array(MYSQLI_ASSOC))
 		{
   			$aCorrespondence[$row['account_id']]= new Correspondence($row);
 		}
	}


	return $aCorrespondence;
	}
	else
	{
		throw new Exception('invalid query supplied for correspondence source');
	}
}

private function _validateQuery()
{
	return true;
}


}
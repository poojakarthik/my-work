<?php
class Correspondence_Logic_Source_Sql extends Correspondence_Source
{

protected $_sSql; //or should this be the actual orm object representing the sql? probably depends on whether we want to make the sql editable

public function __construct($iCorrespondenceSqlId = 0)
{
	//retrieve the sql record from the datatbase and assign the sql value to $this->_sSql;
	$this->_sQl = "SELECT		a.Id 			AS			 'Account Number',
      'EMAIL'  ,
      c.Title,
			c.FirstName    	AS 'firstname',
			c.LastName  	AS 'lastname',
			a.Address1,
			a.Address2,
			a.Suburb,
			a.State,
			a.Postcode,
			c.Email,
			c.Mobile,
			c.Phone 									AS 'Phone',
			a.BusinessName  							AS 'Business Name',
			s.FNN  										AS 'Service Number',
			rp.Name  									AS 'Rate Plan'


FROM 		Account a
			/*only retrieve account records that are currently on rebill motorpass as billing type*/

			JOIN Contact c                    		    ON  (a.PrimaryContact = c.id)
			JOIN Service s                            	ON  (a.Id = s.Account
															  AND s.CreatedOn <=now()
															  AND (ISNULL(s.ClosedOn) OR s.ClosedOn>=Now() )
                                AND s.Id = (select max(t.Id) FROM Service t where t.Account = s.Account)
															)
			JOIN ServiceRatePlan srp           			ON  (
																s.Id = srp.Service
																AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
																AND srp.Id = (
																				SELECT Id
																				FROM ServiceRatePlan
																				WHERE Service = s.Id
																				AND NOW() BETWEEN StartDatetime AND EndDatetime
																				ORDER BY CreatedOn DESC
																				LIMIT 1
																			   )
														   )
			JOIN RatePlan rp               				ON  (rp.Id = srp.RatePlan)

order by a.Id, s.FNN
	";
}

public function getData()
{

	if ($this->_validateQuery())
	{
	$aCorrespondence = array();

	$this->db = DataAccess::getDataAccess();

	if($result = $this->db->refMysqliConnection->query($this->_sQl))
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


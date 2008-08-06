<?php

class JSON_Handler_Ticketing extends JSON_Handler
{
	public function validateAccount($accountId, $ticketId)
	{
		$response = array();
		$response['isValid'] = FALSE;

		// Need to check that an account exists for the given id.
		$account = Account::getForId($accountId);
		if ($account)
		{
			// The account is valid. Now we need to list the services for it.
			$response['isValid'] = TRUE;
		}

		$ticket = Ticketing_Ticket::getForId($ticketId);

		$contacts = Ticketing_Contact::listForAccountAndTicket($account, $ticket);
		$response['contacts'] = array();
		foreach ($contacts as $contact)
		{
			$response['contacts'][] = array('id' => $contact->id, 'name' => $contact->getName());
		}

		// If an account exists, we need to return a list of services and contacts for it
		$response['services'] = $account ? $account->listServices() : array();

		return $response;
	}
	
	// This will run the report, 
	public function buildSummaryReport($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses, $strEarliestTime, $strLatestTime, $strRenderMode)
	{
		
		return print_r($arrOwners, TRUE);
	}
	

	public function getContactDetails($contactId)
	{
		
	}

	public function getContactEditDetails($customerGroupId, $contactId=NULL)
	{
		
	}

	public function saveContactDetails($detailsId)
	{
		
	}
}

?>

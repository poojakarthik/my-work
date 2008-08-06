<?php

class JSON_Handler_Ticketing extends JSON_Handler
{
	public function validateAccount($accountId, $ticketId=NULL)
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
		
		$objReportBuilder = new Ticketing_Summary_Report();
		$objReportBuilder->SetBoundaryConditions($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses);

		$objReportBuilder->BuildReport();
		
		return $objReportBuilder->GetReport($strRenderMode);// . "<br /><pre>". print_r($objReportBuilder->GetTotals(), TRUE) ."</pre>";
	}

	public function getContactDetails($contactId)
	{
		return $this->contactProps(Ticketing_Contact::getForId($contactId));
	}

	public function saveContactDetails($contactId, $title, $firstName, $lastName, $jobTitle, $email, $fax, $mobile, $phone)
	{
		$properties = array();

		if (trim($contactId)) 	$properties['id'] = $contactId;
		if (trim($title)) 		$properties['title'] = $title;
		if (trim($firstName)) 	$properties['firstName'] = $firstName;
		if (trim($lastName)) 	$properties['lastName'] = $lastName;
		if (trim($jobTitle)) 	$properties['jobTitle'] = $jobTitle;
		if (trim($email)) 		$properties['email'] = $email;
		if (trim($fax)) 		$properties['fax'] = $fax;
		if (trim($mobile)) 		$properties['mobile'] = $mobile;
		if (trim($phone)) 		$properties['phone'] = $phone;

		$contact = Ticketing_Contact::getWithProperties($properties);

		// Need to associate with an account!!

		return $this->contactProps($contact);
	}

	private function contactProps($contact)
	{
		if (!$contact)
		{
			throw new Exception('Processing contact details failed.');
		}
		$props = array(
			'title' 	=> $contact->title,
			'firstName' => $contact->firstName,
			'lastName' 	=> $contact->lastName,
			'jobTitle' 	=> $contact->jobTitle,
			'email' 	=> $contact->email,
			'fax' 		=> $contact->fax,
			'mobile' 	=> $contact->mobile,
			'phone' 	=> $contact->phone,
			'contactId' => $contact->id,
		);
		return $props;
	}

}

?>

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
		$arrErrors = array();
		// Do preliminary validation of the Time constraints
		$arrTimeConstraints = array("EarliestTime"	=> $strEarliestTime,
									"LatestTime"	=> $strLatestTime);
		foreach ($arrTimeConstraints as $strConstraint=> $strTime)
		{
			$strTime = $strTime;
			if ($strTime === "" || $strTime === NULL)
			{
				$arrTimeConstraints[$strConstraint] = NULL;
			}
			else
			{
				// Some sort of datetime has been declared
				// Validate it
				if (preg_match('/^(0[0-9]|[1][0-9]|2[0-3])(:(0[0-9]|[1-5][0-9])){2} (0[1-9]|[12][0-9]|3[01])[\/](0[1-9]|1[012])[\/](19|20)[0-9]{2}$/', $strTime))
				{
					// It is valid, convert it to ISO DateTime format
					
					//$arrTimeParts = explode(" ", $strTime);
					//echo "$strTime is in the correct format | ";
					//TODO!
				}
				else
				{
					// It is invalid
					$arrErrors[] = "$strConstraint ($strTime) is invalid";
				}
			}
		}
		
		if (count($arrErrors) > 0)
		{
			// Errors were found
			return array(	"Success"	=> FALSE,
							"Message"	=> "<pre>". implode("\n", $arrErrors) ."</pre>"
						);
		}
		
		$objReportBuilder = new Ticketing_Summary_Report();
		$objReportBuilder->SetBoundaryConditions($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses);

		$objReportBuilder->BuildReport();

		$strReport = $objReportBuilder->GetReport($strRenderMode);
		
		$strRenderMode = strtolower($strRenderMode);
		
		if ($strRenderMode == "html")
		{
			// The user wants the output rendered in the page
			return array(	"Success" => TRUE,
							"Report" => $strReport
						);
		}
		elseif ($strRenderMode == 'excel')
		{
			// The user wants to retrieve the report as an excel spreadsheet
			// Store the report in the user's session, so that the user can retrieve it, not through ajax
			$_SESSION['Ticketing']['SummaryReport']['Content'] = $strReport;
			return array(	"Success" => TRUE,
							"Report" => NULL,
							"ReportLocation" => Href()->TicketingSummaryReport(TRUE)
						);
		}
		else
		{
			// Render it in the page
			return array(	"Success" => TRUE,
							"Report" => $strReport
						);
		}
		
		
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

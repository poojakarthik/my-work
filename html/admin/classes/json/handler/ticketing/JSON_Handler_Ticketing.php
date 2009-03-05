<?php

class JSON_Handler_Ticketing extends JSON_Handler
{
	public function validateAccount($accountId, $ticketId=NULL)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		$response = array();
		$response['isValid'] = FALSE;

		// Need to check that an account exists for the given id.
		$account = Account::getForId($accountId);
		if ($account)
		{
			// The account is valid. Now we need to list the services/contacts for it.
			$response['isValid'] = TRUE;
			
			$customerGroup = Customer_Group::getForId($account->customerGroup);
			$response['customerGroupName'] = ($customerGroup)? $customerGroup->name : '';
		}
		else
		{
			$response['customerGroupName'] = '';
		}

		$ticket = Ticketing_Ticket::getForId($ticketId);

		$contacts = Ticketing_Contact::listForAccountAndTicket($account, $ticket);
		$response['contacts'] = array();
		foreach ($contacts as $contact)
		{
			$response['contacts'][] = array('id' => $contact->id, 'name' => $contact->getName());
		}
		
		$response['customerGroupEmails'] = array();
		if ($account !== NULL)
		{
			$customerGroupEmails = Ticketing_Customer_Group_Email::listForCustomerGroupId($account->customerGroup);
			$customerGroupConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($account->customerGroup);
			foreach ($customerGroupEmails as $customerGroupEmail)
			{
				$response['customerGroupEmails'][] = array(	'id'		=> $customerGroupEmail->id,
															'name'		=> htmlspecialchars("{$customerGroupEmail->name} ({$customerGroupEmail->email})"),
															'isDefault'	=> (bool)($customerGroupEmail->id == $customerGroupConfig->defaultEmailId)
														);
			}
		}
		// If an account exists, we need to return a list of services and contacts for it
		$arrServices			= $account ? $account->listServices(array(SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_PENDING)) : array();
		$response['services']	= array();
		foreach ($arrServices as $objService)
		{
			$response['services'][] = array("service_id"			=> $objService->id,
											"fnn"					=> $objService->fNN,
											"status_description"	=> $GLOBALS['*arrConstant']['service_status'][$objService->status]['Description']
											);
		}

		return $response;
	}

	// This will run the report, 
	public function buildSummaryReport($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses, $strEarliestTime, $strLatestTime, $strRenderMode)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

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
					$arrDateTimeParts = explode(" ", $strTime);
					$arrDateParts	= explode("/", $arrDateTimeParts[1]);
					$strTimePart	= $arrDateTimeParts[0];
					$strDateTime	= "{$arrDateParts[2]}-{$arrDateParts[1]}-{$arrDateParts[0]} $strTimePart";
					$arrTimeConstraints[$strConstraint] = $strDateTime;
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
		$objReportBuilder->SetBoundaryConditions($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses, $arrTimeConstraints['EarliestTime'], $arrTimeConstraints['LatestTime']);

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
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		return $this->contactProps(Ticketing_Contact::getForId($contactId));
	}

	public function setContactDetails($contactId, $title, $firstName, $lastName, $jobTitle, $email, $fax, $mobile, $phone, $accountId, $autoReply)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

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
		$properties['autoReply'] = $autoReply ? ACTIVE_STATUS_ACTIVE : ACTIVE_STATUS_INACTIVE;

		if ((!trim($firstName) && !trim($lastName) && !trim($email)) || (trim($email) && !EmailAddressValid($email)))
		{
			return 'INVALID';
		}

		$contact = Ticketing_Contact::getWithProperties($properties);

		// Need to associate with an account!!
		Ticketing_Contact_Account::associate($contact, $accountId);

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
			'autoReply' => $contact->autoReply(),
		);
		return $props;
	}

	public function deleteGroupEmail($intGroupEmailId)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		$objGroupEmail = Ticketing_Customer_Group_Email::getForId($intGroupEmailId);
		if (!$objGroupEmail)
		{
			throw new Exception("No such customer group email exists. It may have been deleted by another user.");
		}
		$objGroupEmail->delete();
		return $intGroupEmailId;
	}

	public function saveGroupEmail($intGroupEmailId=NULL, $customerGroupId, $strEmail, $strName, $bolAutoReply)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		$strEmail = strtolower(trim($strEmail));
		$strName = trim($strName);
		$bolAutoReply = $bolAutoReply ? TRUE : FALSE;
		if (!EmailAddressValid($strEmail))
		{
			return array('INVALID' => "The email address '$strEmail' is invalid.");
		}

		// Check that the email address is not already in use
		$existing = Ticketing_Customer_Group_Email::getForEmailAddress($strEmail);
		if ($existing && $existing->id != $intGroupEmailId)
		{
			return array('INVALID' => "The email address '$strEmail' is already in use.");
		}

		// If we are editing
		if ($intGroupEmailId)
		{
			$email = Ticketing_Customer_Group_Email::getForId($intGroupEmailId);
			if (!$email)
			{
				return array('INVALID' => "The group email address specified ($intGroupEmailId) could not be found.");
			}
			$email->email = $strEmail;
			$email->name = $strName;
			$email->setAutoReply($bolAutoReply);
		}
		else
		{
			$customerGroup = Customer_Group::getForId($customerGroupId);
			if (!$customerGroup)
			{
				return array('ERROR' => "The specified customer group does not exist.");
			}
			$email = Ticketing_Customer_Group_Email::createForDetails($customerGroupId, $strEmail, $strName, $bolAutoReply);
		}
		$email->save();
		$savedValues = array(
			'id' => $email->id,
			'name' => $email->name,
			'email' => $email->email,
			'autoReply' => $email->autoReply(),
			'new' => ($intGroupEmailId ? FALSE : TRUE),
		);
		return $savedValues;
	}

	public function changeAttachmentBlacklistOverride($attachmentId, $bolOverride)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}
		$attachment = Ticketing_Attachment::getForId($attachmentId);
		if (!$attachment)
		{
			throw new Exception("No attachment found with Id '$attachmentId'.");
		}
		$attachment->setBlacklistOverride($bolOverride);
		return array('id' => $attachment->id, 'allowOverride' => ($attachment->allowBlacklistOverride() ? TRUE : FALSE));
	}

	public function saveTicketingAttachmentType($attachmentTypeId, $extension, $mimeType, $blacklistStatusId)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		$attachmentTypeId = $attachmentTypeId ? intval($attachmentTypeId) : NULL;
		$extension = preg_replace("/^\.+/", '', strtolower(trim($extension)));

		if (!$extension)
		{
			return array('INVALID' => "You must specify a file extension.");
		}

		$mimeType = trim($mimeType);
		if (!preg_match("/^[^\/]+\/[^\/]+$/", $mimeType))
		{
			throw new Exception("You must enter a valid mime type.");
		}

		$blacklistStatus = Ticketing_Attachment_Blacklist_Status::getForId(intval($blacklistStatusId));
		if (!$blacklistStatus)
		{
			throw new Exception("You must speciify a valid blacklist status.");
		}

		$type = Ticketing_Attachment_Type::getForExtension($extension);
		if ($type && (!$attachmentTypeId || $attachmentTypeId != $type->id))
		{
			return array('INVALID' => "File extension '$extension' already exists.");
		}

		if ($attachmentTypeId)
		{
			$type = Ticketing_Attachment_Type::getForId(intval($attachmentTypeId));
			if (!$type)
			{
				throw new Exception('Attachment type not found for id ' . $attachmentTypeId);
			}
			$type->extension = $extension;
			$type->mimeType = $mimeType;
			$type->setBlacklistStatus($blacklistStatus);
			$type->save();
		}
		else
		{
			$type = Ticketing_Attachment_Type::getForExtensionAndMimeType($extension, $mimeType, $blacklistStatus);
		}
		return array(
			'id' => $type->id,
			'extension' => $type->extension,
			'mimeType' => $type->mimeType,
			'statusName' => $blacklistStatus->name,
			'className' => $blacklistStatus->cssName,
			'new' => ($attachmentTypeId ? FALSE : TRUE)
		);
	}
}

?>

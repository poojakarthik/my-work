<?php

class JSON_Handler_Ticketing extends JSON_Handler
{

	public function getTicketStatsForDates($mDateRange) {
		$aResultSet = Array();
		if (is_array($mDateRange)) {
			// Multiple date rnges
			foreach ($mDateRange as $oDateRange) {
				$aResult = $this->_getClosedTicketCountForDateRange($oDateRange->sDateFrom, $oDateRange->sDateTo);
				$aResult['sRangeStart'] = $oDateRange->sDateFrom;
				$aResult['sRangeEnd'] = $oDateRange->sDateTo;
				$aResult['oStatusesAtRangeEnd'] = $this->_getOpenAndPendingTicketCountForDate($oDateRange->sDateFrom);
				$aResultSet[] = $aResult;
			}
		} else {
			// Single date range
			$aResult = $this->_getClosedTicketCountForDateRange($mDateRange->sDateFrom, $mDateRange->sDateTo);
			$aResult['sRangeStart'] = $mDateRange->sDateFrom;
			$aResult['sRangeEnd'] = $mDateRange->sDateTo;
			$aResult['oStatusesAtRangeEnd'] = $this->_getOpenAndPendingTicketCountForDate($mDateRange->sDateFrom);
			$aResultSet[] = $aResult;
		}
		return $aResultSet;
	}


	// TICKETS CLOSED COMPARISON
	private function _getClosedTicketCountForDateRange($sFrom, $sTo) {
		try {
			// Amount of tickets closed between snapshots.
			$oQuery = Query::run("
			SELECT		GREATEST(0, current.tickets_closed - previous.tickets_closed) AS iTicketsClosedInRange

			FROM		(
			SELECT		COUNT(DISTINCT t.id) AS tickets_closed

			FROM		ticketing_ticket t
						JOIN ticketing_ticket_history th ON (
							th.ticket_id = t.id
							AND th.modified_datetime <= <sFrom>
						)
						JOIN ticketing_status ths ON (ths.id = th.status_id)
						JOIN ticketing_status_type thst ON (
							thst.id = ths.status_type_id
							AND thst.const_name = 'TICKETING_STATUS_TYPE_CLOSED'
						)
						) previous,
						(
							SELECT		COUNT(DISTINCT t.id) AS tickets_closed

							FROM		ticketing_ticket t
										JOIN ticketing_ticket_history th ON (
											th.ticket_id = t.id
											AND th.modified_datetime <= <sTo>
										)
										JOIN ticketing_status ths ON (ths.id = th.status_id)
										JOIN ticketing_status_type thst ON (
											thst.id = ths.status_type_id
											AND thst.const_name = 'TICKETING_STATUS_TYPE_CLOSED'
										)
						) current;", array(
							'sFrom'	=> $sFrom,
							'sTo'	=> $sTo
						));

			$aRecord = $oQuery->fetch_assoc();
			return (isset($aRecord)) ? $aRecord : null;
		}
		catch (Exception $oException) {
			// Suppress the normal form of error reporting, by displaying the error as the message of the day
			$aData = array(
						"message"	=> "Please notify your system administrators.\n" . $oException->getMessage()
					);
			return $aData;
		}
	}

	// STATUS SNAPSHOT
	private function _getOpenAndPendingTicketCountForDate($sDate) {

		try {
			
			//$aStatuses = Ticketing_Status::listAll();
			$oQuery = Query::run("
			SELECT	ts.const_name AS status_constant,
					ts.id AS status_id,
					ts.name AS status_name

			FROM	ticketing_status ts
			JOIN	ticketing_status_type tst ON (
			        tst.id = ts.status_type_id
			        AND (
			            tst.const_name IN ('TICKETING_STATUS_TYPE_PENDING', 'TICKETING_STATUS_TYPE_OPEN')
			        )
			)");

			$aStatuses = array();
			while ($aRow = $oQuery->fetch_assoc()) {
				$aStatuses[] = $aRow;	
			}
			// Label: Tickets closed on day.

			$aResultSet = array();
			foreach ($aStatuses as $aStatus) {
				// Number of tickets at a status, at a particular time.
				$oQuery = Query::run("
				SELECT	ths.id AS status_id,
						ths.name AS status_name,
						ths.const_name AS status_constant,
						COUNT(t.id) AS status_count
				FROM	ticketing_ticket t
				JOIN 	ticketing_status ts ON (ts.id = t.status_id)
				JOIN 	ticketing_status_type tst ON (
							tst.id = ts.status_type_id
							AND (
								tst.const_name IN ('TICKETING_STATUS_TYPE_PENDING', 'TICKETING_STATUS_TYPE_OPEN')
								OR t.modified_datetime > <sDate>
							)
						)
				JOIN 	ticketing_ticket_history th ON (
							th.ticket_id = t.id
							AND th.id = (
								SELECT	MAX(id)
								FROM	ticketing_ticket_history
								WHERE	ticket_id = t.id
								AND 	modified_datetime <= <sDate>
							)
						)
						JOIN ticketing_status ths ON (
							ths.id = th.status_id
							AND ths.const_name = <sConst>
						)
				GROUP BY status_constant;", array(
					'sDate'		=> $sDate,
					'sConst'	=> $aStatus['status_constant']
				));
				/*
					#AND ths.const_name = 'TICKETING_STATUS_WITH_INTERNAL' # 0.1992
					#AND ths.const_name = 'TICKETING_STATUS_ASSIGNED' # 0.132
					#AND ths.const_name = 'TICKETING_STATUS_UNASSIGNED' # 0.1294
					#AND ths.const_name = 'TICKETING_STATUS_WITH_CARRIER' # 0.4998
					#AND ths.const_name = 'TICKETING_STATUS_WITH_CUSTOMER' # 0.0684
					#AND ths.const_name = 'TICKETING_STATUS_WITH_INTERNAL' # 0.2 (1.2968 Total)
				*/
				$aResult = $oQuery->fetch_assoc();
				if (isset($aResult)) {
					$aResultSet[] = $aResult;
				} else {
					$aResultSet[] = array(
						'status_id'			=> $aStatus['status_id'],
						'status_name'		=> $aStatus['status_name'],
						'status_constant'	=> $aStatus['status_constant'],
						'status_count'		=> 0
					);
				}
			}
			// $sDate
			return (isset($aResultSet)) ? $aResultSet : null;
		}
		catch (Exception $oException) {
			// Suppress the normal form of error reporting, by displaying the error as the message of the day
			$aData = array(
						"message"	=> "Please notify your system administrators.\n" . $oException->getMessage()
					);
			return $aData;
		}
	}


	public function getTicketsForCurrentUser() {
		try {

			// Authenticate.
			if (!Ticketing_User::currentUserIsTicketingUser()) {
				throw new exception("Ticketing access is not permitted for the current user.");
			}

			// Get a list of 'ticket status type' ids, for 'Open' and 'Pending' tickets
			$iTicketingStatusTypeConglomerateOpenOrPendingId = Ticketing_Status_Type_Conglomerate::TICKETING_STATUS_TYPE_CONGLOMERATE_OPEN_OR_PENDING;
			$oStatusTypeConglomerateOpenOrPending = Ticketing_Status_Type_Conglomerate::getForId($iTicketingStatusTypeConglomerateOpenOrPendingId);
			$aStatusId = $oStatusTypeConglomerateOpenOrPending->listStatusIds();

			// Get the ticket owner id of the currently logged in user
			$iOwnerId = Ticketing_User::getCurrentUser()->id;

			// Set Search Filters
			$aFilter = array();

			// Filter: Owner
			if ($iOwnerId !== NULL) {
				$aFilter['ownerId'] = array(
					'value'			=> $iOwnerId, 
					'comparison'	=> '='
				);
			}
			// Filter: Status
			if ($aStatusId !== NULL) {
				$aFilter['statusId'] = array(
					'value'			=> $aStatusId,
					'comparison'	=> '='
				);
			}

			// Get Tickets matching our filters
			$aTickets = Ticketing_Ticket::findMatching($aColumns=null, $aSort=array(), $aFilter, $iOffset=null, $iLimit=null, $sQuickSearch=null);
			// Prepare result set.
			$aData = array();
			if (isset($aTickets)) {
				foreach($aTickets as $oTicket) {
					
					// Ticket Status
					$oTicketStatus = $oTicket->getStatus();
					$oTicketPriority = $oTicket->getPriority();

					$aData[] = array(
						'id'					=> $oTicket->id,
						'account_id'			=> $oTicket->account_id,
						'category_id'			=> $oTicket->category_id,
						'contact_id'			=> $oTicket->contact_id,
						'creation_datetime'		=> $oTicket->creation_datetime,
						'customer_group_id'		=> $oTicket->customer_group_id,
						'group_ticket_id'		=> $oTicket->group_ticket_id,
						'modified_by_user_id'	=> $oTicket->modified_by_user_id,
						'modified_datetime'		=> $oTicket->modified_datetime,
						'owner_id'				=> $oTicket->owner_id,
						'priority_id'			=> $oTicket->priority_id,
						'status_id'				=> $oTicket->status_id,
						'subject'				=> $oTicket->subject,
						'status'				=> array(
							'id'			=> $oTicketStatus->Id,
							'name'			=> $oTicketStatus->Name,
							'description'	=> $oTicketStatus->Description,
							'constant'		=> $oTicketStatus->Constant
						),
						'priority'				=> array(
							'id'			=> $oTicketPriority->Id,
							'name'			=> $oTicketPriority->Name,
							'description'	=> $oTicketPriority->Description,
							'constant'		=> $oTicketPriority->Constant
						)
					);
				}
			}
		
			return $aData;
		}
		catch(Exception $oException) {
			return array(	'Success'		=> false,
							'ErrorMessage'	=> $oException->getMessage());
		}
	}

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

	// Currently returns account / contact / service details for a given ticket
	public function getTicketDetails($ticketId)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		$response = array();

		$ticket = Ticketing_Ticket::getForId($ticketId);

		if (!$ticket)
		{
			$response['isValid'] = FALSE;
			return $response;
		}

		$response['isValid'] = TRUE;
		
		$account = Account::getForId($ticket->accountId);
		if ($account)
		{
			// An account was found
			$customerGroup = Customer_Group::getForId($account->customerGroup);
			$response['customerGroupName'] = ($customerGroup)? $customerGroup->name : '';
			$response['accountId'] = $account->id;
		}
		else
		{
			// An account was not found, but there could possibly be a customer group associated with the ticket
			$customerGroup = Customer_Group::getForId($ticket->customerGroupId);
			$response['customerGroupName'] = ($customerGroup)? $customerGroup->name : '';
			$response['accountId'] = '';
		}
		
		// Retrieve all services associated with the account, and flag the ones that are associated with the ticket
		$arrServices			= $account ? $account->listServices(array(SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_PENDING)) : array();
		$arrTicketServices		= $ticket->getServices();
		$response['services']	= array();
		foreach ($arrServices as $objService)
		{
			$response['services'][] = array("service_id"			=> $objService->id,
											"fnn"					=> $objService->fNN,
											"status_description"	=> $GLOBALS['*arrConstant']['service_status'][$objService->status]['Description'],
											"selected"				=> array_key_exists($objService->id, $arrTicketServices)
											);
		}
		
		// Contacts
		// Retrieve all contacts associated with the account and ticket, and flag the one that is selected (if one is select, but one always should be)
		$contacts = Ticketing_Contact::listForAccountAndTicket($account, $ticket);
		$response['contacts'] = array();
		foreach ($contacts as $contact)
		{
			$response['contacts'][] = array('id'		=> $contact->id, 
											'name'		=> $contact->getName(),
											'selected'	=> (bool)($ticket->contactId == $contact->id));
		}
		$response['selectedContactId'] = $ticket->contactId;

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

		// Start a transaction
		TransactionStart();
		
		try
		{
			$contact = Ticketing_Contact::getWithProperties($properties);
	
			// If a valid account was passed, then associate this contact with the account
			$intAccountId	= intval($accountId);
			$objAccount		= Account::getForId($intAccountId);

			if ($objAccount !== NULL)
			{
				// Associate with the account
				Ticketing_Contact_Account::associate($contact, $objAccount->id);
			}

			TransactionCommit();
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return array('ERROR' => $e->getMessage());
		}
		
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

	public function archiveGroupEmail($intGroupEmailId)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		TransactionStart();
		try
		{
			$objEmail = Ticketing_Customer_Group_Email::getForId($intGroupEmailId);
			if (!$objEmail)
			{
				throw new Exception("Could not find email with id: {$intGroupEmailId}.");
			}
			
			if ($objEmail->archivedOnDatetime !== null)
			{
				throw new Exception("Email with id: {$intGroupEmailId}, has already been archived.");
			}
			
			// Check that this isn't the default email address for the customer group
			$objCGConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($objEmail->customerGroupId);
			
			if ($objCGConfig->defaultEmailId == $objEmail->id)
			{
				throw new Exception("This email is currently the default one for the customer group, and therefore cannot be archived.");
			}
			
			$objEmail->archivedOnDatetime = GetCurrentISODatetime();
			$objEmail->save();

			TransactionCommit();
			return array(	'Success'	=> true,
							'id'		=> $objEmail->id
						);
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return array(	'Success'		=> false,
							'id'			=> $intGroupEmailId,
							'ErrorMessage'	=> $e->getMessage()
						);
		}


		$objGroupEmail = Ticketing_Customer_Group_Email::getForId($intGroupEmailId);
		if (!$objGroupEmail)
		{
			throw new Exception("No such customer group email exists. It may have been deleted by another user.");
		}
		$objGroupEmail->delete();
		return $intGroupEmailId;
	}

	public function saveGroupEmail($intGroupEmailId=NULL, $intCustomerGroupId, $strEmail, $strName, $bolAutoReply)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			return array('ERROR' => "You are not authorised to perform this action.");
		}

		if ($strEmail !== null)
		{
			$strEmail = strtolower(trim($strEmail));
		}
		
		$strName		= trim($strName);
		$bolAutoReply	= $bolAutoReply ? TRUE : FALSE;

		TransactionStart();

		try
		{
			// Check that a name has been supplied
			if ($strName == '')
			{
				throw new Exception("A name must be supplied for the email address.");
			}
			
			if ($intGroupEmailId)
			{
				// We are editing an existing email
				$objEmail = Ticketing_Customer_Group_Email::getForId($intGroupEmailId);
				if (!$objEmail)
				{
					throw new Exception("The group email address specified ($intGroupEmailId) could not be found.");
				}
	
				// Check that it isn't already archived
				if ($objEmail->isArchivedVersion())
				{
					throw new Exception("The group email address specified ($intGroupEmailId) has been archived, and cannot have further changes made to it.");
				}
				
				// Work out if we have to archive this version, and create a new one, or just update the current one
				if ($objEmail->name != $strName)
				{
					// The name has changed, so we have to archive the old one and create a new one
					$objOldEmail = $objEmail;
					$objOldEmail->archivedOnDatetime = GetCurrentISODatetime();
					$objOldEmail->save();
					
					// Create the new one
					$objEmail = Ticketing_Customer_Group_Email::createForDetails($objOldEmail->customerGroupId, $objOldEmail->email, $objOldEmail->name, $objOldEmail->autoReply());
				}
				
				// Make the changes
				$objEmail->name = $strName;
				$objEmail->setAutoReply($bolAutoReply);
			}
			else
			{
				// We are creating a new email for this customer group
				
				// Check that the email address is valid
				if (!EmailAddressValid($strEmail))
				{
					throw new Exception("The email address '$strEmail' is invalid.");
				}
				
				// Check that the email address isn't currently in use
				$objExistingEmail = Ticketing_Customer_Group_Email::getForEmailAddress($strEmail);
				if ($objExistingEmail !== null)
				{
					throw new Exception("The email address '$strEmail' is already being used.");
				}
				
				$customerGroup = Customer_Group::getForId($intCustomerGroupId);
				if ($customerGroup === null)
				{
					throw new Exception("The specified customer group does not exist.");
				}
				$objEmail = Ticketing_Customer_Group_Email::createForDetails($intCustomerGroupId, $strEmail, $strName, $bolAutoReply);
			}
			
			$objEmail->save();

			// If we had to archive a record, and a ticketing_customer_group_config record references it (via default_email_id), update it to reference the new one
			if (isset($objOldEmail))
			{
				$objCGConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($objOldEmail->customerGroupId);
				if ($objCGConfig !== null && $objCGConfig->defaultEmailId == $objOldEmail->id)
				{
					// The ticketing_customer_group_config record was referencing the now archived ticketing_customer_group_email record, as the default one.
					// Point it to the new one
					$objCGConfig->defaultEmailId = $objEmail->id;
					$objCGConfig->save();
				}
			}
			
			$savedValues = array(	'Success'	=> true, 
									'id'		=> $objEmail->id,
									'name'		=> $objEmail->name,
									'email'		=> $objEmail->email,
									'autoReply'	=> $objEmail->autoReply(),
									'new'		=> ($intGroupEmailId ? FALSE : TRUE) // Note that if we archived an old record and created a new one, this isn't considered a new Email
								);
			if (isset($objOldEmail))
			{
				$savedValues['archivedId'] = $objOldEmail->id;
			}
			
			$objCGConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($objEmail->customerGroupId);
			
			if ($objCGConfig === null)
			{
				throw new Exception('Could not retrieve Ticketing Customer Group Config relating to this email');
			}
			
			$savedValues['isCustomerGroupDefaultEmail'] = ($objCGConfig->defaultEmailId == $objEmail->id) ? true : false;

			TransactionCommit();
			return $savedValues;
		
		}
		catch (Exception $e)
		{
			TransactionRollback();
			return array(	'Success'		=> false,
							'ErrorMessage'	=> $e->getMessage());
		}
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

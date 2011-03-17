<?php

require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_LateNoticeRun extends Cli
{
	// Valid argument switches
	const SWITCH_EFFECTIVE_DATE			= "e";
	const SWITCH_TEST_RUN 				= "t";
	const SWITCH_SAMPLE 				= "p";

	const SAMPLES_PER_CUSTOMER_GROUP	= 1;
	const EMAIL_BILLING_NOTIFICATIONS	= 'ybs-admin@ybs.net.au';

	// If not null, this limits the number of notices to be generated
	// i.e. if set to 1 then there will be 1 notice generated, then the process will stop.
	const TEST_LIMIT					= 5;

	private $_sRunDateTime				= '';
	private $_bTestRun					= true;

	function run()
	{
		$iNow					= time();
		$this->_sRunDateTime	= date('Y-m-d H:i:s', $iNow);
		$sPathDate				= date('Ymd', $iNow);
		$oDataAccess			= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			$this->showUsage('There was an error starting the transaction');
			return 1;
		}
		try
		{
			// NOTE: This class is deprecated
			Flex::assert(false, "Cli_App_LateNoticeRun has been deprecated, late notices are sent using a collection event of type 'Correspondence'.");
			
			// Validate and examine arguments
			$aArgs				= $this->getValidatedArguments();
			$this->_bTestRun	= (bool)$aArgs[self::SWITCH_TEST_RUN];
			if ($this->_bTestRun)
			{
				$sLimit	= ((self::TEST_LIMIT !== null) ? " Only ".self::TEST_LIMIT." notice".((self::TEST_LIMIT == 1) ? '' : 's')." will be generated." : '');
				$this->log("Running in Test Mode. All database changes will be rolled back.{$sLimit}", TRUE);
			}
			
			if (!file_exists(FILES_BASE_PATH))
			{
				throw new Exception('The configured FILES_BASE_PATH does not exists. Please add a valid setting to the configuration file.');
			}

			$aNoticeTypes	= 	array(
									DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER 	=> AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER,
									DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE 		=> AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE,
									DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE 	=> AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE,
									DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND 		=> AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND,
								);

			$aOutputs 				= array();
			$aSummary 				= array();
			$aGeneralErrors 		= array();
			$aInvoiceRunAutoFields 	= array();
			$aCorrespondence		= array();
			$aAccountsById			= array();
			$iErrors 				= 0;
			$bSendEmail 			= FALSE;
			
			// Test Mode Only: Used to keep track of how many notices have been generated
			$iTestCount	= 0;
			foreach($aNoticeTypes as $iNoticeType => $iAutomaticInvoiceAction)
			{
				if ($this->_bTestRun && (self::TEST_LIMIT !== null) && ($iTestCount >= self::TEST_LIMIT))
				{
					// In test mode & the limit has been reached for email & post notices
					break;
				}
				
				// This query is repeated by the GenerateLatePaymentNotices function. Consider revising.
				$aInvoiceRunIds	= ListInvoiceRunsForAutomaticInvoiceActionAndDate($iAutomaticInvoiceAction, $aArgs[self::SWITCH_EFFECTIVE_DATE]);
				if (!count($aInvoiceRunIds))
				{
					$this->log("No applicable invoice runs found for action type $iAutomaticInvoiceAction.");
					continue;
				}

				$mResult		= GenerateLatePaymentNotices($iAutomaticInvoiceAction, $aArgs[self::SWITCH_EFFECTIVE_DATE]);
				$sLetterType	= GetConstantDescription($iNoticeType, "DocumentTemplateType");
				$bSendEmail		= TRUE;

				// Check result
				if ($mResult === FALSE)
				{
					// No notices generated
					$sMessage	= "ERROR: Generating " . $sLetterType . "s failed, unexpectedly";
					$this->log($sMessage, 0);
					$aGeneralErrors[] = $sMessage;
					$iErrors++;
				}
				else
				{
					// Notices generated
					$aOutputs[$sLetterType]['success'] = $mResult['Successful'];
					$aOutputs[$sLetterType]['failure'] = $mResult['Failed'];
					if ($mResult['Failed'])
					{
						$iErrors++;
					}
					$this->log("{$sLetterType}s successfully generated  : {$mResult['Successful']}");
					$this->log("{$sLetterType}s that failed to generate : {$mResult['Failed']}");

					// Used to record which invoice runs need to have their automatic invoice action 'actioned'
					if (!array_key_exists($iAutomaticInvoiceAction, $aInvoiceRunAutoFields))
					{
						$aInvoiceRunAutoFields[$iAutomaticInvoiceAction] = array();
					}

					// If we're in Test Mode, get samples
					$aSampleAccounts	= array(DELIVERY_METHOD_POST => null, DELIVERY_METHOD_EMAIL => null);
					if ($this->_bTestRun)
					{
						$aDeliveryMethodAccounts	= array(DELIVERY_METHOD_POST => array(), DELIVERY_METHOD_EMAIL => array());
						foreach ($mResult['Details'] as $mKey => $aDetails)
						{
							$iCustomerGroup		= $aDetails['Account']['CustomerGroup'];
							$iDeliveryMethod	= $aDetails['Account']['DeliveryMethod'];
							$aDeliveryMethodAccounts[$iDeliveryMethod][$iCustomerGroup][]	= $mKey;
						}

						// Pick a random sample for each Delivery Method
						foreach ($aDeliveryMethodAccounts as $iDeliveryMethod => $aCustomerGroups)
						{
							$sDeliveryMethod	= GetConstantDescription($iDeliveryMethod, 'delivery_method');
							foreach ($aCustomerGroups as $iCustomerGroupId => $aAccounts)
							{
								$iSamples	= 0;
								while ($iSamples < self::SAMPLES_PER_CUSTOMER_GROUP)
								{
									$mRandomKey		= array_rand($aAccounts);
									$iRandomAccount	= $aAccounts[$mRandomKey];
									$aAccount		= $mResult['Details'][$iRandomAccount]['Account'];
									if (!isset($aSampleAccounts[$iDeliveryMethod]) || !in_array($aAccount['AccountId'], $aSampleAccounts[$iDeliveryMethod]))
									{
										// The account id hasn't yet been picked as a sample, use it
										$aSampleAccounts[$iDeliveryMethod][$iCustomerGroupId]	= $aAccount['AccountId'];
										$iSamples++;
										$this->log("{$aAccount['AccountId']} has been selected as a random sample for {$aAccount['CustomerGroupName']}:{$sDeliveryMethod}");
									}
								}
							}
						}
					}

					// Create array to hold the correspondence for this automatic invoice action (id)
					$aCorrespondence[$iAutomaticInvoiceAction]	= array();

					// Create a samples queue for the letter type
					$oSamplesEmailQueue	= Email_Flex_Queue::get("SAMPLES_{$sLetterType}");

					// We now need to create correspondence for each of the notices that have been generated
					foreach($mResult['Details'] as $aDetails)
					{
						// Test Mode Only: Checks if the notice limits have been reached
						if ($this->_bTestRun && (self::TEST_LIMIT !== null) && ($iTestCount >= self::TEST_LIMIT))
						{
							break;
						}

						$iCustGrp 				= $aDetails['Account']['CustomerGroup'];
						$sCustGroupName			= $aDetails['Account']['CustomerGroupName'];
						$iAccountId 			= $aDetails['Account']['AccountId'];
						$sXMLFilePath 			= $aDetails['XMLFilePath'];
						$iAutoInvoiceAction		= $aDetails['Account']['automatic_invoice_action'];
						$sLowerCustGroupName	= strtolower(str_replace(' ', '_', $sCustGroupName));
						$sLowerLetterType		= strtolower(str_replace(' ', '_', $sLetterType));

						// Build summary data
						if (!array_key_exists($sCustGroupName, $aSummary))
						{
							$aSummary[$sCustGroupName]	= array();
						}

						// ... more summary setup
						if (!array_key_exists($sLetterType, $aSummary[$sCustGroupName]))
						{
							$aSummary[$sCustGroupName][$sLetterType]['emails']				= array();
							$aSummary[$sCustGroupName][$sLetterType]['prints']				= array();
							$aSummary[$sCustGroupName][$sLetterType]['errors'] 				= array();
							$aSummary[$sCustGroupName][$sLetterType]['output_directory']	= realpath(FILES_BASE_PATH) . '/' . $sLowerLetterType . '/' . 'pdf' . '/' . $sPathDate . '/' . $sLowerCustGroupName;
						}

						// Record how many accounts there are per automatic invoice action & invoice run id
						$iInvoiceRunId	= $aDetails['Account']['invoice_run_id'];
						if (!array_key_exists($iInvoiceRunId, $aInvoiceRunAutoFields[$iAutomaticInvoiceAction]))
						{
							$aInvoiceRunAutoFields[$iAutomaticInvoiceAction][$iInvoiceRunId]	= 0;
						}
						$aInvoiceRunAutoFields[$iAutomaticInvoiceAction][$iInvoiceRunId]++;

						// Cache this information for use when reporting on email send status
						$aAccountsById[$iAccountId]	= $aDetails['Account'];
						
						// Generate the correspondence for the account/notice
						$iDeliveryMethod				= $aDetails['Account']['DeliveryMethod'];
						$sCorrespondenceDeliveryMethod	= null;
						$sSummarySubType				= null;
						switch ($iDeliveryMethod)
						{
							case DELIVERY_METHOD_POST:
								$sSummaryDeliveryMethod			= 'prints';
								$sCorrespondenceDeliveryMethod	= Correspondence_Delivery_Method::getForId(CORRESPONDENCE_DELIVERY_METHOD_POST)->system_name;
								break;
							
							case DELIVERY_METHOD_EMAIL:
								// Verify that an email address exists for the account contact
								if (($aDetails['Account']['Email'] == '') || ($aDetails['Account']['Email'] === null)) 
								{
									$sMessage	= "No email for Account {$iAccountId}, cannot create email correspondence";
									$this->log($sMessage, TRUE);
									$aSummary[$sCustGroupName][$sLetterType]['errors'][]	= $sMessage;
									$iErrors++;
									break;
								}
								
								$sSummaryDeliveryMethod			= 'emails';
								$sCorrespondenceDeliveryMethod	= Correspondence_Delivery_Method::getForId(CORRESPONDENCE_DELIVERY_METHOD_EMAIL)->system_name;
								break;
						}
						
						if ($sCorrespondenceDeliveryMethod === null)
						{
							continue;
						}
						
						// We need to generate the pdf for the XML and save it to the
						// files/type/pdf/date/cust_group/account.pdf storage
						// Need to add a note of this to the email
						$this->log("Generating print PDF $sLetterType for account ". $aDetails['Account']['AccountId']);
						$sPDFContent	= $this->getPDFContent($iCustGrp, $aArgs[self::SWITCH_EFFECTIVE_DATE], $iNoticeType, $sXMLFilePath, 'PRINT');

						// Check the pdf content
						if (!$sPDFContent)
						{
							// PDF generation failed.
							$sError 	= $this->getCachedError();
							$sMessage 	= "Failed to generate PDF $sLetterType for " . $iAccountId . "\n" . $sError;
							$aSummary[$sCustGroupName][$sLetterType]['errors'][] = $sMessage;
							$iErrors++;
							$this->log($sMessage, TRUE);
						}
						else
						{
							// We have a PDF, so we should store it for sending to the mail house (as correspondence)
							$this->log("Storing PDF $sLetterType for account ". $iAccountId);

							$sOutputDirectory	= $aSummary[$sCustGroupName][$sLetterType]['output_directory'];
							if (!file_exists($sOutputDirectory))
							{
								// This ensures that the output directory exists by testing each directory in the hierarchy above (and including) the target directory
								$aOutputDirectories	= explode('/', str_replace('\\', '/', $sOutputDirectory));
								$sDirectory 		= '';
								foreach($aOutputDirectories as $sSubDirectory)
								{
									// If root directory on linux/unix
									if (!$sSubDirectory)
									{
										continue;
									}
									$sXdirectory	= $sDirectory . '/' . $sSubDirectory;
									if (!file_exists($sXdirectory))
									{
										$bOk	= @mkdir($sXdirectory);
										if (!$bOk)
										{
											$this->log("Failed to create directory for PDF output: $sXdirectory", TRUE);
										}
									}
									$sDirectory	= $sXdirectory . '/';
								}
								$sOutputDirectory	= realpath($sDirectory) . '/';
							}
							else
							{
								$sOutputDirectory	= realpath($sOutputDirectory) . '/';
							}

							$aSummary[$sCustGroupName][$sLetterType]['output_directory']	= $sOutputDirectory;

							// Write the PDF file contents to storage
							$sTargetFile	= $sOutputDirectory . $iAccountId . '.pdf';
							$rFile			= @fopen($sTargetFile, 'w');
							$bOk			= FALSE;
							if ($rFile)
							{
								$bOk	= @fwrite($rFile, $sPDFContent);
							}

							if ($bOk === FALSE)
							{
								// Failed
								$sMessage	= "Failed to write PDF $sLetterType for account $iAccountId to $sTargetFile.";
								$this->log($sMessage, TRUE);
								$aSummary[$sCustGroupName][$sLetterType]['errors'][]	= $sMessage;
							}
							else
							{
								// PDF stored successfully
								@fclose($rFile);

								$aSummary[$sCustGroupName][$sLetterType]['pdfs'][]		= $sTargetFile;

								// We need to log the fact that we've created it, by updating the account automatic_invoice_action
								if ($this->_bTestRun === false)
								{
									$mOutcome	= $this->changeAccountAutomaticInvoiceAction($iAccountId, $iAutoInvoiceAction, $iAutomaticInvoiceAction, "$sLetterType stored for printing in $sOutputDirectory", $iInvoiceRunId);
									if ($mOutcome !== TRUE)
									{
										$aSummary[$sCustGroupName][$sLetterType]['errors'][] = $mOutcome;
										$iErrors++;
									}
								}
							}
							
							$this->log("Generating Correspondence Data for account ". $iAccountId);
							
							// Cache the correspondence data for the notice
							$aItem	= 	array(
											'account_id'						=> $iAccountId,
											'customer_group_id'					=> $iCustGrp,
											'correspondence_delivery_method_id'	=> $sCorrespondenceDeliveryMethod,
											'account_name'						=> $aDetails['Account']['BusinessName'],
											'title'								=> $aDetails['Account']['Title'],
											'first_name'						=> $aDetails['Account']['FirstName'],
											'last_name'							=> $aDetails['Account']['LastName'],
											'address_line_1'					=> $aDetails['Account']['AddressLine1'],
											'address_line_2'					=> $aDetails['Account']['AddressLine2'],
											'suburb'							=> $aDetails['Account']['Suburb'],
											'postcode'							=> $aDetails['Account']['Postcode'],
											'state'								=> $aDetails['Account']['State'],
											'email'								=> $aDetails['Account']['Email'],
											'mobile'							=> $aDetails['Account']['Mobile'],
											'landline'							=> $aDetails['Account']['Landline'],
											'pdf_file_path'						=> $sTargetFile,
											'letter_type'						=> $iNoticeType
										);
							$aCorrespondence[$iAutomaticInvoiceAction][]	= $aItem;
							
							// Update the summary information for the delivery method
							$aSummary[$sCustGroupName][$sLetterType][$sSummaryDeliveryMethod][]	= $iAccountId;

							// Queue a sample Notice to be sent (if the account was picked for sampling)
							$sDeliveryMethodName	= Delivery_Method::getForId($iDeliveryMethod)->name;
							if ($this->_bTestRun && ($aSampleAccounts[$iDeliveryMethod][$iCustGrp] === $iAccountId))
							{
								$sSubject 		= 	"[SAMPLE:{$sDeliveryMethodName}] {$sCustGroupName} {$sLetterType} for Account {$iAccountId}";
								$sTo 			= 	self::EMAIL_BILLING_NOTIFICATIONS;
								$sContent 		= 	"Please find attached a SAMPLE {$sDeliveryMethodName} {$sCustGroupName} {$sLetterType} for Account {$iAccountId}.";
								$aAttachment	= 	array(
														self::EMAIL_ATTACHMENT_NAME			=> "{$iAccountId}.pdf",
														self::EMAIL_ATTACHMENT_MIME_TYPE	=> 'application/pdf',
														self::EMAIL_ATTACHMENT_CONTENT		=> $sPDFContent
													);

								// Add to the samples queue for this notice type, giving it the account id as an email id
								$oSamplesEmailQueue->push(
									Email_Notification::factory(
										'LATE_NOTICE',					 	// Type
										$iCustGrp, 							// Customer group
										self::EMAIL_BILLING_NOTIFICATIONS, 	// To
										$sSubject, 							// Subject
										NULL, 								// Body html
										$sContent, 							// Body text
										array($aAttachment), 				// Attachments
										TRUE								// Fail without an exception being thrown
									),
									$iAccountId
								);
							}
						}

						// Test Mode Only: Update the notice count & log it
						if ($this->_bTestRun && (self::TEST_LIMIT !== null))
						{
							$iTestCount++;
							$this->log("\t ** TEST COUNT NOW AT: {$iTestCount} **");
						}
					}
				}
			}

			if ($this->_bTestRun === false)
			{
				// Action the automatic invoice run event for each invoice run
				foreach ($aInvoiceRunAutoFields as $iAutomaticInvoiceAction => $invoiceRunCounts)
				{
					foreach ($invoiceRunCounts as $iInvoiceRunId => $count)
					{
						$mResult	= $this->changeInvoiceRunAutoActionDateTime($iInvoiceRunId, $iAutomaticInvoiceAction);
						if ($mResult !== TRUE)
						{
							$aGeneralErrors[] = $mResult;
						}
					}
				}
			}

			// TODO: DEV ONLY -- UNCOMMENT THIS CONDITION
			if ($this->_bTestRun === false)
			{
				// Generate a correspondence run for each automatic invoice action and to it correspondence that is to be posted for that notice type
				$this->log("START: Creating Correspondence Runs");
				foreach ($aCorrespondence as $iAutomaticInvoiceAction => $aCorrespondenceData)
				{
					// Check number of correspondence items for the invoice action
					if (count($aCorrespondenceData) > 0)
					{
						// Got correspondence data, create run
						$this->log("Creating Correspondence Run for automatic invoice action '{$iAutomaticInvoiceAction}'");
						try
						{
							// Create Correspondence Run using the pre-deterimined (System) Correspondence Template name
							$this->log("Retrieving Template");
	
							// Get the template
							$oTemplate	= Correspondence_Logic_Template::getForAutomaticInvoiceAction($iAutomaticInvoiceAction);
							$this->log("Template retrieved, creating Correspondence Run");
	
							// Create the correspondence run
							$oRun	= $oTemplate->createRun(true, $aCorrespondenceData);
							$this->log("Run created succesfully (id={$oRun->id})");
						}
						catch (Correspondence_DataValidation_Exception $oEx)
						{
							// Use the exception information to display a meaningful message
							$sErrorType	= GetConstantName($oEx->iError, 'correspondence_run_error');
							throw new Exception("Correspondence Run Processing Failed:\n - Validation errors in the correspondence data for the run: \n -- Error Type: $oEx->iError => '{$sErrorType}'");
						}
					}
					else
					{
						// No correspondence data, no run
						$this->log("No Correspondence data for automatic invoice action '{$iAutomaticInvoiceAction}', no run created");
					}
				}
				$this->log("FINISHED: Creating Correspondence Runs");
			}
			
			// TODO: DEV ONLY -- UNCOMMENT THIS CONDITION
			if ($this->_bTestRun === false)
			{
				// Live run, attempt transaction commit before the emails are sent
				if (!$oDataAccess->TransactionCommit())
				{
					throw new Exception("Transaction Commit Failed");
				}
			}

			// Attempt to send all emails
			$this->log("START: Sending all emails that have been queued");
			foreach($aNoticeTypes as $iNoticeType => $iAutomaticInvoiceAction)
			{
				$sLetterType	= GetConstantDescription($iNoticeType, 'DocumentTemplateType');
				if ($this->_bTestRun)
				{
					// Test Mode Only: Send SAMPLES email queue & show status of each email (ONLY IF IN TEST MODE)
					$oSamplesQueue	= Email_Flex_Queue::get("SAMPLES_{$sLetterType}");
					$oSamplesQueue->commit();
					$oSamplesQueue->send();
					$aSampleEmails	= $oSamplesQueue->getEmails();
					foreach ($aSampleEmails as $iAccountId => $oEmail)
					{
						$aAccountDetails	= $aAccountsById[$iAccountId];
						$sCustomerGroupName	= $aAccountDetails['CustomerGroupName'];
						switch ($aAccountDetails['DeliveryMethod'])
						{
							case DELIVERY_METHOD_POST:
								$sDeliveryMethod	= 'POST';
								break;
							case DELIVERY_METHOD_EMAIL:
								$sDeliveryMethod	= 'EMAIL';
								break;
							default:
								throw new Exception("Invalid delivery method for Account '{$iAccountId}': '".$aAccountDetails['DeliveryMethod']."', this should never happen");
								break;
						}

						$sRecipients	= implode(', ', $oEmail->getRecipients());
						$mStatus		= $oEmail->getSendStatus();
						if ($mStatus === Email_Flex::SEND_STATUS_SENT)
						{
							// Email sent
							$this->log("[SAMPLE:SUCCESS]: Sample {$sDeliveryMethod} {$sLetterType} for {$sCustomerGroupName} delivered to '{$sRecipients}'");
						}
						else if ($mStatus === Email_Flex::SEND_STATUS_FAILED)
						{
							// Email sending failed
							$this->log("[SAMPLE:ERROR]: Unable to deliver Sample {$sDeliveryMethod} {$sLetterType} for {$sCustomerGroupName} to '{$sRecipients}'");
						}
						else if ($mStatus === Email_Flex::SEND_STATUS_NOT_SENT)
						{
							// Not sent
							$this->log("[SAMPLE:NOT SENT]: Sample {$sDeliveryMethod} {$sLetterType} for {$sCustomerGroupName} was not sent");
						}
					}
				}
			}
			$this->log("FINISHED: Sending all emails that have been queued");

			// We now need to build a report detailing actions taken for each of the customer groups
			if (!$bSendEmail)
			{
				$this->log("No applicable accounts found. Exiting normally.");
				return 0;
			}

			$this->log("Building report");
			$sSubject	= ($iErrors ? '[FAILURE]' : '[SUCCESS]') . ($this->_bTestRun ? ' [TEST]' : '') . ' Automated late notice generation log for run dated ' . $this->_sRunDateTime;
			$aReport	= array();
			if ($this->_bTestRun)
			{
				$aReport[]	= "***RUN TEST MODE - EMAILS WERE NOT SENT TO ACCOUNT HOLDERS***";
				$aReport[]	= "";
			}
			if ($iErrors)
			{
				$aReport[]	= "***ERRORS WERE DETECTED WHILST GENERATING LATE NOTICES***";
				$aReport[]	= "";
			}
			else
			{
				$aReport[]	= "The late notice generation completed without any errors being detected.";
				$aReport[]	= "";
			}
			if (!empty($aGeneralErrors))
			{
				$aReport[]	= "***GENERAL ERRORS***";
				$aReport[]	= "The following general errors were encountered: -";
				$aReport	= array_merge($aReport, $aGeneralErrors);
				$aReport[]	= "";
				$aReport[]	= "";
			}
			$aReport[]	= "Breakdown of XML generation: -";
			foreach ($aOutputs as $sLetterType => $aResults)
			{
				$aReport[]	= "    $sLetterType: " . $aResults['success'] . " XML files Created, " . $aResults['failure'] . " XML file generations Failed";
			}
			$aReport[]	= "";
			$aReport[]	= "";
			if (count($aSummary))
			{
				$aReport[]	= "Breakdown of late notice generation by customer group (for successfully generated XML files only): -";
				foreach ($aSummary as $sCustGroup => $aLetterTypeSummarries)
				{
					$aReport[]	= "";
					$aReport[]	= "";
					$aReport[]	= "Customer Group: $sCustGroup";

					foreach ($aLetterTypeSummarries as $sLetterType => $aLetterTypeSummary)
					{
						$aReport[]	= "[Start of $sLetterType breakdown for Customer Group: $sCustGroup]";
						if (!empty($aLetterTypeSummary['errors']))
						{
							$aReport[]	= "";
							$aReport[]	= "***ERRORS ENCOUNTERED***";
							$aReport		= array_merge($aReport, $aLetterTypeSummary['errors']);
							$aReport[]	= "";
						}
						else
						{
							$aReport[]	= "";
							$aReport[] 	= "No errors were encountered.";
							$aReport[]	= "";
						}
						if (!empty($aLetterTypeSummary['prints']))
						{
							$aReport[] 	= "Print: " . count($aLetterTypeSummary['prints']) . " {$sLetterType}s were created for printing and stored in " . $aLetterTypeSummary['output_directory'] . '.';
						}
						else
						{
							$aReport[] 	= "Print: No documents were created for printing";
						}
						if (!empty($aLetterTypeSummary['emails']))
						{
							$aReport[] 	= "Email: " . count($aLetterTypeSummary['emails']) . " {$sLetterType}s were created and emailed.";
							$aReport[] 	= "Emails were sent for the following accounts: -";
							$aReport[] 	= implode(', ', $aLetterTypeSummary['emails']);
						}
						else
						{
							$aReport[] 	= "Email: No documents were emailed";
						}
						$aReport[] 	= "[End of $sLetterType breakdown for Customer Group: $sCustGroup]";
						$aReport[] 	= "";
					}

					$aReport[] 	= "[End of breakdown for Customer Group: $sCustGroup]";
					$aReport[] 	= "";
					$aReport[] 	= "";
				}
			}
			else
			{
				$aReport[] 	= "No automated late notices were generated.";
			}

			$sBody	= implode("\r\n", $aReport);

			// Send the report email, not queued
			$this->log("Sending report");
			if (Email_Notification::sendEmailNotification('LATE_NOTICE_REPORT', NULL, NULL, $sSubject, NULL, $sBody, NULL, TRUE))
			{
				$this->log("Report sent");
			}
			else
			{
				$this->log("Failed to email report.", TRUE);
			}

			$this->log("Finished.");

			// TODO: DEV ONLY -- UNCOMMENT THIS CONDITION
			if ($this->_bTestRun)
			{
				throw new Exception("Test Mode!");
			}

			return $iErrors;
		}
		catch(Exception $exception)
		{
			if (!$oDataAccess->TransactionRollback())
			{
				$this->showUsage('Transaction Rollback Failed');
				return 1;
			}

			$this->showUsage('Rolling back all database changes. '.$exception->getMessage());

			return 1;
		}
	}

	private function changeInvoiceRunAutoActionDateTime($iInvoiceRunId, $iAutomaticInvoiceAction)
	{
		$oQuery 		= new Query();
		$iInvoiceRunId	= $oQuery->EscapeString($iInvoiceRunId);
		$sSQL			= "UPDATE automatic_invoice_run_event SET actioned_datetime = '$this->_sRunDateTime' WHERE invoice_run_id IN (SELECT Id FROM InvoiceRun WHERE invoice_run_id = '$iInvoiceRunId') AND automatic_invoice_action_id = $iAutomaticInvoiceAction";
		$sMessage		= TRUE;
		if (!$oQuery->Execute($sSQL))
		{
			$sMessage	= ' Failed to update automatic_invoice_run_event for invoice_run ' . $iInvoiceRunId . ' and action ' . $iAutomaticInvoiceAction . ' to ' . $this->_sRunDateTime . '. '. $oQuery->Error();
			$this->log($sMessage, TRUE);
		}
		return $sMessage;
	}

	private function changeAccountAutomaticInvoiceAction($iAccount, $iFrom, $iTo, $sReason, $iInvoiceRunId)
	{
		$mError	= ChangeAccountAutomaticInvoiceAction($iAccount, $iFrom, $iTo, $sReason, $this->_sRunDateTime, $iInvoiceRunId);
		if ($mError !== TRUE)
		{
			$this->log($mError, TRUE);
		}
		return $mError;
	}

	private function getPDFContent($iCustGroupId, $sEffectiveDate, $iDocumentTypeId, $sPathToXMLFile, $sTargetMedia)
	{
		$this->startErrorCatching();

		$fileContents	= file_get_contents($sPathToXMLFile);
		$oPDFTemplate	= new Flex_Pdf_Template($iCustGroupId, $sEffectiveDate, $iDocumentTypeId, $fileContents, $sTargetMedia, TRUE);
		$oPDF			= $oPDFTemplate->createDocument();
		$oPDFTemplate->destroy();
		$oPDF			= $oPDF->render();

		if ($this->getCachedError())
		{
			return FALSE;
		}

		return $oPDF;
	}

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_EFFECTIVE_DATE => array(
				self::ARG_LABEL			=> "EFFECTIVE_DATE",
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "is the effective date for the generation of notices in 'YYYY-mm-dd " .
										"format [optional, default is today]",
				self::ARG_DEFAULT		=> time(),
				self::ARG_VALIDATION	=> 'Cli::_validDate("%1$s")'
			),

			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [fully functional EXCEPT emails will not be sent to clients]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

			self::SWITCH_SAMPLE => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "will send sample Notices to ".self::EMAIL_BILLING_NOTIFICATIONS,
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),

		);
	}
}

?>

<?php
/*
 * Cli_App_RecurringCharges
 * 
 * This program generates the installment charges of all RecurringCharges, that are eligible for installment charges to be generated
 * 
 * Each RecurringCharge is processed within its own transaction, and if it fails, it will not affect any of the other RecurringCharges
 */

class Cli_App_Recurring_Charges extends Cli
{
	const	SWITCH_TEST_RUN		= "t";
	
	private $_strLog = "";
	
	function run()
	{
		try
		{
			$this->log("Starting.");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				throw new Exception('Running in TEST MODE is not currently supported');
			}
			
			// Load Flex Framework
			$this->requireOnce('lib/classes/Flex.php');
			Flex::load();
			
			// Create variables for the report
			$arrRecChargesThatGeneratedMultipleCharges		= array();
			$arrRecChargesCompletedWithoutGeneratingCharges	= array();
			$intRecChargesSuccessfullyProcessed				= 0;
			$intRecChargesCompletedAfterGeneratingCharges	= 0;
			$intRecChargesNotDueForChargeGeneration = 0;
			$intActiveContinuingDebitRecChargesThatHaveSatisfiedRequirementsForCompletion	= 0;
			$intActiveContinuingCreditRecChargesThatHaveSatisfiedRequirementsForCompletion	= 0;
			$arrRecChargesFailedToProcess					= array();
			$arrRecChargesDueForChargeGeneration			= array(	"Successful" => array(),
																		"Failed"	=> array()
																	);
			$arrRecChargeDescriptions						= array();
			
			
			
			$intRecurringChargeStatusCompleted = Recurring_Charge_Status::getIdForSystemName('COMPLETED');
			$intRecurringChargeStatusActive = Recurring_Charge_Status::getIdForSystemName('ACTIVE');
			
			// This script will should be ok to run during an outstanding live invoice run, so don't bother testing for now
			
			// Retrieve all ACTIVE RecurringCharges
			$arrRecCharges = Recurring_Charge::getAllActiveRecurringCharges();
			$intTotalActiveRecCharges = count($arrRecCharges);
			$this->log("Processing {$intTotalActiveRecCharges} ACTIVE recurring charges");

			foreach ($arrRecCharges as $objRecCharge)
			{
				// Build a string to identify the RecurringCharge in the log
				$strRecCharge = "RecurringCharge {$objRecCharge->chargeType} - {$objRecCharge->description} ({$objRecCharge->id}) Account: {$objRecCharge->account}";
				if ($objRecCharge->service != null)
				{
					$strRecCharge .= " Service: {$objRecCharge->serviceFNN}";
				}
				$strRecCharge .= " MinCharge: \$". number_format($objRecCharge->minCharge, 4, '.', '');
				$strRecCharge .= " TotalCharged: \$". number_format($objRecCharge->totalCharged, 4, '.', '');
				$strRecCharge .= " Installment Charge: \$". number_format($objRecCharge->recursionCharge, 4, '.', '');
				
				$strRecCharge .= ($objRecCharge->continuable == 0)? " Not Continuable" : " Continuable";
				
				// Override
				$strRecCharge = $objRecCharge->getIdentifyingDescription(false, true, true, true, true, 4);
				
				$arrRecChargeDescriptions[$objRecCharge->id] = $strRecCharge;
				$this->log($strRecCharge);

				TransactionStart();
				try
				{
					// Check if the RecurringCharge has already satisfied the conditions for completion and is not continuable
					if ($objRecCharge->hasSatisfiedRequirementsForCompletion() && $objRecCharge->continuable == 0)
					{
						// Set it to completed
						$objRecCharge->setToCompleted();
						$intRecChargesNotDueForChargeGeneration++;
						$this->log("\tSet to COMPLETED without requiring any charges to be generated.  (it is not continuable)");
						
						$arrRecChargesCompletedWithoutGeneratingCharges[] = $objRecCharge;
					}
					elseif ($objRecCharge->needsToCreateInstallments())
					{
						// The recurring charge needs to create some installments
						try
						{
							// Generate the outstanding charge installments
							$arrInstallmentCharges = $objRecCharge->createOutstandingChargeInstallments();
							
							// Log the charges generated
							foreach ($arrInstallmentCharges as $objCharge)
							{
								$strCharge = "Created Charge: {$objCharge->id} charged on {$objCharge->chargedOn} for \$". number_format($objCharge->amount, 4, '.', '');
								$this->log("\t{$strCharge}");
							}
							
							// Check how many adjustments were created
							$intChargeCount = count($arrInstallmentCharges);
							if ($intChargeCount > 1)
							{
								// Multiple adustments were created, which should be uncommon
								if (!array_key_exists($intChargeCount, $arrRecChargesThatGeneratedMultipleCharges))
								{
									$arrRecChargesThatGeneratedMultipleCharges[$intChargeCount] = array();
								}
								$arrRecChargesThatGeneratedMultipleCharges[$intChargeCount][] = array(	"RecCharge"	=> $objRecCharge,
																										"Charges"	=> $arrInstallmentCharges
																										);
							}
							
							if ($objRecCharge->recurringChargeStatusId == $intRecurringChargeStatusCompleted)
							{
								// The Recurring Charge has now been flagged as completed
								$this->log("\tHas now been set to COMPLETED");
								
								$intRecChargesCompletedAfterGeneratingCharges++;
							}
							
							$arrRecChargesDueForChargeGeneration['Successful'][] = $objRecCharge;
						}
						catch (Exception $e)
						{
							// An Exception was thrown when trying to generate the installment charges
							throw new Exception_ChargeGeneration($e->getMessage());
						}
					}
					else
					{
						// The recurring charge doesn't need to create any installments right now
						$intRecChargesNotDueForChargeGeneration++;
						$this->log("\tNot due for charge generation");
					}
					
					// Check if the RecurringAdjustment has satisfied the requirements for completion, but is still Active (a continuable recurring charge)
					if ($objRecCharge->recurringChargeStatusId == $intRecurringChargeStatusActive && $objRecCharge->hasSatisfiedRequirementsForCompletion())
					{
						// It has and it is
						if ($objRecCharge->nature == NATURE_CR)
						{
							$intActiveContinuingCreditRecChargesThatHaveSatisfiedRequirementsForCompletion++;
						}
						else
						{
							$intActiveContinuingDebitRecChargesThatHaveSatisfiedRequirementsForCompletion++;
						}
					}
					
					$intRecChargesSuccessfullyProcessed++;
					
					TransactionCommit();
				}
				catch (Exception_ChargeGeneration $e)
				{
					// Failed while trying to generate charges
					TransactionRollback();
					$this->log("\tFailed to generate the charges (Changes have been rolled back).  Error: ". $e->getMessage());
					
					$arrRecChargesFailedToProcess[] = array(	'RecCharge'	=> $objRecCharge,
																'Error'		=> $e->getMessage(),
																'FailedDuringGeneration'	=> true
															);
					$arrRecChargesDueForChargeGeneration['Failed'][] = array(	'RecCharge'	=> $objRecCharge,
																				'Error'		=> $e->getMessage()
																				);
				}
				catch (Exception $e)
				{
					// Failed for any reason other than trying to generate the charges
					TransactionRollback();
					$this->log("\tError (Changes have been rolled back): ". $e->getMessage());
					
					$arrRecChargesFailedToProcess[] = array(	'RecCharge'					=> $objRecCharge,
																'Error'						=> $e->getMessage(),
																'FailedDuringGeneration'	=> false
															);
				}
			}

			$this->log("");
			$this->log("Generating Email Report...");
			$arrReportParts = array();

			$bolIssuesEncountered = false;

			$arrSummaryTableRows = array();
			
			$arrSummaryTableRows[] = array(	'Title'		=> 'Considered (ACTIVE)',
											'Value'		=> $intTotalActiveRecCharges,
											'Highlight'	=> false);
			
			$arrSummaryTableRows[] = array(	'Title'		=> 'Successfully Processed',
											'Value'		=> $intRecChargesSuccessfullyProcessed,
											'Highlight'	=> false);

			$arrSummaryTableRows[] = array(	'Title'		=> 'Failed to Process',
											'Value'		=> count($arrRecChargesFailedToProcess),
											'Highlight'	=> ((count($arrRecChargesFailedToProcess) > 0)? true : false));
			
			$arrSummaryTableRows[] = array(	'Title'		=> 'Due/eligible for Installment Generation',
											'Value'		=> count($arrRecChargesDueForChargeGeneration['Successful']) + count($arrRecChargesDueForChargeGeneration['Failed']),
											'Highlight'	=> false);

			$arrSummaryTableRows[] = array(	'Title'		=> 'Not due/eligible for Installment Generation',
											'Value'		=> $intRecChargesNotDueForChargeGeneration,
											'Highlight'	=> false);
			
			$arrSummaryTableRows[] = array(	'Title'		=> 'Successfully Generated Installments',
											'Value'		=> count($arrRecChargesDueForChargeGeneration['Successful']),
											'Highlight'	=> false);
			$arrSummaryTableRows[] = array(	'Title'		=> 'Failed when Generating Installments',
											'Value'		=> count($arrRecChargesDueForChargeGeneration['Failed']),
											'Highlight'	=> ((count($arrRecChargesDueForChargeGeneration['Failed']) > 0)? true : false));

			$intCountFailedForOtherReasons = count($arrRecChargesFailedToProcess) - count($arrRecChargesDueForChargeGeneration['Failed']);
			$arrSummaryTableRows[] = array(	'Title'		=> 'Failed for other reasons',
											'Value'		=> $intCountFailedForOtherReasons,
											'Highlight'	=> (($intCountFailedForOtherReasons > 0)? true : false));
			
			// Build the Email Report
			if (count($arrRecChargesThatGeneratedMultipleCharges) > 0)
			{
				// Some RecurringCharges generated more than 1 Charge
				foreach ($arrRecChargesThatGeneratedMultipleCharges as $intChargeCount=>$arrMultiChargedRecCharges)
				{
					$arrSummaryTableRows[] = array(	'Title'		=> "Generated {$intChargeCount} Installments",
													'Value'		=> count($arrMultiChargedRecCharges),
													'Highlight'	=> true);
				}
			}

			$arrSummaryTableRows[] = array(	'Title'		=> 'Set to COMPLETED after installments were made',
											'Value'		=> $intRecChargesCompletedAfterGeneratingCharges,
											'Highlight'	=> false);
			
			$arrSummaryTableRows[] = array(	'Title'		=> 'Set to COMPLETED without needing installments generated',
											'Value'		=> count($arrRecChargesCompletedWithoutGeneratingCharges),
											'Highlight'	=> ((count($arrRecChargesCompletedWithoutGeneratingCharges) > 0)? true : false));


			$arrSummaryTableRows[] = array(	'Title'		=> 'Recurring Credits that are continuing beyond the Minimum Credit',
											'Value'		=> $intActiveContinuingCreditRecChargesThatHaveSatisfiedRequirementsForCompletion,
											'Highlight'	=> (($intActiveContinuingCreditRecChargesThatHaveSatisfiedRequirementsForCompletion > 0)? true : false));

			$arrSummaryTableRows[] = array(	'Title'		=> 'Recurring Debits that are continuing beyond the Minimum Charge',
											'Value'		=> $intActiveContinuingDebitRecChargesThatHaveSatisfiedRequirementsForCompletion,
											'Highlight'	=> false);
			

			$strSummaryTableRows = "";
			foreach ($arrSummaryTableRows as $arrRow)
			{
				$strTitle = $arrRow['Title'];
				$strValue = $arrRow['Value'];
				if ($arrRow['Highlight'])
				{
					$strValue = "<strong style='color:#FF0000'>{$strValue}</strong>";
				}
				$strSummaryTableRows .= "
<tr>
	<td><strong>{$strTitle}</strong></td>
	<td>{$strValue}</td>
</tr>";
			}
			$strSummaryTable = "<br /><h1>Recurring Charges:</h1><table>$strSummaryTableRows</table>";
			
			$strNow = date('d-m-Y H:i:s');
			$arrReportParts[] = "<h1>Summary of Recurring Charge Installment Generation - $strNow</h1>";
			$arrReportParts[] = $strSummaryTable;

			$strFailedWhenGeneratingInstallments = "";
			if (count($arrRecChargesDueForChargeGeneration['Failed']))
			{
				$bolIssuesEncountered = true;
				
				$strFailedWhenGeneratingInstallments .= "<h2>RecurringCharges that failed during Installment Generation</h2><ul>";
				foreach ($arrRecChargesDueForChargeGeneration['Failed'] as $arrDetails)
				{
					$strRecCharge	= htmlspecialchars($arrRecChargeDescriptions[$arrDetails['RecCharge']->id]);
					$strError		= htmlspecialchars($arrDetails['Error']);
					$strFailedWhenGeneratingInstallments .= "
<li>
	$strRecCharge
	<ul>
		<li>$strError</li>
	</ul>
</li>";
				}
				$strFailedWhenGeneratingInstallments .= "</ul>";
				
				$arrReportParts[] = $strFailedWhenGeneratingInstallments;
			}
			
			$strFailedForOtherReason = "";
			if ($intCountFailedForOtherReasons > 0)
			{
				$bolIssuesEncountered = true;
				
				$strFailedForOtherReason .= "<h2>RecurringCharges that failed for reasons other than Installment Generation</h2><ul>";
				foreach ($arrRecChargesFailedToProcess as $arrDetails)
				{
					if ($arrDetails['FailedDuringGeneration'] == true)
					{
						// These have already been accounted for
						continue;
					}
					$strRecCharge	= htmlspecialchars($arrRecChargeDescriptions[$arrDetails['RecCharge']->id]);
					$strError		= htmlspecialchars($arrDetails['Error']);
					$strFailedForOtherReason .= "
<li>
	$strRecCharge
	<ul>
		<li>$strError</li>
	</ul>
</li>";
				}
				$strFailedForOtherReason .= "</ul>";
				
				$arrReportParts[] = $strFailedForOtherReason;
			}

			$strCompletedWithoutGeneratingCharges = "";
			if (count($arrRecChargesCompletedWithoutGeneratingCharges) > 0)
			{
				$bolIssuesEncountered = true;
				
				$strCompletedWithoutGeneratingCharges .= "<h2>Recurring Charges set to COMPLETED without needing installments generated</h2><ul>";
				foreach ($arrRecChargesCompletedWithoutGeneratingCharges as $objRecCharge)
				{
					$strRecCharge	= htmlspecialchars($arrRecChargeDescriptions[$objRecCharge->id]);
					$strCompletedWithoutGeneratingCharges .= "\n<li>$strRecCharge</li>";
				}
				$strCompletedWithoutGeneratingCharges .= "</ul>";
				$arrReportParts[] = $strCompletedWithoutGeneratingCharges;
			}
			
			$strRecChargesThatGeneratedMultipleCharges = "";
			if (count($arrRecChargesThatGeneratedMultipleCharges) > 0)
			{
				$bolIssuesEncountered = true;
				
				// Some RecurringCharges generated more than 1 Charge
				foreach ($arrRecChargesThatGeneratedMultipleCharges as $intChargeCount=>$arrMultiChargedRecCharges)
				{

					$strRecChargesThatGeneratedMultipleCharges = "<h2>Recurring Charges that Generated {$intChargeCount} Installments</h2><ul>";
					foreach ($arrMultiChargedRecCharges as $arrDetails)
					{
						$strRecCharge = htmlspecialchars($arrRecChargeDescriptions[$arrDetails['RecCharge']->id]);
						$strRecChargesThatGeneratedMultipleCharges .= "
<li>
	$strRecCharge
	<ol>";
						foreach ($arrDetails['Charges'] as $objCharge)
						{
							$strRecChargesThatGeneratedMultipleCharges .= "\n<li>Amount (ex GST): \${$objCharge->amount}, Charged On: {$objCharge->chargedOn}</li>";
							
						}
						
						$strRecChargesThatGeneratedMultipleCharges .= "
	</ol>
</li>";
					}
					$strRecChargesThatGeneratedMultipleCharges .= "</ul>";
					
					$arrReportParts[] = $strRecChargesThatGeneratedMultipleCharges;
				}
			}
			
			

			if ($bolIssuesEncountered)
			{
				$strEmailSubject = "[SUCCESS - ISSUES ENCOUNTERED] Recurring Charge Installment Generation - {$strNow}";
			}
			else
			{
				$strEmailSubject = "[SUCCESS] Recurring Charge Installment Generation - {$strNow}";
			}
			
			$arrReportParts[] = "";
			$arrReportParts[] = "Regards";
			$arrReportParts[] = "Flexor";
			
			$strEmailBody = implode("<br />", $arrReportParts);

			// Send email report
			$this->log("");
			$this->log("Emailing Recurring Charges Report...");
			$objEmailNotification = new Email_Notification(EMAIL_NOTIFICATION_RECURRING_CHARGE_REPORT);
			$objEmailNotification->setSubject($strEmailSubject);
			$objEmailNotification->setBodyHtml($strEmailBody);
			$objEmailNotification->addAttachment($this->_strLog, 'recurring_charge_installment_generation_log'. date('Ymd_His', strtotime($strNow)) .'.txt', 'text/plain');
			$objEmailNotification->send();
			$this->log("Sent successfully");
			$this->log("");
			

			$this->log("Finished.");
			return 0;
		}
		catch(Exception $exception)
		{
			// SEND EMAIL!!!
			$objEmailNotification = new Email_Notification(EMAIL_NOTIFICATION_RECURRING_CHARGE_REPORT);
			$objEmailNotification->setSubject("[FAILURE] Recurring Charge Installment Generation - ". date('d-m-Y H:i:s'));
			
			$strEmailBody = "The Recurring Charge Installment Generation program failed!!!\n\n".
							"Error Message: ". $exception->getMessage() ."\n\n".
							"Regards\nFlexor";
			
			$objEmailNotification->setBodyText($strEmailBody);
			$objEmailNotification->addAttachment($this->_strLog, 'recurring_charge_installment_generation_log'. date('Ymd_His', strtotime($strNow)) .'.txt', 'text/plain');
			$objEmailNotification->send();

			// We can now show the error message
			$this->log($exception->__toString());
			$this->showUsage($exception->getMessage());
			
			return 1;
		}
	}

	// I want to attach the log to the RecurringChargeSummary email
	protected function log($message, $isError=FALSE, $suppressNewLine=FALSE, $alwaysEcho=FALSE)
	{
		parent::log($message, $isError, $suppressNewLine, $alwaysEcho);
		
		$this->_strLog .= $message;
		if (!$suppressNewLine)
		{
			$this->_strLog .= "\n";
		}
	} 

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [performs full rollout and rollback (i.e. there should be no change)]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
		);
	}
}

class Exception_ChargeGeneration extends Exception {}

?>
<?php

class JSON_Handler_FollowUp extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}



	public function getDelinquentDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oFieldsToSort=null, $oFilter=null, $iSummaryCharacterLimit=30)
	{
		try
		{
			// Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL)))
			{
				throw new JSON_Handler_FollowUp_Exception('You do not have permission to view Follow-Ups.');
			}

			$aFilter		= get_object_vars($oFilter);
			$iNowSeconds	= time();

			// Convert the 'status' filter value to valid filters
			if (isset($aFilter['status']))
			{
				$mStatus	= $aFilter['status'];
				if (is_numeric($mStatus))
				{
					$aFilter['followup_closure_id']	= (int)$mStatus;
				}
				else
				{
					$oNewDueDateTimeConstraint	= false;
					switch ($mStatus)
					{
						case 'ACTIVE':
							$aFilter['followup_closure_id']			= 'NULL';
							break;
						case 'CURRENT':
							$aFilter['followup_closure_id']			= 'NULL';
							$oNewDueDateTimeConstraint				= new StdClass();
							$oNewDueDateTimeConstraint->mFrom		= date('Y-m-d H:i:s', $iNowSeconds);
							break;
						case 'OVERDUE':
							$aFilter['followup_closure_id']			= 'NULL';
							$oNewDueDateTimeConstraint				= new StdClass();
							$oNewDueDateTimeConstraint->mTo			= date('Y-m-d H:i:s', $iNowSeconds);
							break;
						case 'COMPLETED':
							$aFilter['followup_closure_type_id']	= FOLLOWUP_CLOSURE_TYPE_COMPLETED;
							break;
						case 'DISMISSED':
							$aFilter['followup_closure_type_id']	= FOLLOWUP_CLOSURE_TYPE_DISMISSED;
							break;
					}

					// Add a new due date time constraint if needed
					if ($oNewDueDateTimeConstraint)
					{
						if (isset($aFilter['due_datetime']))
						{
							// Existing due_datetime constraint, turn it into an 'AND' array constraint
							$aFilter['due_datetime']	= array($aFilter['due_datetime'], $oNewDueDateTimeConstraint);
						}
						else
						{
							// No exising, add the new one as the only due_datetime
							$aFilter['due_datetime']	= $oNewDueDateTimeConstraint;
						}
					}
				}

				// Remove the status filter as it is not a valid followup field alias
				unset($aFilter['status']);
			}

			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> FollowUp::searchFor(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
			else
			{
				$iLimit		= (max($iLimit, 0) == 0) ? null : (int)$iLimit;
				$iOffset	= ($iLimit === null) ? null : max((int)$iOffset, 0);
				$aFollowUps	= FollowUp::searchFor($iLimit, $iOffset, get_object_vars($oFieldsToSort), $aFilter);
				$aResults	= array();
				$iCount		= 0;

				foreach ($aFollowUps as $aFollowUp)
				{
					// Create ORM object
					$oFollowUp			= new FollowUp($aFollowUp);
					$oFollowUpStdClass	= $oFollowUp->toStdClass();

					// Add special 'followup_id' field (from temporary table 'followup_search')
					$oFollowUpStdClass->followup_id	= $aFollowUp['followup_id'];

					// Add other special fields
					$oFollowUpStdClass->followup_closure_type_id		= $aFollowUp['followup_closure_type_id'];
					$oFollowUpStdClass->followup_recurring_iteration	= $aFollowUp['followup_recurring_iteration'];
					$oFollowUpStdClass->assigned_employee_label			= Employee::getForId($oFollowUp->assigned_employee_id)->getName();
					$oFollowUpStdClass->followup_category_label			= FollowUp_Category::getForId($oFollowUp->followup_category_id)->name;
					$oFollowUpStdClass->status							= FollowUp::getStatus($oFollowUp->followup_closure_id, $oFollowUp->due_datetime);

					if ($oFollowUp->followup_recurring_id)
					{
						// Get the followup_recurring orm object to get the details
						$oFollowUpRecurring			= FollowUp_Recurring::getForId($oFollowUp->followup_recurring_id);
						$oFollowUpStdClass->details	= $oFollowUpRecurring->getDetails();
						$oFollowUpStdClass->summary	= $oFollowUpRecurring->getSummary($iSummaryCharacterLimit);
					}
					else
					{
						// Get the actual followup orm object to get details
						$oFollowUpTemp				= FollowUp::getForId($oFollowUpStdClass->followup_id);
						$oFollowUpStdClass->details	= $oFollowUpTemp->getDetails();
						$oFollowUpStdClass->summary	= $oFollowUpTemp->getSummary($iSummaryCharacterLimit);
					}

					// Add to Result Set
					$aResults[$iCount+$iOffset]	= $oFollowUpStdClass;
					$iCount++;
				}

				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> FollowUp::searchFor(null, null, get_object_vars($oFieldsToSort), $aFilter, true)
						);
			}
		}
		catch (JSON_Handler_CDR_Exception $oException)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}


//------------------------------------------------------------------------//
	// GetDelinquentFNNs
	//------------------------------------------------------------------------//
	/**
	 * GetDelinquentFNNs()
	 *
	 * Retrieves all the FNNs that have Delinquent CDRs with StartDatetime between that of the Date Range specified
	 *
	 * Retrieves all the FNNs that have Delinquent CDRs with StartDatetime between that of the Date Range specified
	 * It assumes the following data is passed:
	 * 		DBO()->Delinquents->StartDate		Date Range for the StartDatetime of the Delinquent CDRs
	 * 		DBO()->Delinquents->EndDate
	 *
	 * @return		void
	 * @method		GetDelinquentFNNs
	 */
	function GetDelinquentFNNs()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		$strStartDate	= DBO()->Delinquents->StartDate->Value;
		$strEndDate		= DBO()->Delinquents->EndDate->Value;

		if (!(Validate("ShortDate", $strStartDate) && Validate("ShortDate", $strStartDate)))
		{
			Ajax()->AddCommand("Alert", "ERROR: Dates must be specified as dd/mm/yyyy");
			return TRUE;
		}

		$strStartDate		= ConvertUserDateToMySqlDate($strStartDate);
		$strEndDate			= ConvertUserDateToMySqlDate($strEndDate);
		$arrColumns			= Array("FNN"					=>	"FNN",
									"ServiceType"			=>	"ServiceType",
									"Carrier"				=>	"Carrier",
									"TotalCost"				=>	"SUM(Cost)",
									"EarliestStartDatetime"	=>	"MIN(StartDatetime)",
									"LatestStartDatetime"	=>	"MAX(StartDatetime)",
									"Count"					=>	"Count(Id)");
		$strWhere			= "Status = ". CDR_BAD_OWNER ." AND StartDatetime BETWEEN <StartDate> AND <EndDate>";
		$strOrderBy			= "LatestStartDatetime DESC, FNN ASC, ServiceType ASC, Carrier ASC";
		$selDelinquentCDRs	= new StatementSelect("CDR", $arrColumns, $strWhere, $strOrderBy, "", "FNN, ServiceType, Carrier");
		$mixResult			= $selDelinquentCDRs->Execute(Array("StartDate" => $strStartDate, "EndDate" => $strEndDate));

		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Retrieving CDRs failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}

		$arrRecordSet	= $selDelinquentCDRs->FetchAll();
		$arrFNNs		= Array();

		foreach ($arrRecordSet as $arrRecord)
		{
			$strCarrier		= GetConstantDescription($arrRecord['Carrier'], "Carrier");
			$strTotalCost	= OutputMask()->MoneyValue($arrRecord['TotalCost']);
			$strEarliest	= date("d/m/Y", strtotime($arrRecord['EarliestStartDatetime']));
			$strLatest		= date("d/m/Y", strtotime($arrRecord['LatestStartDatetime']));

			// If the FNN is relating to an ADSL service then append the "i" to its description
			$strFNN = ($arrRecord['ServiceType'] == SERVICE_TYPE_ADSL) ? $arrRecord['FNN'] . "i" : $arrRecord['FNN'];

			// Build the description
			$strDescription  = str_pad($strFNN, 13, " ", STR_PAD_RIGHT);
			$strDescription .= str_pad(substr($strCarrier, 0, 25), 26, " ", STR_PAD_RIGHT);
			$strDescription .= str_pad($strTotalCost, 11, " ", STR_PAD_LEFT);
			$strDescription .= str_pad($arrRecord['Count'], 10, " ", STR_PAD_LEFT);
			$strDescription .= str_pad($strEarliest, 15, " ", STR_PAD_LEFT) . "  -  ";
			$strDescription .= str_pad($strLatest, 10, " ", STR_PAD_LEFT);

			$arrFNNs[] = Array(	"FNN"			=> $arrRecord['FNN'],
								"ServiceType"	=> $arrRecord['ServiceType'],
								"Carrier"		=> $arrRecord['Carrier'],
								"Description"	=> $strDescription);
		}

		// Return this Array to the client
		AjaxReply($arrFNNs);
		return TRUE;
	}




}

class JSON_Handler_CDR_Exception extends Exception
{
	// No changes
}

?>
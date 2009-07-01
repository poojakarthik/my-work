<?php
//----------------------------------------------------------------------------//
// Recurring_Charge
//----------------------------------------------------------------------------//
/**
 * Recurring_Charge
 *
 * Models a record of the RecurringCharge table
 *
 * Models a record of the RecurringCharge table
 *
 * @class	Recurring_Charge
 */
class Recurring_Charge extends ORM_Cached
{
	protected 			$_strTableName			= "RecurringCharge";
	protected static	$_strStaticTableName	= "RecurringCharge";
	
	// This defines the margin of error, when determing if the minimum charge has been reached (RecurringCharge.TotalCharge >= (RecurringCharge.MinCharge - MIN_CHARGE_MARGIN_OF_ERROR))
	const MIN_CHARGE_MARGIN_OF_ERROR = 0.5;
	
	const SEARCH_CONSTRAINT_RECURRING_CHARGE_STATUS_ID	= "RecurringCharge|recurring_charge_status_id";

	const ORDER_BY_ACCOUNT_NAME		= "Account|accountName";
	const ORDER_BY_SERVICE_FNN		= "Service|serviceFNN";
	const ORDER_BY_ACCOUNT_ID		= "RecurringCharge|Account";
	const ORDER_BY_CHARGE_TYPE		= "RecurringCharge|ChargeType";
	const ORDER_BY_DESCRIPTION		= "RecurringCharge|Description";
	const ORDER_BY_CREATED_ON		= "RecurringCharge|CreatedOn";
	const ORDER_BY_STARTED_ON		= "RecurringCharge|StartedOn";
	const ORDER_BY_MIN_CHARGE		= "RecurringCharge|MinCharge";
	const ORDER_BY_RECURSION_CHARGE	= "RecurringCharge|RecursionCharge";
	const ORDER_BY_NATURE			= "RecurringCharge|Nature";

	// This will store the pagination details of the last call to searchFor
	private static $lastSearchPaginationDetails = null;

	public static function getLastSearchPaginationDetails()
	{
		return self::$lastSearchPaginationDetails;
	}

	// Retrieves a list of column names (array[tidyName] = 'ActualColumnName')
	private static function _getColumns()
	{
		static $arrColumns;
		if (!isset($arrColumns))
		{
			$arrTableDefine = DataAccess::getDataAccess()->FetchTableDefine(self::$_strStaticTableName);
			
			foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
			{
				$arrColumns[self::tidyName($strName)] = $strName;
			}
			$arrColumns[self::tidyName($arrTableDefine['Id'])] = $arrTableDefine['Id'];
		}
		
		return $arrColumns;
	}


	// Note that this currently only handles "prop IS NULL", "prop IN (list of unquoted values)", "prop = unquoted value"
	private static function _prepareSearchConstraint($strProp, $mixValue)
	{
		$strSearch = "";
		if ($mixValue === NULL)
		{
			$strSearch = "$strProp IS NULL";
		}
		elseif (is_array($mixValue))
		{
			$strSearch = "$strProp IN (". implode(", ", $mixValue) .")";
		}
		else
		{
			$strSearch = "$strProp = $mixValue";
		}
		return $strSearch;
	}

	// Performs a search for Charges
	// It is assumed that none of the arguments are escaped yet
	// This will just return the TotalRecordCount if $bolGetTotalRecordCountOnly == true
	public static function searchFor($arrFilter=null, $arrSort=null, $intLimit=null, $intOffset=null, $bolGetTotalRecordCountOnly=false)
	{
		$arrWhereParts		= array();
		$arrOrderByParts	= array();
		
		// Build WHERE clause
		$arrWhereClauseParts = array();
		if (is_array($arrFilter))
		{
			foreach ($arrFilter as $arrConstraint)
			{
				switch ($arrConstraint['Type'])
				{
					case self::SEARCH_CONSTRAINT_RECURRING_CHARGE_STATUS_ID:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint(str_replace( '|', '.', self::SEARCH_CONSTRAINT_RECURRING_CHARGE_STATUS_ID), $arrConstraint['Value']);
						break;
				}
			}
		}
		$strWhereClause = (count($arrWhereClauseParts))? implode(" AND ", $arrWhereClauseParts) : "";
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				switch ($strColumn)
				{
					case self::ORDER_BY_ACCOUNT_NAME:
					case self::ORDER_BY_SERVICE_FNN:
					case self::ORDER_BY_ACCOUNT_ID:
					case self::ORDER_BY_CHARGE_TYPE:
					case self::ORDER_BY_DESCRIPTION:
					case self::ORDER_BY_CREATED_ON:
					case self::ORDER_BY_STARTED_ON:
					case self::ORDER_BY_MIN_CHARGE:
					case self::ORDER_BY_RECURSION_CHARGE:
					case self::ORDER_BY_NATURE:
						$arrOrderByParts[] = str_replace('|', '.', $strColumn) . ($bolAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__METHOD__ ." - Illegal sorting identifier: $strColumn");
						break;
				}
			}
		}
		$strOrderByClause = (count($arrOrderByParts) > 0)? implode(", ", $arrOrderByParts) : NULL;
		
		// Build LIMIT clause
		if ($intLimit !== NULL)
		{
			$strLimitClause = intval($intLimit);
			if ($intOffset !== NULL)
			{
				$strLimitClause .= " OFFSET ". intval($intOffset);
			}
			else
			{
				$intOffset = 0;
			}
		}
		else
		{
			$strLimitClause = "";
		}
		
		// Build SELECT statement
		$strFromClause = "RecurringCharge INNER JOIN Account ON RecurringCharge.Account = Account.Id LEFT JOIN Service ON RecurringCharge.Service = Service.Id";
		// Create the SELECT clause
		$arrColumns = self::_getColumns();

		$arrColumnsForSelectClause = array();
		foreach ($arrColumns as $strTidyName=>$strName)
		{
			$arrColumnsForSelectClause[] = "RecurringCharge.{$strName} AS $strTidyName";
		}
		// Add the ones that aren't from the charge table
		$arrColumnsForSelectClause[] = "COALESCE(Account.BusinessName, Account.TradingName) AS accountName";
		$arrColumnsForSelectClause[] = "Service.FNN AS serviceFNN";

		$strSelectClause = implode(',', $arrColumnsForSelectClause);
		
		// Create query to find out how many rows there are in total
		$strRowCountQuery = "SELECT COUNT(RecurringCharge.Id) as row_count FROM $strFromClause WHERE $strWhereClause;";
		
		// Check how many rows there are
		$objQuery = new Query();
		
		$mixResult = $objQuery->Execute($strRowCountQuery);
		if ($mixResult === FALSE)
		{
			throw new Exception("Failed to retrieve total record count for 'RecurringCharge Search' query - ". $objQuery->Error());
		}
		
		$intTotalRecordCount = intval(current($mixResult->fetch_assoc()));
		
		if ($bolGetTotalRecordCountOnly)
		{
			// return the total record count
			return $intTotalRecordCount;
		}
		
		// Create the proper query
		$selRecurringCharges = new StatementSelect($strFromClause, $strSelectClause, $strWhereClause, $strOrderByClause, $strLimitClause);
		
		if ($selRecurringCharges->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve records for 'RecurringCharge Search' query - ". $selRecurringCharges->Error());
		}

		// Create the Recurring_Charge objects (these objects will also include the fields accountName and serviceFNN)
		$arrRecChargeObjects = array();
		while ($arrRecord = $selRecurringCharges->Fetch())
		{
			$arrRecChargeObjects[$arrRecord['id']] = new self($arrRecord);
		}
		
		// Create the pagination details, if a Limit clause was used
		if ($intLimit === NULL || count($arrRecChargeObjects) == 0)
		{
			// Don't bother calulating pagination details
			self::$lastSearchPaginationDetails = null;
		}
		else
		{
			self::$lastSearchPaginationDetails = new PaginationDetails($intTotalRecordCount, $intLimit, intval($intOffset));
		}
		
		return $arrRecChargeObjects;
	}

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}

	// This will cancel the RecurringCharge (or request for recurring adjustment), and generate a payout adjustment if one is required.
	// If the Minimum Charge has been met, then the RecurringCharge will be flagged as having been completed
	// If a payout adjustment is required, then it will return the object created (this object will have been saved)
	// This will log the "Adjustment Request" action, if a payout adjustment is created, and a note declaring the cancelation of the RecurringAdjustment,
	// but only if $bolGenerateAppropriateActionsAndNotes is set to true
	public function setToCancelled($intEmployeeId=null, $bolLogAppropriateActionsAndNotes=true, $strAdditionalNotes=null)
	{
		$intEmployeeId	= ($intEmployeeId == null)? Employee::SYSTEM_EMPLOYEE_ID : $intEmployeeId;
		$strNowDateTime	= GetCurrentISODateTime();
		$strNowDate		= date('Y-m-d', strtotime($strNowDateTime));
		
		// Retrieve RecurringCharge Statuses that will be referenced often
		$intRecChargeStatusAwaitingApproval	= Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL');
		$intRecChargeStatusActive			= Recurring_Charge_Status::getIdForSystemName('ACTIVE');
		$intRecChargeStatusCancelled		= Recurring_Charge_Status::getIdForSystemName('CANCELLED');
		
		// Verify that the RecurringAdjustment is eligible for cancellation, based on the status
		switch ($this->recurringChargeStatusId)
		{
			case $intRecChargeStatusAwaitingApproval:
				// Set up vars pertaining to the fact that this is cancelling a request for a Recurring Adjustment
				$strActionPastTense = "Cancelled recurring adjustment that was pending approval";
				break;
				
			case $intRecChargeStatusActive:
				// The RecurringAdjustment is currently active.

				// Check if it has satisfied the requirements for completion
				if ($this->hasSatisfiedRequirementsForCompletion())
				{
					// Flag it as completed, instead of cancelled
					$this->setToCompleted($intEmployeeId, $bolLogAppropriateActionsAndNotes, $strAdditionalNotes);
					return NULL;
				}

				// Set up vars pertaining to the fact that this is cancelling a request for a Recurring Adjustment
				$strActionPastTense = "Cancelled recurring adjustment";

				break;
				
			default:
				$objCurrentStatus = Recurring_Charge_Status::getForId($this->recurring_charge_status_id);
				throw new Exception("Cannot cancel recurring adjustment when status is set to: {$objCurrentStatus->name}");
				break;
		}
		
		
		// Check if a payout adjustment should be made
		if ($this->recurringChargeStatusId == $intRecChargeStatusActive && $this->nature == NATURE_DR && !$this->hasReachedMinimumCharge())
		{
			// Create a payout adjustment
			$fltAmount = ($this->minCharge - $this->totalCharged) + $this->cancellationFee;
			
			$objPayoutCharge = new Charge();
			$objPayoutCharge->accountGroup	= $this->accountGroup;
			$objPayoutCharge->account		= $this->account;
			$objPayoutCharge->service		= $this->service;
			$objPayoutCharge->createdBy		= $intEmployeeId;
			$objPayoutCharge->createdOn		= $strNowDate;
			$objPayoutCharge->chargeType	= $this->chargeType;
			$objPayoutCharge->description	= $this->description;
			$objPayoutCharge->chargedOn		= $strNowDate;
			$objPayoutCharge->nature		= $this->nature;
			$objPayoutCharge->amount		= $fltAmount;
			$objPayoutCharge->notes			= "Payout due to premature cancellation of recurring charge";
			$objPayoutCharge->linkType		= CHARGE_LINK_RECURRING_CANCEL;
			$objPayoutCharge->linkId		= $this->id;
			$objPayoutCharge->status		= CHARGE_WAITING;
			$objPayoutCharge->global_tax_exempt = 0;
			
			$objPayoutCharge->save();
			
			// Add the record to the charge_recurring_charge table
			$objChargeRecurringCharge = new Charge_Recurring_Charge();
			$objChargeRecurringCharge->chargeId = $objPayoutCharge->id;
			$objChargeRecurringCharge->recurringChargeId = $this->id;
			$objChargeRecurringCharge->save();
			
			if ($bolLogAppropriateActionsAndNotes)
			{
				// Log the request for adjustment
				$strNature				= ($objPayoutCharge->nature == NATURE_DR)? "Debit" : "Credit";
				$strPayoutAmount		= number_format(AddGST($objPayoutCharge->amount), 2, '.', '');
				$strActionExtraDetails	= 	"Payout for cancellation of recurring adjustment\n".
											"Type: {$objPayoutCharge->chargeType} - {$objPayoutCharge->description} ({$strNature})\n".
											"Amount (Inc GST): \${$strPayoutAmount} {$strNature}";
				
				Action::createAction('Adjustment Requested', $strActionExtraDetails, $objPayoutCharge->account, $objPayoutCharge->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
			}
		}
		
		if ($bolLogAppropriateActionsAndNotes)
		{
			// Create a system note defining what has happened
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature = ($this->nature == NATURE_DR)? "Debit" : "Credit";
			
			$strMinCharge			= number_format(AddGST($this->minCharge), 2, ".", "");
			$strTotalCharged		= number_format(AddGST($this->totalCharged), 2, ".", "");
			
			$strNote = 	"{$strActionPastTense} (id: {$this->id})\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature})\n".
						"Originally configured on: {$strCreatedOn} by $strCreatedByEmployeeName\n".
						"Minimum charge (inc GST): \${$strMinCharge} {$strNature}\n".
						"Charged so far (inc GST): \${$strTotalCharged} {$strNature}";
						
			if ($this->cancellationFee > 0.0)
			{
				$strCancellationFee = number_format(AddGST($this->cancellationFee), 2, ".", "");
				$strNote .= "\nCancellation Fee (inc GST): \${$strCancellationFee} {$strNature}";
			}
			
			if (isset($objPayoutCharge))
			{
				$strPayoutAmount = number_format(AddGST($objPayoutCharge->amount), 2, '.', '');
				$strNote .= "\nA payout adjustment has been requested for \${$strPayoutAmount}";
			}
			
			$strAdditionalNotes = trim($strAdditionalNotes);
			if ($strAdditionalNotes != '')
			{
				$strNote .= "\nUser Comments:\n{$strAdditionalNotes}";
			}
			
			Note::createSystemNote($strNote, $intEmployeeId, $this->account, $this->service);
		}

		// Update the status of the RecurringCharge record
		$this->recurringChargeStatusId = $intRecChargeStatusCancelled;
		$this->save();
	}
	
	public function hasSatisfiedRequirementsForCompletion()
	{
		return $this->hasReachedMinimumCharge();
	}
	
	public function hasReachedMinimumCharge()
	{
		$intTimesToCharge	= $this->getTimesToCharge();

		$bolTotalChargedTest	= (bool)($this->totalCharged >= ($this->minCharge - self::MIN_CHARGE_MARGIN_OF_ERROR));
		
		// This test is probably more accurate
		$bolTotalRecursionsTest	= (bool)($this->TotalRecursions >= $intTimesToCharge);
		
		// Sanity check that both these tests arrived at the same answer
		if ($bolTotalChargedTest != $bolTotalRecursionsTest)
		{
			throw new Exception_Assertion("Check to see if RecurringCharge has reached the minimum charge gave 2 different answers when testing ".
					"against TotalCharged (check: TotalCharged >= (MinCharge - MarginOfError), gives: {$this->totalCharged} >= ({$this->minCharge} - ". self::MIN_CHARGE_MARGIN_OF_ERROR .") == ". print_r($bolTotalChargedTest, true) .") " .
					"and TotalRecursions (check: TotalRecursions >= calculatedTimesToCharge), gives: {$this->totalCharged} >= {$intTimesToCharge} == ". print_r($bolTotalRecursionsTest, true) .")", "RecurringCharge object: \n". print_r($this, true), "RecurringCharge Record Data Integrity Breach");
		}
		
		return $bolTotalChargedTest;
	}
	
	// Returns the theoretical ChargedOn date for the charge installment in question
	// It is a precondition that all necessary fields of the Recurring_Charge object, have been set to valid values
	// It uses the following fields of the RecurringCharge record to calculate when the installment is due: RecurringFreq, RecurringFreqType, in_advance, StartedOn
	// For example, if you want to know the ChargedOn date for the first installment then call getChargedOnDateForInstallment(1)
	//*************************** TODO - TEST THIS FUNCTION THOROUGHLY - TODO ****************************************************************************** 
	public function getChargedOnDateForInstallment($intInstallment)
	{
		$strTimeToAdd = "";
		
		if ($this->inAdvance)
		{
			// Charges are made in advance (on the first day of the period they represent)
			// Work out when the next payment is due based on StartedOn, TotalRecursions, RecurringFreq and RecurringFreqType
			
			switch ($this->recurringFreqType)
			{
				case BILLING_FREQ_DAY:
					$intTotalDaysFromStartedOn = $this->recurringFreq * ($intInstallment - 1);
					$strTimeToAdd = "+{$intTotalDaysFromStartedOn} days";
					break;
					
				case BILLING_FREQ_MONTH:
					$intTotalMonthsFromStartedOn = $this->recurringFreq * ($intInstallment - 1);
					$strTimeToAdd = "+{$intTotalMonthsFromStartedOn} months";
					break;
				
				case BILLING_FREQ_HALF_MONTH:
					// This one sucks (it could possibly be written more acurately)
					$intTotalNumberOfHalfMonths		= $this->recurringFreq * ($intInstallment - 1);
					$intTotalNumberOfWholeMonths	= floor($intTotalNumberOfHalfMonths / 2);
					$strTimeToAdd = "+{$intTotalNumberOfWholeMonths} months";
					if ($intTotalNumberOfHalfMonths % 2 == 1)
					{
						// There is a remaining half month to account for.  It should suffice to just add another 15 days
						$strTimeToAdd .= " 15 days";
					}
					break;
					
				default:
					throw new Exception("Unknown RecurringFreqType: {$this->recurringFreqType}");
			}
		}
		else
		{
			// Charges are made in arrears, (on the first day after the period they represent)
			switch ($this->recurringFreqType)
			{
				case BILLING_FREQ_DAY:
					$intTotalDaysFromStartedOn = $this->recurringFreq * $intInstallment;
					$strTimeToAdd = "+{$intTotalDaysFromStartedOn} days";
					break;
					
				case BILLING_FREQ_MONTH:
					$intTotalMonthsFromStartedOn = $this->recurringFreq * $intInstallment;
					$strTimeToAdd = "+{$intTotalMonthsFromStartedOn} months";
					break;
				
				case BILLING_FREQ_HALF_MONTH:
					// This one sucks (it could possibly be written more acurately)
					$intTotalNumberOfHalfMonths		= $this->recurringFreq * $intInstallment;
					$intTotalNumberOfWholeMonths	= floor($intTotalNumberOfHalfMonths / 2);
					$strTimeToAdd = "+{$intTotalNumberOfWholeMonths} months";
					if ($intTotalNumberOfHalfMonths % 2 == 1)
					{
						// There is a remaining half month to account for.  It should suffice to just add another 15 days
						$strTimeToAdd .= " 15 days";
					}
					break;
					
				default:
					throw new Exception("Unknown RecurringFreqType: {$this->recurringFreqType}");
			}
		}
		
		$strChargedOnDateForInstallment = date('Y-m-d', strtotime("{$this->startedOn} {$strTimeToAdd}"));
		
		return $strChargedOnDateForInstallment;
		
	}
	
	// This probably isn't necessary
	public function getChargedOnDateForNextInstallment($intEffectiveTotalInstallmentsAlreadyMade=null)
	{
		$intTotalInstallmentsAlreadyMade = ($intEffectiveTotalInstallmentsAlreadyMade !== null)? $intEffectiveTotalInstallmentsAlreadyMade : $this->totalRecursions;
		return $this->getChargedOnDateForInstallment($intTotalInstallmentsAlreadyMade);
	}
	
	// This returns the obligated times to charge (this can be zero if MinCharge is zero)
	public function getTimesToCharge()
	{
		$intTimesToCharge = round($this->minCharge / $this->recursionCharge);
		
		if (($intTimesToCharge * $this->recursionCharge) < ($this->minCharge - self::MIN_CHARGE_MARGIN_OF_ERROR))
		{
			// We must add one more time to charge to meet the minimum charge (this could possibly be a partial charge, if the recurring charge isn't continuable)
			$intTimesToCharge = $intTimesToCharge + 1;
		}
		
		return $intTimesToCharge;
	}
	
	public function hasPartialFinalInstallmentCharge()
	{
		if ($this->continuable != 0)
		{
			// The recurring charge will continue, after it has reached the minimum charge
			// There will be no final installment
			return false;
		}
		
		// Must not be continuable
		$intTimesToCharge = $this->getTimesToCharge();
		
		if 	(	(($intTimesToCharge * $this->recursionCharge) < ($this->minCharge - self::MIN_CHARGE_MARGIN_OF_ERROR))
				||
				(($intTimesToCharge * $this->recursionCharge) > ($this->minCharge + self::MIN_CHARGE_MARGIN_OF_ERROR))
			)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	// This is Ex GST
	public function calculatePartialFinalInstallmentCharge()
	{
		if (!$this->hasPartialFinalInstallmentCharge())
		{
			// The final installment will be the normal recursion charge 
			return $this->recursionCharge;
		}
		
		$intTimesToCharge = $this->getTimesToCharge();
		if ($intTimesToCharge < 1)
		{
			throw new Exception_Assertion(__METHOD__ ." - RecurringCharge (Id: {$this->id}, Account: {$this->account}, Service: {$this->service}) has calculated timesToCharge = '{$intTimesToCharge}', which should never be the case", "RecurringCharge object: \n". print_r($this, true), "RecurringCharge Record Data Integrity Breach");
		}
		
		$fltTotalChargedBeforeFinalInstallment = $this->recursionCharge * ($intTimesToCharge - 1);
		
		return $this->minCharge - $fltTotalChargedBeforeFinalInstallment;
	}
	
	// This calculates the next installment charge based on MinCharge, TotalCharged, RecursionCharge and Continuable
	public function calculateNextInstallmentCharge()
	{
		if ($this->continuable != 0)
		{
			// The Recurring Charge is continuable, so always charge the full RecursionCharge
			return $this->recursionCharge;
		}
		
		// The RecurringCharge is not continuable, so we should only charge up to MinCharge
		if (($this->totalCharged + $this->recursionCharge) <= $this->minCharge)
		{
			// Charge the full recursion charge amount
			return $this->recursionCharge;
		}
		
		// Sanity check that totalCharged < minCharge;
		if ($this->totalCharged >= $this->minCharge)
		{
			throw new Exception_Assertion("Trying to calculate the NextInstallmentCharge, but TotalCharged >= MinCharge ({$this->totalCharged} >= {$this->minCharge}) and it is not continuable", "RecurringCharge:\n". print_r($this, true));
		}
		
		return $this->minCharge - $this->totalCharged;
	}
	
	
	public function setToCompleted($intEmployeeId=null, $bolLogNote=true, $strAdditionalNotes=null)
	{
		$intEmployeeId	= ($intEmployeeId == null)? Employee::SYSTEM_EMPLOYEE_ID : $intEmployeeId;
		
		// Retrieve RecurringCharge Statuses that will be referenced often
		$intRecChargeStatusActive			= Recurring_Charge_Status::getIdForSystemName('ACTIVE');
		$intRecChargeStatusCompleted		= Recurring_Charge_Status::getIdForSystemName('COMPLETED');
		
		// Check that the requirements for Completion have been met
		if (!$this->hasSatisfiedRequirementsForCompletion())
		{
			throw new Exception("Cannot set recurring adjustment to COMPLETED because it has not satisfied the requirements for completion");
		}
		
		if ($this->recurringChargeStatusId != $intRecChargeStatusActive)
		{
			$objCurrentStatus = Recurring_Charge_Status::getForId($this->recurringChargeStatusId);
			throw new Exception("Cannot set recurring adjustment to COMPLETED because it is not currently active.  Its current status is: {$objCurrentStatus->name}");
		}
		
		if ($bolLogNote)
		{
			// Create a system note defining what has happened
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature = ($this->nature == NATURE_DR)? "Debit" : "Credit";
			
			$strMinCharge			= number_format(AddGST($this->minCharge), 2, ".", "");
			$strTotalCharged		= number_format(AddGST($this->totalCharged), 2, ".", "");
			
			$strNote = 	"Recurring Adjustment has been completed and discontinued (id: {$this->id})\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature})\n".
						"Originally configured on: {$strCreatedOn} by $strCreatedByEmployeeName\n".
						"Minimum charge (inc GST): \${$strMinCharge} {$strNature}\n".
						"Total charged (inc GST): \${$strTotalCharged} {$strNature}";

			$strAdditionalNotes = trim($strAdditionalNotes);
			if ($strAdditionalNotes != '')
			{
				$strNote .= "\nUser Comments:\n{$strAdditionalNotes}";
			}
			
			Note::createSystemNote($strNote, $intEmployeeId, $this->account, $this->service);
		}

		// Update the status of the RecurringCharge record
		$this->recurringChargeStatusId = $intRecChargeStatusCompleted;
		$this->save();
	}
	
	public function setToDeclined($intEmployeeId=null, $bolLogAction=true, $strReason=null)
	{
		$intEmployeeId	= ($intEmployeeId == null)? Employee::SYSTEM_EMPLOYEE_ID : $intEmployeeId;
		
		// Retrieve RecurringCharge Statuses that will be referenced often
		$intRecChargeStatusDeclined				= Recurring_Charge_Status::getIdForSystemName('DECLINED');
		$intRecChargeStatusAwaitingApproval		= Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL');
		
		// Check that the recurring charge is currently awaiting approval
		if ($this->recurringChargeStatusId != $intRecChargeStatusAwaitingApproval)
		{
			$objCurrentStatus = Recurring_Charge_Status::getForId($this->recurringChargeStatusId);
			throw new Exception("Cannot decline the request for recurring adjustment because it isn't currently awaiting approval.  Its current status is: {$objCurrentStatus->name}");
		}
		
		if ($bolLogAction)
		{
			// Log the 'Recurring Adjustment Request Outcome' action, having taken place
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature = ($this->nature == NATURE_DR)? "Debit" : "Credit";
			
			$strMinCharge					= number_format(AddGST($this->minCharge), 2, '.', '');
			$strRecursionChargeDescription	= $this->getRecursionChargeDescription(true);
			
			$strNote = 	"Request for the recurring adjustment has been REJECTED.\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature}) (id: {$this->id})\n".
						"Requested on: {$strCreatedOn} by {$strCreatedByEmployeeName}\n".
						"Minimum charge (inc GST): \${$strMinCharge} {$strNature}\n".
						"Recurring charge (inc GST): {$strRecursionChargeDescription}";

			$strReason = trim($strReason);
			if ($strReason != '')
			{
				$strNote .= "\nReason:\n{$strReason}";
			}
			
			Action::createAction('Recurring Adjustment Request Outcome', $strNote, $this->account, $this->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}

		// Update the RecurringCharge record
		$this->recurringChargeStatusId = $intRecChargeStatusDeclined;
		$this->save();
	}
	
	public function setToApproved($intEmployeeId=null, $bolLogAction=true)
	{
		$intEmployeeId	= ($intEmployeeId == null)? Employee::SYSTEM_EMPLOYEE_ID : $intEmployeeId;
		
		// Retrieve RecurringCharge Statuses that will be referenced often
		$intRecChargeStatusActive				= Recurring_Charge_Status::getIdForSystemName('ACTIVE');
		$intRecChargeStatusAwaitingApproval		= Recurring_Charge_Status::getIdForSystemName('AWAITING_APPROVAL');
		
		// Check that the recurring charge is currently awaiting approval
		if ($this->recurringChargeStatusId != $intRecChargeStatusAwaitingApproval)
		{
			$objCurrentStatus = Recurring_Charge_Status::getForId($this->recurringChargeStatusId);
			throw new Exception("Cannot approve the request for recurring adjustment because it isn't currently awaiting approval.  Its current status is: {$objCurrentStatus->name}");
		}
		
		if ($bolLogAction)
		{
			// Log the 'Recurring Adjustment Request Outcome' action, having taken place
			$objCreatedByEmployee		= Employee::getForId($this->createdBy);
			$strCreatedOn				= date('d-m-Y', strtotime($this->createdOn));
			$strCreatedByEmployeeName	= $objCreatedByEmployee->getName();
			
			$strNature = ($this->nature == NATURE_DR)? "Debit" : "Credit";
			
			$strMinCharge					= number_format(AddGST($this->minCharge), 2, '.', '');
			$strRecursionChargeDescription	= $this->getRecursionChargeDescription(true);
			
			$strNote = 	"Request for the recurring adjustment has been APPROVED.\n".
						"Type: {$this->chargeType} - {$this->description} ({$strNature}) (id: {$this->id})\n".
						"Requested on: {$strCreatedOn} by {$strCreatedByEmployeeName}\n".
						"Minimum charge (inc GST): \${$strMinCharge} {$strNature}\n".
						"Recurring charge (inc GST): {$strRecursionChargeDescription}";
			
			Action::createAction('Recurring Adjustment Request Outcome', $strNote, $this->account, $this->service, null, $intEmployeeId, Employee::SYSTEM_EMPLOYEE_ID);
		}

		// Update the RecurringCharge record
		$this->approvedBy = $intEmployeeId;
		$this->recurringChargeStatusId = $intRecChargeStatusActive;
		$this->save();
	}
	
	
	// Returns a string defining the charge installment, whether or not it's charged in advance or in arrears, and the charge frequency
	// And whether or not it is a credit or debit. I.E. '$100.00 credited in arrears every 2 months' or '$50.00 charged in advance, every month'
	public function getRecursionChargeDescription($bolIncludeGST, $intDecPlaces=2)
	{
		switch ($this->recurringFreqType)
		{
			case BILLING_FREQ_DAY:
				$strFreqType = "day";
				break;
				
			case BILLING_FREQ_MONTH:
				$strFreqType = "month";
				break;
				
			case BILLING_FREQ_HALF_MONTH:
				$strFreqType = "half-month";
				break;
		}
		if ($this->recurringFreq == 1)
		{
			$strFreq = '';
			$strFreqTypePluraliserSuffix = '';
		}
		else
		{
			$strFreq = "$intRecurringFreq ";
			$strFreqTypePluraliserSuffix = 's';
		}
		
		$strIndividualChargePeriod	= "{$strFreq}{$strFreqType}{$strFreqTypePluraliserSuffix}";
		$strInAdvanceInArrears		= ($this->inAdvance == true)? "advance" : "arrears";
		$strNatureAsAction			= ($this->nature == NATURE_DR)? "charged" : "credited";
		$fltRecursionCharge			= $this->recursionCharge;
		
		if ($this->hasPartialFinalInstallmentCharge())
		{
			$fltFinalInstallmentCharge = $this->calculatePartialFinalInstallmentCharge();
			$fltFinalInstallmentCharge = ($bolIncludeGST)? AddGST($fltFinalInstallmentCharge) : $fltFinalInstallmentCharge;
			$strFinalInstallmentCharge = number_format($fltFinalInstallmentCharge, $intDecPlaces, '.', '');
			
			$strFinalInstallmentChargeClause = " with final charge: \${$strFinalInstallmentCharge}";
		}
		else
		{
			$strFinalInstallmentChargeClause = "";
		}

		$strContinuable = ($this->continuable)? " (Continuable)" : " (Not Continuable)";

		$fltRecursionCharge = ($bolIncludeGST)? AddGST($fltRecursionCharge) : $fltRecursionCharge;
		
		$intTimesToCharge = $this->getTimesToCharge();
		$strRecursionCharge = number_format($fltRecursionCharge, $intDecPlaces, '.', '');
		
		$strDescription = "\${$strRecursionCharge} {$strNatureAsAction} in {$strInAdvanceInArrears} every {$strIndividualChargePeriod} $intTimesToCharge times{$strFinalInstallmentChargeClause}{$strContinuable}";
		return $strDescription;
	}
	
	// Builds a description which can be used to identify the recurring adjustment
	public function getIdentifyingDescription($bolIncludeGST, $bolIncludeAccountAndServiceIds=false, $bolIncludeRecChargeId=false, $bolIncludeStartDate=false, $bolIncludeCreationDate=false, $intDecPlaces=2)
	{
		$strRecChargeDescription	= $this->getRecursionChargeDescription($bolIncludeGST, $intDecPlaces);
		
		if ($bolIncludeGST)
		{
			$strMinChargeFormatted		= number_format(AddGST($this->minCharge), $intDecPlaces, '.', '');
			$strIncludeExcludeGST		= "Inc";
		}
		else
		{
			$strMinChargeFormatted		= number_format($this->minCharge, $intDecPlaces, '.', '');
			$strIncludeExcludeGST		= "Ex";
		}
		
		$strNature					= ($this->nature == NATURE_DR)? 'Debit' : 'Credit';
		
		$strDesc					= "{$this->chargeType} - {$this->description}, Minimum {$strNature}: \${$strMinChargeFormatted} ({$strIncludeExcludeGST} GST) having {$strRecChargeDescription}";
		
		if ($bolIncludeAccountAndServiceIds)
		{
			$strDesc .= ", Account: {$this->account}";
			if ($this->service != null)
			{
				$objService = Service::getForId($this->service);
				
				$strDesc .= ", Service: {$objService->FNN}";
			}
		}
		
		if ($bolIncludeStartDate)
		{
			$strDesc .= ", Starting: ". date('d-m-Y', strtotime($this->startedOn));
		}

		if ($bolIncludeCreationDate)
		{
			$strDesc .= ", Configured: ". date('d-m-Y', strtotime($this->createdOn));
		}
		
		if ($bolIncludeRecChargeId)
		{
			$strDesc .= " (Id: {$this->id})";
		}
		
		return $strDesc;
	}
	
	// Returns true if the RecurringCharge is allowed to generate charges, based on the current state of the account that those
	// charges are applied to. Else returns fales
	public function isAccountEligibleForChargeGeneration()
	{
		$objAccount = Account::getForId($this->account);
		if ($objAccount == null)
		{
			throw new Exception("Associated Account (Id: {$this->account}) could not be found");
		}
		
		// RecurringCharges only get generated if their associated account is active
		if ($objAccount->archived == ACCOUNT_STATUS_ACTIVE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	// Returns true if the RecurringCharge is allowed to generate charges, based on the current state of the service that those
	// charges are applied to. Else returns fales (this does not consider the state of the account)
	// This will throw an exception if the RecurringCharge is not associated with a service
	public function isServiceEligibleForChargeGeneration()
	{
		if ($this->service == null)
		{
			throw new Exception_Assertion("RecurringCharge is not associated with a service, yet the 'isServiceEligibleForChargeGeneration' test was called", "RecurringCharge:\n". print_r($this, true));
		}
		
		// Get the most recent service object modelling this service on this account
		$objService = Service::getForId($this->service, false, true);
		
		// RecurringCharges only get generated if their associated service is active
		if ($objService->status == SERVICE_ACTIVE)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	// Returns TRUE if the RecurringAdjustment needs to create installment adjustments pending as of today (NOW())
	// It is a precondition that $this->recurringChargeStatusId == ACTIVE and if the minimum number of installments has already been made, then 
	// the recurring charge must be continuable
	// THIS FUNCTION DOES NOT USE RecurringCharge.LastChargedOn to check if the next installment to make, is greater then LastChargedOn and <= TODAY
	// I think this is the way to go but you should check this with Rich *****************************************************************************************
	public function needsToCreateInstallments()
	{
		if ($this->recurringChargeStatusId != Recurring_Charge_Status::getIdForSystemName('ACTIVE'))
		{
			throw new Exception('Recurring Charge is not ACTIVE');
		}
 		
		if (!$this->isAccountEligibleForChargeGeneration())
		{
			return false;
		}
		
		if ($this->service != null)
		{
			if (!$this->isServiceEligibleForChargeGeneration())
			{
				return false;
			}
		}
		
		$intNextInstallment	= $this->totalRecursions + 1;
		$intTimesToCharge	= $this->getTimesToCharge();
		$strTodaysDate		= GetCurrentISODate();
		
		if ($intNextInstallment > $intTimesToCharge)
		{
			// All obligated installments have been created
			// Considering the RecurringCharge is still ACTIVE, then it must be continuable, otherwise it would have been set to COMPLETED
			if ($this->continuable == 0)
			{
				// This should never happen
				throw new Exception_Assertion("All obligated charges have been produced and this recurring charge is not continuable, but still ACTIVE.  It should be set to COMPLETED", "Method: ". __METHOD__ ."\nRecurringCharge object: \n". print_r($this, true), "RecurringCharge Record Data Integrity Breach");
			}
		}

		// Get the ChargedOn timestamp for the next installment
		$strChargedOnForNextInstallment = $this->getChargedOnDateForInstallment($intNextInstallment);
		
		// Don't bother checking the $strChargedOnForNextInstallment > $this->lastChargedOn
		
		return ($strChargedOnForNextInstallment <= $strTodaysDate)? true : false;
	}
	
	// Generates however many installment charges are pending, for the date in question
	// Multiple charges might need to be created for the one recurring adjustment
	// Whatever charges are created will be returned in an array
	// If none are created, then the array will be empty
	// This will actually save the Charge records, and also update the RecurringCharge record appropriately (and charge_recurring_charge).
	// It will throw an exception on error
	// This will set the RecurringCharge to completed, if it has satisfied the requirements for completion and is not continuable
	public function createOutstandingChargeInstallments()
	{
		$arrInstallments = array();
		$strToday = GetCurrentISODate();
		
		// Check if the RecurringCharge has already satisfied the conditions for completion and is not continuable
		if ($this->hasSatisfiedRequirementsForCompletion() && $this->continuable == 0)
		{
			// Flag it as completed (this will save the record)
			$this->setToCompleted();
			return $arrInstallments;
		}
		
		while ($this->needsToCreateInstallments())
		{
			$intNextInstallment = $this->totalRecursions + 1;
			$strChargedOnForNextInstallment = $this->getChargedOnDateForInstallment($intNextInstallment);
			
			if ($strChargedOnForNextInstallment > $strToday)
			{
				// This should never happen
				throw new Exception_Assertion(__METHOD__ ." - objRecCharge->needsToCreateInstallments() == TRUE but ChargedOn date for next installment is in the future (ChargedOn = $strChargedOnForNextInstallment, Today = $strToday)", "RecurringCharge object: \n". print_r($this, true), "RecurringCharge Record Data Integrity Breach");
			}
			
			// I should probably check that there aren't more associated Charge records, than there should be
			// This will somewhat protect against more than one instance of this process being run at the one time
			$intCurrentInstallmentChargeRecordCount = $this->_getInstallmentChargeRecordCount();
			if ($intCurrentInstallmentChargeRecordCount > $this->totalRecursions)
			{
				// This should never happen
				throw new Exception_Assertion(__METHOD__ ." - There are currently '{$intCurrentInstallmentChargeRecordCount}' installment charge records associated with this recurring charge, but there should only be '{$this->totalRecursions}' records", "RecurringCharge object: \n". print_r($this, true), "RecurringCharge Record Data Integrity Breach");
			}
			
			// Calculate how much to charge (could be a partial installment)
			$fltAmountToCharge = $this->calculateNextInstallmentCharge();
			
			if ($fltAmountToCharge <= 0.0000)
			{
				// This should never happen
				throw new Exception_Assertion(__METHOD__ ." - Calculated amount to charge == {$fltAmountToCharge}", "RecurringCharge object: \n". print_r($this, true));
			}
			
			// Create the installment Charge
			$objCharge					= new Charge();
			$objCharge->accountGroup	= $this->accountGroup;
			$objCharge->account			= $this->account;
			$objCharge->service			= $this->service;
			$objCharge->createdBy		= $this->createdBy;
			$objCharge->approvedBy		= $this->approvedBy;
			$objCharge->createdOn		= $this->createdOn;
			$objCharge->chargedOn		= $strChargedOnForNextInstallment;
			$objCharge->chargeType		= $this->chargeType;
			$objCharge->description		= $this->description;
			$objCharge->nature			= $this->nature;
			$objCharge->amount			= $fltAmountToCharge;
			$objCharge->linkType		= CHARGE_LINK_RECURRING;
			$objCharge->linkId			= $this->id;
			$objCharge->status			= CHARGE_APPROVED;
			$objCharge->notes			= "Installment {$intNextInstallment}";
			$objCharge->globalTaxExempt	= 0;
			$objCharge->save();
			
			$arrInstallments[] = $objCharge;
			
			// Now save the charge_recurring_charge record
			$objChargeRecurringCharge						= new Charge_Recurring_Charge();
			$objChargeRecurringCharge->chargeId				= $objCharge->id;
			$objChargeRecurringCharge->recurringChargeId	= $this->id;
			$objChargeRecurringCharge->save();
			
			// Now update the RecurringCharge record
			$this->lastChargedOn	= $strChargedOnForNextInstallment;
			$this->totalCharged		= $this->totalCharged + $fltAmountToCharge;
			$this->totalRecursions	= $this->totalRecursions + 1;
			
			// Check if the RecurringCharge has satisfied the conditions for completion and is not continuable
			if ($this->hasSatisfiedRequirementsForCompletion() && $this->continuable == 0)
			{
				// Flag it as completed (this will save the record)
				$this->setToCompleted();
				break;
			}
			$this->save();
		}
		
		return $arrInstallments;
	}

	private function _getInstallmentChargeRecordCount()
	{
		$selInstallmentChargeCount = $this->_preparedStatement('selCountInstallmentCharges');
		
		if ($selInstallmentChargeCount->Execute(Array('RecurringChargeId' => $this->id)) === false)
		{
			throw new Exception('Could not calculate the count of installment charge records relating to this recurring charge');
		}
		
		$arrRecord = $selInstallmentChargeCount->Fetch();
		return $arrRecord['charge_record_count'];
	}

	// Retrieves all RecurringCharges that are currently active
	public static function getAllActiveRecurringCharges()
	{
		// Set the filter constraints (only retrieve the recurring adjusments that are awaiting approval)
		$arrFilter = array('recurringChargeStatus'	=> array(	'Type'	=> self::SEARCH_CONSTRAINT_RECURRING_CHARGE_STATUS_ID,
																'Value'	=> Recurring_Charge_Status::getIdForSystemName('ACTIVE')
													)
						);
		
		// Order by AccountId ascending
		$arrSort = array(Recurring_Charge::ORDER_BY_ACCOUNT_ID => true);
		return self::searchFor($arrFilter, $arrSort);
	}
	
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "Id ASC");
					break;
				case 'selCountInstallmentCharges':
					$arrColumns		=	Array("charge_record_count" => "COUNT(*)");
					$strFromClause	=	"charge_recurring_charge ".
										"INNER JOIN RecurringCharge ON charge_recurring_charge.recurring_charge_id = RecurringCharge.Id ".
										"INNER JOIN Charge ON charge_recurring_charge.charge_id = Charge.Id AND Charge.LinkType = ". CHARGE_LINK_RECURRING;
					$arrPreparedStatements[$strStatement]	= new StatementSelect($strFromClause, $arrColumns, "RecurringCharge.Id = <RecurringChargeId>");
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>
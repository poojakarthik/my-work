<?php

// TODO: Remove this explicit require
require_once dirname(__FILE__) . '/data/Data_Source.php';

class CDR extends ORM_Cached {
	protected $_strTableName = "CDR";
	protected static $_strStaticTableName = "CDR";

	public function rate($bForceReRate=false, $bUseExistingRate=false) {
		static $oQuery;
		$oQuery = ($oQuery) ? $oQuery : new Query();

		if (in_array($this->Status, array(CDR_NORMALISED, CDR_RERATE, CDR_RATE_NOT_FOUND)) || ($bForceReRate && $this->Rate)) {
			// Update Earliest/Latest CDR
			$oService = Service::getForId($this->Service);
			$oService->EarliestCDR = ($oService->EarliestCDR) ? min($oService->EarliestCDR, $this->StartDatetime) : $this->StartDatetime;
			$oService->LatestCDR = ($oService->LatestCDR) ? max($oService->LatestCDR, $this->StartDatetime) : $this->StartDatetime;
			$oService->save();
			
			$oRate = ($bUseExistingRate && $this->Rate) ? Rate::getForId($this->Rate) : Rate::getForCDR($this);

			// Did we find a Rate?
			if (!$oRate) {
				$this->Charge = null;
				$this->Rate = null;
				$this->Status = CDR_RATE_NOT_FOUND;
				$this->save();

				throw new Exception_Rating_RateNotFound("No Rate found for CDR {$this->Id}");
			}
			$this->Rate = $oRate->Id;

			// DISCOUNTING (per-CDR): Eligibility
			$bDiscount = false;

			// Does the Rate Plan have discounting enabled?
			$sRatePlanEffectiveDatetime = min(max($this->StartDatetime, $oService->CreatedOn), ($oService->ClosedOn === null) ? Data_Source_Time::END_OF_TIME : $oService->ClosedOn);
			$oRatePlan = $oService->getCurrentPlan($sRatePlanEffectiveDatetime, false);
			if ($oRatePlan->discount_cap !== null && (float)$oRatePlan->discount_cap >= 0.01) {
				// Plan has a Discount Cap
				// Check to see that this CDR is not older than our latest discounted CDR
				if ($oService->discount_start_datetime !== null && $this->StartDatetime < $oService->discount_start_datetime) {
					// We need to re-rate all CDRs that are newer or as new as this CDR to ensure our ordering is correct
					throw new Exception_Rating_CDROutOfSequence($this->Id);
				} else {
					$bDiscount = true;
				}
			}

			// CHARGE: Calculate Base Charge
			$this->Charge = $this->calculcateCharge();

			// DISCOUNTING (per-CDR): Application
			if ($bDiscount) {
				$oGetUnbilledCDRTotal = self::_preparedStatement('selUnbilledCDRTotal');
				if ($oGetUnbilledCDRTotal->Execute(array('service_id'=>$this->Service,'invoice_run_id'=>$this->invoice_run_id)) === false) {
					throw new Exception_Database($oGetUnbilledCDRTotal->Error());
				}
				$aUnbilledCDRTotal = $oGetUnbilledCDRTotal->Fetch();

				// Have we exceeded our discount cap?
				if ((float)$aUnbilledCDRTotal['cdr_charge'] >= (float)$oRatePlan->discount_cap) {
					// Apply Discount
					$fDiscountPercentage = (float)$oRate->discount_percentage;
					if ($fDiscountPercentage) {
						// NOTE: This looks incorrect (discount percentage should be divided by 100), but this is how it behaved in the last implementation
						//$this->Charge -= $this->Charge * $fDiscountPercentage;

						// Replaced with correct code -- couldn't find any cases of discounting in reality
						$this->Charge -= $this->Charge * ($fDiscountPercentage / 100);
					}
				} else {
					// Update
					$oService->discount_start_datetime = $this->StartDatetime;
					$oService->save();
				}
			}

			// ROUNDING
			$this->Charge = Rate::roundToRatingStandard($this->Charge);

			// SERVICE TOTALS: Update progressive totals (deprecated, but we'll copy the functionality anyway)
			if ($this->Charge > 0 && $this->Credit == 0) {
				if ($oRate->Uncapped) {
					$oService->UncappedCharge += $this->Charge;
				} else {
					$oService->CappedCharge += $this->Charge;
				}
				$oService->save();
			}

			$this->Status = CDR_RATED;
			$this->RatedOn = Data_Source_Time::currentTimestamp();

			$this->save();
		} else {
			throw new Exception("Cannot Rate CDR #{$this->Id} with Status ".GetConstantName($this->Status, 'CDR').((!$bForceReRate) ? '' : ' (even when forced)'));
		}
	}

	public function calculcateCharge() {
		if ($this->Rate) {
			$oRate = Rate::getForId($this->Rate);
			return $oRate->calculateChargeForCDR($this);
		} else {
			throw new Exception("Cannot calculate Charge for CDR #{$this->Id} with Status ".GetConstantName($this->Status, 'CDR'));
		}
	}

	public static function getForServiceAndStatus($iServiceId, $aStatus) {
		$oQuery = new Query();
		$sStatus = implode(",", $aStatus);
		$mResult = $oQuery->Execute("
			SELECT *
			FROM CDR
			WHERE Service = {$iServiceId}
			AND Status IN ({$sStatus})"
		);

		$aResult = array();
		if ($mResult) {
			while ($aRow = $mResult->fetch_assoc()) {
				$aResult[] = new self($aRow);
			}
		}
		return $aResult;
	}

	public static function GetDelinquentCDRsPaginated($iLimit, $iOffset, $aSortFields, $aFilter , $bCountOnly=false) {

		$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
		$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$sWhere = '';
		if (isset($aFilter['also_include'])) {
			if (count($aFilter['also_include']->aValues) > 0) {
				$sWhere = " OR c.Id in (".implode(",", $aFilter['also_include']->aValues).")";
			}
			unset($aFilter['also_include']);
		}

		$aAliases = array('Status'=>'c.Status', 'FNN'=>'c.FNN', 'ServiceType'=>'c.ServiceType', 'Carrier'=>'c.Carrier', 'StartDatetime'=>'c.StartDatetime');

		$aWhere = StatementSelect::generateWhere($aAliases, $aFilter);
		$sOrderByClause = StatementSelect::generateOrderBy(array(), $aSortFields);
		$sLimitClause = StatementSelect::generateLimit($iLimit, $iOffset);

		$arrColumns = array(
			"Id" => "c.Id",
			"Cost" => "c.Cost * IF(c.Credit, -1, 1)",
			"StartDatetime" => "c.StartDatetime",
			"Status" => "c.Status",
			"StatusDescr" => " CASE c.Status WHEN ".CDR_BAD_OWNER." THEN '{$sDelinquentStatusDescr}' WHEN ". CDR_DELINQUENT_WRITTEN_OFF." THEN '{$sWriteOffStausDescr}' ELSE CONCAT (CONCAT_WS(' ', 'Account ID:', s.Account), CONCAT_WS(' ',' FNN:', s.FNN)) END",
			"WrittenOffBy" => "concat_ws(' ',e.FirstName,e.LastName)",
			"WrittenOffOn" => "w.created_datetime"
		);

		$selDelinquentCDRs = new StatementSelect("CDR c LEFT JOIN Service s ON (c.Service = s.Id ) LEFT JOIN cdr_delinquent_writeoff w ON (c.Id= w.cdr_id) LEFT JOIN Employee e ON (w.created_employee_id = e.Id)", $arrColumns, $aWhere['sClause'].$sWhere, $sOrderByClause, $sLimitClause);

		$mixResult = $selDelinquentCDRs->Execute($aWhere['aValues']);

		// Process the retrieved CDRs
		$arrCDRs = array();
		$arrRecordSet = $selDelinquentCDRs->FetchAll();

		$iRecordNumber = $iOffset;
		$aResult = array();
		foreach($arrRecordSet as $aRecord) {
			$aResult[$iRecordNumber++] = array(
				"Id" => $aRecord['Id'],
				"Time" => $aRecord['StartDatetime'],//;//$strStartDatetime,
				"Cost" => $aRecord['Cost'],//$strCost,
				"Status" => $aRecord['StatusDescr'],
				"StatusId" => $aRecord['Status'],
				"WrittenOffBy"=> $aRecord['WrittenOffBy'],
				"WrittenOffOn"=> $aRecord['WrittenOffOn']
			);
		}

		return $bCountOnly?count($aResult):$aResult;
	}

/*	private static function generateWhere($aAliases=array(), $aConstraints=null) {
		$aWhereParts = array();
		$aResult = array('sClause' => '','aValues' => array());

		if ($aConstraints) {
			foreach($aConstraints as $sOriginalAlias => $mValue) {
				$sAlias = $sOriginalAlias;
				if (isset($aAliases[$sOriginalAlias])) {
					$sAlias = $aAliases[$sOriginalAlias];
				}

				self::processWhereConstraint($sOriginalAlias, $sAlias, $mValue, $aWhereParts, $aResult);
			}
		}

		$aResult['sClause'] = implode(' AND ', $aWhereParts);
		return $aResult;
	}*/

	public static function getPossibleOwnersForFNN($strFNN, $intServiceType) {
		$strIndialFNN = substr($strFNN, 0, -2) . "__";
		$arrColumns = array(
			"Id" => "S.Id",
			"CreatedOn" => "S.CreatedOn",
			"ClosedOn" => "S.ClosedOn",
			"Indial100" => "S.Indial100",
			"Status" => "S.Status",
			"Account" => "A.Id",
			"AccountName" => "CASE WHEN A.BusinessName = \"\" THEN A.TradingName ELSE A.BusinessName END",
			"FNN" =>"FNN"
		);

		if ($intServiceType == SERVICE_TYPE_ADSL) {
			// ADSL Services have an "i" appended to their FNNs in the Service table, but don't in the CDR table
			$strWhere = "S.FNN = '{$strFNN}i'";
		} else {
			$strWhere = "(S.FNN = <FNN> OR (S.Indial100 = TRUE AND S.FNN LIKE <IndialFNN>))";
		}

		$arrWhere = array("FNN" => $strFNN, "IndialFNN" => $strIndialFNN);
		$strTables = "Service AS S INNER JOIN Account AS A ON S.Account = A.Id";
		$strOrderBy = "(S.ClosedOn IS NULL) DESC, S.CreatedOn DESC";
		$selPossibleOwners = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "");

		$mixResult = $selPossibleOwners->Execute($arrWhere);

		if ($mixResult === false) {
			Ajax()->AddCommand("Alert", "ERROR: Retrieving potential owner services failed, unexpectedly.  Please notify your system administrator");
			return true;
		}

		// Prepare the data to be sent to the client
		$arrServices = array();
		$arrRecordSet = $selPossibleOwners->FetchAll();
		foreach ($arrRecordSet as $arrRecord) {
			$strCreatedOn = substr($arrRecord['CreatedOn'], 8, 2) ."/". substr($arrRecord['CreatedOn'], 5, 2) ."/". substr($arrRecord['CreatedOn'], 0, 4);
			if ($arrRecord['ClosedOn'] != null) {
				$strClosedOn = substr($arrRecord['ClosedOn'], 8, 2) ."/". substr($arrRecord['ClosedOn'], 5, 2) ."/". substr($arrRecord['ClosedOn'], 0, 4);
			} else {
				$strClosedOn = "Still Open";
			}
			$strClosedOn = str_pad($strClosedOn, 12, " ", STR_PAD_RIGHT);

			$strServiceId = str_pad($arrRecord['Id'], 11, " ", STR_PAD_LEFT);

			$strAccountName = ($arrRecord['AccountName'] != "") ? substr($arrRecord['AccountName'], 0, 28) : "[No Name]";
			$strAccountName = str_pad($strAccountName, 28, " ", STR_PAD_RIGHT);
			if (strlen($arrRecord['AccountName']) > 28) {
				// Show that the account name has been truncacted
				$strAccountName = substr($strAccountName, 0, -3) . "...";
			}

			$strIndial = ($arrRecord['Indial100']) ? "(Indial) " : "         ";

			$strStatus = GetConstantDescription($arrRecord['Status'], "service_status");
			$strStatus = str_pad($strStatus, 13, " ", STR_PAD_RIGHT);

			// Build a description for the Service
			$strAccountDescription = "{$arrRecord['Account']} - {$strAccountName}";
			$strDescription = "{$arrRecord['Account']} {$strAccountName} {$strIndial} {$strStatus} {$strCreatedOn} - {$strClosedOn} {$strServiceId}";

			$arrRecord['Description'] = htmlspecialchars($strDescription, ENT_QUOTES);
			$arrRecord['AccountDescription'] = htmlspecialchars($strAccountDescription, ENT_QUOTES);
			$arrRecord['DateRange'] = "$strCreatedOn - $strClosedOn";
			$arrRecord['CreatedOn'] = $strCreatedOn;
			$arrRecord['ClosedOn'] = $strClosedOn;
			//$arrRecord['FNN'] = $strClosedOn;
			$arrServices[$arrRecord['Id']] = $arrRecord;
		}

		return $arrServices;
	}

	public static function GetDelinquentCDRIDs($strStartDate, $strEndDate, $strFNN ,$intCarrier, $intServiceType, $iStatus=CDR_BAD_OWNER) {
		// Retrieve all the CDRs
		$strWhere = "Status = <BadOwner> AND FNN = <FNN> AND ServiceType = <ServiceType> AND Carrier = <Carrier> AND StartDatetime BETWEEN <StartDate> AND <EndDate>";
		$arrWhere = array(
			"BadOwner" => $iStatus,
			"FNN" => $strFNN,
			"ServiceType" => $intServiceType,
			"Carrier" => $intCarrier,
			"StartDate" => $strStartDate,
			"EndDate" => $strEndDate
		);
		$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
		$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$arrColumns = array("Id"=> "Id");
		$selDelinquentCDRs = new StatementSelect("CDR", $arrColumns, $strWhere, "StartDatetime DESC, Id ASC");

		$mixResult = $selDelinquentCDRs->Execute($arrWhere);

		// Process the retrieved CDRs
		$arrCDRs = array();
		$aRecords = $selDelinquentCDRs->FetchAll();

		foreach($aRecords as $aRecord) {
			$arrCDRs[] = $aRecord['Id'];
		}
		return $arrCDRs;
	}

	public static function GetStatusInfoForCDRs($aCDRIds) {
		$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
		$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$sSql = "
			SELECT		c.Id as Id,
						c.StartDatetime as Time,
						c.Cost as Cost,
						c.Status as StatusId,
						CASE c.Status
							WHEN ".CDR_BAD_OWNER."
								THEN '{$sDelinquentStatusDescr}'
							WHEN ". CDR_DELINQUENT_WRITTEN_OFF."
								THEN '{$sWriteOffStausDescr}'
							ELSE
								CONCAT (CONCAT_WS(' ', 'Account ID:', s.Account), CONCAT_WS(' ',' FNN:', s.FNN)) END as Status
			FROM		CDR c
						LEFT JOIN Service s ON (c.Service = s.Id)
			WHERE		c.Id IN (".implode(',',$aCDRIds).")
			ORDER BY	c.StartDatetime DESC,
						c.Id ASC";

		$oQuery = new Query();
		$result = $oQuery->Execute($sSql);
		$aResultSet = array();
		if ($result) {
			while ($aRow = $result->fetch_assoc()) {
				$aResultSet[$aRow['Id']] = $aRow;//$oOrm->toArray();
			}
		}

		return $aResultSet;
	}

	public static function GetDelinquentFNNs($iLimit, $iOffset, $aSortFields, $aFilter , $bCountOnly=false) {
		if (!array_key_exists( 'Status', $aFilter)) {
			$object = new stdClass();
			$object->aValues = array(CDR_BAD_OWNER, CDR_DELINQUENT_WRITTEN_OFF);
			$aFilter['Status'] = $object;
		} else if ($aFilter['Status'] == -1) {
			$object = new stdClass();
			$object->aValues = array(CDR_BAD_OWNER, CDR_DELINQUENT_WRITTEN_OFF);
			$aFilter['Status'] = $object;
		}

		$aAliases = array('StartDatetime'=>'date(StartDatetime)', 'EndDateTime'=>'date(EndDateTime)');
		$aWhere = StatementSelect::generateWhere($aAliases, $aFilter);
		$sOrderByClause = StatementSelect::generateOrderBy(array(), $aSortFields);
		$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
		$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$sLimitClause = StatementSelect::generateLimit($iLimit, $iOffset);

		$arrColumns = array(
			"FNN" => "FNN",
			"ServiceType" => "ServiceType",
			"Carrier" => "Carrier",
			"carrier_label" => "Carrier.Name",
			"TotalCost" => "ROUND(SUM(
				Cost * IF(Credit, -1, 1)
			), 2)",
			"EarliestStartDatetime" => "MIN(StartDatetime)",
			"LatestStartDatetime" => "MAX(StartDatetime)",
			"Count" => "Count(CDR.Id)",
			"Status" => "Status",
			"StatusDescr" => "IF(Status = ".CDR_BAD_OWNER.", '{$sDelinquentStatusDescr}','{$sWriteOffStausDescr}')"
		);
		$sCarrierJoin = $aWhere['sClause'] != "" ? " AND CDR.Carrier = Carrier.Id" : " WHERE CDR.Carrier = Carrier.Id";

		$selDelinquentCDRs = new StatementSelect("CDR, Carrier", $arrColumns, $aWhere['sClause']." AND CDR.Carrier = Carrier.Id", $sOrderByClause, $sLimitClause, "FNN, ServiceType, Carrier, Status");
		$mixResult = $selDelinquentCDRs->Execute($aWhere['aValues']);
		$arrRecordSet = $selDelinquentCDRs->FetchAll();
		$aResult = array();
		$iRecordNumber= $iOffset;
		foreach($arrRecordSet as $aRecord) {
			$aResult[$iRecordNumber++]= $aRecord;
		}

		return $bCountOnly ? count($aResult) : $aResult;
	}

	// NOTE: As of 31/05/2011 the code associated entirely with calculating whether a CDR's start date is within the 189 day period before the current bill cycle has been commented out.
	public function assignCDRsToService($strFNN , $intCarrier, $intServiceType, $arrCDRs) {
		$arrSuccessfulCDRs = array();

		$strNow = GetCurrentISODateTime();

		// Retrieve all possible owners for the CDRs
		if ($intServiceType == SERVICE_TYPE_ADSL) {
			// ADSL Services have an "i" appended to their FNNs in the Service table, but don't in the CDR table
			$strWhere = "FNN = '{$strFNN}i'";
		} else {
			$strWhere = "(FNN = <FNN> OR (Indial100 = TRUE AND FNN LIKE <IndialFNN>))";
		}

		$strIndialFNN = substr($strFNN, 0, -2) . "__";
		$selServices = new StatementSelect("Service", "*", $strWhere);
		if ($selServices->Execute(Array("FNN"=>$strFNN, "IndialFNN"=>$strIndialFNN)) === false) {
			$arrReturnObject["Success"] = false;
			$arrReturnObject["ErrorMsg"] = "ERROR: Retrieving the services from the database failed, unexpectedly. Operation aborted.  Please notify your system administrator";
			AjaxReply($arrReturnObject);
			return true;
		}
		$arrRecordSet = $selServices->FetchAll();
		$arrServices = Array();
		foreach ($arrRecordSet as $arrRecord) {
			$arrServices[$arrRecord['Id']] = $arrRecord;
			//This commented block of code is related to the obsolete rule that the CDR start date must be within 189 days from the current billing period
			/*
			try {
				$account = Account::getForId($arrRecord['Account']);
				if ($account === null) {
			 		throw new exception("There is no account associated with this service record");
				}
				$strStartOfCurrentBillingPeriod = Invoice_Run::getLastInvoiceDateByCustomerGroup($account->customerGroup, $strNow);
			} catch (Exception $e) {
				// The start of the current billing period could not be calculated, for the account in question
				// Just use the current timestamp, because clearly we are in the current billing period
				$strStartOfCurrentBillingPeriod = $strNow;
			}
			$arrServices[$arrRecord['Id']]['EarliestAllowableCDRStartDate'] = date("Y-m-d", strtotime("-189 days {$strStartOfCurrentBillingPeriod}"));
			*/
		}

		// Build the Database objects required
		$selCDR = new StatementSelect("CDR", "Id, FNN, Service, Account, AccountGroup, Status, StartDatetime", "Id = <Id>");

		$arrUpdateColumns = array("Service"=>null, "Account"=>null, "AccountGroup"=>null, "Status"=>null);
		$updCDR = new StatementUpdateById("CDR", $arrUpdateColumns);

		// Process the CDRs
		$strErrorMsg = "";

		foreach ($arrCDRs as $objCDR) {
			if (!isset($arrServices[$objCDR->Service])) {
				// The Service to assign the CDR to is not in the list of allowable services (it must have a different FNN)
				$strErrorMsg = "ERROR: Could not find the assigned service for record {$objCDR->Record}. Operation aborted.";
				break;
			}
			$arrService = $arrServices[$objCDR->Service];

			// Retrieve the CDR record
			if ($selCDR->Execute(array("Id" => $objCDR->Id)) != 1) {
				// Could not retrieve the CDR record
				$strErrorMsg = "ERROR: Could not retrieve CDR {$objCDR->Record} from the database (CDR Id = {$objCDR->Id}). Operation aborted.  Please notify your system administrator";
				break;
			}
			$arrCDRRecord = $selCDR->Fetch();

			$strStartDate = substr($arrCDRRecord['StartDatetime'], 0, 10);
			// This commented block of code is related to the obsolete rule that the CDR start date must be within 189 days from the current billing period
			/*
			// Check that the CDR's StartDatetime is within 189 days of the next bill date of the account that the CDR will be allocated to
			if ($strStartDate < $arrService['EarliestAllowableCDRStartDate']) {
				// CDR is too old
				$strStartTime = date("H:i:s d/m/Y", strtotime($arrCDRRecord['StartDatetime']));
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} with start time: {$strStartTime} is considered too old to be billed to this customer. Operation aborted.";
				break;
			}
			*/
			// Check the FNNs match
			if ($strFNN != $arrCDRRecord['FNN']) {
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} does not have FNN {$strFNN}. Operation Aborted.";
				break;
			}

			// Check the FNN has Status == CDR_BAD_OWNER
			if ($arrCDRRecord['Status'] != CDR_BAD_OWNER) {
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} does not have 'Bad Owner' status.  Operation Aborted.";
				break;
			}

			// Everything is valid.  Update the FNN
			$arrUpdateColumns['Id'] = $objCDR->Id;
			$arrUpdateColumns['Service'] = $arrService['Id'];
			$arrUpdateColumns['Account'] = $arrService['Account'];
			$arrUpdateColumns['AccountGroup'] = $arrService['AccountGroup'];
			$arrUpdateColumns['Status'] = CDR_NORMALISED;

			if ($updCDR->Execute($arrUpdateColumns) === false) {
				// Updating the CDR failed
				$strErrorMsg = "ERROR: Updating the CDR {$objCDR->Record} (CDR Id: {$objCDR->Id}) failed, unexpectedly.  Operation Aborted.  Please notify your system administrator.";
				break;
			}

			// Add the CDR to the list of successfully owned CDRs
			$arrSuccessfulCDRs[] = array('account_id'=>$arrService['Account'], 'service_id'=>$arrService['Id'], 'fnn'=>$arrService['FNN']);
		}

		return count($arrSuccessfulCDRs) > 0 ?$arrSuccessfulCDRs : $strErrorMsg;
	}

	public function updateQuarantineStatus() {
		// Determine what original CDR this is tied to, and retrieve it
		// TODO

		switch ($this->Status) {
			case CDR_RECHARGE:
				break;

			case CDR_CREDIT_QUARANTINE:
				break;

			default:
				// This is not a Quarantined CDR, return nicely
				return;
		}
	}

	// writeOff: If delinquent, sets the status of the cdr and creates a cdr_delinquent_writeoff record
	public function writeOff() {
		Log::getLog()->log("Writing off CDR {$this->Id}");

		// Very that we're a delinquent
		if ($this->Status !== CDR_BAD_OWNER) {
			try {
				$sStatus = Constant_Group::getConstantGroup('CDR')->getConstantName($this->Status);
			} catch(Exception $e) {
				throw new Exception("Malformed CDR Data. Non existent status.");
			}
			throw new Exception("Failed to write of CDR ({$this->Id}), not a delinquent. Status is {$sStatus}");
		}

		Log::getLog()->log("... is delinquent, updating status");

		// Update status
		$this->Status = CDR_DELINQUENT_WRITTEN_OFF;
		$this->save();

		Log::getLog()->log("... status updated, create cdr_delinquent_writeoff record");

		// Create cdr_delinquent_writeoff record
		$oLog = CDR_Delinquent_WriteOff::createForCDR($this);

		Log::getLog()->log("CDR {$this->Id} written off");
	}

	public static function getForInvoice($mxdInvoice) {
		try {
			// Invoiced CDRs can either be in the CDR database, or the flex one.
			// The CDR database has one table for the invoiced CDRs, but the table is partitioned
			// by the invoice_run_id of the invoice. To query this table we MUST include the
			// invoice_run_id in the where clause of the query.
	
			$objInvoice = is_numeric($mxdInvoice) ? new Invoice(array('Id'=>$mxdInvoice), true) : $mxdInvoice;
	
			$dataSource = self::getDataSourceForInvoiceRunCDRs($objInvoice->invoiceRunId);
	
			// We now have all the details needed to load the CDRs from either database
	
			// Try to load the records from the cdr_invoiced table of the CDR db
			$cdrDb = Data_Source::get($dataSource);
			if ($dataSource == FLEX_DATABASE_CONNECTION_CDR) {
				$strCdrSelect = "
					SELECT		id AS \"Id\",
								fnn AS \"FNN\",
								file AS \"File\",
								carrier AS \"Carrier\",
								carrier_ref AS \"CarrierRef\",
								source AS \"Source\",
								destination AS \"Destination\",
								start_date_time AS \"StartDatetime\",
								end_date_time AS \"EndDatetime\",
								units AS \"Units\",
								account_group AS \"AccountGroup\",
								account AS \"Account\",
								service AS \"Service\",
								cost AS \"Cost\",
								status AS \"Status\",
								cdr AS \"CDR\",
								description AS \"Description\",
								destination_code AS \"DestinationCode\",
								record_type AS \"RecordType\",
								service_type AS \"ServiceType\",
								charge AS \"Charge\",
								rate AS \"Rate\",
								normalised_on AS \"NormalisedOn\",
								rated_on AS \"RatedOn\",
								invoice_run_id,
								sequence_no AS \"SequenceNo\",
								credit as \"Credit\"
					FROM		cdr_invoiced
					WHERE		account = " . $cdrDb->quote($objInvoice->account) . "
								AND invoice_run_id = " . $cdrDb->quote($objInvoice->invoiceRunId) . "
					ORDER BY	service_type ASC,
								fnn ASC,
								record_type ASC,
								start_date_time ASC
				";
			} else {
				// Must be in CDR table of default db
				$strCdrSelect = "
					SELECT		Id AS \"Id\",
								FNN AS \"FNN\",
								File AS \"File\",
								Carrier AS \"Carrier\",
								CarrierRef AS \"CarrierRef\",
								Source AS \"Source\",
								Destination AS \"Destination\",
								StartDatetime AS \"StartDatetime\",
								EndDatetime AS \"EndDatetime\",
								Units AS \"Units\",
								AccountGroup AS \"AccountGroup\",
								Account AS \"Account\",
								Service AS \"Service\",
								Cost AS \"Cost\",
								Status AS \"Status\",
								CDR AS \"CDR\",
								Description AS \"Description\",
								DestinationCode AS \"DestinationCode\",
								RecordType AS \"RecordType\",
								ServiceType AS \"ServiceType\",
								Charge AS \"Charge\",
								Rate AS \"Rate\",
								NormalisedOn AS \"NormalisedOn\",
								RatedOn AS \"RatedOn\",
								invoice_run_id,
								SequenceNo AS \"SequenceNo\",
								Credit AS \"Credit\"
					FROM		CDR
					WHERE		Account = " . $cdrDb->quote($objInvoice->account) . "
								AND invoice_run_id = " . $cdrDb->quote($objInvoice->invoiceRunId) . "
					ORDER BY	ServiceType ASC,
								FNN ASC,
								RecordType ASC,
								StartDatetime ASC
				";
			}
	
			// Proceed with a query...
			$res =& $cdrDb->query($strCdrSelect);
	
			// Always check that result is not an error
			if (MDB2::isError($res)) {
				throw new Exception($res->getMessage() . "\n{$strCdrSelect}");
			}
	
			$rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
	
			// Otherwise, we should assume that there weren't any.
			return $rows;
		} catch (Exception_Database $oEx) {
			// Suppress exception and return empty set, the cdr database may be unreachable
			return array();
		}
	}

	public static function getDataSourceForInvoiceRunCDRs($intInvoiceRunId) {
		$strQuery = "
			SELECT	CASE
						WHEN (
							SELECT	'Still In CDR table'
							FROM	CDR
							WHERE	invoice_run_id = {$intInvoiceRunId} LIMIT 1
						) = 'Still In CDR table' THEN
							'". FLEX_DATABASE_CONNECTION_DEFAULT ."'
						ELSE
							'". FLEX_DATABASE_CONNECTION_CDR ."' END AS DataSource
		";
		$db = Data_Source::get();
		$res = $db->query($strQuery);

		if (MDB2::isError($res)) {
			throw new Exception("Failed to find the data source storing CDRs for invoice run: {$intInvoiceRunId} - " . $res->getMessage());
		}

		$strDataSourceName = $res->fetchOne();

		return $strDataSourceName;
	}

	public static function getCDRDetails($iCdrId, $iInvoiceRunId=null) {
		$rFlexDb = Data_Source::get(FLEX_DATABASE_CONNECTION_DEFAULT);
		$aResult = array();
		$rCDRDb = null;

		// Check if the cdr is invoiced or not
		if (is_null($iInvoiceRunId)) {
			// MySQL Database, not invoiced
			$rCDRDb = Data_Source::get(FLEX_DATABASE_CONNECTION_DEFAULT);
			$sCdr = "
				SELECT	t.Name AS \"RecordType\",
						c.Description AS \"Description\",
						c.Source AS \"Source\",
						c.Destination AS \"Destination\",
						c.EndDatetime AS \"EndDatetime\",
						c.StartDatetime AS \"StartDatetime\",
						c.Units AS \"Units\",
						t.DisplayType AS \"DisplayType\",
						c.Charge AS \"Charge\",
						c.File AS \"FileId\",
						c.Carrier AS \"CarrierId\",
						c.CarrierRef AS \"CarrierRef\",
						c.Cost AS \"Cost\",
						c.Status AS \"Status\",
						c.DestinationCode AS \"DestinationCode\",
						c.Rate AS \"RateId\",
						c.NormalisedOn AS \"NormalisedOn\",
						c.RatedOn AS \"RatedOn\",
						c.SequenceNo AS \"SequenceNo\",
						c.Credit AS \"Credit\",
						c.CDR AS \"RawCDR\"
				FROM	CDR c INNER JOIN RecordType t ON c.RecordType = t.Id
				WHERE	c.Id = {$iCdrId}
			";
		} else {
			// Get the data source for the service CDR data
			$sDataSource = CDR::getDataSourceForInvoiceRunCDRs($iInvoiceRunId);
			$rCDRDb = Data_Source::get($sDataSource);

			if ($sDataSource == FLEX_DATABASE_CONNECTION_DEFAULT) {
				// MySQL Database, invoiced
				$sCdr = "
					SELECT	t.Name AS \"RecordType\",
							c.Description AS \"Description\",
							c.Source AS \"Source\",
							c.Destination AS \"Destination\",
							c.EndDatetime AS \"EndDatetime\",
							c.StartDatetime AS \"StartDatetime\",
							c.Units AS \"Units\",
							t.DisplayType AS \"DisplayType\",
							c.Charge AS \"Charge\",
							c.File AS \"FileId\",
							c.Carrier AS \"CarrierId\",
							c.CarrierRef AS \"CarrierRef\",
							c.Cost AS \"Cost\",
							c.Status AS \"Status\",
							c.DestinationCode AS \"DestinationCode\",
							c.Rate AS \"RateId\",
							c.NormalisedOn AS \"NormalisedOn\",
							c.RatedOn AS \"RatedOn\",
							c.SequenceNo AS \"SequenceNo\",
							c.Credit AS \"Credit\",
							c.CDR AS \"RawCDR\"
					FROM	CDR c
							JOIN RecordType t ON (c.RecordType = t.Id)
					WHERE	invoice_run_id = {$iInvoiceRunId}
							AND c.Id = {$iCdrId}
				";
			} else {
				// PostgreSQL Database,
				$sCdr = "
					SELECT t.name AS \"RecordType\",
							c.description AS \"Description\",
							c.source AS \"Source\",
							c.destination AS \"Destination\",
							c.end_date_time AS \"EndDatetime\",
							c.start_date_time AS \"StartDatetime\",
							c.units AS \"Units\",
							t.display_type AS \"DisplayType\",
							c.charge AS \"Charge\",
							c.file AS \"FileId\",
							c.carrier AS \"CarrierId\",
							c.carrier_ref AS \"CarrierRef\",
							c.cost AS \"Cost\",
							c.status AS \"Status\",
							c.destination_code AS \"DestinationCode\",
							c.rate AS \"RateId\",
							c.normalised_on AS \"NormalisedOn\",
							c.rated_on AS \"RatedOn\",
							c.sequence_no AS \"SequenceNo\",
							c.credit AS \"Credit\",
							c.cdr AS \"RawCDR\"
					FROM	cdr_invoiced c
							JOIN record_type t ON (c.record_type = t.id)
					WHERE	invoice_run_id = {$iInvoiceRunId}
							AND c.id = {$iCdrId}
				";
			}
		}

		// Run the CDR query
		$rCdr = $rCDRDb->query($sCdr);

		if (MDB2::isError($rCdr)) {
			throw new Exception("Failed to load CDR details: " . $rCdr->getMessage() ." - Query: {$sCdr}");
		}

		$aCDR = $rCdr->fetchRow(MDB2_FETCHMODE_ASSOC);

		// Get more information for certain fields
		$aCDR['RateName'] = '';
		$aCDR['CarrierName'] = '';
		$aCDR['DestinationCodeDescription'] = '';
		$aCDR['FileName'] = '';

		// Rate name
		if ($aCDR['RateId']) {
			$oRate = Rate::getForId($aCDR['RateId']);
			$aCDR['RateName'] = $oRate->Name;
		}

		// Carrier name
		if ($aCDR['CarrierId']) {
			$aCDR['CarrierName'] = Carrier::getForId($aCDR['CarrierId'])->Name;
		}

		// Destination code description
		if ($aCDR['DestinationCode']) {
			$oDestination = Destination::getForCode($aCDR['DestinationCode']);
			$aCDR['DestinationCodeDescription'] = $oDestination->Description;
		}

		// File name
		if ($aCDR['FileId']) {
			$oFileImport = File_Import::getForId($aCDR['FileId']);
			$aCDR['FileName'] = "{$oFileImport->FileName} ({$oFileImport->Location})";
		}

		return $aCDR;
	}

	protected static function getCacheName() {
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName)) {
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize() {
		return 100;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//    START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects) {
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false) {
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false) {
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function importResult($aResultSet) {
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//    END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;

				case 'selAll':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;

				case 'selUnbilledCDRTotal':
					$arrPreparedStatements[$strStatement] = new StatementSelect("CDR",
							"COUNT(Id) AS cdr_count, SUM(Charge) AS cdr_charge",
							"Status = ".CDR_RATED." AND Service = <service_id> AND (ISNULL(<invoice_run_id>) OR invoice_run_id = <invoice_run_id>)");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement] = new StatementInsert(self::$_strStaticTableName);
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement] = new StatementUpdateById(self::$_strStaticTableName);
					break;

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}

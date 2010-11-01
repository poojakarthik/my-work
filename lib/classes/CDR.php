<?php

require_once dirname(__FILE__) . '/data/Data_Source.php';

/**
 * CDR
 *
 * CDR Table
 *
 * @class	CDR
 */
class CDR extends ORM_Cached
{
	protected 			$_strTableName			= "CDR";
	protected static	$_strStaticTableName	= "CDR";

	public function rate($bForceReRate=false, $bUseExistingRate=false)
	{
		static	$oQuery;
		$oQuery	= ($oQuery) ? $oQuery : new Query();

		if (in_array($this->Status, array(CDR_NORMALISED, CDR_RERATE, CDR_RATE_NOT_FOUND)) || ($bForceReRate && $this->Rate))
		{
			$oRate	= ($bUseExistingRate && $this->Rate) ? Rate::getForId($this->Rate) : Rate::getForCDR($this);

			// Did we find a Rate?
			if (!$oRate)
			{
				$this->Charge	= null;
				$this->Rate		= null;
				$this->Status	= CDR_RATE_NOT_FOUND;
				$this->save();

				throw new Exception_Rating_RateNotFound("No Rate found for CDR {$this->Id}");
			}
			$this->Rate		= $oRate->Id;

			// DISCOUNTING (per-CDR): Eligibility
			$bDiscount	= false;

			// Does the Rate Plan have discounting enabled?
			$oService	= Service::getForId($this->Service);
			$oRatePlan	= $oService->getCurrentPlan($this->StartDatetime, false);
			if ($oRatePlan->discount_cap !== null && (float)$oRatePlan->discount_cap >= 0.01)
			{
				// Plan has a Discount Cap
				// Check to see that this CDR is not older than our latest discounted CDR
				if ($oService->discount_start_datetime !== null && $this->StartDatetime < $oService->discount_start_datetime)
				{
					// We need to re-rate all CDRs that are newer or as new as this CDR to ensure our ordering is correct
					throw new Exception_Rating_CDROutOfSequence($this->Id);
				}
				else
				{
					$bDiscount	= true;
				}
			}

			// CHARGE: Calculate Base Charge
			$this->Charge	= $this->calculcateCharge();

			// DISCOUNTING (per-CDR): Application
			if ($bDiscount)
			{
				$oGetUnbilledCDRTotal	= self::_preparedStatement('selUnbilledCDRTotal');
				if ($oGetUnbilledCDRTotal->Execute(array('service_id'=>$this->Service,'invoice_run_id'=>$this->invoice_run_id)) === false)
				{
					throw new Exception($oGetUnbilledCDRTotal->Error());
				}
				$aUnbilledCDRTotal	= $oGetUnbilledCDRTotal->Fetch();

				// Have we exceeded our discount cap?
				if ((float)$aUnbilledCDRTotal['cdr_charge'] >= (float)$oRatePlan->discount_cap)
				{
					// Apply Discount
					$fDiscountPercentage	= (float)$oRate->discount_percentage;
					if ($fDiscountPercentage)
					{
						// NOTE: This looks incorrect (discount percentage should be divided by 100), but this is how it behaved in the last implementation
						//$this->Charge	-= $this->Charge * $fDiscountPercentage;

						// Replaced with correct code -- couldn't find any cases of discounting in reality
						$this->Charge	-= $this->Charge * ($fDiscountPercentage / 100);
					}
				}
				else
				{
					// Update
					$oService->discount_start_datetime	= $this->StartDatetime;
					$oService->save();
				}
			}

			// ROUNDING
			$this->Charge	= Rate::roundToRatingStandard($this->Charge);

			// SERVICE TOTALS: Update progressive totals (deprecated, but we'll copy the functionality anyway)
			if ($this->Charge > 0 && $this->Credit == 0)
			{
				if ($oRate->Uncapped)
				{
					$oService->UncappedCharge	+= $this->Charge;
				}
				else
				{
					$oService->CappedCharge		+= $this->Charge;
				}
				$oService->save();
			}

			$this->Status	= CDR_RATED;
			$this->RatedOn	= Data_Source_Time::currentTimestamp();

			$this->save();
		}
		else
		{
			throw new Exception("Cannot Rate CDR #{$this->Id} with Status ".GetConstantName($this->Status, 'CDR').((!$bForceReRate) ? '' : ' (even when forced)'));
		}
	}

	public function calculcateCharge()
	{
		if ($this->Rate)
		{
			$oRate	= Rate::getForId($this->Rate);
			return $oRate->calculateChargeForCDR($this);
		}
		else
		{
			throw new Exception("Cannot calculate Charge for CDR #{$this->Id} with Status ".GetConstantName($this->Status, 'CDR'));
		}
	}


	public static function GetDelinquentCDRs($strStartDate, $strEndDate, $strFNN	,$intCarrier, $intServiceType, $iStatus = CDR_BAD_OWNER)
	{
		// Retrieve all the CDRs
		$strWhere			= "Status = <BadOwner> AND FNN = <FNN> AND ServiceType = <ServiceType> AND Carrier = <Carrier> AND StartDatetime BETWEEN <StartDate> AND <EndDate>";
		$arrWhere			= Array(	"BadOwner"		=> $iStatus,
										"FNN"			=> $strFNN,
										"ServiceType"	=> $intServiceType,
										"Carrier"		=> $intCarrier,
										"StartDate"		=> $strStartDate,
										"EndDate"		=> $strEndDate);
		$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
		$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$arrColumns			= Array("Id"					=>	"Id",
									"Cost"			=>	"Cost",
									"StartDatetime"				=>	"StartDatetime",
		 							"Status"				=>	"Status",
									"StatusDescr"			=>	"if (Status = ".CDR_BAD_OWNER.", '".$sDelinquentStatusDescr."','".$sWriteOffStausDescr."')"
									);


		$selDelinquentCDRs	= new StatementSelect("CDR", $arrColumns, $strWhere, "StartDatetime DESC, Id ASC");

		$mixResult = $selDelinquentCDRs->Execute($arrWhere);


		// Retrieve all the possible Owners of the CDRs
		$strIndialFNN	= substr($strFNN, 0, -2) . "__";
		$arrColumns		= Array(	"Id"			=> "S.Id",
									"CreatedOn"		=> "S.CreatedOn",
									"ClosedOn"		=> "S.ClosedOn",
									"Indial100"		=> "S.Indial100",
									"Status"		=> "S.Status",
									"Account"		=> "A.Id",
									"AccountName"	=> "CASE WHEN A.BusinessName = \"\" THEN A.TradingName ELSE A.BusinessName END",
								);

		if ($intServiceType == SERVICE_TYPE_ADSL)
		{
			// ADSL Services have an "i" appended to their FNNs in the Service table, but don't in the CDR table
			$strWhere = "S.FNN = '{$strFNN}i'";
		}
		else
		{
			$strWhere = "(S.FNN = <FNN> OR (S.Indial100 = TRUE AND S.FNN LIKE <IndialFNN>))";
		}

		$arrWhere	= Array("FNN" => $strFNN, "IndialFNN" => $strIndialFNN);
		$strTables	= "Service AS S INNER JOIN Account AS A ON S.Account = A.Id";
		$strOrderBy	= "(S.ClosedOn IS NULL) DESC, S.CreatedOn DESC";
		$selPossibleOwners = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "");

		$mixResult = $selPossibleOwners->Execute($arrWhere);

		if ($mixResult === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Retrieving potential owner services failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}

		// Prepare the data to be sent to the client
		$arrServices = Array();
		$arrRecordSet = $selPossibleOwners->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$strCreatedOn = substr($arrRecord['CreatedOn'], 8, 2) ."/". substr($arrRecord['CreatedOn'], 5, 2) ."/". substr($arrRecord['CreatedOn'], 0, 4);
			if ($arrRecord['ClosedOn'] != NULL)
			{
				$strClosedOn = substr($arrRecord['ClosedOn'], 8, 2) ."/". substr($arrRecord['ClosedOn'], 5, 2) ."/". substr($arrRecord['ClosedOn'], 0, 4);
			}
			else
			{
				$strClosedOn = "[Still Open]";
			}
			$strClosedOn = str_pad($strClosedOn, 12, " ", STR_PAD_RIGHT);

			$strServiceId = str_pad($arrRecord['Id'], 11, " ", STR_PAD_LEFT);

			$strAccountName = ($arrRecord['AccountName'] != "") ? substr($arrRecord['AccountName'], 0, 28) : "[No Name]";
			$strAccountName = str_pad($strAccountName, 28, " ", STR_PAD_RIGHT);
			if (strlen($arrRecord['AccountName']) > 28)
			{
				// Show that the account name has been truncacted
				$strAccountName = substr($strAccountName, 0, -3) . "...";
			}

			$strIndial = ($arrRecord['Indial100']) ? "(Indial) " : "         ";

			$strStatus = GetConstantDescription($arrRecord['Status'], "service_status");
			$strStatus = str_pad($strStatus, 13, " ", STR_PAD_RIGHT);

			// Build a description for the Service
			$strAccountDescription				= "{$arrRecord['Account']} - $strAccountName";
			$strDescription						= "{$arrRecord['Account']} $strAccountName $strIndial $strStatus $strCreatedOn - $strClosedOn $strServiceId";

			$arrRecord['Description']			= htmlspecialchars($strDescription, ENT_QUOTES);
			$arrRecord['AccountDescription']	= htmlspecialchars($strAccountDescription, ENT_QUOTES);
			$arrRecord['DateRange']				= "$strCreatedOn - $strClosedOn";

			$arrServices[$arrRecord['Id']] = $arrRecord;
		}

		// Process the retrieved CDRs
		$arrCDRs = Array();
		$arrRecordSet = $selDelinquentCDRs->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$strStartDatetime	= date("H:i:s d/m/Y", strtotime($arrRecord['StartDatetime']));
			$strCost			= OutputMask()->MoneyValue($arrRecord['Cost']);

			$arrCDRs[$arrRecord['Id']] = Array(	"Id"	=> $arrRecord['Id'],
								"Time"	=> $strStartDatetime,
								"Cost"	=> $strCost,
								"Status" =>$arrRecord['StatusDescr'],
								"StatusId"	=>	$arrRecord['Status']
								);

		}

		// Build the Html required of the Service Selector popup
		//$strServiceSelectorHtml = $this->_RenderDelinquentCDRServiceSelector($arrServices);

		// Return data to the client
		$arrCDRs = count($arrCDRs)>0?$arrCDRs:new stdClass();

		$arrReturnData['Services']				= $arrServices;
		$arrReturnData['CDRs']					= $arrCDRs;
		return $arrReturnData;



	}


	public static function GetCDRsForCSVExport ($aCDRIds)
	{
		$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
		$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$sSql = "SELECT
			      c.Id as Id,
			      c.StartDatetime as Time,
			      c.Cost as Cost,
			      CASE  c.Status WHEN ".CDR_BAD_OWNER." THEN '".$sDelinquentStatusDescr."' WHEN ". CDR_DELINQUENT_WRITTEN_OFF." THEN  '".$sWriteOffStausDescr."' ELSE CONCAT (CONCAT_WS(' ', 'Account ID:', s.Account), CONCAT_WS(' ',' FNN:', s.FNN)) END as Status
			    FROM CDR c LEFT JOIN Service s ON (c.Service = s.Id ) where c.Id in (".implode(',',$aCDRIds).")";

		$oQuery = new Query();
		$result = $oQuery->Execute($sSql);
		$aResultSet = array();
		if ($result)
		{
			while ($aRow = $result->fetch_assoc())
			{
				$aResultSet[]= $aRow;//$oOrm->toArray();
			}
		}

		return $aResultSet;


	}

	public static function GetDelinquentFNNs($iLimit, $iOffset, $aSortFields, $aFilter , $bCountOnly = false)
	{

		if (!array_key_exists( 'Status',$aFilter))
		{
			$object = new stdClass();
			$object->aValues = array(CDR_BAD_OWNER, CDR_DELINQUENT_WRITTEN_OFF);
			$aFilter['Status'] = $object;

		}
		else if ($aFilter['Status'] == -1)
		{
			$object = new stdClass();
			$object->aValues = array(CDR_BAD_OWNER, CDR_DELINQUENT_WRITTEN_OFF);
			$aFilter['Status'] = $object;
		}

		$aWhere	= StatementSelect::generateWhere(null, $aFilter);
		$sOrderByClause	=	StatementSelect::generateOrderBy(array(), $aSortFields);
$sDelinquentStatusDescr = GetConstantDescription(CDR_BAD_OWNER, "CDR");
$sWriteOffStausDescr = GetConstantDescription(CDR_DELINQUENT_WRITTEN_OFF, "CDR");
		$sLimitClause	= StatementSelect::generateLimit($iLimit, $iOffset);

		$arrColumns			= Array("FNN"					=>	"FNN",
									"ServiceType"			=>	"ServiceType",
									"Carrier"				=>	"Carrier",
		 							"carrier_label"			=> "Carrier.Name",
									"TotalCost"				=>	"ROUND(SUM(Cost),2)",
									"EarliestStartDatetime"	=>	"MIN(StartDatetime)",
									"LatestStartDatetime"	=>	"MAX(StartDatetime)",
									"Count"					=>	"Count(CDR.Id)",
									"Status"				=>	"Status",
									"StatusDescr"			=>	"if (Status = ".CDR_BAD_OWNER.", '".$sDelinquentStatusDescr."','".$sWriteOffStausDescr."')"
									);
		$sCarrierJoin = $aWhere['sClause']!=""?" AND CDR.Carrier = Carrier.Id":" WHERE CDR.Carrier = Carrier.Id";

		$selDelinquentCDRs	= new StatementSelect("CDR, Carrier", $arrColumns, $aWhere['sClause']." AND CDR.Carrier = Carrier.Id", $sOrderByClause, $sLimitClause, "FNN, ServiceType, Carrier, Status");
		$mixResult			= $selDelinquentCDRs->Execute($aWhere['aValues']);
		$arrRecordSet	= $selDelinquentCDRs->FetchAll();
		$aResult = array();
		$iRecordNumber= $iOffset;
		foreach($arrRecordSet as $aRecord)
		{
			$aResult[$iRecordNumber++]= $aRecord;

		}


		return $bCountOnly?count($aResult):$aResult;

	}

	public function assignCDRsToService($strFNN	, $intCarrier, $intServiceType, $arrCDRs)
	{

		$arrSuccessfulCDRs = Array();

		$strNow = GetCurrentISODateTime();

		// Retrieve all possible owners for the CDRs
		if ($intServiceType == SERVICE_TYPE_ADSL)
		{
			// ADSL Services have an "i" appended to their FNNs in the Service table, but don't in the CDR table
			$strWhere = "FNN = '{$strFNN}i'";
		}
		else
		{
			$strWhere = "(FNN = <FNN> OR (Indial100 = TRUE AND FNN LIKE <IndialFNN>))";
		}

		$strIndialFNN	= substr($strFNN, 0, -2) . "__";
		$selServices	= new StatementSelect("Service", "*", $strWhere);
		if ($selServices->Execute(Array("FNN"=>$strFNN, "IndialFNN"=>$strIndialFNN)) === FALSE)
		{
			$arrReturnObject["Success"]		= FALSE;
			$arrReturnObject["ErrorMsg"]	= "ERROR: Retrieving the services from the database failed, unexpectedly. Operation aborted.  Please notify your system administrator";
			AjaxReply($arrReturnObject);
			return TRUE;
		}
		$arrRecordSet	= $selServices->FetchAll();
		$arrServices	= Array();
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrServices[$arrRecord['Id']] = $arrRecord;

			try
			{
				$account = Account::getForId($arrRecord['Account']);
				if ($account === NULL)
				{
					throw new exception("There is no account associated with this service record");
				}
				$strStartOfCurrentBillingPeriod = Invoice_Run::getLastInvoiceDateByCustomerGroup($account->customerGroup, $strNow);
			}
			catch (Exception $e)
			{
				// The start of the current billing period could not be calculated, for the account in question
				// Just use the current timestamp, because clearly we are in the current billing period
				$strStartOfCurrentBillingPeriod = $strNow;
			}

			$arrServices[$arrRecord['Id']]['EarliestAllowableCDRStartDate']	= date("Y-m-d", strtotime("-189 days $strStartOfCurrentBillingPeriod"));
		}

		// Build the Database objects required
		$selCDR = new StatementSelect("CDR", "Id, FNN, Service, Account, AccountGroup, Status, StartDatetime", "Id = <Id>");

		$arrUpdateColumns = Array("Service"=>NULL, "Account"=>NULL, "AccountGroup"=>NULL, "Status"=>NULL);
		$updCDR = new StatementUpdateById("CDR", $arrUpdateColumns);

		// Process the CDRs
		$strErrorMsg = "";

		foreach ($arrCDRs as $objCDR)
		{
			if (!isset($arrServices[$objCDR->Service]))
			{
				// The Service to assign the CDR to is not in the list of allowable services (it must have a different FNN)
				$strErrorMsg = "ERROR: Could not find the assigned service for record {$objCDR->Record}. Operation aborted.";
				break;
			}
			$arrService = $arrServices[$objCDR->Service];

			// Retrieve the CDR record
			if ($selCDR->Execute(Array("Id" => $objCDR->Id)) != 1)
			{
				// Could not retrieve the CDR record
				$strErrorMsg = "ERROR: Could not retrieve CDR {$objCDR->Record} from the database (CDR Id = {$objCDR->Id}). Operation aborted.  Please notify your system administrator";
				break;
			}
			$arrCDRRecord = $selCDR->Fetch();

			$strStartDate = substr($arrCDRRecord['StartDatetime'], 0, 10);

			// Check that the CDR's StartDatetime is within 189 days of the next bill date of the account that the CDR will be allocated to
			if ($strStartDate < $arrService['EarliestAllowableCDRStartDate'])
			{
				// CDR is too old
				$strStartTime = date("H:i:s d/m/Y", strtotime($arrCDRRecord['StartDatetime']));
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} with start time: $strStartTime is considered too old to be billed to this customer.  Operation aborted.";
				break;
			}

			// Check the FNNs match
			if ($strFNN != $arrCDRRecord['FNN'])
			{
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} does not have FNN $strFNN. Operation Aborted.";
				break;
			}

			// Check the FNN has Status == CDR_BAD_OWNER
			if ($arrCDRRecord['Status'] != CDR_BAD_OWNER)
			{
				$strErrorMsg = "ERROR: CDR {$objCDR->Record} does not have 'Bad Owner' status.  Operation Aborted.";
				break;
			}

			// Everything is valid.  Update the FNN
			$arrUpdateColumns['Id']				= $objCDR->Id;
			$arrUpdateColumns['Service']		= $arrService['Id'];
			$arrUpdateColumns['Account']		= $arrService['Account'];
			$arrUpdateColumns['AccountGroup']	= $arrService['AccountGroup'];
			$arrUpdateColumns['Status']			= CDR_NORMALISED;

			if ($updCDR->Execute($arrUpdateColumns) === FALSE)
			{
				// Updating the CDR failed
				$strErrorMsg = "ERROR: Updating the CDR {$objCDR->Record} (CDR Id: $objCDR->Id) failed, unexpectedly.  Operation Aborted.  Please notify your system administrator.";
				break;
			}

			// Add the CDR to the list of successfully owned CDRs
			$arrSuccessfulCDRs[$objCDR->Id] = array('account_id'=>$arrService['Account'], 'service_id'=>$arrService['Id'], 'fnn'=>$arrService['FNN']);
		}

		return $arrSuccessfulCDRs;
	}



	/**
	 * updateQuarantineStatus()
	 *
	 * Updates the Quarantine Status for this CDR
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	public function updateQuarantineStatus()
	{
		// Determine what original CDR this is tied to, and retrieve it
		// TODO

		switch ($this->Status)
		{
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
	public function writeOff()
	{
		Log::getLog()->log("Writing off CDR {$this->Id}");

		// Very that we're a delinquent
		if ($this->Status !== CDR_BAD_OWNER)
		{
			$sStatus	= Constant_Group::getConstantGroup('CDR')->getConstantName($this->Status);
			throw new Exception("Failed to write of CDR ({$this->Id}), not a delinquent. Status is {$sStatus}");
		}

		Log::getLog()->log("... is delinquent, updating status");

		// Update status
		$this->Status	= CDR_DELINQUENT_WRITTEN_OFF;
		$this->save();

		Log::getLog()->log("... status updated, create cdr_delinquent_writeoff record");

		// Create cdr_delinquent_writeoff record
		$oLog	= CDR_Delinquent_WriteOff::createForCDR($this);

		Log::getLog()->log("CDR {$this->Id} written off");
	}

	/**
	 * getForInvoice
	 *
	 * Returns all CDRs for the given invoice
	 *
	 * @param	mixed	$mxdInvoice		integer			: Invoice Id representing the invoice in question
	 * 									Invoice object	: Representing the invoice in question
	 *
	 * @return	array					array of associative arrays representing the cdr records
	 * @method
	 */
	public static function getForInvoice($mxdInvoice)
	{
		// Invoiced CDRs can either be in the CDR database, or the flex one.
		// The CDR database has one table for the invoiced CDRs, but the table is partitioned
		// by the invoice_run_id of the invoice. To query this table we MUST include the
		// invoice_run_id in the where clause of the query.

		$objInvoice = is_numeric($mxdInvoice)? new Invoice(array('Id'=>$mxdInvoice), true) : $mxdInvoice;

		$dataSource = self::getDataSourceForInvoiceRunCDRs($objInvoice->invoiceRunId);

		// We now have all the details needed to load the CDRs from either database

		// Try to load the records from the cdr_invoiced table of the CDR db
		$cdrDb = Data_Source::get($dataSource);
		if ($dataSource == FLEX_DATABASE_CONNECTION_CDR)
		{
			$strCdrSelect =
				'SELECT id as "Id", fnn as "FNN", file as "File", carrier as "Carrier", carrier_ref as "CarrierRef", source as "Source", destination as "Destination", start_date_time as "StartDatetime", end_date_time as "EndDatetime", units as "Units", account_group as "AccountGroup", account as "Account", service as "Service", cost as "Cost", status as "Status", cdr as "CDR", description as "Description", destination_code as "DestinationCode", record_type as "RecordType", service_type as "ServiceType", charge as "Charge", rate as "Rate", normalised_on as "NormalisedOn", rated_on as "RatedOn", invoice_run_id, sequence_no as "SequenceNo", credit as "Credit" ' .
				"  FROM cdr_invoiced " .
				" WHERE account = " . $cdrDb->quote($objInvoice->account) .
				"   AND invoice_run_id = " . $cdrDb->quote($objInvoice->invoiceRunId) .
				" ORDER BY service_type ASC, fnn ASC, record_type ASC, start_date_time ASC";
		}
		else
		{
			// Must be in CDR table of default db
			$strCdrSelect =
				'SELECT Id as "Id", FNN as "FNN", File as "File", Carrier as "Carrier", CarrierRef as "CarrierRef", Source as "Source", Destination as "Destination", StartDatetime as "StartDatetime", EndDatetime as "EndDatetime", Units as "Units", AccountGroup as "AccountGroup", Account as "Account", Service as "Service", Cost as "Cost", Status as "Status", CDR as "CDR", Description as "Description", DestinationCode as "DestinationCode", RecordType as "RecordType", ServiceType as "ServiceType", Charge as "Charge", Rate as "Rate", NormalisedOn as "NormalisedOn", RatedOn as "RatedOn", invoice_run_id, SequenceNo as "SequenceNo", Credit as "Credit" ' .
				"  FROM CDR " .
				" WHERE Account = " . $cdrDb->quote($objInvoice->account) .
				"   AND invoice_run_id = " . $cdrDb->quote($objInvoice->invoiceRunId) .
				" ORDER BY ServiceType ASC, FNN ASC, RecordType ASC, StartDatetime ASC";
		}

		// Proceed with a query...
		$res =& $cdrDb->query($strCdrSelect);

		// Always check that result is not an error
		if (PEAR::isError($res)) {
			throw new Exception($res->getMessage() . "\n$strCdrSelect");
		}

		$rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

		// Otherwise, we should assume that there weren't any.
		return $rows;
	}

	/**
	 * getDataSourceForInvoiceRunCDRs
	 *
	 * Returns the name of the data source which currently stores the CDRs for the invoice run specified
	 * The way it does this is, if it finds at least 1 cdr in the flex data source referencing this invoice run, then it assumes all the cdrs are in the flex
	 * data source, else it will assume the CDRs are in the cdr data source (but won't actually test this)
	 *
	 * @param	int		$intInvoiceRunId	The invoice run id for the CDRs to find
	 *
	 * @return	string						either FLEX_DATABASE_CONNECTION_DEFAULT or FLEX_DATABASE_CONNECTION_CDR
	 * @method
	 */
	public static function getDataSourceForInvoiceRunCDRs($intInvoiceRunId)
	{
		$strQuery	= "SELECT CASE WHEN (SELECT 'Still In CDR table' FROM CDR WHERE invoice_run_id = $intInvoiceRunId LIMIT 1) = 'Still In CDR table' THEN '". FLEX_DATABASE_CONNECTION_DEFAULT ."' ELSE '". FLEX_DATABASE_CONNECTION_CDR ."' END AS DataSource";
		$db			= Data_Source::get();
		$res		= $db->query($strQuery);

		if (PEAR::isError($res))
		{
			throw new Exception("Failed to find the data source storing CDRs for invoice run: $intInvoiceRunId - " . $res->getMessage());
		}

		$strDataSourceName = $res->fetchOne();

		return $strDataSourceName;
	}

	public static function getCDRDetails($iCdrId, $iInvoiceRunId=null)
	{
		$rFlexDb	= Data_Source::get(FLEX_DATABASE_CONNECTION_DEFAULT);
		$aResult	= array();
		$rCDRDb		= null;

		// Check if the cdr is invoiced or not
		if (is_null($iInvoiceRunId))
		{
			// MySQL Database, not invoiced
			$rCDRDb	= Data_Source::get(FLEX_DATABASE_CONNECTION_DEFAULT);
			$sCdr 	= "
				SELECT	t.Name as \"RecordType\", c.Description as \"Description\", c.Source as \"Source\", c.Destination as \"Destination\", c.EndDatetime as \"EndDatetime\", c.StartDatetime as \"StartDatetime\", c.Units as \"Units\", t.DisplayType as \"DisplayType\", c.Charge as \"Charge\",
					   	c.File as \"FileId\", c.Carrier as \"CarrierId\", c.CarrierRef as \"CarrierRef\", c.Cost as \"Cost\", c.Status as \"Status\", c.DestinationCode as \"DestinationCode\", c.Rate as \"RateId\", c.NormalisedOn as \"NormalisedOn\", c.RatedOn as \"RatedOn\", c.SequenceNo as \"SequenceNo\", c.Credit as \"Credit\", c.CDR as \"RawCDR\"
				FROM 	CDR c INNER JOIN RecordType t ON c.RecordType = t.Id
				WHERE 	c.Id = $iCdrId
			";
		}
		else
		{
			// Get the data source for the service CDR data
			$sDataSource	= CDR::getDataSourceForInvoiceRunCDRs($iInvoiceRunId);
			$rCDRDb 		= Data_Source::get($sDataSource);

			if ($sDataSource == FLEX_DATABASE_CONNECTION_DEFAULT)
			{
				// MySQL Database, invoiced
				$sCdr = "
					SELECT	t.Name as \"RecordType\", c.Description as \"Description\", c.Source as \"Source\", c.Destination as \"Destination\", c.EndDatetime as \"EndDatetime\", c.StartDatetime as \"StartDatetime\", c.Units as \"Units\", t.DisplayType as \"DisplayType\", c.Charge as \"Charge\",
						   	c.File as \"FileId\", c.Carrier as \"CarrierId\", c.CarrierRef as \"CarrierRef\", c.Cost as \"Cost\", c.Status as \"Status\", c.DestinationCode as \"DestinationCode\", c.Rate as \"RateId\", c.NormalisedOn as \"NormalisedOn\", c.RatedOn as \"RatedOn\", c.SequenceNo as \"SequenceNo\", c.Credit as \"Credit\", c.CDR as \"RawCDR\"
					FROM 	CDR c INNER JOIN RecordType t ON c.RecordType = t.Id
					WHERE 	invoice_run_id = $iInvoiceRunId
					AND 	c.Id = $iCdrId
				";
			}
			else
			{
				// PostgreSQL Database,
				$sCdr = "
					SELECT 	t.name as \"RecordType\", c.description as \"Description\", c.source as \"Source\", c.destination as \"Destination\", c.end_date_time as \"EndDatetime\", c.start_date_time as \"StartDatetime\", c.units as \"Units\", t.display_type as \"DisplayType\", c.charge as \"Charge\",
						   	c.file as \"FileId\", c.carrier as \"CarrierId\", c.carrier_ref as \"CarrierRef\", c.cost as \"Cost\", c.status as \"Status\", c.destination_code as \"DestinationCode\", c.rate as \"RateId\", c.normalised_on as \"NormalisedOn\", c.rated_on as \"RatedOn\", c.sequence_no as \"SequenceNo\", c.credit as \"Credit\", c.cdr as \"RawCDR\"
					FROM 	cdr_invoiced c INNER JOIN record_type t ON c.record_type = t.id
					WHERE 	invoice_run_id = $iInvoiceRunId
					AND 	c.id = $iCdrId
				";
			}
		}

		// Run the CDR query
		$rCdr	= $rCDRDb->query($sCdr);

		if (PEAR::isError($rCdr))
		{
			throw new Exception("Failed to load CDR details: " . $rCdr->getMessage() ." - Query: $sCdr");
		}

		$aCDR	= $rCdr->fetchRow(MDB2_FETCHMODE_ASSOC);

		// Get more information for certain fields
		$aCDR['RateName']					= '';
		$aCDR['CarrierName']				= '';
		$aCDR['DestinationCodeDescription']	= '';
		$aCDR['FileName']					= '';

		// Rate name
		if ($aCDR['RateId'])
		{
			$oRate				= Rate::getForId($aCDR['RateId']);
			$aCDR['RateName']	= $oRate->Name;
		}

		// Carrier name
		if ($aCDR['CarrierId'])
		{
			$aCDR['CarrierName']	= Constant_Group::getConstantGroup('Carrier')->getConstantName($aCDR['CarrierId']);
		}

		// Destination code description
		if ($aCDR['DestinationCode'])
		{
			$oDestination						= Destination::getForCode($aCDR['DestinationCode']);
			$aCDR['DestinationCodeDescription'] = $oDestination->Description;
		}

		// File name
		if ($aCDR['FileId'])
		{
			$oFileImport		= File_Import::getForId($aCDR['FileId']);
			$aCDR['FileName']	= "{$oFileImport->FileName} ({$oFileImport->Location})";
		}

		return $aCDR;
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

	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selUnbilledCDRTotal':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"CDR",
																					"	COUNT(Id)	AS cdr_count,
																						SUM(Charge)	AS cdr_charge",
																					"	Status	= ".CDR_RATED."
																						AND Service = <service_id>
																						AND (ISNULL(<invoice_run_id>) OR invoice_run_id = <invoice_run_id>)");
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
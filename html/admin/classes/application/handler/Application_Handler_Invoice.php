<?php

class Application_Handler_Invoice extends Application_Handler
{
	private static 	$_aInterimEligibilityColumns	=	array
														(
															'ACCOUNT_ID'							=> 'Account',
															'ACCOUNT_NAME'							=> 'Account Name',
															'DELIVERY_METHOD'						=> 'Delivery Method',
															'SERVICE_FNN'							=> 'Service FNN',
															'ACTIVE_SERVICES'						=> 'Total No. Active Services',
															'ACTIVE_PENDING_SERVICES'				=> 'Total No. Active & Pending Services',
															'HAS_TOLLED'							=> 'Has Tolled',
															'CURRENT_PLAN'							=> 'Current Plan',
															'REQUIRES_CDR'							=> 'Requires CDR',
															'MONTHLY_PLAN_FEE'						=> 'Monthly Plan Fee',
															'DAILY_RATE'							=> 'Daily Plan Fee',
															'PLAN_CHARGE'							=> 'Plan Charge',
															'PLAN_CHARGE_DAYS'						=> 'Plan Charge Days',
															'PLAN_CHARGE_DESCRIPTION'				=> 'Plan Charge Description',
															'INTERIM_PLAN_CREDIT'					=> 'Plan Credit - Interim Bill',
															'INTERIM_PLAN_CREDIT_DAYS'				=> 'Plan Credit - Interim Bill - Days',
															'INTERIM_PLAN_CREDIT_DESCRIPTION'		=> 'Plan Credit Description - Interim Bill',
															'PRODUCTION_PLAN_CREDIT'				=> 'Plan Credit - 1st Bill',
															'PRODUCTION_PLAN_CREDIT_DAYS'			=> 'Plan Credit - 1st Bill - Days',
															'PRODUCTION_PLAN_CREDIT_DESCRIPTION'	=> 'Plan Credit Description - 1st Bill',
															//'DEBUG_BILLING_PERIOD_START'			=> 'DEBUG: Billing Period Start Date',
															//'DEBUG_BILLING_PERIOD_END'				=> 'DEBUG: Billing Period End Date',
															//'DEBUG_BILLING_PERIOD_DAYS'				=> 'DEBUG: Billing Period Length (Days)',
														);
	
	private static 	$_aInterimExceptionsColumns	=	array
													(
														'ACCOUNT_ID'	=> 'Account',
														'SERVICE_FNN'	=> 'Service FNN',
														'REASON'		=> 'Reason for Exception'
													);
	
	private static	$_aInterimProcessingColumns	=	array
													(
														'ACCOUNT_ID'							=> 'Account',
														'SERVICE_FNN'							=> 'Service FNN',
														'PLAN_CHARGE'							=> 'Plan Charge',
														'PLAN_CHARGE_DESCRIPTION'				=> 'Plan Charge Description',
														'INTERIM_PLAN_CREDIT'					=> 'Plan Credit - Interim Bill',
														'INTERIM_PLAN_CREDIT_DESCRIPTION'		=> 'Plan Credit Description - Interim Bill',
														'PRODUCTION_PLAN_CREDIT'				=> 'Plan Credit - 1st Bill',
														'PRODUCTION_PLAN_CREDIT_DESCRIPTION'	=> 'Plan Credit Description - 1st Bill',
													);
	
	// View all the breakdown for a service on an invoice
	public function Service($subPath)
	{
		try
		{
			$intServiceTotal = count($subPath) ? intval(array_shift($subPath)) : 0;

			$intRecordType = 0;

			
			$db = Data_Source::get();
			
			$sqlServiceTotal = "
				SELECT i.Id AS InvoiceId, t.Account as AccountId, t.FNN as FNN, t.Service as ServiceId, a.BusinessName as BusinessName, a.TradingName as TradingName, s.ServiceType as ServiceType, t.invoice_run_id as InvoiceRunId, t.Id ServiceTotal, 
						CASE WHEN (SELECT 'Still In CDR table' FROM CDR WHERE invoice_run_id = i.invoice_run_id LIMIT 1) = 'Still In CDR table' THEN '". FLEX_DATABASE_CONNECTION_DEFAULT ."' ELSE '". FLEX_DATABASE_CONNECTION_CDR ."' END AS DataSource
				  FROM Invoice i, ServiceTotal t, Service s, Account a, InvoiceRun r 
				 WHERE t.Id = $intServiceTotal 
				   AND i.invoice_run_id = t.invoice_run_id 
				   AND i.invoice_run_id = r.Id 
				   AND i.Account = t.Account 
				   AND s.Id = t.Service 
				   AND a.Id = t.Account 
			";
	
			$res = $db->query($sqlServiceTotal);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load service details: " . $res->getMessage());
			}
	
			$serviceDetails = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
	
			if (!$serviceDetails)
			{
				throw new Exception("Failed to find service details for service total $intServiceTotal.");
			}
	
			$intAccountId = $serviceDetails['AccountId'];
			$intInvoiceId = $serviceDetails['InvoiceId'];
			$intInvoiceRunId = $serviceDetails['InvoiceRunId'];
			$intServiceId = $serviceDetails['ServiceId'];
			$intServiceType = $serviceDetails['ServiceType'];
			$fnn = $serviceDetails['FNN'];
			$dataSource = $serviceDetails['DataSource'];

			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($intAccountId, true);
			BreadCrumb()->InvoicesAndPayments($intAccountId);
			BreadCrumb()->ViewInvoice($intInvoiceId, $intAccountId);
			BreadCrumb()->SetCurrentPage("Service: $fnn");
			AppTemplateAccount::BuildContextMenu($intAccountId);
			
			$arrDetailsToRender = array();
	
			$arrDetailsToRender['Invoice'] = $serviceDetails;
	
	
	
			// Need to load up the Adjustments for the invoice
			$aVisibleChargeTypes	= array(CHARGE_TYPE_VISIBILITY_VISIBLE);
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT))
			{
				$aVisibleChargeTypes[]	= CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL;
			}
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
			{
				$aVisibleChargeTypes[]	= CHARGE_TYPE_VISIBILITY_HIDDEN;
			}
	
			$sqlAdjustments = "
				SELECT c.Id as ChargeId, c.ChargeType ChargeType, c.Description Description, s.Id as ServiceId, s.FNN as FNN, ChargedOn as Date, c.Amount Amount, c.Nature as Nature
				  FROM Service s JOIN Charge c ON (s.Id = c.Service) LEFT JOIN ChargeType ct ON (ct.Id = c.charge_type_id OR c.ChargeType = ct.ChargeType)
				 WHERE c.invoice_run_id = $intInvoiceRunId
			       AND c.Service = $intServiceId
			       AND ct.charge_type_visibility_id IN (".implode(', ', $aVisibleChargeTypes).")
			";
	
			$res = $db->query($sqlAdjustments);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load adjustments: $sqlAdjustments " . $res->getMessage());
			}
			$arrDetailsToRender['Adjustments'] = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
			
			
			
	
			// Need to load up the RecordTypes for filtering
	
			$sqlRecordTypes = " SELECT Id, Name, Description, DisplayType FROM RecordType WHERE ServiceType = $intServiceType ";
	
			$res = $db->query($sqlRecordTypes, array('integer', 'text', 'text', 'integer'));
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load call record types: " . $res->getMessage());
			}
			
			// Use the id of each RecordType as the key into the array $arrDetailsToRender['RecordTypes'] for easier referal
			$arrDetailsToRender['RecordTypes'] = KeyifyArray($res->fetchAll(MDB2_FETCHMODE_ASSOC), "Id");

			// Need to load up the CDRs from the cdr_invoiced table for the current range of CDRs & record type
			
			$cdrDb = Data_Source::get($dataSource);

			$offset = 0;
			$limit = 30;

			$arrDetailsToRender['filter'] = array(
				'offset' => array_key_exists('offset', $_REQUEST) ? intval($_REQUEST['offset']) : 0,
				'limit' => 30,
				'recordType' => (array_key_exists('recordType', $_REQUEST) && $_REQUEST['recordType']) ? intval($_REQUEST['recordType']) : NULL,
				'recordCount' => 0,
			);

			$alises = array('ChargeId', 'ChargeType', 'Description', 'FNN', 'Date', 'Amount', 'Nature');

			if ($dataSource == FLEX_DATABASE_CONNECTION_DEFAULT)
			{
				$sqlCdrs = "
					SELECT c.Id as \"Id\", c.RecordType as \"RecordTypeId\", c.Description as \"Description\", c.Source as \"Source\", c.Destination as \"Destination\", c.StartDatetime as \"StartDatetime\", c.Units as \"Units\", c.Charge as \"Charge\", c.Credit as \"Credit\"
					  FROM CDR c
					 WHERE invoice_run_id = $intInvoiceRunId
					   AND Account = $intAccountId
				       AND c.Service = $intServiceId
				";
	
				$sqlCountCdrs = "SELECT COUNT(*) FROM CDR c WHERE invoice_run_id = $intInvoiceRunId AND Account = $intAccountId AND c.Service = $intServiceId";
	
				if ($arrDetailsToRender['filter']['recordType'])
				{
					$sqlCdrs .= " AND c.RecordType = " . $arrDetailsToRender['filter']['recordType'] . " ";
					$sqlCountCdrs .= " AND c.RecordType = " . $arrDetailsToRender['filter']['recordType'] . " ";
				}
	
				$sqlCdrs .= " ORDER BY c.StartDatetime ASC LIMIT " . $arrDetailsToRender['filter']['limit'] . " OFFSET " . $arrDetailsToRender['filter']['offset'] . " ";
			}
			else
			{
				$sqlCdrs = "
					SELECT c.id as \"Id\", c.record_type as \"RecordTypeId\", c.description as \"Description\", c.source as \"Source\", c.destination as \"Destination\", c.start_date_time as \"StartDatetime\", c.units as \"Units\", c.charge as \"Charge\", c.credit as \"Credit\"
					  FROM cdr_invoiced c
					 WHERE invoice_run_id = $intInvoiceRunId
					   AND account = $intAccountId
				       AND c.service = $intServiceId
				";
	
				$sqlCountCdrs = "SELECT COUNT(*) FROM cdr_invoiced c WHERE invoice_run_id = $intInvoiceRunId AND account = $intAccountId AND c.Service = $intServiceId";
	
				if ($arrDetailsToRender['filter']['recordType'])
				{
					$sqlCdrs .= " AND c.record_type = " . $arrDetailsToRender['filter']['recordType'] . " ";
					$sqlCountCdrs .= " AND c.record_type = " . $arrDetailsToRender['filter']['recordType'] . " ";
				}
	
				$sqlCdrs .= " ORDER BY c.start_date_time ASC LIMIT " . $arrDetailsToRender['filter']['limit'] . " OFFSET " . $arrDetailsToRender['filter']['offset'] . " ";
			}

			$res = $cdrDb->query($sqlCountCdrs);
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to count CDRs: " . $res->getMessage());
			}
			$arrDetailsToRender['filter']['recordCount'] = $res->fetchOne();

			$res = $cdrDb->query($sqlCdrs);

			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load CDRs: " . $res->getMessage());
			}

			$arrDetailsToRender['CDRs'] = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

			$this->LoadPage('invoice_service', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to load the Invoice Service details page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}

	public function CDR($subPath)
	{
		try
		{
			if (count($subPath) !== 3 || !is_numeric($subPath[0]) || !is_numeric($subPath[1]) || !is_numeric($subPath[2]))
			{
				throw new Exception('Invalid arguments passed to page: ' . htmlspecialchars(implode(', ', $subPath)));
			}

			$db = Data_Source::get();
			
			$intServiceTotal = intval($subPath[0]);
			$intInvoiceRunId = intval($subPath[1]);
			$intCdrId = intval($subPath[2]);
			
			$sqlServiceTotal = "
				SELECT i.Id AS InvoiceId, t.Account as AccountId, t.FNN as FNN, t.Service as ServiceId, a.BusinessName as BusinessName, a.TradingName as TradingName, s.ServiceType as ServiceType, t.invoice_run_id as InvoiceRunId, t.Id ServiceTotal, 
						CASE WHEN (SELECT 'Still In CDR table' FROM CDR WHERE invoice_run_id = i.invoice_run_id LIMIT 1) = 'Still In CDR table' THEN '". FLEX_DATABASE_CONNECTION_DEFAULT ."' ELSE '". FLEX_DATABASE_CONNECTION_CDR ."' END AS DataSource
				  FROM Invoice i, ServiceTotal t, Service s, Account a, InvoiceRun r
				 WHERE t.Id = $intServiceTotal 
				   AND i.invoice_run_id = t.invoice_run_id 
				   AND i.invoice_run_id = r.Id 
				   AND i.Account = t.Account 
				   AND s.Id = t.Service 
				   AND a.Id = t.Account 
			";
	
			$res = $db->query($sqlServiceTotal);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load service details: " . $res->getMessage());
			}
	
			$serviceDetails = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
	
			if (!$serviceDetails)
			{
				throw new Exception("Failed to find service details for service total $intServiceTotal.");
			}
	
			$intAccountId = $serviceDetails['AccountId'];
			$intInvoiceId = $serviceDetails['InvoiceId'];
			$intInvoiceRunId = $serviceDetails['InvoiceRunId'];
			$intServiceId = $serviceDetails['ServiceId'];
			$intServiceType = $serviceDetails['ServiceType'];
			$fnn = $serviceDetails['FNN'];
			$dataSource = $serviceDetails['DataSource'];

			$cdrDb = Data_Source::get($dataSource);

			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($intAccountId, true);
			BreadCrumb()->InvoicesAndPayments($intAccountId);
			BreadCrumb()->ViewInvoice($intInvoiceId);
			BreadCrumb()->ViewInvoiceService($intServiceTotal, $fnn);
			BreadCrumb()->SetCurrentPage("Record Id: " . $intCdrId);
			AppTemplateAccount::BuildContextMenu($intAccountId);

			if ($dataSource == FLEX_DATABASE_CONNECTION_DEFAULT)
			{
				$sqlCdr = "
					SELECT t.Name as \"RecordType\", c.Description as \"Description\", c.Source as \"Source\", c.Destination as \"Destination\", c.EndDatetime as \"EndDatetime\", c.StartDatetime as \"StartDatetime\", c.Units as \"Units\", t.DisplayType as \"DisplayType\", c.Charge as \"Charge\",
						   c.File as \"FileId\", c.Carrier as \"CarrierId\", c.CarrierRef as \"CarrierRef\", c.Cost as \"Cost\", c.Status as \"Status\", c.DestinationCode as \"DestinationCode\", c.Rate as \"RateId\", c.NormalisedOn as \"NormalisedOn\", c.RatedOn as \"RatedOn\", c.SequenceNo as \"SequenceNo\", c.Credit as \"Credit\", c.CDR as \"RawCDR\"
					  FROM CDR c INNER JOIN RecordType t ON c.RecordType = t.Id
					 WHERE invoice_run_id = $intInvoiceRunId
						AND c.Id = $intCdrId
				";
			}
			else
			{
				$sqlCdr = "
					SELECT t.name as \"RecordType\", c.description as \"Description\", c.source as \"Source\", c.destination as \"Destination\", c.end_date_time as \"EndDatetime\", c.start_date_time as \"StartDatetime\", c.units as \"Units\", t.display_type as \"DisplayType\", c.charge as \"Charge\",
						   c.file as \"FileId\", c.carrier as \"CarrierId\", c.carrier_ref as \"CarrierRef\", c.cost as \"Cost\", c.status as \"Status\", c.destination_code as \"DestinationCode\", c.rate as \"RateId\", c.normalised_on as \"NormalisedOn\", c.rated_on as \"RatedOn\", c.sequence_no as \"SequenceNo\", c.credit as \"Credit\", c.cdr as \"RawCDR\"
					  FROM cdr_invoiced c INNER JOIN record_type t ON c.record_type = t.id
					 WHERE invoice_run_id = $intInvoiceRunId
						AND c.id = $intCdrId
				";
			}

			$res = $cdrDb->query($sqlCdr);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load CDR details: " . $res->getMessage() ." - Query: $sqlCdr");
			}

			$arrCDR = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

			if ($arrCDR['RateId'])
			{
				$sqlName = 'SELECT Name as "Name" FROM Rate WHERE Id = ' . $arrCDR['RateId'];
				$res = $db->query($sqlName);
				if (PEAR::isError($res))
				{
					throw new Exception("Failed to load Rate name: " . $res->getMessage());
				}
				$rateName = $res->fetchOne();
			}
			else
			{
				$rateName = '';
			}

			if ($arrCDR['CarrierId'])
			{
				$sqlName = 'SELECT Name as "Name" FROM Carrier WHERE Id = ' . $arrCDR['CarrierId'];
				$res = $db->query($sqlName);
				if (PEAR::isError($res))
				{
					throw new Exception("Failed to load Carrier name: " . $res->getMessage());
				}
				$carrierName = $res->fetchOne();
			}
			else
			{
				$carrierName = '';
			}


			if ($arrCDR['DestinationCode'])
			{
				$sqlName = 'SELECT Description as "Description" FROM Destination WHERE Code = ' . $arrCDR['DestinationCode'];
				$res = $db->query($sqlName);
				if (PEAR::isError($res))
				{
					throw new Exception("Failed to load Destination name: " . $res->getMessage());
				}
				$destination = $res->fetchOne() . ' (' . $arrCDR['DestinationCode'] . ')';
			}
			else
			{
				$destination = '';
			}


			if ($arrCDR['FileId'])
			{
				$sqlName = 'SELECT FileName as "FileName", Location as "FileLocation" FROM FileImport WHERE Id = ' . $arrCDR['FileId'];
				$res = $db->query($sqlName);
				if (PEAR::isError($res))
				{
					throw new Exception("Failed to load File name: " . $res->getMessage());
				}
				$arrFile = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
				$fileName = $arrFile['FileName'] . ' (' . $arrFile['FileLocation'] . ')';
			}
			else
			{
				$fileName = '';
			}

			$status = $GLOBALS['*arrConstant']['CDR'][$arrCDR['Status']]['Description'];

			$arrDetailsToRender = array();
			
			$arrDetailsToRender['FNN'] 				= $fnn;
			$arrDetailsToRender['Id'] 				= $intCdrId;
			$arrDetailsToRender['InvoiceId'] 		= $intInvoiceId;
			$arrDetailsToRender['FileName'] 		= $fileName;
			$arrDetailsToRender['Carrier'] 			= $carrierName;
			$arrDetailsToRender['CarrierRef'] 		= $arrCDR['CarrierRef'];
			$arrDetailsToRender['Source'] 			= $arrCDR['Source'];#)
			$arrDetailsToRender['Destination'] 		= $arrCDR['Destination'];#)
			$arrDetailsToRender['StartDatetime'] 	= $arrCDR['StartDatetime'];#)
			$arrDetailsToRender['EndDatetime'] 		= $arrCDR['EndDatetime'];#)
			$arrDetailsToRender['Cost'] 			= $arrCDR['Cost'];
			$arrDetailsToRender['Status'] 			= $status;
			$arrDetailsToRender['Description'] 		= $arrCDR['Description'];#)
			$arrDetailsToRender['DestinationCode'] 	= $destination;
			$arrDetailsToRender['RecordType'] 		= $arrCDR['RecordType'];#)
			$arrDetailsToRender['Charge'] 			= $arrCDR['Charge'];#)
			$arrDetailsToRender['Rate'] 			= $rateName;
			$arrDetailsToRender['RateId'] 			= $arrCDR['RateId'];
			$arrDetailsToRender['NormalisedOn'] 	= $arrCDR['NormalisedOn'];
			$arrDetailsToRender['RatedOn'] 			= $arrCDR['RatedOn'];
			$arrDetailsToRender['InvoiceRunId'] 	= $intInvoiceRunId;
			$arrDetailsToRender['SequenceNo'] 		= $arrCDR['SequenceNo'];
			$arrDetailsToRender['Credit'] 			= $arrCDR['Credit'];
			$arrDetailsToRender['RawCDR'] 			= $arrCDR['RawCDR'];
			$arrDetailsToRender['Units'] 			= $arrCDR['Units'];
			$arrDetailsToRender['DisplayType'] 		= $arrCDR['DisplayType'];
			


			$this->LoadPage('Invoice_CDR', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
			
			return;
			
			$arrDetailsToRender['Message'] = "An error occured when trying to load the invoiced CDR details page";
			$arrDetailsToRender['ErrorMessage'] = "No handler has been defined for this request.";
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to load the invoiced CDR details page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	public function PDF($subPath)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR_VIEW))
			{
				throw new Exception('You do not have permission to view this PDF.');
			}
			
			if (!isset($subPath[0]) || !isset($_GET['Account']) || !isset($_GET['Invoice_Run_Id']) || !isset($_GET['Year']) || !isset($_GET['Month']))
			{
				throw new Exception('Invalid arguments passed to page');
			}
			
			$iInvoiceId		= $subPath[0];
			$iAccountId		= $_GET['Account'];
			$iInvoiceRunId	= $_GET['Invoice_Run_Id'];
			$iYear 			= $_GET['Year'];
			$iMonth 		= $_GET['Month'];
			
			// Try to pull the Invoice PDF
			$sInvoice 		= GetPDFContent($iAccountId, $iYear, $iMonth, $iInvoiceId, $iInvoiceRunId);
			
			if (!$sInvoice)
			{
				throw new Exception("PDF Not Found");
			}
		
			$sInvoiceFilename = GetPdfFilename($iAccountId, $iYear, $iMonth, $iInvoiceId, $iInvoiceRunId);
	
			header("Content-Type: application/pdf");
			header("Content-Disposition: attachment; filename=\"$sInvoiceFilename\"");
			echo $sInvoice;
			exit;
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] = "An error occured when trying to retrieve the PDF";
			$aDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function InterimEligibilityReport($subPath)
	{
		// DEBUG
		//Flex_Date::periodLength(time(), time() + Flex_Date::SECONDS_IN_DAY, $sAccuracy='d');
		
		// Prepare the CSV File
		$oCSVFile	= self::buildInterimEligibilityReport(self::getInterimEligibileServices());
		
		// Output the Report & return to the User Agent
		$sFileName	= "interim-invoice-eligibility-report-".date("YmdHis").".csv";
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=\"{$sFileName}\"");
		echo $oCSVFile->save();
		exit;
	}
	
	public function ActionInterimInvoicesReport($subPath)
	{
		$oResponse			= new stdClass();
		$oFlexDataAccess	= DataAccess::getDataAccess();
		
		try
		{
			// Ensure that we have the appropriate Permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_CREDIT_MANAGEMENT))
			{
				throw new Exception("You do not have permission to submit an Interim Invoice Eligibility Report.");
			}
			
			// Try to start a Transaction
			if (!$oFlexDataAccess->TransactionStart())
			{
				throw new Exception("There was an internal error in Flex.  Please notify YBS of this issue with the following message: 'Unable to start a Transaction'");
			}
			
			try
			{
				$sSubmittedEligibilityReportPath		= dirname($_FILES['Invoice_Interim_EligibilityUpload_File']['tmp_name']).'/'.$_FILES['Invoice_Interim_EligibilityUpload_File']['name'];
				$sSubmittedEligibilityReportFileName	= $_FILES['Invoice_Interim_EligibilityUpload_File']['name'];
				
				move_uploaded_file($_FILES['Invoice_Interim_EligibilityUpload_File']['tmp_name'], $sSubmittedEligibilityReportPath);
				
				// Ensure the submitted file meets a few contraints
				// MIME Type
				$sMIMEType	= mime_content_type($sSubmittedEligibilityReportPath);
				if (!in_array($sMIMEType, array('text/csv', 'text/plain')))
				{
					throw new Exception("The submitted File is of the wrong File Type.  (Expected: text/csv; Actual: {$sMIMEType})");
				}
				
				// Filename
				// TODO?
				
				// Parse the Report	
				$oCSVImportFile	= new File_CSV();
				$oCSVImportFile->setColumns(array_values(self::$_aInterimEligibilityColumns));
				$oCSVImportFile->importFile($sSubmittedEligibilityReportPath, true);
				
				// Get updated eligibility list
				$aServices	= self::getInterimEligibileServices();
				
				$aAccounts	= array();
				
				// Verify the details for all of the submitted Services
				foreach ($oCSVImportFile as $aImportService)
				{
					$iAccountId				= (int)$aImportService[self::$_aInterimEligibilityColumns['ACCOUNT_ID']];
					
					// Dirty hacks to prepend 0s to FNNs which have had them stripped off
					$sFNN					= $aImportService[self::$_aInterimEligibilityColumns['SERVICE_FNN']];
					$sFNN					= self::preg_match_string("/\d{9,10}(i)?$/", $sFNN);
					if ($sFNN[0] != '0' && !preg_match("/^(13\d{4}|1[38]00\d{6})$/", $sFNN))
					{
						$sFNN	= '0'.$sFNN;
					}
					
					$sAccountServiceIndex	= "{$iAccountId}.{$sFNN}";
					$aAccounts[$iAccountId]	= (array_key_exists($iAccountId, $aAccounts)) ? $aAccounts[$iAccountId] : array('aBlacklist'=>array(), 'aWhitelist'=>array(), 'aGreylist'=>array());
					
					try
					{
						// Does this Service exist in the current eligibility list?
						if (array_key_exists($sAccountServiceIndex, $aServices))
						{
							// Found it -- do our figures match?
							$aService	= &$aServices[$sAccountServiceIndex];
							
							// Monthly Plan Fee
							self::_compareInterimEligible(	(float)$aImportService[self::$_aInterimEligibilityColumns['MONTHLY_PLAN_FEE']],
															(float)$aService['plan_charge'],
															"Monthly Plan Fee mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['MONTHLY_PLAN_FEE']]."'; Calculated: '".(float)$aService['plan_charge']."')");
							
							// Daily Rate
							self::_compareInterimEligible(	(float)$aImportService[self::$_aInterimEligibilityColumns['DAILY_RATE']],
															(float)$aService['aAdjustments']['daily_rate'],
															"Daily Rate mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['DAILY_RATE']]."'; Calculated: '".(float)$aService['aAdjustments']['daily_rate']."')");
							
							// Plan Charge
							self::_compareInterimEligible(	(float)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE']],
															(float)$aService['aAdjustments']['plan_charge'],
															"Plan Charge mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE']]."'; Calculated: '".(float)$aService['aAdjustments']['plan_charge']."')");
							
							// Plan Charge Days
							self::_compareInterimEligible(	(int)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DAYS']],
															(int)$aService['aAdjustments']['plan_charge_days'],
															"Plan Charge Days mismatch (Supplied: '".(int)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DAYS']]."'; Calculated: '".(int)$aService['aAdjustments']['plan_charge_days']."')");
							
							// Plan Charge Description
							self::_compareInterimEligible(	(string)$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DESCRIPTION']],
															(string)$aService['aAdjustments']['plan_charge_description'],
															"Plan Charge Description mismatch (Supplied: '".$aImportService[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DESCRIPTION']]."'; Calculated: '".$aService['aAdjustments']['plan_charge_description']."')",
															false);
							
							// Interim Plan Credit
							self::_compareInterimEligible(	(float)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT']],
															(float)$aService['aAdjustments']['interim_plan_credit'],
															"Interim Plan Credit mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT']]."'; Calculated: '".(float)$aService['aAdjustments']['interim_plan_credit']."')");
							
							// Interim Plan Credit Days
							self::_compareInterimEligible(	(int)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DAYS']],
															(int)$aService['aAdjustments']['interim_plan_credit_days'],
															"Interim Plan Credit Days mismatch (Supplied: '".(int)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DAYS']]."'; Calculated: '".(int)$aService['aAdjustments']['interim_plan_credit_days']."')");
							
							// Interim Plan Credit Description
							self::_compareInterimEligible(	(string)$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']],
															(string)$aService['aAdjustments']['interim_plan_credit_description'],
															"Interim Plan Credit Description mismatch (Supplied: '".$aImportService[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']]."'; Calculated: '".$aService['aAdjustments']['interim_plan_credit_description']."')",
															false);
							
							// Production Plan Credit
							self::_compareInterimEligible(	(float)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT']],
															(float)$aService['aAdjustments']['production_plan_credit'],
															"Production Plan Credit mismatch (Supplied: '".(float)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT']]."'; Calculated: '".(float)$aService['aAdjustments']['production_plan_credit']."')");
							
							// Production Plan Credit Days
							self::_compareInterimEligible(	(int)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DAYS']],
															(int)$aService['aAdjustments']['production_plan_credit_days'],
															"Production Plan Credit Days mismatch (Supplied: '".(int)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DAYS']]."'; Calculated: '".(int)$aService['aAdjustments']['production_plan_credit_days']."')");
							
							// Production Plan Credit Description
							self::_compareInterimEligible(	(string)$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']],
															(string)$aService['aAdjustments']['production_plan_credit_description'],
															"Production Plan Credit Description mismatch (Supplied: '{$aImportService[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']]}'; Calculated: '{$aService['aAdjustments']['production_plan_credit_description']}')",
															false);
							
							// Everthing appears to match -- add to Action list
							$aAccounts[$iAccountId]['aWhitelist'][$sFNN]	= true;
							
							// Clear the $aService reference
							unset($aService);
						}
						else
						{
							// Can't find it -- this Service is probably not eligible anymore
							throw new Exception_Invoice_InterimEligibilityMismatch("{$sFNN} exists in the resubmitted Authentication Report, but not in the current Eligibility Report");
						}
					}
					catch (Exception_Invoice_InterimEligibilityMismatch $eException)
					{
						/*var_dump($aService);
						var_dump($aImportService);
						throw new Exception("Debug");
						*/
						// Add to the Blacklist
						$aAccounts[$iAccountId]['aBlacklist'][$sFNN]	= $eException->getMessage();
					}
				}
				
				// Check to see if the number of submitted eligible Services for an Account matches the new list
				$iEligibleServices	= 0;
				foreach ($aServices as $sAccountServiceIndex=>$aService)
				{
					$aAccountServiceIndex	= explode('.', $sAccountServiceIndex);
					$iAccountId				= $aAccountServiceIndex[0];
					$sFNN					= $aAccountServiceIndex[1];
					
					$aAccounts[$iAccountId]	= (array_key_exists($iAccountId, $aAccounts)) ? $aAccounts[$iAccountId] : array('aBlacklist'=>array(), 'aWhitelist'=>array(), 'aGreylist'=>array());
					
					if (!array_key_exists($sFNN, $aAccounts[$iAccountId]['aWhitelist']) && !array_key_exists($sFNN, $aAccounts[$iAccountId]['aBlacklist']))
					{
						// Service wasn't referenced in the Submitted Report -- add to Greylist
						 $aAccounts[$iAccountId]['aGreylist'][$sFNN]	= "{$sFNN} exists in the current Eligibility Report, but not in the resubmitted Authentication Report";
					}
				}
				
				// Action the Eligible Accounts
				$oCSVExceptionsReport	= new File_CSV();
				$oCSVExceptionsReport->setColumns(array_values(self::$_aInterimExceptionsColumns));
				
				$oCSVProcessingReport	= new File_CSV();
				$oCSVProcessingReport->setColumns(array_values(self::$_aInterimProcessingColumns));
				
				$iAccountsInvoiced			= 0;
				$iAccountsIgnored			= 0;
				$iAccountsFailed			= 0;
				$iAccountsAdjustmentsAdded	= 0;
				$iServicesInvoiced			= 0;
				$iServicesIgnored			= 0;
				$iServicesFailed			= 0;
				$iServicesAdjustmentsAdded	= 0;
				$fTotalPlanCharge			= 0.0;
				$fTotalInterimPlanCredit	= 0.0;
				$fTotalProductionPlanCredit	= 0.0;
				foreach ($aAccounts as $iAccountId=>$aAccount)
				{
					// If we have any Exceptions, add all Black/Whitelisted Services to the Exceptions report
					if (count($aAccount['aBlacklist']))
					{
						foreach ($aAccount['aBlacklist'] as $sFNN=>$sReason)
						{
							$oCSVExceptionsReport->addRow(array	(
																	self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
																	self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> $sFNN,
																	self::$_aInterimExceptionsColumns['REASON']			=> $sReason
																));
							$iServicesFailed++;
						}
						
						foreach ($aAccount['aGreylist'] as $sFNN=>$sReason)
						{
							$oCSVExceptionsReport->addRow(array	(
																	self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
																	self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> $sFNN,
																	self::$_aInterimExceptionsColumns['REASON']			=> "Account {$iAccountId} Rejected -- check other Services for details"
																));
							$iServicesFailed++;
						}
						
						foreach ($aAccount['aWhitelist'] as $sFNN=>$bWhitelisted)
						{
							$oCSVExceptionsReport->addRow(array	(
																	self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
																	self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> $sFNN,
																	self::$_aInterimExceptionsColumns['REASON']			=> "Account {$iAccountId} Rejected -- check other Services for details"
																));
							$iServicesFailed++;
						}
						
						$iAccountsFailed++;
					}
					elseif (!count($aAccount['aWhitelist']))
					{
						// Ignore this Account
						$iAccountsIgnored++;
						$iServicesIgnored	+= count($aAccount['aGreylist']);
					}
					else
					{
						// Action this Account!
						if (!$oFlexDataAccess->TransactionStart())
						{
							throw new Exception("There was an internal error in Flex.  Please notify YBS of this issue with the following message: 'Unable to start the inner Transaction' ({$oFlexDataAccess->refMysqliConnection->error})");
						}
						try
						{
							// Add the Adjustments for each Service
							foreach($aAccount['aWhitelist'] as $sFNN=>$bWhitelisted)
							{
								self::applyInterimInvoiceAdjustments($aServices["{$iAccountId}.{$sFNN}"]);
							}
							
							// Generate an Interim Invoice for this Account
							$oAccount	= new Account(array('Id'=>$iAccountId), false, true);
							
							// Calculate Billing Date
							$iInvoiceDatetime	= strtotime(date('Y-m-d', strtotime('+1 day')));
							
							// Generate the Invoice
							try
							{
								$oInvoiceRun	= new Invoice_Run();
								$oInvoiceRun->generateSingle($oAccount->CustomerGroup, INVOICE_RUN_TYPE_INTERIM, $iInvoiceDatetime, $oAccount->Id);
							}
							catch (Exception $eException)
							{
								// Perform a Revoke on the Temporary Invoice Run
								if ($oInvoiceRun->Id)
								{
									$oInvoiceRun->revoke();
								}
								throw $eException;
							}
							
							// Commit this mini-transaction
							$oFlexDataAccess->TransactionCommit();
							
							// Add to Processing Report (all Services that had Debits/Credits added)
							$bAccountHasAdjustments	= false;
							foreach($aAccount['aWhitelist'] as $sFNN=>$bWhitelisted)
							{
								$sServiceKey	= "{$iAccountId}.{$sFNN}";
								if ($aServices[$sServiceKey]['aAdjustments']['plan_charge'])
								{
									$oCSVProcessingReport->addRow(array	(
																			self::$_aInterimProcessingColumns['ACCOUNT_ID']							=> $iAccountId,
																			self::$_aInterimProcessingColumns['SERVICE_FNN']						=> $sFNN,
																			self::$_aInterimProcessingColumns['PLAN_CHARGE']						=> number_format($aServices[$sServiceKey]['aAdjustments']['plan_charge'], 2, '.', ''),
																			self::$_aInterimProcessingColumns['PLAN_CHARGE_DESCRIPTION']			=> $aServices[$sServiceKey]['aAdjustments']['plan_charge_description'],
																			self::$_aInterimProcessingColumns['INTERIM_PLAN_CREDIT']				=> number_format($aServices[$sServiceKey]['aAdjustments']['interim_plan_credit'], 2, '.', ''),
																			self::$_aInterimProcessingColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']	=> $aServices[$sServiceKey]['aAdjustments']['interim_plan_credit_description'],
																			self::$_aInterimProcessingColumns['PRODUCTION_PLAN_CREDIT']				=> number_format($aServices[$sServiceKey]['aAdjustments']['production_plan_credit'], 2, '.', ''),
																			self::$_aInterimProcessingColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']	=> $aServices[$sServiceKey]['aAdjustments']['production_plan_credit_description'],
																		));
									
									$fTotalPlanCharge			+= $aServices[$sServiceKey]['aAdjustments']['plan_charge'];
									$fTotalInterimPlanCredit	+= $aServices[$sServiceKey]['aAdjustments']['interim_plan_credit'];
									$fTotalProductionPlanCredit	+= $aServices[$sServiceKey]['aAdjustments']['production_plan_credit'];
									$iServicesAdjustmentsAdded++;
									
									$bAccountHasAdjustments	= true;
								}
							}
							$iAccountsAdjustmentsAdded	+= ($bAccountHasAdjustments) ? 1 : 0; 
							
							$iAccountsInvoiced++;
							$iServicesInvoiced	+= count($aAccount['aWhitelist']);
						}
						catch (Exception $eException)
						{
							$oFlexDataAccess->TransactionRollback();
							$oCSVExceptionsReport->addRow(array	(
																	self::$_aInterimExceptionsColumns['ACCOUNT_ID']		=> $iAccountId,
																	self::$_aInterimExceptionsColumns['SERVICE_FNN']	=> '[ ALL ]',
																	self::$_aInterimExceptionsColumns['REASON']			=> "Error while generating the Interim Invoice: ".$eException->getMessage()
																));
							$iAccountsFailed++;
							$iServicesFailed	+= count($aAccount['aWhitelist']);
						}
					}
				}
				
				// Generate & Send Processing/Exceptions Report
				$bHasExceptions	= (bool)$oCSVExceptionsReport->count();
				
				$oProcessingEmailNotification	= new Email_Notification(EMAIL_NOTIFICATION_FIRST_INTERIM_INVOICE_REPORT);
				$oProcessingEmailNotification->setSubject("First Interim Invoice Processing/Exceptions Report - ".date('Y-m-d H:i:s'));
				
				$sProcessingReportFileName	= "processing-report-".date("YmdHis").".csv";
				$oProcessingEmailNotification->addAttachment($oCSVProcessingReport->save(), $sProcessingReportFileName, 'text/csv');
				
				$sSubmittedEligibilityReportFileName	= "submitted-{$sSubmittedEligibilityReportFileName}";
				$oProcessingEmailNotification->addAttachment(file_get_contents($sSubmittedEligibilityReportPath), $sSubmittedEligibilityReportFileName, 'text/csv');
				
				if ($bHasExceptions)
				{
					$oCSVEligibilityReport				= self::buildInterimEligibilityReport($aServices);
					$sCurrentEligibilityReportFileName	= "current-interim-invoice-eligibility-report-".date("YmdHis").".csv";
					$oProcessingEmailNotification->addAttachment($oCSVEligibilityReport->save(), $sCurrentEligibilityReportFileName, 'text/csv');
					
					$sExceptionsReportFileName	= "exceptions-report-".date("YmdHis").".csv";
					$oProcessingEmailNotification->addAttachment($oCSVExceptionsReport->save(), $sExceptionsReportFileName, 'text/csv');
					
					$sReportsSummary	.= "
			<li><strong>Processing Report</strong><em> ({$sProcessingReportFileName})</em> &mdash;&nbsp;Lists which Accounts/Services had Interim Invoice Adjustments added to them</li>
			<li><strong>Exceptions Report</strong><em> ({$sExceptionsReportFileName})</em> &mdash;&nbsp;Lists which Accounts/Services failed in processing and the reasons why</li>
			<li><strong>Submitted Interim Eligibility Report</strong><em> ({$sSubmittedEligibilityReportFileName})</em> &mdash;&nbsp;The Report you submitted to initiate this process</li>
			<li><strong>Current Interim Eligibility Report</strong><em> ({$sCurrentEligibilityReportFileName})</em> &mdash;&nbsp;Current version of the Interim Eligibility Report</li>
";
				}
				else
				{
					$sReportsSummary	.= "
			<li><strong>Processing Report</strong><em> ({$sProcessingReportFileName})</em> &mdash;&nbsp;Lists which Accounts/Services had Interim Invoice Adjustments added to them</li>
			<li><strong>Submitted Interim Eligibility Report</strong><em> ({$sSubmittedEligibilityReportFileName})</em> &mdash;&nbsp;The Report you submitted to initiate this process</li>
";
				}
				
				$sEmailBody	= "
<div style='font-family: Calibri,Arial,sans-serif;'>
	<h1 style='font-size: 1.5em;'>First Interim Invoice Processing Report</h1>
	
	<p>
		Please find attached the following Reports:
		<ul>
			{$sReportsSummary}
		</ul>
	</p>
	
	<table style='font-family: Calibri,Arial,sans-serif; border: 1px solid #111; border-collapse: collapse;'>
		<tbody>
			<tr>
				<td style='vertical-align: top; padding: 1em;'>
					<h2 style='font-size: 1.2em;'>Invoice Summary</h2>
					<table style='font-family: Calibri,Arial,sans-serif; margin-left: 0.5em; font-family: inherit;'>
						<tbody>
							<tr>
								<th style='text-align: left;' >Accounts Invoiced&nbsp;:&nbsp;</th>
								<td>{$iAccountsInvoiced}</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Services Invoiced&nbsp;:&nbsp;</th>
								<td>{$iServicesInvoiced}</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Accounts Ignored&nbsp;:&nbsp;</th>
								<td>{$iAccountsIgnored}</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Services Ignored&nbsp;:&nbsp;</th>
								<td>{$iServicesIgnored}</td>
							</tr>
						</tbody>
					</table>
				</td>
				<td rowspan='2' style='vertical-align: top; border-left: 1px solid #111; padding: 1em;'>
					<h2 style='font-size: 1.2em;'>Adjustments Summary</h2>
					<table style='font-family: Calibri,Arial,sans-serif; margin-left: 0.5em; font-family: inherit;'>
						<tbody>
							<tr>
								<th style='text-align: left;' >Accounts with Adjustments&nbsp;:&nbsp;</th>
								<td>{$iAccountsAdjustmentsAdded}</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Services with Adjustments&nbsp;:&nbsp;</th>
								<td>{$iServicesAdjustmentsAdded}</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Total Plan Charge Value&nbsp;:&nbsp;</th>
								<td>\$".number_format($fTotalPlanCharge, 2, '.', ',')."</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Total Interim Plan Credit Value&nbsp;:&nbsp;</th>
								<td>\$".number_format($fTotalInterimPlanCredit, 2, '.', ',')."</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Total Production Plan Credit Value&nbsp;:&nbsp;</th>
								<td>\$".number_format($fTotalProductionPlanCredit, 2, '.', ',')."</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td style='vertical-align: top; padding: 1em; border-top: 1px solid #111;'>
					<h2 style='font-size: 1.2em;'>Exceptions Summary (see Exceptions Report for details)</h2>
					<table style='font-family: Calibri,Arial,sans-serif; margin-left: 0.5em; font-family: inherit;'>
						<tbody>
							<tr>
								<th style='text-align: left;' >Accounts Failed&nbsp;:&nbsp;</th>
								<td>{$iAccountsFailed}</td>
							</tr>
							<tr>
								<th style='text-align: left;' >Services Failed&nbsp;:&nbsp;</th>
								<td>{$iServicesFailed}</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
	</table>
	
	<p>
		Regards<br />
		<strong>Flexor</strong>
	</p>
</div>
		";
				
				$oProcessingEmailNotification->setBodyHTML($sEmailBody);
				
				$oProcessingEmailNotification->send();
				
				//throw new Exception("TEST MODE --- But everything seems to have worked!");
				
				// Everything looks ok -- Commit
				$oFlexDataAccess->TransactionCommit();
			}
			catch (Exception $eException)
			{
				// Rollback the Transaction and pass the Exception through
				$oFlexDataAccess->TransactionRollback();
				throw $eException;
			}
			
			// Return JSON response
			$oResponse->Success				= true;
			$oResponse->iAccountsInvoiced	= $iAccountsInvoiced;
			$oResponse->iServicesInvoiced	= $iServicesInvoiced;
			$oResponse->iAccountsFailed		= $iAccountsFailed;
			$oResponse->iServicesFailed		= $iServicesFailed;
		}
		catch (Exception $eException)
		{
			$oResponse->Success	= false;
			$oResponse->Message	= $eException->getMessage();
			
			file_put_contents('/tmp/action-interim-invoices-report-'.date("YmdHis").'.csv', $eException->getMessage());
		}
		
		echo JSON_Services::instance()->encode($oResponse);
		exit;
	}
	
	public static function getInterimEligibileServices()
	{
		$qQuery	= new Query();
		
		$sSQL	= "
SELECT		a.Id																											AS account_id,
			a.BusinessName																									AS account_name,
			dm.name																											AS delivery_method,
			CASE
				WHEN CAST(DATE_FORMAT(CURDATE(), '%d') AS UNSIGNED) < pt.invoice_day
					THEN CAST(DATE_FORMAT(CURDATE(), CONCAT('%Y-%m-', LPAD(pt.invoice_day, 2, '0'))) AS DATE)
				ELSE
					ADDDATE(CAST(DATE_FORMAT(CURDATE(), CONCAT('%Y-%m-', LPAD(pt.invoice_day, 2, '0'))) AS DATE), INTERVAL 1 MONTH)
			END																												AS next_invoice_date,
			service_status_count.services_active																			AS services_active,
			service_status_count.services_pending																			AS services_pending,
			s.Id																											AS service_id,
			s.FNN																											AS fnn,
			srp.RatePlan																									AS rate_plan_id,
			rp.Name																											AS rate_plan_name,
			rp.MinMonthly																									AS plan_charge,
			IF(s.EarliestCDR IS NOT NULL, 1, 0)																				AS has_tolled,
			rp.cdr_required																									AS cdr_required,
			CASE
				WHEN rp.cdr_required THEN IF(CAST(s.EarliestCDR AS DATE) <= CURDATE(), s.EarliestCDR, NULL /* LAST INVOICE DATE */)
				ELSE IF(s.CreatedOn > srp.StartDatetime, s.CreatedOn, srp.StartDatetime)
			END																												AS invoice_from_date

FROM		Account a
			JOIN account_status a_s ON (a.Archived = a_s.id)
			JOIN delivery_method dm ON (dm.id = a.BillingMethod)
			JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
			JOIN payment_terms pt ON (cg.Id = pt.customer_group_id)
			JOIN
			(
				SELECT		Service.Account																						AS account_id,
							COUNT(IF(service_status.const_name = 'SERVICE_ACTIVE', Service.Id, NULL))							AS services_active,
							COUNT(IF(service_status.const_name = 'SERVICE_PENDING', Service.Id, NULL))							AS services_pending,
							COUNT(IF(RatePlan.Shared = 1 AND service_status.const_name = 'SERVICE_ACTIVE', Service.Id, NULL))	AS shared_services_active
				FROM		Service
							JOIN service_status ON (service_status.id = Service.Status)
							JOIN ServiceRatePlan ON (Service.Id = ServiceRatePlan.Service AND NOW() BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime)
							JOIN RatePlan ON (ServiceRatePlan.RatePlan = RatePlan.Id)
				WHERE		ServiceRatePlan.Id =	(
														SELECT		Id
														FROM		ServiceRatePlan
														WHERE		NOW() BETWEEN StartDatetime AND EndDatetime
																	AND Service = Service.Id
														ORDER BY	CreatedOn DESC
														LIMIT		1
													)
				GROUP BY	Service.Account
				HAVING		shared_services_active = 0
							AND services_active > 0
			) service_status_count ON (a.Id = service_status_count.account_id)
			JOIN Service s ON (a.Id = s.Account)
			JOIN service_status ss ON (s.Status = ss.id)
			JOIN ServiceRatePlan srp ON (srp.Service = s.Id AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime)
			JOIN RatePlan rp ON (srp.RatePlan = rp.Id)
			LEFT JOIN
			(
				Invoice i_last
				JOIN InvoiceRun ir_last ON (i_last.invoice_run_id = ir_last.Id)
				JOIN invoice_run_type irt_last ON (irt_last.id = ir_last.invoice_run_type_id AND irt_last.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES'))
			) ON (a.Id = i_last.Account)

WHERE		(
				ir_last.Id IS NULL
				OR
				(
					ir_last.Id =	(
										SELECT		InvoiceRun.Id
										FROM		InvoiceRun
													JOIN invoice_run_type ON (invoice_run_type.id = InvoiceRun.invoice_run_type_id)
													JOIN Invoice ON (InvoiceRun.Id = Invoice.invoice_run_id)
										WHERE		Invoice.Account = a.Id
													AND invoice_run_type.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES')
										ORDER BY	Invoice.CreatedOn DESC
										LIMIT		1
									)
					AND irt_last.const_name NOT IN ('INVOICE_RUN_TYPE_INTERIM', 'INVOICE_RUN_TYPE_FINAL')
					AND	(
							SELECT		COUNT(c.Id)
							FROM		Charge c
										JOIN InvoiceRun ir_charge ON (ir_charge.Id = c.invoice_run_id)
										JOIN invoice_run_type irt_charge ON (ir_charge.invoice_run_type_id = irt_charge.id AND irt_charge.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES'))
							WHERE		c.Account = a.Id
										AND c.ChargeType IN ('PCAD', 'PCAR')
							LIMIT		1
						) = 0
					AND	(
							SELECT		COUNT(stt.Id)
							FROM		ServiceTypeTotal stt
										JOIN InvoiceRun ir_cdr ON (ir_cdr.Id = stt.invoice_run_id)
										JOIN invoice_run_type irt_cdr ON (ir_cdr.invoice_run_type_id = irt_cdr.id AND irt_cdr.const_name NOT IN ('INVOICE_RUN_TYPE_INTERNAL_SAMPLES', 'INVOICE_RUN_TYPE_SAMPLES'))
										JOIN RecordType rt ON (stt.RecordType = rt.Id)
							WHERE		stt.Account = a.Id
										AND rt.Code IN ('S&E')
										AND stt.Records > 0
							LIMIT		1
						) = 0
					AND (SELECT COUNT(Invoice.Id) FROM Invoice WHERE Invoice.Account = a.Id AND Status NOT IN (100)) <= 3
				)
			)
			AND ss.const_name IN ('SERVICE_ACTIVE')
			AND a_s.const_name = 'ACCOUNT_STATUS_ACTIVE'
			AND srp.Id =	(
								SELECT		Id
								FROM		ServiceRatePlan
								WHERE		Service = s.Id
											AND NOW() BETWEEN StartDatetime AND EndDatetime
								ORDER BY	CreatedOn DESC
								LIMIT		1
							)

HAVING		next_invoice_date >= ADDDATE(CURDATE(), INTERVAL 7 DAY)
			AND services_active > 0
			

ORDER BY	account_id,
			service_id
";
		$rResult	= $qQuery->Execute($sSQL);
		if ($rResult === false)
		{
			throw new Exception($qQuery->Error());
		}
		$aServices	= array();
		$aAccounts	= array();
		while ($aService = $rResult->fetch_assoc())
		{
			$aService['aAdjustments']	= self::calculateInterimInvoiceAdjustments($aService);
			
			$aAccounts[$aService['account_id']]	= (array_key_exists($aService['account_id'], $aAccounts)) ? $aAccounts[$aService['account_id']] : array('aServices'=>array(), 'bAdjustmentEligible'=>false);
			
			// Add to Account's list of Services
			$aAccounts[$aService['account_id']]['aServices'][$aService['fnn']]	= $aService;
			
			// If this Service will receive Interim Adjustments, then this Account is eligible for 1st Interim Invoicing
			if (!$aAccounts[$aService['account_id']]['bAdjustmentEligible'])
			{
				if	($aService['aAdjustments']['plan_charge'])
				{
					$aAccounts[$aService['account_id']]['bAdjustmentEligible']	= true;
				}
			} 
		}
		
		// Check Account-level Eligibility
		foreach ($aAccounts as $iAccount=>$aAccount)
		{
			// Add Services if at least one of the Account's Services will receive an Interim Adjustment
			if ($aAccount['bAdjustmentEligible'])
			{
				foreach ($aAccount['aServices'] as $sFNN=>$aService)
				{
					$aServices["{$aService['account_id']}.{$aService['fnn']}"]	= $aService;
				}
			}
		}
		
		return $aServices;
	}
	
	public static function calculateInterimInvoiceAdjustments($aService)
	{
		$aAdjustments	=	array
							(
								'daily_rate'							=> 0.0,
								'plan_charge'							=> 0.0,
								'plan_charge_days'						=> 0,
								'plan_charge_description'				=> '',
								'interim_plan_credit'					=> 0.0,
								'interim_plan_credit_days'				=> 0,
								'interim_plan_credit_description'		=> '',
								'production_plan_credit'				=> 0.0,
								'production_plan_credit_days'			=> 0,
								'production_plan_credit_description'	=> ''
							);
		
		$iTime	= time();
		$sDate	= date('Y-m-d', $iTime);
		$iDate	= strtotime($sDate);
		
		$iNextInvoiceDate	= strtotime($aService['next_invoice_date']);
		$iLastInvoiceDate	= strtotime("-1 month", $iNextInvoiceDate);
		
		$iBillingPeriodEndDate	= $iNextInvoiceDate - Flex_Date::SECONDS_IN_DAY;
		
		$fPlanCharge			= (float)$aService['plan_charge'];
		
		if ($fPlanCharge && $aService['invoice_from_date'])
		{
			$iServiceInvoiceFromDate	= ($aService['invoice_from_date']) ? strtotime($aService['invoice_from_date']) : $iLastInvoiceDate;
			
			// Calculate Daily Rate
			$iDaysInBillingPeriod	= (int)date('t', $iLastInvoiceDate);
			
			$iBillingPeriodDays	= floor(Flex_Date::periodLength($iLastInvoiceDate, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aAdjustments['billing_period_start']	= date("Y-m-d H:i:s", $iLastInvoiceDate);
			$aAdjustments['billing_period_end']		= date("Y-m-d H:i:s", $iBillingPeriodEndDate);
			$aAdjustments['billing_period_days']	= $iBillingPeriodDays;
			
			// Tidy Plan Charge
			$iProratePeriodDays	= floor(Flex_Date::periodLength($iServiceInvoiceFromDate, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aAdjustments['daily_rate']			= $fPlanCharge / $iBillingPeriodDays;
			$aAdjustments['plan_charge_days']	= $iProratePeriodDays;
			
			$fltProratedAmount				= ($fPlanCharge / $iBillingPeriodDays) * $iProratePeriodDays;
			$aAdjustments['plan_charge']	= round($fltProratedAmount, 2);
			
			$aAdjustments['plan_charge_description']	= Invoice::buildPlanChargeDescription($aService['rate_plan_name'], Charge_Type::getByCode('PCAR')->Description, $iServiceInvoiceFromDate, $iBillingPeriodEndDate);
			
			// Interim Invoice Credit
			$iProratePeriodDays	= floor(Flex_Date::periodLength($iServiceInvoiceFromDate, $iDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			//$iBillingPeriodDays	= floor(Flex_Date::periodLength($iLastInvoiceDate, $iDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aAdjustments['interim_plan_credit_days']	= $iProratePeriodDays;
			
			$fltProratedAmount						= ($fPlanCharge / $iBillingPeriodDays) * $iProratePeriodDays;
			$aAdjustments['interim_plan_credit']	= round($fltProratedAmount, 2);
			
			$aAdjustments['interim_plan_credit_description']	= Invoice::buildPlanChargeDescription($aService['rate_plan_name'], Charge_Type::getByCode('PCAR')->Description, $iServiceInvoiceFromDate, $iDate);
			
			// Production Invoice Credit
			$iProratePeriodDays	= floor(Flex_Date::periodLength($iDate + Flex_Date::SECONDS_IN_DAY, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			//$iBillingPeriodDays	= floor(Flex_Date::periodLength($iLastInvoiceDate, $iBillingPeriodEndDate, 'd') / Flex_Date::SECONDS_IN_DAY);
			
			$aAdjustments['production_plan_credit_days']	= $iProratePeriodDays;
			
			$fltProratedAmount						= ($fPlanCharge / $iBillingPeriodDays) * $iProratePeriodDays;
			$aAdjustments['production_plan_credit']	= round($fltProratedAmount, 2);
			
			$aAdjustments['production_plan_credit_description']	= Invoice::buildPlanChargeDescription($aService['rate_plan_name'], Charge_Type::getByCode('PCAR')->Description, $iDate + Flex_Date::SECONDS_IN_DAY, $iBillingPeriodEndDate);
		}
		
		return $aAdjustments;
	}
	
	public static function buildInterimEligibilityReport($aServices)
	{
		// Prepare the CSV File
		$oCSVFile	= new File_CSV();
		$oCSVFile->setColumns(array_values(self::$_aInterimEligibilityColumns));
		
		// Get data & insert into the CSV Report
		foreach ($aServices as &$aService)
		{
			$aOutput	= array();
			
			$aOutput[self::$_aInterimEligibilityColumns['ACCOUNT_ID']]							= $aService['account_id'];
			$aOutput[self::$_aInterimEligibilityColumns['ACCOUNT_NAME']]						= $aService['account_name'];
			$aOutput[self::$_aInterimEligibilityColumns['DELIVERY_METHOD']]						= $aService['delivery_method'];
			$aOutput[self::$_aInterimEligibilityColumns['SERVICE_FNN']]							= $aService['fnn'];
			$aOutput[self::$_aInterimEligibilityColumns['ACTIVE_SERVICES']]						= $aService['services_active'];
			$aOutput[self::$_aInterimEligibilityColumns['ACTIVE_PENDING_SERVICES']]				= ((int)$aService['services_pending'] + (int)$aService['services_active']);
			$aOutput[self::$_aInterimEligibilityColumns['HAS_TOLLED']]							= ($aService['has_tolled']) ? 'Yes' : 'No';
			$aOutput[self::$_aInterimEligibilityColumns['CURRENT_PLAN']]						= $aService['rate_plan_name'];
			$aOutput[self::$_aInterimEligibilityColumns['REQUIRES_CDR']]						= ($aService['cdr_required']) ? 'Yes' : 'No';
			$aOutput[self::$_aInterimEligibilityColumns['MONTHLY_PLAN_FEE']]					= number_format((float)$aService['plan_charge'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['DAILY_RATE']]							= (float)$aService['aAdjustments']['daily_rate'];
			$aOutput[self::$_aInterimEligibilityColumns['PLAN_CHARGE']]							= number_format((float)$aService['aAdjustments']['plan_charge'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DAYS']]					= $aService['aAdjustments']['plan_charge_days'];
			$aOutput[self::$_aInterimEligibilityColumns['PLAN_CHARGE_DESCRIPTION']]				= $aService['aAdjustments']['plan_charge_description'];
			$aOutput[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT']]					= number_format((float)$aService['aAdjustments']['interim_plan_credit'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DAYS']]			= $aService['aAdjustments']['interim_plan_credit_days'];
			$aOutput[self::$_aInterimEligibilityColumns['INTERIM_PLAN_CREDIT_DESCRIPTION']]		= $aService['aAdjustments']['interim_plan_credit_description'];
			$aOutput[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT']]				= number_format((float)$aService['aAdjustments']['production_plan_credit'], 2, '.', '');
			$aOutput[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DAYS']]			= $aService['aAdjustments']['production_plan_credit_days'];
			$aOutput[self::$_aInterimEligibilityColumns['PRODUCTION_PLAN_CREDIT_DESCRIPTION']]	= $aService['aAdjustments']['production_plan_credit_description'];
			//$aOutput[self::$_aInterimEligibilityColumns['DEBUG_BILLING_PERIOD_START']]			= $aService['aAdjustments']['billing_period_start'];
			//$aOutput[self::$_aInterimEligibilityColumns['DEBUG_BILLING_PERIOD_END']]			= $aService['aAdjustments']['billing_period_end'];
			//$aOutput[self::$_aInterimEligibilityColumns['DEBUG_BILLING_PERIOD_DAYS']]			= $aService['aAdjustments']['billing_period_days'];
			
			// Add the CSV
			$oCSVFile->addRow($aOutput);
		}
		unset($aService);
		
		return $oCSVFile;
	}
	
	public static function applyInterimInvoiceAdjustments($aService)
	{
		// Skip Adjustments with no value
		if (!(float)$aService['aAdjustments']['plan_charge'])
		{
			return;
		}
		
		static	$aChargeTypes;
		if (!isset($aChargeTypes))
		{
			$aChargeTypes			= array();
			$aChargeTypes['PCAR']	= Charge_Type::getByCode('PCAR');
			$aChargeTypes['PCAD']	= Charge_Type::getByCode('PCAD');
		}
		
		try
		{
			$oAccount	= Account::getForId($aService['account_id']);
			
			// Add new Plan Charge
			$oPlanCharge	= new Charge();
			
			$oPlanCharge->AccountGroup		= $oAccount->AccountGroup;
			$oPlanCharge->Account			= $oAccount->Id;
			$oPlanCharge->Service			= $aService['service_id'];
			$oPlanCharge->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oPlanCharge->CreatedOn			= date("Y-m-d");
			$oPlanCharge->ApprovedBy		= Employee::SYSTEM_EMPLOYEE_ID;
			$oPlanCharge->ChargeType		= 'PCAR';
			$oPlanCharge->charge_type_id	= $aChargeTypes['PCAR']->Id;
			$oPlanCharge->Description		= $aService['aAdjustments']['plan_charge_description'];
			$oPlanCharge->ChargedOn			= date("Y-m-d");
			$oPlanCharge->Nature			= 'DR';
			$oPlanCharge->Amount			= $aService['aAdjustments']['plan_charge'];
			$oPlanCharge->Status			= CHARGE_APPROVED;
			$oPlanCharge->Notes				= '1st Interim Invoice Plan Debit';
			$oPlanCharge->global_tax_exempt	= false;
			
			$oPlanCharge->save();
			
			// Add Interim Plan Credit
			$oInterimPlanCredit	= new Charge();
			
			$oInterimPlanCredit->AccountGroup		= $oAccount->AccountGroup;
			$oInterimPlanCredit->Account			= $oAccount->Id;
			$oInterimPlanCredit->Service			= $aService['service_id'];
			$oInterimPlanCredit->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oInterimPlanCredit->CreatedOn			= date("Y-m-d");
			$oInterimPlanCredit->ApprovedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oInterimPlanCredit->ChargeType			= 'PCAR';
			$oInterimPlanCredit->charge_type_id		= $aChargeTypes['PCAR']->Id;
			$oInterimPlanCredit->Description		= $aService['aAdjustments']['interim_plan_credit_description'];
			$oInterimPlanCredit->ChargedOn			= date("Y-m-d");
			$oInterimPlanCredit->Nature				= 'CR';
			$oInterimPlanCredit->Amount				= abs($aService['aAdjustments']['interim_plan_credit']);
			$oInterimPlanCredit->Status				= CHARGE_APPROVED;
			$oInterimPlanCredit->Notes				= '1st Interim Invoice Plan Credit';
			$oInterimPlanCredit->global_tax_exempt	= false;
			
			$oInterimPlanCredit->save();
			
			// Add Production Plan Credit
			$oProductionPlanCredit	= new Charge();
			
			$oProductionPlanCredit->AccountGroup		= $oAccount->AccountGroup;
			$oProductionPlanCredit->Account				= $oAccount->Id;
			$oProductionPlanCredit->Service				= $aService['service_id'];
			$oProductionPlanCredit->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oProductionPlanCredit->CreatedOn			= date("Y-m-d");
			$oProductionPlanCredit->ApprovedBy			= Employee::SYSTEM_EMPLOYEE_ID;
			$oProductionPlanCredit->ChargeType			= 'PCAR';
			$oProductionPlanCredit->charge_type_id		= $aChargeTypes['PCAR']->Id;
			$oProductionPlanCredit->Description			= $aService['aAdjustments']['production_plan_credit_description'];
			$oProductionPlanCredit->ChargedOn			= date("Y-m-d", strtotime("+1 day", time()));
			$oProductionPlanCredit->Nature				= 'CR';
			$oProductionPlanCredit->Amount				= abs($aService['aAdjustments']['production_plan_credit']);
			$oProductionPlanCredit->Status				= CHARGE_APPROVED;
			$oProductionPlanCredit->Notes				= '1st Production Invoice Plan Credit';
			$oProductionPlanCredit->global_tax_exempt	= false;
			
			$oProductionPlanCredit->save();
		}
		catch (Exception $eException)
		{
			throw new Exception("There was an error adding the Interim Adjustments: ".$eException->getMessage());
		}
	}
	
	private static function _compareInterimEligible($mLeftValue, $mRightValue, $sMessage, $bStrict=true)
	{
		// Round floats to 8 decimal places
		$mLeftValue		= (is_float($mLeftValue)) ? round($mLeftValue, 8) : $mLeftValue;
		$mRightValue	= (is_float($mRightValue)) ? round($mRightValue, 8) : $mRightValue;
		
		return self::_assertInterimEligible((($bStrict && $mLeftValue === $mRightValue) || $mLeftValue == $mRightValue), $sMessage);
	}
	
	private static function _assertInterimEligible($mExpression, $sMessage)
	{
		if (!(bool)$mExpression)
		{
			throw new Exception_Invoice_InterimEligibilityMismatch($sMessage);
		}
		return true;
	}
	
	public static function preg_match_string($sRegex, $sMatch)
	{
		$aMatches	= array();
		preg_match($sRegex, $sMatch, $aMatches);
		return (count($aMatches)) ? $aMatches[0] : null;
	}
}

class Exception_Invoice_InterimEligibilityMismatch extends Exception{};
?>
<?php

class Application_Handler_Invoice extends Application_Handler
{

	// View all the breakdown for a service on an invoice
	public function Service($subPath)
	{
		try
		{
			$intServiceTotal = count($subPath) ? intval(array_shift($subPath)) : 0;

			$intRecordType = 0;

			
			$db = Data_Source::get();
			
			$sqlServiceTotal = "
				SELECT i.Id AS InvoiceId, t.Account as AccountId, t.FNN as FNN, t.Service as ServiceId, a.BusinessName as BusinessName, a.TradingName as TradingName, s.ServiceType as ServiceType, t.invoice_run_id as InvoiceRunId, t.Id ServiceTotal
				  FROM Invoice i, ServiceTotal t, Service s, Account a 
				 WHERE t.Id = $intServiceTotal 
				   AND i.invoice_run_id = t.invoice_run_id 
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
	
			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($intAccountId, true);
			BreadCrumb()->InvoicesAndPayments($intAccountId);
			BreadCrumb()->ViewInvoice($intInvoiceId);
			BreadCrumb()->SetCurrentPage("Service: $fnn");
	
			$arrDetailsToRender = array();
	
			$arrDetailsToRender['Invoice'] = $serviceDetails;
	
	
	
			// Need to load up the Adjustments for the invoice
	
			$sqlAdjustments = "
				SELECT c.Id as ChargeId, c.ChargeType ChargeType, c.Description Description, s.Id as ServiceId, s.FNN as FNN, ChargedOn as Date, c.Amount Amount, c.Nature as Nature
				  FROM Service s, Charge c
				 WHERE c.Service = s.Id	
				   AND c.invoice_run_id = $intInvoiceRunId
			       AND c.Service = $intServiceId
			";
	
			$res = $db->query($sqlAdjustments);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load adjustments: $sqlAdjustments " . $res->getMessage());
			}
			$arrDetailsToRender['Adjustments'] = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
			
			
			
	
			// Need to load up the RecordTypes for filtering
	
			$sqlRecordTypes = " SELECT Id, Name FROM RecordType WHERE ServiceType = $intServiceType ";
	
			$res = $db->query($sqlRecordTypes);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load service types: " . $res->getMessage());
			}
			
			$arrDetailsToRender['RecordTypes'] = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
	
	
	

			// Need to load up the CDRs from the cdr_invoiced table for the current range of CDRs & record type
			
			$cdrDb = Data_Source::get(FLEX_DATABASE_CONNECTION_CDR);

			$offset = 0;
			$limit = 30;

			$arrDetailsToRender['filter'] = array(
				'offset' => array_key_exists('offset', $_REQUEST) ? intval($_REQUEST['offset']) : 0,
				'limit' => 30,
				'recordType' => (array_key_exists('recordType', $_REQUEST) && $_REQUEST['recordType']) ? intval($_REQUEST['recordType']) : NULL,
				'recordCount' => 0,
			);

			$alises = array('ChargeId', 'ChargeType', 'Description', 'FNN', 'Date', 'Amount', 'Nature');

			$sqlCdrs = "
				SELECT c.id as \"Id\", t.id as \"RecordTypeId\", t.name as \"RecordType\", c.description as \"Description\", t.description as \"DisplayType\", c.source as \"Source\", c.destination as \"Destination\", c.end_date_time - c.start_date_time as \"Duration\", c.start_date_time as \"StartDatetime\", c.units as \"Currency\", c.charge as \"Charge\"
				  FROM cdr_invoiced c, record_type t
				 WHERE invoice_run_id = $intInvoiceRunId
				   AND account = $intAccountId
				   AND c.record_type = t.id
			       AND c.Service = $intServiceId
			";

			$sqlCountCdrs = "SELECT COUNT(*) FROM cdr_invoiced c WHERE invoice_run_id = $intInvoiceRunId AND account = $intAccountId AND c.Service = $intServiceId";

			if ($arrDetailsToRender['filter']['recordType'])
			{
				$sqlCdrs .= " AND c.record_type = " . $arrDetailsToRender['filter']['recordType'] . " ";
				$sqlCountCdrs .= " AND c.record_type = " . $arrDetailsToRender['filter']['recordType'] . " ";
			}

			$sqlCdrs .= " ORDER BY c.id LIMIT " . $arrDetailsToRender['filter']['limit'] . " OFFSET " . $arrDetailsToRender['filter']['offset'] . " ";

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
			$cdrDb = Data_Source::get(FLEX_DATABASE_CONNECTION_CDR);
			
			$intServiceTotal = intval($subPath[0]);
			$intInvoiceRunId = intval($subPath[1]);
			$intCdrId = intval($subPath[2]);
			
			$sqlServiceTotal = "
				SELECT i.Id AS InvoiceId, t.Account as AccountId, t.FNN as FNN, t.Service as ServiceId, a.BusinessName as BusinessName, a.TradingName as TradingName, s.ServiceType as ServiceType, t.invoice_run_id as InvoiceRunId, t.Id ServiceTotal
				  FROM Invoice i, ServiceTotal t, Service s, Account a 
				 WHERE t.Id = $intServiceTotal 
				   AND i.invoice_run_id = t.invoice_run_id 
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

			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($intAccountId, true);
			BreadCrumb()->InvoicesAndPayments($intAccountId);
			BreadCrumb()->ViewInvoice($intInvoiceId);
			BreadCrumb()->ViewInvoiceService($intServiceTotal, $fnn);
			BreadCrumb()->SetCurrentPage("Record Id: " . $intCdrId);

			$sqlCdr = "
				SELECT t.name as \"RecordType\", c.description as \"Description\", c.source as \"Source\", c.destination as \"Destination\", c.end_date_time as \"EndDatetime\", c.start_date_time as \"StartDatetime\", c.units as \"Currency\", c.charge as \"Charge\",
					   c.file as \"FileId\", c.carrier as \"CarrierId\", c.carrier_ref as \"CarrierRef\", c.cost as \"Cost\", c.status as \"Status\", c.destination_code as \"DestinationCode\", c.rate as \"RateId\", c.normalised_on as \"NormalisedOn\", c.rated_on as \"RatedOn\", c.sequence_no as \"SequenceNo\", c.credit as \"Credit\"
				  FROM cdr_invoiced c, record_type t
				 WHERE invoice_run_id = $intInvoiceRunId
				   AND account = $intAccountId
				   AND c.record_type = t.id
			       AND c.Service = $intServiceId
			";

			$res = $cdrDb->query($sqlCdr);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load CDR details: " . $res->getMessage());
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
			$arrDetailsToRender['NormalisedOn'] 	= $arrCDR['NormalisedOn'];
			$arrDetailsToRender['RatedOn'] 			= $arrCDR['RatedOn'];
			$arrDetailsToRender['InvoiceRunId'] 	= $intInvoiceRunId;
			$arrDetailsToRender['SequenceNo'] 		= $arrCDR['SequenceNo'];
			$arrDetailsToRender['Credit'] 			= $arrCDR['Credit'];




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
}

?>

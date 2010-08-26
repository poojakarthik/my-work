<?php

class Application_Handler_Invoice extends Application_Handler
{
	// View all the breakdown for a service on an invoice
	public function Service($subPath)
	{
		$aDetailsToRender	= array();
		
		try
		{
			$iServiceTotal 	= count($subPath) ? intval(array_shift($subPath)) : 0;
			$db 			= Data_Source::get();
			$sServiceTotal	= "
				SELECT 	i.Id AS InvoiceId, t.Account as AccountId, t.FNN as FNN, t.Service as ServiceId,
						a.BusinessName as BusinessName, a.TradingName as TradingName, s.ServiceType as ServiceType,
						t.invoice_run_id as InvoiceRunId, t.Id ServiceTotal
				FROM 	Invoice i, ServiceTotal t, Service s, Account a, InvoiceRun r
				WHERE 	t.Id = $iServiceTotal
				AND 	i.invoice_run_id = t.invoice_run_id
				AND 	i.invoice_run_id = r.Id
				AND 	i.Account = t.Account
				AND 	s.Id = t.Service
				AND 	a.Id = t.Account
			";
	
			$res = $db->query($sServiceTotal);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load service details: " . $res->getMessage());
			}
	
			$serviceDetails = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
	
			if (!$serviceDetails)
			{
				throw new Exception("Failed to find service details for service total $iServiceTotal.");
			}
	
			$iAccountId 	= $serviceDetails['AccountId'];
			$iInvoiceId 	= $serviceDetails['InvoiceId'];
			$iInvoiceRunId 	= $serviceDetails['InvoiceRunId'];
			$iServiceId 	= $serviceDetails['ServiceId'];
			$iServiceType 	= $serviceDetails['ServiceType'];
			$fnn 			= $serviceDetails['FNN'];
			$oService		= Service::getForId($iServiceId);
			
			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($iAccountId, true);
			BreadCrumb()->InvoicesAndPayments($iAccountId);
			BreadCrumb()->ViewInvoice($iInvoiceId, $iAccountId);
			BreadCrumb()->SetCurrentPage("Service: $fnn");
			AppTemplateAccount::BuildContextMenu($iAccountId);
			
			$aDetailsToRender['Invoice'] 		= $serviceDetails;
			$aDetailsToRender['ServiceType']	= $iServiceType;
	
			// Need to load up the Charges for the invoice
			$aDetailsToRender['Charges']	= $oService->getCharges($iInvoiceRunId);
			
			// Need to load up the RecordTypes for filtering
			$aDetailsToRender['RecordTypes']	= Record_Type::getForServiceType($iServiceType);
			
			// Filter information
			$aDetailsToRender['filter']	= array(
				'offset' 		=> array_key_exists('offset', $_REQUEST) ? intval($_REQUEST['offset']) : 0,
				'limit'			=> 30,
				'recordType'	=> (array_key_exists('recordType', $_REQUEST) && $_REQUEST['recordType']) ? intval($_REQUEST['recordType']) : NULL,
				'recordCount'	=> 0,
			);
			
			// Get the cdr information
			$aCDRsResult	= 	$oService->getCDRs(
									$iInvoiceRunId,
									$aDetailsToRender['filter']['recordType'],
									$aDetailsToRender['filter']['limit'],
									$aDetailsToRender['filter']['offset']
								);
			
			$aDetailsToRender['CDRs']					= $aCDRsResult['CDRs'];
			$aDetailsToRender['filter']['recordCount']	= $aCDRsResult['recordCount'];
			
			$this->LoadPage('invoice_service', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] = "An error occured when trying to load the Invoice Service details page";
			$aDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
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
			
			$iServiceTotal 	= intval($subPath[0]);
			$iInvoiceRunId 	= intval($subPath[1]);
			$iCdrId 		= intval($subPath[2]);
			$sServiceTotal 	= "
				SELECT	i.Id AS InvoiceId, t.Account as AccountId, t.FNN as FNN, t.Service as ServiceId, a.BusinessName as BusinessName,
						a.TradingName as TradingName, s.ServiceType as ServiceType, t.invoice_run_id as InvoiceRunId, t.Id ServiceTotal
				FROM	Invoice i, ServiceTotal t, Service s, Account a, InvoiceRun r
				WHERE	t.Id = $iServiceTotal
				AND 	i.invoice_run_id = t.invoice_run_id
				AND 	i.invoice_run_id = r.Id
				AND 	i.Account = t.Account
				AND 	s.Id = t.Service
				AND 	a.Id = t.Account
			";
	
			$res	= $db->query($sServiceTotal);
			
			if (PEAR::isError($res))
			{
				throw new Exception("Failed to load service details:$sServiceTotal; " . $res->getMessage());
			}
	
			$serviceDetails	= $res->fetchRow(MDB2_FETCHMODE_ASSOC);
	
			if (!$serviceDetails)
			{
				throw new Exception("Failed to find service details for service total $iServiceTotal.");
			}
	
			$iAccountId 	= $serviceDetails['AccountId'];
			$iInvoiceId 	= $serviceDetails['InvoiceId'];
			$iInvoiceRunId 	= $serviceDetails['InvoiceRunId'];
			$iServiceId 	= $serviceDetails['ServiceId'];
			$iServiceType	= $serviceDetails['ServiceType'];
			$fnn 			= $serviceDetails['FNN'];
			
			BreadCrumb()->EmployeeConsole();
			BreadCrumb()->AccountOverview($iAccountId, true);
			BreadCrumb()->InvoicesAndPayments($iAccountId);
			BreadCrumb()->ViewInvoice($iInvoiceId, $iAccountId);
			BreadCrumb()->ViewInvoiceService($iServiceTotal, $fnn);
			BreadCrumb()->SetCurrentPage("Record Id: " . $iCdrId);
			AppTemplateAccount::BuildContextMenu($iAccountId);
			
			$aCDR	= CDR::getCDRDetails($iCdrId, $iInvoiceRunId);
			$status	= $GLOBALS['*arrConstant']['CDR'][$aCDR['Status']]['Description'];

			$aDetailsToRender	= array();
			
			$aDetailsToRender['FNN'] 				= $fnn;
			$aDetailsToRender['Id'] 				= $iCdrId;
			$aDetailsToRender['InvoiceId'] 			= $iInvoiceId;
			$aDetailsToRender['InvoiceRunId'] 		= $iInvoiceRunId;
			$aDetailsToRender['Status'] 			= $status;
			
			$aDetailsToRender['FileName'] 			= $aCDR['FileName'];
			$aDetailsToRender['Carrier'] 			= $aCDR['CarrierName'];
			$aDetailsToRender['CarrierRef'] 		= $aCDR['CarrierRef'];
			$aDetailsToRender['Source'] 			= $aCDR['Source'];
			$aDetailsToRender['Destination'] 		= $aCDR['Destination'];
			$aDetailsToRender['StartDatetime'] 		= $aCDR['StartDatetime'];
			$aDetailsToRender['EndDatetime'] 		= $aCDR['EndDatetime'];
			$aDetailsToRender['Cost'] 				= $aCDR['Cost'];
			$aDetailsToRender['Description'] 		= $aCDR['Description'];
			$aDetailsToRender['DestinationCode'] 	= $aCDR['DestinationCodeDescription'];
			$aDetailsToRender['RecordType'] 		= $aCDR['RecordType'];
			$aDetailsToRender['Charge'] 			= $aCDR['Charge'];
			$aDetailsToRender['Rate'] 				= $aCDR['RateName'];
			$aDetailsToRender['RateId'] 			= $aCDR['RateId'];
			$aDetailsToRender['NormalisedOn'] 		= $aCDR['NormalisedOn'];
			$aDetailsToRender['RatedOn'] 			= $aCDR['RatedOn'];
			$aDetailsToRender['SequenceNo'] 		= $aCDR['SequenceNo'];
			$aDetailsToRender['Credit'] 			= $aCDR['Credit'];
			$aDetailsToRender['RawCDR'] 			= $aCDR['RawCDR'];
			$aDetailsToRender['Units'] 				= $aCDR['Units'];
			$aDetailsToRender['DisplayType'] 		= $aCDR['DisplayType'];
			
			$this->LoadPage('Invoice_CDR', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] = "An error occured when trying to load the invoiced CDR details page";
			$aDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function PDF($subPath)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR_VIEW, PERMISSION_OPERATOR_EXTERNAL)))
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
		// Prepare the CSV File
		$oCSVFile	= Invoice_Interim::generateEligibilityReport();
		
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
			
			$sSubmittedEligibilityReportPath	= dirname($_FILES['Invoice_Interim_EligibilityUpload_File']['tmp_name']).'/'.$_FILES['Invoice_Interim_EligibilityUpload_File']['name'];
			move_uploaded_file($_FILES['Invoice_Interim_EligibilityUpload_File']['tmp_name'], $sSubmittedEligibilityReportPath);
			$aChanges	= Invoice_Interim::processEligibilityReport($sSubmittedEligibilityReportPath);
			
			// Return JSON response
			$oResponse->Success				= true;
			$oResponse->iAccountsInvoiced	= $aChanges['iAccountsInvoiced'];
			$oResponse->iServicesInvoiced	= $aChanges['iServicesInvoiced'];
			$oResponse->iAccountsFailed		= $aChanges['iAccountsFailed'];
			$oResponse->iServicesFailed		= $aChanges['iServicesFailed'];
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
}

?>
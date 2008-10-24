<?php

class Application_Handler_Contract extends Application_Handler
{
	const	RECORD_DISPLAY_LIMIT	= 24;
	
	// View all Breached Contracts which are pending approval
	public function ManageBreached($subPath)
	{
		// Build List of Breached Contracts and their recommended actions
		try
		{
			// Check for any passed variables
			$intOffset	= (int)$_REQUEST['offset'];
			
			// Limit
			$strLimit	= self::RECORD_DISPLAY_LIMIT . (($intOffset) ? " OFFSET {$intOffset}" : '');
			
			// Sorting
			$arrOrderBy	= Array();
			if (is_array($_REQUEST['sort']))
			{
				foreach ($_REQUEST['sort'] as $strColumn=>$strDirection)
				{
					switch ($strDirection)
					{
						case 'a':
							$arrOrderBy[]	= "{$strColumn} ASC";
							break;
							
						case 'd':
							$arrOrderBy[]	= "{$strColumn} DESC";
							break;
					}
				}
			}
			$strOrderBy	= (count($arrOrderBy)) ? implode(', ', $arrOrderBy) : NULL;
			
			// Get the list of Contracts to display
			$arrColumns	= Array(
									'id'				=> "SRP.Id",
									'account'			=> "Service.Account",
									'service'			=> "Service.FNN",
									'ratePlan'			=> "RatePlan.Name",
									'contractTerm'		=> "RatePlan.ContractTerm",
									'contractStarted'	=> "CAST(SRP.StartDatetime AS DATE)",
									'contractBreached'	=> "SRP.contract_effective_end_datetime",
									'breachNature'		=> "SRP.contract_breach_reason_description",
									'minMonthly'		=> "RatePlan.MinMonthly",
									'monthsLeft'		=> "PERIOD_DIFF(DATE_FORMAT(contract_scheduled_end_datetime, '%Y%m'), DATE_FORMAT(contract_effective_end_datetime, '%Y%m'))",
									'payout'			=> "CASE " .
																"WHEN COUNT(ServiceTotal.Id) < (SELECT contract_payout_minimum_invoices FROM contract_terms ORDER BY id DESC LIMIT 1) THEN 0.0 " .
																"ELSE ROUND(RatePlan.MinMonthly * PERIOD_DIFF(DATE_FORMAT(contract_scheduled_end_datetime, '%Y%m'), DATE_FORMAT(contract_effective_end_datetime, '%Y%m')) * (RatePlan.contract_payout_percentage / 100), 2) " .
															"END",
									'exitFee'			=> "CASE " .
																"WHEN COUNT(ServiceTotal.Id) < (SELECT exit_fee_minimum_invoices FROM contract_terms ORDER BY id DESC LIMIT 1) THEN 0.0 " .
																"ELSE RatePlan.contract_exit_fee " .
															"END"
								);
			$selBreachedContracts	= new StatementSelect(	"Service JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service JOIN Account ON Service.Account = Account.Id JOIN RatePlan ON RatePlan.Id = SRP.RatePlan JOIN ServiceTotal ON ServiceTotal.service_rate_plan = SRP.Id",
															$arrColumns,
															"contract_breach_fees_charged_on IS NULL AND contract_status_id = ".CONTRACT_STATUS_BREACHED." AND Service.Status != ".SERVICE_ARCHIVED,
															$strOrderBy,
															$strLimit,
															"SRP.Id");
			
			if ($selBreachedContracts->Execute() === FALSE)
			{
				throw new Exception($selBreachedContracts->Error());
			}
			$arrDetailsToRender['Contracts']	= $selBreachedContracts->FetchAll();
			
			$this->LoadPage('console', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}

?>

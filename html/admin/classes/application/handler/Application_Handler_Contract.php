<?php

class Application_Handler_Contract extends Application_Handler
{
	const	RECORD_DISPLAY_LIMIT	= 24;

	// View all Breached Contracts which are pending approval
	public function ManageBreached($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		// Build List of Breached Contracts and their recommended actions
		try
		{
			// Check for any passed variables
			$intOffset					= (int)$_REQUEST['offset'];

			// Limit
			$strLimit	= self::RECORD_DISPLAY_LIMIT . (($intOffset) ? " OFFSET {$intOffset}" : '');

			// Sorting
			$arrOrderBy	= Array();
			if (is_array($_REQUEST['sort']))
			{
				foreach ($_REQUEST['sort'] as $strColumn=>$strDirection)
				{
					$strColumn								= trim($strColumn, "\"'");
					$arrDetailsToRender['Sort'][$strColumn]	= $strDirection;
					switch (trim($strDirection))
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

			$selContractTerms	= new StatementSelect("contract_terms", "*", "1", "id DESC", "1");
			if ($selContractTerms->Execute() === FALSE)
			{
				throw new Exception_Database($selContractTerms->Error());
			}
			$arrContractTerms	= $selContractTerms->Fetch();

			// Get the list of Contracts to display
			$arrColumns	= Array(
									'id'				=> "SRP.Id",
									'account'			=> "Service.Account",
									'service'			=> "Service.FNN",
									'serviceId'			=> "SRP.Service",
									'ratePlan'			=> "RatePlan.Name",
									'contractTerm'		=> "RatePlan.ContractTerm",
									'contractStarted'	=> "CAST(SRP.StartDatetime AS DATE)",
									'contractBreached'	=> "CAST(SRP.contract_effective_end_datetime AS DATE)",
									'contractInvoices'	=> "COUNT(ServiceTotal.Id)",
									'breachNature'		=> "SRP.contract_breach_reason_description",
									'minMonthly'		=> "ROUND(COALESCE(SRP.min_monthly, RatePlan.MinMonthly), 2)",
									'monthsLeft'		=> "CASE " .
																"WHEN PERIOD_DIFF(DATE_FORMAT(contract_scheduled_end_datetime, '%Y%m'), DATE_FORMAT(contract_effective_end_datetime, '%Y%m')) > RatePlan.ContractTerm THEN RatePlan.ContractTerm " .
																"ELSE PERIOD_DIFF(DATE_FORMAT(contract_scheduled_end_datetime, '%Y%m'), DATE_FORMAT(contract_effective_end_datetime, '%Y%m')) " .
															"END",
									'payout'			=> "CASE " .
																"WHEN COUNT(ServiceTotal.Id) < {$arrContractTerms['contract_payout_minimum_invoices']} THEN 0 " .
																"ELSE ROUND(RatePlan.contract_payout_percentage) " .
															"END",
									'payoutAmount'		=> "CASE " .
																"WHEN COUNT(ServiceTotal.Id) < {$arrContractTerms['contract_payout_minimum_invoices']} THEN 0.00 " .
																"ELSE ROUND(COALESCE(SRP.min_monthly, RatePlan.MinMonthly) * " .
																	"(CASE " .
																		"WHEN PERIOD_DIFF(DATE_FORMAT(contract_scheduled_end_datetime, '%Y%m'), DATE_FORMAT(contract_effective_end_datetime, '%Y%m')) > RatePlan.ContractTerm THEN RatePlan.ContractTerm " .
																		"ELSE PERIOD_DIFF(DATE_FORMAT(contract_scheduled_end_datetime, '%Y%m'), DATE_FORMAT(contract_effective_end_datetime, '%Y%m')) " .
																	"END)" .
																	" * (RatePlan.contract_payout_percentage / 100), 2) " .
															"END",
									'exitFee'			=> "CASE " .
																"WHEN COUNT(ServiceTotal.Id) < {$arrContractTerms['exit_fee_minimum_invoices']} THEN 0.00 " .
																"ELSE ROUND(RatePlan.contract_exit_fee, 2) " .
															"END"
								);
			$selBreachedContractsCount	= new StatementSelect(	"(Service JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service JOIN Account ON Service.Account = Account.Id JOIN RatePlan ON RatePlan.Id = SRP.RatePlan) LEFT JOIN ServiceTotal ON ServiceTotal.service_rate_plan = SRP.Id",
																$arrColumns,
																"contract_breach_fees_charged_on IS NULL AND contract_status_id = ".CONTRACT_STATUS_BREACHED." AND Service.Status != ".SERVICE_ARCHIVED,
																NULL,
																NULL,
																"SRP.Id");

			$selBreachedContracts		= new StatementSelect(	"(Service JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service JOIN Account ON Service.Account = Account.Id JOIN RatePlan ON RatePlan.Id = SRP.RatePlan) LEFT JOIN ServiceTotal ON ServiceTotal.service_rate_plan = SRP.Id",
																$arrColumns,
																"contract_breach_fees_charged_on IS NULL AND contract_status_id = ".CONTRACT_STATUS_BREACHED." AND Service.Status != ".SERVICE_ARCHIVED,
																$strOrderBy,
																$strLimit,
																"SRP.Id");

			$intTotal	= $selBreachedContractsCount->Execute();
			if ($intTotal === FALSE)
			{
				throw new Exception_Database($selBreachedContractsCount->Error());
			}

			$intShown	= $selBreachedContracts->Execute();
			if ($intShown === FALSE)
			{
				throw new Exception_Database($selBreachedContracts->Error());
			}
			$arrDetailsToRender['Contracts']	= $selBreachedContracts->FetchAll();

			// Build Pagination
			$arrDetailsToRender['Pagination']	= Array(
															'intCurrent'	=> $intOffset,
															'intPrevious'	=> max($intOffset - self::RECORD_DISPLAY_LIMIT, 0),
															'intNext'		=> min($intTotal - self::RECORD_DISPLAY_LIMIT - 1, $intOffset + self::RECORD_DISPLAY_LIMIT),
															'intLast'		=> $intTotal - 1 - self::RECORD_DISPLAY_LIMIT,
															'intStart'		=> $intOffset+1,
															'intEnd'		=> $intOffset+1+$intShown,
															'intTotal'		=> $intTotal
														);

			$this->LoadPage('contract_manage_breached', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
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

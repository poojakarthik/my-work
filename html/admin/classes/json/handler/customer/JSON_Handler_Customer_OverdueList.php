<?php

class JSON_Handler_Customer_OverdueList extends JSON_Handler
{
	const RESULTS_PER_PAGE = 10;
	
	// returns an array of details
	// I should probably just get this to return an array of account ids, and then use the Account class to grab extra details
	// This doesn't yet factor in unbilled credit charges and disputed amounts
	private function _getOverdueAccounts()
	{
		$strEffectiveDate = "'".getCurrentISODate()."'";
	
		// Find all Accounts that fit the requirements for Late Notice generation
		$arrColumns = Array(	'invoice_run_id'		=> "MAX(ir_overdue.Id)",
								'AccountId'				=> "a.Id",
								'BusinessName'			=> "a.BusinessName",
								'TradingName'			=> "a.TradingName",
								'AccountStatus'			=> "a.Archived",
								'CustomerGroup'			=> "a.CustomerGroup",
								'Overdue'				=> "SUM(CASE WHEN {$strEffectiveDate} > i_overdue.DueOn THEN i_overdue.Balance END) + COALESCE(aua.adjustment_total, 0)",
								'TotalOutstanding'		=> "SUM(i_overdue.Balance) + aua.adjustment_total");
		
		$strTables	= "
(
	SELECT	{$strEffectiveDate} AS effective_date
) config
JOIN Account a
JOIN Contact c ON (c.Id = a.PrimaryContact)
JOIN account_status a_s ON (a_s.id = a.Archived AND a_s.send_late_notice = 1)
JOIN credit_control_status ccs ON (ccs.id = a.credit_control_status AND ccs.send_late_notice = 1)
JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
JOIN payment_terms pt ON (pt.id = (SELECT id FROM payment_terms WHERE customer_group_id = cg.Id ORDER BY id DESC LIMIT 1))

JOIN Invoice i_overdue ON (i_overdue.Account = a.Id AND i_overdue.Status NOT IN (100, 106))
JOIN InvoiceRun ir_overdue ON (i_overdue.invoice_run_id = ir_overdue.Id)
JOIN invoice_run_type irt_overdue ON (irt_overdue.id = ir_overdue.invoice_run_type_id AND irt_overdue.const_name IN ('INVOICE_RUN_TYPE_LIVE', 'INVOICE_RUN_TYPE_FINAL', 'INVOICE_RUN_TYPE_INTERIM'))
JOIN invoice_run_status irs_overdue ON (irs_overdue.id = ir_overdue.invoice_run_status_id AND irs_overdue.const_name = 'INVOICE_RUN_STATUS_COMMITTED')

LEFT JOIN
(
	SELECT		c.Account																						AS account_id,
				COALESCE(
					SUM(
						COALESCE(
							IF(
								c.Nature = 'CR',
								0 - c.Amount,
								c.Amount
							), 0
						)
						*
						IF(
							c.global_tax_exempt = 1,
							1,
							(
								SELECT		COALESCE(EXP(SUM(LN(1 + tt.rate_percentage))), 1)
								FROM		tax_type tt
								WHERE		c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
											AND tt.global = 1
							)
						)
					), 0
				)																								AS adjustment_total
	FROM		Charge c
	WHERE		c.Status IN (101, 102)	/* Approved or Temp Invoice */
				AND c.charge_model_id IN (SELECT id FROM charge_model WHERE system_name = 'ADJUSTMENT')
				AND c.Nature = 'CR'
				AND c.ChargedOn >= {$strEffectiveDate}
	GROUP BY	c.Account
) /* account_unbilled_adjustments */ aua ON (a.Id = aua.account_id)";

		$strWhere	= "(a.LatePaymentAmnesty IS NULL OR a.LatePaymentAmnesty < config.effective_date) AND vip = 0 AND tio_reference_number IS NULL";
		
		$strGroupBy	= "a.Id HAVING EligibleOverdue >= minBalanceToPursue AND TotalOutstanding >= minBalanceToPursue AND EligibleOverdue > (TotalFromEligibleOverdueInvoices * 0.25)";
		
		$strOrderBy	= "a.Id ASC";
		
		
		$selOverdue = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "2000", $strGroupBy);
		if (($intRecCount = $selOverdue->Execute()) === FALSE)
		{
			throw new Exception("Failed to retrieve overdue accounts - ". $selOverdue->Error());
		}
		
		$arrAccounts = array();
		if ($intRecCount > 0)
		{
			$arrAccounts = $selOverdue->FetchAll();
		}
		return $arrAccounts;
	}
	
	// Builds the CustomerOverdueListPopup
	public function buildPopup()
	{
		// Check user permissions
		if (!(AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR_VIEW))))
		{
			return array(
							'Success'	=> false,
							'ERROR'		=> "PERMISSIONS",
						);
		}
		
		try
		{
			// Get the list of overdue accounts
			$arrAccounts = $this->_getOverdueAccounts();
			$intRecCount = count($arrAccounts);
			$strTableHtml = $this->_buildTable($arrAccounts);
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody'>
	<div class='GroupedContent'>
		<div id='CustomerOverdueListContainerContainer'>
			<div id='CustomerOverdueListContainer' style='overflow:auto; height:400px; width:auto; padding: 0px 3px 0px 3px'>
	$strTableHtml
			</div>
		</div>
		<div style='text-align:center;padding-top:3px'>Showing $intRecCount Records</div>
	</div>
	<div style='padding-top:3px;height:auto:width:100%'>
		<input type='button' value='Close' onclick='Vixen.Popup.Close(this)' style='float:right'></input>
		<div style='clear:both;float:none'></div>
	</div>
</div>
";

			return array(	"Success"		=> TRUE,
							"RecordCount"	=> count($arrAccounts),
							"PopupContent"	=> $strHtml);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	
	// Builds a html table displaying details of the accounts
	private function _buildTable($arrAccounts)
	{
		$strRows = "";
		$bolAlt = FALSE;
		
		$intRecCount = count($arrAccounts);
		if ($intRecCount == 0)
		{
			$strRows = "<tr><td colspan='4'>No records to display</td></tr>";
		}
		else
		{
			foreach ($arrAccounts as $arrAccount)
			{
				$strRowClass		= ($bolAlt)? "class='alt'" : "";
				
				$intAccountId		= $arrAccount['AccountId'];
				$strAccountName		= htmlspecialchars($arrAccount['BusinessName'] ? $arrAccount['BusinessName'] : ($arrAccount['TradingName'] ? $arrAccount['TradingName'] : ''));
				$strOverdueAmount	= number_format($arrAccount['Overdue'], 2, ".", "");
				$strAccountStatus	= GetConstantDescription($arrAccount['AccountStatus'], "account_status");
				
				$strAccountOverviewLink = Href()->AccountOverview($intAccountId);
				
				$strRows .= "
<tr $strRowClass>
	<td><a href='$strAccountOverviewLink'>$intAccountId</a></td>
	<td>$strAccountName</td>
	<td>$strAccountStatus</td>
	<td align='right'>$strOverdueAmount</td>
</tr>";
				$bolAlt = !$bolAlt;
			}
		}
		
		$strHtml = "
<table class='reflex highlight-rows' id='CustomerOverdueListTable' name='CustomerOverdueListTable'>
	<thead>
		<tr>
			<th>Account&nbsp;#</th>
			<th>Name</th>
			<th>Status</th>
			<th align='right'>Overdue&nbsp;(\$)</th>
		</tr>
	</thead>
	<tbody>
$strRows
	</tbody>
</table>
";
		return $strHtml;
	}
	
}

?>

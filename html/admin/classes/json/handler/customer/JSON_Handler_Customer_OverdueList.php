<?php

class JSON_Handler_Customer_OverdueList extends JSON_Handler
{
	const RESULTS_PER_PAGE = 10;
	
	// returns an array of details
	// I should probably just get this to return an array of account ids, and then use the Account class to grab extra details
	// This doesn't yet factor in unbilled credit adjustments and disputed amounts
	private function _getOverdueAccounts()
	{
		$strEffectiveDate = getCurrentISODate();
	
		// Find all Accounts that fit the requirements for Late Notice generation
		$arrColumns = Array(	'invoice_run_id'		=> "MAX(CASE WHEN '$strEffectiveDate' <= Invoice.DueOn THEN 0 ELSE Invoice.invoice_run_id END)",
								'AccountId'				=> "Invoice.Account",
								'BusinessName'			=> "Account.BusinessName",
								'TradingName'			=> "Account.TradingName",
								'AccountStatus'			=> "Account.Archived",
								'Overdue'				=> "SUM(CASE WHEN '$strEffectiveDate' > Invoice.DueOn THEN Invoice.Balance END)",
								'TotalOutstanding'		=> "SUM(Invoice.Balance)");
	
		$strTables	= "Invoice INNER JOIN Account ON Invoice.Account = Account.Id AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < '$strEffectiveDate')";
	
		$arrApplicableAccountStatuses = array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED);
		$arrApplicableInvoiceStatuses = array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT);
		$strApplicableAccountStatuses = implode(", ", $arrApplicableAccountStatuses);
		$strApplicableInvoiceStatuses = implode(", ", $arrApplicableInvoiceStatuses);
		
		// I don't think this needs to be so complicated for this one
		/*$strWhere = "Account.Id IN (
										SELECT DISTINCT(Account.Id) 
										FROM InvoiceRun 
										JOIN Invoice
										  ON Invoice.Status IN ($strApplicableInvoiceStatuses) 
										 AND InvoiceRun.Id = Invoice.invoice_run_id
										JOIN Account 
										  ON Account.Id = Invoice.Account
									         AND Account.Archived IN ($strApplicableAccountStatuses)
									         AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < '$strEffectiveDate')
									)";
		*/
		$strWhere = "Account.Archived IN ($strApplicableAccountStatuses) AND Invoice.Status IN ($strApplicableInvoiceStatuses)";
		$pt = GetPaymentTerms(NULL);
	
		$strOrderBy	= "Account.Archived ASC, Invoice.Account ASC";
		$strGroupBy	= "Invoice.Account HAVING Overdue >= ". $pt['minimum_balance_to_pursue'];
	
		
		// DEBUG: Output the query that gets run
		/*
		$select = array();
		foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
		echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nWHERE $strWhere\nGROUP BY $strGroupBy\nORDER BY $strOrderBy\n\n";
		return FALSE;
		*/
	
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
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
	<td>$strOverdueAmount</td>
</tr>";
				$bolAlt = !$bolAlt;
			}
		}
		
		$strHtml = "
<table class='reflex highlight-rows' id='CustomerOverdueListTable' name='CustomerOverdueListTable'>
	<thead>
		<tr>
			<th>Account #</th>
			<th>Name</th>
			<th>Status</th>
			<th>Overdue (\$)</th>
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

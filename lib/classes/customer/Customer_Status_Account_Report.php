<?php

// Logic for the Customer Status Account Report
class Customer_Status_Account_Report
{
	protected $_arrCustomerStatusIds	= array();
	protected $_arrInvoiceRunIds		= array();
	protected $_arrCustomerGroups		= array();
	
	protected $_arrRecords				= array();

	// Throws an exception if any of the boundary conditions are invalid
	// $arrCustomerStatusIds is an array of CustomerStatus Ids representing the ones we are interested in
	// $arrInvoiceRunIds is an array of Invoice Run Ids representing the ones we are interested in
	// $arrCustomerGroups is an array of Customer Groups we are interested in
	public function SetBoundaryConditions($arrCustomerGroups=NULL, $arrCustomerStatusIds=NULL, $arrInvoiceRunIds)
	{
		// Process the CustomerGroups declared
		if (is_array($arrCustomerGroups) && count($arrCustomerGroups) > 0)
		{
			// Specific CustomerGroups have been specified
			$arrCustomerGroupObjects = Customer_Group::getAll();
			foreach ($arrCustomerGroups as $intId)
			{
				if (!array_key_exists($intId, $arrCustomerGroupObjects))
				{
					throw new Exception("Customer Group with id: $intId, doesn't exist");
				}
				$this->_arrCustomerGroups[] = $intId;
			}
		}
		else
		{
			// No customer groups have been specified
			$this->_arrCustomerGroups = NULL;
		}
		
		// Process the CustomerStatus Ids passed
		if (is_array($arrCustomerStatusIds) && count($arrCustomerStatusIds) > 0)
		{
			// Specific CustomerStatuses have been specified
			$arrCustomerStatusObjects = Customer_Status::getAll();
			foreach ($arrCustomerStatusIds as $intId)
			{
				if (!array_key_exists($intId, $arrCustomerStatusObjects))
				{
					throw new Exception("Customer Status with id: $intId, doesn't exist");
				}
				$this->_arrCustomerStatusIds[] = $intId;
			}
		}
		else
		{
			// No customer statuses have been specified
			$this->_arrCustomerStatusIds = NULL;
		}
		
		// Process the Invoice Run Ids passed
		if (!is_array($arrInvoiceRunIds) || count($arrInvoiceRunIds) == 0)
		{
			throw new Exception("At least one Invoice Run must be specified");
		}
		
		foreach ($arrInvoiceRunIds as $intId)
		{
			if (!is_int($intId))
			{
				throw new Exception("Invalid Invoice Run id: $intId");
			}
		}
		
		$this->_arrInvoiceRunIds = $arrInvoiceRunIds;
	}

	//public function BuildReport($intOffset=0, $intMaxRecords=100, $strOrderBy=NULL)
	public function BuildReport()
	{
		$this->_arrRecords = array();

		if (is_array($this->_arrCustomerGroups) && count($this->_arrCustomerGroups) > 0)
		{
			$strCustomerGroupConstraint	= "AND a.CustomerGroup IN (". implode(", ", $this->_arrCustomerGroups) .")";
		}
		else
		{
			$strCustomerGroupConstraint = "";
		}

		if (is_array($this->_arrCustomerStatusIds) && count($this->_arrCustomerStatusIds) > 0)
		{
			$strCustomerStatusConstraint	= "AND csh.customer_status_id IN (". implode(", ", $this->_arrCustomerStatusIds) .")";
		}
		else
		{
			$strCustomerStatusConstraint = "";
		}
		
		if (is_array($this->_arrInvoiceRunIds) && count($this->_arrInvoiceRunIds) > 0)
		{
			$strInvoiceRunConstraint	= "AND csh.invoice_run_id IN (". implode(", ", $this->_arrInvoiceRunIds) .")";
		}
		else
		{
			$strInvoiceRunConstraint = "";
		}
		
		// Build the query
		$strQuery = "
SELECT a.CustomerGroup AS customer_group, csh.invoice_run_id AS invoice_run_id, csh.customer_status_id AS customer_status_id, a.Id AS account_id, a.BusinessName AS business_name, a.TradingName AS trading_name
FROM Account AS a INNER JOIN customer_status_history AS csh ON a.Id = csh.account_id
WHERE TRUE $strCustomerGroupConstraint $strCustomerStatusConstraint $strInvoiceRunConstraint
ORDER BY invoice_run_id DESC, customer_group ASC, customer_status_id DESC, account_id ASC
";

		// Run the query
		$qryQuery = new Query('flex');
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve Customer Status Account Report data from database. " . mysqli_errno() . '::' . mysqli_error());
		}

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$this->_arrRecords[] = $arrRecord;
		}
	}
	
	public function GetRecords()
	{
		return $this->_arrRecords;
	}
	
	// returns the report as either html or excel
	public function GetReport($strRenderMode)
	{
		switch(strtolower($strRenderMode))
		{
			case "html":
				return $this->GenerateAsHtml();
				break;
				
			case "excel":
				return $this->GenerateAsExcel();
				break;
				
			default:
				return $this->GenerateAsHtml();
				break;
		}
	}
	
	// if $bolRenderLinks === TRUE, then each total will be a link to view the accounts that comprise the total
	private function GenerateAsHtml()
	{
		// Build Invoice Run information
		$arrInvoiceRuns = array();
		foreach ($this->_arrInvoiceRunIds as $intInvoiceRunId)
		{
			$objInvoiceRun = Invoice_Run::getForId($intInvoiceRunId);
			
			if ($objInvoiceRun == NULL)
			{
				// The invoice run could not be found
				continue;
			}
			
			$arrInvoiceRuns[$intInvoiceRunId] = array(	"id"			=> $objInvoiceRun->id,
														"billingDate"	=> date("d/m/Y", strtotime($objInvoiceRun->billingDate))
													);
		}
		$arrCustomerStatusObjects	= Customer_Status::getAll();
		$arrCustomerGroupObjects	= Customer_Group::getAll();

		// Build each row of the table
		$strRows = "";
		$bolAlternateRow = FALSE;
		foreach ($this->_arrRecords as $arrRecord)
		{
			$strRows .= "
<tr ". (($bolAlternateRow)? "class='alt'":"") .">
	<td>{$arrRecord['invoice_run_id']}</td>
	<td>{$arrInvoiceRuns[$arrRecord['invoice_run_id']][billingDate]}</td>
	<td>{$arrCustomerGroupObjects[$arrRecord['customer_group']]->name}</td>
	<td>{$arrCustomerStatusObjects[$arrRecord['customer_status_id']]->name}</td>
	<td>{$arrRecord['account_id']}</td>
	<td>". htmlspecialchars($arrRecord['business_name']) ."</td>
	<td>". htmlspecialchars($arrRecord['trading_name']) ."</td>
</tr>";
		}
		
		$strHtml = "
<table class='reflex' id='ReportResults'>
	<thead class='header'>
		<tr>
			<th>Invoice Run Id</th>
			<th>Billing Date</th>
			<th>Customer Group</th>
			<th>Customer Status</th>
			<th>Account</th>
			<th>Business Name</th>
			<th>Trading Name</th>
		</tr>
	</thead>
	<tbody>
		$strRows
	</tbody>
	<tfoot class='footer'>
	</tfoot>
</table>
";
		return $strHtml;
	}
	
	private function GenerateAsExcel()
	{
		$strReport = "<html>
	<head>
		<meta http-equiv=\"content-type\" content=\"application/excel\">
	</head>
	<body>". $this->GenerateAsHtml() ."</body>
</html>
";
		return $strReport;
	}

}

?>

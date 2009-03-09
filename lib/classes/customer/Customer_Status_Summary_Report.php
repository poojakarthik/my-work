<?php

// Logic for the Customer Status Summary Report
class Customer_Status_Summary_Report
{
	protected $_arrCustomerStatusIds	= array();
	protected $_arrInvoiceRunIds		= array();
	protected $_arrCustomerGroups		= array();
	protected $_bolAnyCustomerGroup		= FALSE;
	
	protected $_arrTotals				= array();

	// Throws an exception if any of the boundary conditions are invalid
	// $arrCustomerStatusIds is an array of CustomerStatus Ids representing the ones we are interested in
	// $arrInvoiceRunIds is an array of Invoice Run Ids representing the ones we are interested in
	public function SetBoundaryConditions($arrCustomerGroups, $arrCustomerStatusIds, $arrInvoiceRunIds)
	{
		// Process the CustomerGroups declared
		if (!is_array($arrCustomerGroups) || count($arrCustomerGroups) == 0)
		{
			throw new Exception("At least one customer group must be specified");
		}
		$intIndex = array_search("any", $arrCustomerGroups, TRUE);
		$bolAnyCustomerGroup = FALSE;
		if ($intIndex !== FALSE)
		{
			// The "any" CustomerGroup option has been declared
			$bolAnyCustomerGroup = TRUE;
			array_splice($arrCustomerGroups, $intIndex, 1);
		}
		foreach ($arrCustomerGroups as $intId)
		{
			if (!is_int($intId))
			{
				throw new Exception("Invalid customer group id: $intId");
			}
		}
		
		// Process the CustomerStatus Ids passed
		if (!is_array($arrCustomerStatusIds) || count($arrCustomerStatusIds) == 0)
		{
			throw new Exception("At least one Customer Status must be specified");
		}
		
		foreach ($arrCustomerStatusIds as $intId)
		{
			if (!is_int($intId))
			{
				throw new Exception("Invalid Customer Status id: $intId");
			}
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

		$this->_arrCustomerStatusIds	= $arrCustomerStatusIds;
		$this->_arrInvoiceRunIds		= $arrInvoiceRunIds;
		$this->_arrCustomerGroups		= $arrCustomerGroups;
		$this->_bolAnyCustomerGroup		= $bolAnyCustomerGroup;
	}

	public function BuildReport()
	{
		// Create the structure that will store all the totals
		$this->_arrTotals = array();
		
		$arrTotalPerStatus = array();
		foreach ($this->_arrCustomerStatusIds as $intStatusId)
		{
			$arrTotalPerStatus[$intStatusId] = 0;
		}
		$arrTotalsPerCustomerGroup = array();
		foreach ($this->_arrInvoiceRunIds as $intInvoiceRunId)
		{
			$arrTotalsPerCustomerGroup[$intInvoiceRunId] = $arrTotalPerStatus;
		}
		
		if ($this->_bolAnyCustomerGroup)
		{
			$this->_arrTotals['any'] = $arrTotalsPerCustomerGroup;
		}
		foreach ($this->_arrCustomerGroups as $intCustomerGroup)
		{
			$this->_arrTotals[$intCustomerGroup] = $arrTotalsPerCustomerGroup;
		}
		
		$strInvoiceRunIds		= implode(", ", $this->_arrInvoiceRunIds);
		$strCustomerStatusIds	= implode(", ", $this->_arrCustomerStatusIds);
		
		if ($this->_bolAnyCustomerGroup)
		{
			// We have to retrieve details regarding all CustomerGroups
			$strCustomerGroupConstraint = "";
		}
		else
		{
			// Just retrieve details regarding the specific customer groups requested
			$strCustomerGroupConstraint = "AND a.CustomerGroup IN (". implode(", ", $this->_arrCustomerGroups) .")";
		}
		
		// Build the query
		$strQuery = "
SELECT a.CustomerGroup AS customer_group, csh.invoice_run_id AS invoice_run_id, csh.customer_status_id AS customer_status_id, count(csh.id) AS account_count
FROM Account AS a INNER JOIN customer_status_history AS csh ON a.Id = csh.account_id
WHERE invoice_run_id IN ($strInvoiceRunIds) AND customer_status_id IN ($strCustomerStatusIds) $strCustomerGroupConstraint
GROUP BY customer_group, invoice_run_id, customer_status_id
";
		// Run the query
		$qryQuery = new Query('flex');
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve CustomerStatus/InvoiceRun totals. " . mysqli_errno() . '::' . mysqli_error());
		}

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			if ($this->_bolAnyCustomerGroup)
			{
				// Add this total to the "any" customer group's total
				$this->_arrTotals['any'][$arrRecord['invoice_run_id']][$arrRecord['customer_status_id']] += $arrRecord['account_count'];
			}
			if (array_key_exists($arrRecord['customer_group'], $this->_arrTotals))
			{
				// Assign this total to the specific customer group
				$this->_arrTotals[$arrRecord['customer_group']][$arrRecord['invoice_run_id']][$arrRecord['customer_status_id']] = $arrRecord['account_count'];
			}
		}
	}
	
	public function GetTotals()
	{
		return $this->_arrTotals;
	}
	
	// returns the report as either html or excel
	public function GetReport($strRenderMode)
	{
		switch(strtolower($strRenderMode))
		{
			case "html":
				return $this->GenerateAsHtml(TRUE);
				break;
				
			case "excel":
				return $this->GenerateAsExcel();
				break;
				
			default:
				return $this->GenerateAsHtml(TRUE);
				break;
		}
	}
	
	// if $bolRenderLinks === TRUE, then each total will be a link to view the accounts that comprise the total
	private function GenerateAsHtml($bolRenderLinks)
	{
		// Build the header (this doesn't change)
		$strHeaderColumns = "<th>Invoice</th>\n";
		
		$arrCustomerStatusIds = array();
		foreach ($this->_arrCustomerStatusIds as $intId)
		{
			$objStatus = Customer_Status::getForId($intId);
			if ($objStatus == NULL)
			{
				// The status could not be found
				continue;
			}
			$strHeaderColumns .= "\t\t\t<th>". htmlspecialchars($objStatus->name) ."</th>\n";
			
			// We can guarantee that this list is correct
			$arrCustomerStatusIds[] = $intId;
		}
		
		$arrCustomerGroups = Customer_Group::getAll();
		
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
														"billingDate"	=> date("d-m-Y", strtotime($objInvoiceRun->billingDate))
													);
			$arrInvoiceRuns[$intInvoiceRunId]['name'] = "{$arrInvoiceRuns[$intInvoiceRunId]['billingDate']} - Id: {$arrInvoiceRuns[$intInvoiceRunId]['id']}". (array_key_exists($objInvoiceRun->customerGroupId, $arrCustomerGroups)? " - {$arrCustomerGroups[$objInvoiceRun->customerGroupId]->internal_name}" : ""); 
		}
		
		// This stores data for the link to the Account Summary functionality, if links will be present in the html
		$arrAccountSummaryData = array("RenderMode" => "excel");

		$strHtml = "";
		foreach ($this->_arrTotals as $mixCustomerGroup=>$arrTotals)
		{
			// Build the Caption
			if ($mixCustomerGroup == 'any')
			{
				$strTitle = "All Customer Groups";

				$arrAccountSummaryData['CustomerGroups'] = array();
			}
			else
			{
				$objCustomerGroup = Customer_Group::GetForId($mixCustomerGroup);
				if ($objCustomerGroup == NULL)
				{
					// Cannot find the customer Group
					continue;
				}
				$strTitle = htmlspecialchars($objCustomerGroup->name);

				$arrAccountSummaryData['CustomerGroups'] = array($mixCustomerGroup);
			}
			
			// Build each row of the table
			$strRows = "";
			$bolAlternateRow = FALSE;
			foreach ($arrInvoiceRuns as $intInvoiceRunId=>$arrInvoiceRun)
			{
				$strRows .= "\t\t<tr ". (($bolAlternateRow)? "class='alt'":"") .">\n\t\t\t<td class='row-title'>{$arrInvoiceRun['name']}</td>\n";

				$arrAccountSummaryData['InvoiceRun'] = $intInvoiceRunId;
	
				foreach ($arrCustomerStatusIds as $intCustomerStatusId)
				{
					$intTotal = $this->_arrTotals[$mixCustomerGroup][$intInvoiceRunId][$intCustomerStatusId];
					if ($bolRenderLinks && $intTotal > 0)
					{
						$arrAccountSummaryData['CustomerStatuses'] = array($intCustomerStatusId);
						$jsonAccountSummaryData = JSON_Services::encode($arrAccountSummaryData);
						$strCell = "<a onclick='Flex.CustomerStatusReports.RunAccountReport($jsonAccountSummaryData)'>$intTotal</a>";
					}
					else
					{
						$strCell = $intTotal;
					}
					$strRows .= "\t\t\t<td>$strCell</td>\n";
				}
				
				$strRows .= "\t\t</tr>\n";
				
				$bolAlternateRow = !$bolAlternateRow;
			}
			
			$strHtml .= "
<br />
<table class='reflex' id='customer_group_$mixCustomerGroup'>
	<caption>
		<div id='caption_bar'>
			<div id='caption_title'> $strTitle
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			$strHeaderColumns
		</tr>
	</thead>
	<tbody>
		$strRows
	</tbody>
	<tfoot class='footer'>
	</tfoot>
</table>
";
		}
		
		return $strHtml;
	}
	
	private function GenerateAsExcel()
	{
		$strReport = "<html>
	<head>
		<meta http-equiv=\"content-type\" content=\"application/excel\">
	</head>
	<body>". $this->GenerateAsHtml(FALSE) ."</body>
</html>
";
		return $strReport;
	}

}

?>

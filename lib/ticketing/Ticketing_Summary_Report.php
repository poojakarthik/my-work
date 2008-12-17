<?php


// Logic for the Ticketing Summary Report
class Ticketing_Summary_Report
{
	protected $_arrOwners			= array();
	protected $_bolAllOwners		= FALSE;
	protected $_arrCategories		= array();
	protected $_bolAllCategories	= FALSE;
	protected $_arrStatusTypes		= array();
	protected $_arrStatuses			= array();
	protected $_strEarliestTime		= NULL;
	protected $_strLatestTime		= NULL;

	protected $_arrReport				= array();
	protected $_arrTotals				= array();
	protected $_arrStatusStatusTypeMap	= array();

	public function __construct()
	{
	}

	// Throws an exception if any of the boundary conditions are invalid
	public function SetBoundaryConditions($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses, $strEarliestTime=NULL, $strLatestTime=NULL)
	{
		// Process the owners declared
		if (!is_array($arrOwners) || count($arrOwners) == 0)
		{
			throw new Exception("At least one owner must be specified");
		}
		$intIndex = array_search("all", $arrOwners, TRUE);
		$bolAllOwners = FALSE;
		if ($intIndex !== FALSE)
		{
			// The "all" owner option has been declared
			$bolAllOwners = TRUE;
			array_splice($arrOwners, $intIndex, 1);
		}
		
		// Process the categories declared
		if (!is_array($arrCategories) || count($arrCategories) == 0)
		{
			throw new Exception("At least one category must be specified");
		}

		$intIndex = array_search("all", $arrCategories, TRUE);
		$bolAllCategories = FALSE;
		if ($intIndex !== FALSE)
		{
			// The "all" category option has been declared
			$bolAllCategories = TRUE;
			array_splice($arrCategories, $intIndex, 1);
		}
		
		// Process the Statuses and StatusTypes declared
		if ((!is_array($arrStatuses) || count($arrStatuses) == 0) && (!is_array($arrStatusTypes) || count($arrStatusTypes) == 0))
		{
			throw new Exception("At least one status or status type must be specified");
		}
		
		// Assume EarliestTime and EndTime are valid ISO DateTime strings or NULL
		
		$this->_arrOwners			= $arrOwners;
		$this->_bolAllOwners		= $bolAllOwners;
		$this->_arrCategories		= $arrCategories;
		$this->_bolAllCategories	= $bolAllCategories;
		$this->_arrStatusTypes		= $arrStatusTypes;
		$this->_arrStatuses			= $arrStatuses;
		$this->_strEarliestTime		= $strEarliestTime;
		$this->_strLatestTime		= $strLatestTime;

		return TRUE;
	}

	public function BuildReport()
	{
		
		// Create the structure that will store all the totals
		$arrTotals = array();
		
		// Initialise the array which will store the totals of tickets, at the Status level
		$arrTotalPerStatus = array();
		foreach ($this->_arrStatuses as $intStatusId)
		{
			$arrTotalPerStatus[$intStatusId] = 0;
		}
		
		// Initialise the array which will store the totals of tickets, at the StatusType level
		$arrTotalPerStatusType = array();
		foreach ($this->_arrStatusTypes as $intStatusTypeId)
		{
			$arrTotalPerStatusType[$intStatusTypeId] = 0;
		}
		
		// Initialise the array which will store the totals of tickets, at the Category level
		$arrTotalPerCategory = array();
		foreach ($this->_arrCategories as $intCategoryId)
		{
			$arrTotalPerCategory[$intCategoryId] = array(	'Status'		=> $arrTotalPerStatus,
															'StatusType'	=> $arrTotalPerStatusType
														);
		}
		if ($this->_bolAllCategories)
		{
			// We also want to store the status/status_type totals combined for all categories
			$arrTotalPerCategory['All'] = array(	'Status'		=> $arrTotalPerStatus,
													'StatusType'	=> $arrTotalPerStatusType
												);
		}
		
		// Initialise the array which will store the totals of tickets, at the Owner level
		$arrTotalPerOwner = array();
		foreach ($this->_arrOwners as $intOwnerId)
		{
			$arrTotalPerOwner[$intOwnerId] = $arrTotalPerCategory;
		}
		if ($this->_bolAllOwners)
		{
			$arrTotalPerOwner['All'] = $arrTotalPerCategory;
		}
		$this->_arrTotals = $arrTotalPerOwner;
		
		// Create an array that maps statuses to their status types
		$qryQuery		= new Query('flex');
		$objRecordSet	= $qryQuery->Execute("SELECT id, status_type_id FROM ticketing_status ORDER BY id ASC");
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve details from the ticketing_status table. " . mysqli_errno() . '::' . mysqli_error());
		}
		$arrStatusStatusTypeMap = array();
		$arrStatusTypeStatusMap = array();
		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			$arrStatusStatusTypeMap[$arrRecord['id']] = $arrRecord['status_type_id'];
			if (!array_key_exists($arrRecord['status_type_id'], $arrStatusTypeStatusMap))
			{
				$arrStatusTypeStatusMap[$arrRecord['status_type_id']] = array();
			}
			$arrStatusTypeStatusMap[$arrRecord['status_type_id']][] = $arrRecord['id'];
		}
		$this->_arrStatusStatusTypeMap = $arrStatusStatusTypeMap;
		
		// Build the TimeRange WHERE clause
		if ($this->_strLatestTime === NULL)
		{
			// No Latest Time was chosen, so use the NOW() function
			$strLatestTime = "NOW()";
		}
		else
		{
			// LatestTime has been declared
			$strLatestTime = "'{$this->_strLatestTime}'";
		}
		if ($this->_strEarliestTime === NULL)
		{
			// No EarliestTime has been specified.  Just limit it by LatestTime
			$strTimeRangeConstraint = "modified_datetime <= $strLatestTime";
		}
		else
		{
			// An EarliestTime has been specified.  Limit the results to being between the 2 dates
			$strTimeRangeConstraint = "modified_datetime BETWEEN '{$this->_strEarliestTime}' AND $strLatestTime";
		}
		
		
		// Build the owner constraint
		$strOwnerConstraint = ($this->_bolAllOwners)? "1=1" : "owner_id IN (". implode(", ", $this->_arrOwners) .")";
		
		// Build the category constraint
		$strCategoryConstraint = ($this->_bolAllCategories)? "TRUE" : "category_id IN (". implode(", ", $this->_arrCategories) .")";
		
		// Build the status constraint
		$arrConstrainingStatuses = $this->_arrStatuses;
		foreach ($this->_arrStatusTypes as $intStatusTypeId)
		{
			$arrConstrainingStatuses = array_merge($arrConstrainingStatuses, $arrStatusTypeStatusMap[$intStatusTypeId]);
		}
		$arrConstrainingStatuses = array_unique($arrConstrainingStatuses);
		$strStatusConstraint = "status_id IN (". implode(", ", $arrConstrainingStatuses) .")";
		
		// Build the query
		$strQuery = "
SELECT owner_id, category_id, status_id, count(ticket_id) as ticket_count
FROM (
	SELECT tth1.id, tth1.ticket_id, tth1.owner_id, tth1.category_id, tth1.status_id, tth1.modified_datetime, ts1.status_type_id
	FROM ticketing_ticket_history AS tth1 INNER JOIN (
		SELECT ticket_id, MAX(id) AS max_id
		FROM ticketing_ticket_history
		WHERE $strTimeRangeConstraint
		GROUP BY ticket_id
	) AS tth2 ON tth1.id = tth2.max_id INNER JOIN ticketing_status AS ts1 ON tth1.status_id = ts1.id
) as ticket_snapshot
WHERE $strOwnerConstraint AND $strCategoryConstraint AND $strStatusConstraint
GROUP BY owner_id, category_id, status_id
";
		// Run the query
		$objRecordSet = $qryQuery->Execute($strQuery);
		if (!$objRecordSet)
		{
			throw new Exception("Failed to retrieve ticket totals. " . mysqli_errno() . '::' . mysqli_error());
		}

		while ($arrRecord = $objRecordSet->fetch_assoc())
		{
			// Record the number of tickets grouped each way being requested
			
			// Check if the "All" owner is being requested
			if ($this->_bolAllOwners)
			{
				// It is
				$this->AddTicketTotalToOwner("All", $arrRecord['category_id'], $arrRecord['status_id'], $arrRecord['ticket_count']);
			}
			
			// Check if the individual owner is being requested
			if (array_key_exists($arrRecord['owner_id'], $this->_arrTotals))
			{
				// It is
				$this->AddTicketTotalToOwner($arrRecord['owner_id'], $arrRecord['category_id'], $arrRecord['status_id'], $arrRecord['ticket_count']);
			}
		}
		
		return TRUE;
	}
	
	private function AddTicketTotalToOwner($mixOwner, $mixCategory, $intStatus, $intTicketCount)
	{
		// Check if the "All" Category is being requested
		if ($this->_bolAllCategories)
		{
			// It is
			$this->AddTicketTotalToCategory($mixOwner, "All", $intStatus, $intTicketCount);
		}
		// Check if the individual category is being requested
		if (array_key_exists($mixCategory, $this->_arrTotals[$mixOwner]))
		{
			// It is
			$this->AddTicketTotalToCategory($mixOwner, $mixCategory, $intStatus, $intTicketCount);
		}
	}
	
	private function AddTicketTotalToCategory($mixOwner, $mixCategory, $intStatus, $intTicketCount)
	{
		// Check if the individual status is being requested
		if (array_key_exists($intStatus, $this->_arrTotals[$mixOwner][$mixCategory]['Status']))
		{
			// It is.  Set it
			$this->_arrTotals[$mixOwner][$mixCategory]['Status'][$intStatus] += $intTicketCount;
		}
		
		// Check if the StatusType of this status is being requested
		if (array_key_exists($this->_arrStatusStatusTypeMap[$intStatus], $this->_arrTotals[$mixOwner][$mixCategory]['StatusType']))
		{
			// It is.  Add the Status Ticket Count to it
			$this->_arrTotals[$mixOwner][$mixCategory]['StatusType'][$this->_arrStatusStatusTypeMap[$intStatus]] += $intTicketCount;
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
	
	private function GenerateAsHtml()
	{
		$strHtml = "";
		
		// Build the header.  This will be the same for each table
		reset($this->_arrTotals);
		$arrFirstOwner = current($this->_arrTotals);
		reset($arrFirstOwner);
		$arrFirstOwnerStatusTotals = current($arrFirstOwner);
		$strHeaderColumns = "";
		// Check if the user has requested StatusType totals
		if (array_key_exists("StatusType", $arrFirstOwnerStatusTotals))
		{
			// Get the name of each StatusType
			foreach ($arrFirstOwnerStatusTotals['StatusType'] as $intStatusType=>$intStatusTypeTotal)
			{
				$strHeaderColumns .= "\t\t<th>". htmlspecialchars(GetConstantDescription($intStatusType, "ticketing_status_type")) ."</th>\n";
			}
		}
		// Check if the user has requested individual Status totals
		if (array_key_exists("Status", $arrFirstOwnerStatusTotals))
		{
			// Get the name of each StatusType
			foreach ($arrFirstOwnerStatusTotals['Status'] as $intStatus=>$intStatusTotal)
			{
				$strHeaderColumns .= "\t\t<th>". htmlspecialchars(GetConstantDescription($intStatus, "ticketing_status")) ."</th>\n";
			}
		}
		
		$strHeader = "
<tr>
	<th></th>
	$strHeaderColumns
</tr>
";
		
		// Build time constraint description
		if ($this->_strEarliestTime !== NULL)
		{
			$strEarliestTime = date("H:i:s d/m/Y", strtotime($this->_strEarliestTime));
		}
		
		if ($this->_strLatestTime !== NULL)
		{
			$strLatestTime = date("H:i:s d/m/Y", strtotime($this->_strLatestTime));
		}
		else
		{
			// LatestTime was not defined, but NOW() would have been used
			$strLatestTime = date("H:i:s d/m/Y", strtotime(GetCurrentISODateTime()));
		}
		
		// Tickets that were worked on (ticketing_ticket modified) as at [Latest] and since [Earliest]
		$strTitleTemplate = "Tickets worked on by <Owner> as at $strLatestTime";
		if ($this->_strEarliestTime !== NULL)
		{
			// Only the EarliestTime constraint has been declared
			$strTitleTemplate .= " and since $strEarliestTime";
		}
		
		// Retrieve list of Ticketing Categories
		$arrCategories = Ticketing_Category::listAll();
		
		foreach ($this->_arrTotals as $mixOwner=>$arrOwnerTotals)
		{
			if ($mixOwner === "All")
			{
				$strTitle = str_replace("<Owner>", "any owner", $strTitleTemplate);
			}
			else
			{
				$objUser = Ticketing_User::getForId($mixOwner);
				$strTitle = str_replace("<Owner>", htmlspecialchars($objUser->getName()), $strTitleTemplate);
			}
			
			// Build each row of the table
			$strRows = "";
			$bolAlternateRow = FALSE;
			foreach ($arrOwnerTotals as $mixCategory=>$arrCategoryTotals)
			{
				// Work out the Category
				if ($mixCategory === "All")
				{
					$strCategoryName = "All Categories";
				}
				else
				{
					$strCategoryName = htmlspecialchars($arrCategories[$mixCategory]->description);
				}
				
				
				$strRows .= "<tr ". (($bolAlternateRow)? "class='alt'":"") .">\n\t\t\t<td class='row-title'>$strCategoryName</td>\n";
				foreach ($arrCategoryTotals['StatusType'] as $intTotal)
				{
					$strRows .= "\t\t\t<td>$intTotal</td>\n";
				}
				foreach ($arrCategoryTotals['Status'] as $intTotal)
				{
					$strRows .= "\t\t\t<td>$intTotal</td>\n";
				}
				
				$strRows .= "\t\t</tr>\n";
				
				$bolAlternateRow = !$bolAlternateRow;
			}
			
			
			$strHtml .= "
<br />
<table class='reflex' id='owner_id_$mixOwner'>
	<caption>
		<div id='caption_bar'>
			<div id='caption_title'> $strTitle
			</div>
		</div>
	</caption>
	<thead class='header'>
		$strHeader
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
		$strReport = "
<html>
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

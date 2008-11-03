<?php

class HtmlTemplate_Dealer_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript('dealer_management');
		$this->LoadJavascript('dealer');
		$this->LoadJavascript('tab');
		$this->LoadJavascript('validation');
	}

	public function Render()
	{
		$arrSort	= $this->mxdDataToRender['Sort'];
		$arrFilter	= $this->mxdDataToRender['Filter'];

		$arrDealers			= $this->mxdDataToRender['Dealers'];
		$arrPagination		= $this->mxdDataToRender['Pagination'];
		$arrDealerStatuses	= $this->mxdDataToRender['DealerStatuses'];
		$arrManagers		= $this->mxdDataToRender['Managers'];
		
		$intTotalRecordCount	= $arrPagination['TotalRecordCount'];
		$intPageRecordCount		= $arrPagination['PageRecordCount'];
		$intCurrentOffset		= $arrPagination['CurrentOffset'];
		$intFirstOffset			= $arrPagination['FirstOffset'];
		$intPreviousOffset		= $arrPagination['PreviousOffset'];
		$intNextOffset			= $arrPagination['NextOffset'];
		$intLastOffset			= $arrPagination['LastOffset'];
		
		if ($intTotalRecordCount > 0)
		{
			$strTitle = "Viewing ". ($intCurrentOffset+1) ." to ". ($intCurrentOffset+$intPageRecordCount) ." of $intTotalRecordCount Dealers";
		}
		else
		{
			$strTitle = "0 Dealers Found";
		}

		// Define the columns to show
		$arrColumns = array("Id"			=> array("Title" => "Id",				"SortField" => "id"),
							"FirstName"		=> array("Title" => "First Name",		"SortField" => "firstName"),
							"LastName"		=> array("Title" => "Last Name",		"SortField" => "lastName"),
							"Username"		=> array("Title" => "Username",			"SortField" => "username"),
							"IsManager"		=> array("Title" => "Manages Others",	"SortField" => NULL),
							"Manager"		=> array("Title" => "Up Line Manager",	"SortField" => "upLineId"),
							"CanVerify"		=> array("Title" => "Can Verify Sales",	"SortField" => "canVerify"),
							"Status"		=> array("Title" => "Status",			"SortField" => "dealerStatusId"),
							"IsEmployee"	=> array("Title" => "Is Employee",		"SortField" => "employeeId"),
							"Actions"		=> array("Title" => "Actions",			"SortField" => NULL)
							);
		$intColumnCount = count($arrColumns);
		
		// Build the body of the table
		if ($intTotalRecordCount > 0)
		{
			$strBodyRows = "";
			$bolAlt = FALSE;
			foreach ($arrDealers as $objDealer)
			{
				$strRowClass	= ($bolAlt)? "class='alt'" : "";
				$bolAlt			= !$bolAlt;
				
				$strId			= $objDealer->id;
				$strFirstName	= htmlspecialchars($objDealer->firstName);
				$strLastName	= htmlspecialchars($objDealer->lastName);
				$strUsername	= htmlspecialchars($objDealer->username);
				$strIsManager	= (array_key_exists($objDealer->id, $arrManagers))? "Yes" : "No";
				$strManager		= ($objDealer->upLineId !== NULL && array_key_exists($objDealer->upLineId, $arrManagers))? htmlspecialchars($arrManagers[$objDealer->upLineId]->getName()) : "";
				$strCanVerify	= ($objDealer->canVerify)? "Yes" : "No";
				$strStatus		= htmlspecialchars(Dealer_Status::getForId($objDealer->dealerStatusId)->name);
				$strIsEmployee	= ($objDealer->employeeId !== NULL)? "Yes" : "No";
				
				$arrActions		= array();
				$arrActions[]	= "<a onclick='". Href()->ViewDealer($objDealer->id) ."'>View</a>";
				$arrActions[]	= "<a onclick='". Href()->EditDealer($objDealer->id) ."'>Edit</a>";;
				$strActions		= implode("&nbsp;&nbsp;", $arrActions);
				
				$strBodyRows .= "
			<tr $strRowClass>
				<td>$strId</td>	
				<td>$strFirstName</td>	
				<td>$strLastName</td>	
				<td>$strUsername</td>	
				<td>$strIsManager</td>	
				<td>$strManager</td>	
				<td>$strCanVerify</td>	
				<td>$strStatus</td>	
				<td>$strIsEmployee</td>	
				<td>$strActions</td>	
			</tr>";
			}
		}
		else
		{
			$strBodyRows = "
			<tr>
				<td colspan='$intColumnCount'>No records to show</td>
			</tr>";
		}
		
		$arrNavLinks = array();
		if ($intCurrentOffset != $intFirstOffset)
		{
			// Include pagination links to the first page and the previous page
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Dealer/ListDealers/Last/?offset=$intFirstOffset'>First</a>";
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Dealer/ListDealers/Last/?offset=$intPreviousOffset'>Previous</a>";
		}
		if ($intCurrentOffset < $intNextOffset)
		{
			// Include pagination links to the next and final pages
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Dealer/ListDealers/Last/?offset=$intNextOffset'>Next</a>";
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Dealer/ListDealers/Last/?offset=$intLastOffset'>Last</a>";
		}
		
		$strNavControls = (count($arrNavLinks) > 0)? implode("&nbsp;&nbsp;", $arrNavLinks) : "&nbsp;";
		
		// Build the header stuff
		$strHeaderRow = "";
		foreach ($arrColumns as $arrColumn)
		{
			
			if ($arrColumn['SortField'] !== NULL)
			{
				// The column can be sorted
				if (array_key_exists($arrColumn['SortField'], $arrSort))
				{
					// The column has been used in sorting the current results
					$strSortDirection	= $arrSort[$arrColumn['SortField']]? 'd' : 'a';  // Toggles between ascending and descending
					$strSortClass		= $arrSort[$arrColumn['SortField']]? "reflex-sorted-ascending" : "reflex-sorted-descending";
				}
				else
				{
					// The column was not used to sort the current results
					$strSortDirection	= 'a';
					$strSortClass		= "reflex-unsorted";
				}
				
				$strScript	= "document.location = '". Flex::getUrlBase() . "reflex.php/Dealer/ListDealers/Last/?sort[\\'{$arrColumn['SortField']}\\']=$strSortDirection'";
				$strOnClick	= "onclick=\"$strScript\"";
			}
			else
			{
				// The column can not be sorted
				$strOnClick		= "";
				$strSortClass	= "";
			}
			
			$strHeaderRow .= "\t\t\t<th class='$strSortClass' $strOnClick>{$arrColumn['Title']}</th>\n";
		}
		$strHeaderRow = "\t\t<tr>\n$strHeaderRow\t\t</tr>";


		// Output the table of dealers
		echo "
<table class='reflex highlight-rows' id='DealerListTable' name='DealerListTable'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				$strTitle
			</div>
			<div id='caption_options' name='caption_options'>
				<a onclick='Dealer.loadNewDealerPopup()'>New Dealer</a>
			</div>
		</div>
	</caption>
	<thead>
$strHeaderRow
	</thead>
	<tbody>
$strBodyRows
	</tbody>
	<tfoot class='footer'>
		<tr>
			<th colspan='$intColumnCount' style='text-align:right'>
				$strNavControls
			</th>
		</tr>
	</tfoot>
</table>
";
	}
}

?>

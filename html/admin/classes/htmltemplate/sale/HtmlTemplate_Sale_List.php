<?php

class HtmlTemplate_Sale_List extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$arrSort	= $this->mxdDataToRender['Sort'];
		$arrFilter	= $this->mxdDataToRender['Filter'];

		$arrSales			= $this->mxdDataToRender['Sales'];
		$arrPagination		= $this->mxdDataToRender['Pagination'];
		
		$intTotalRecordCount	= $arrPagination['TotalRecordCount'];
		$intPageRecordCount		= $arrPagination['PageRecordCount'];
		$intCurrentOffset		= $arrPagination['CurrentOffset'];
		$intFirstOffset			= $arrPagination['FirstOffset'];
		$intPreviousOffset		= $arrPagination['PreviousOffset'];
		$intNextOffset			= $arrPagination['NextOffset'];
		$intLastOffset			= $arrPagination['LastOffset'];
		
		if ($intTotalRecordCount > 0)
		{
			$strTitle = "Viewing ". ($intCurrentOffset+1) ." to ". ($intCurrentOffset+$intPageRecordCount) ." of $intTotalRecordCount Sales";
		}
		else
		{
			$strTitle = "0 Dealers Found";
		}

		// Define the columns to show
		$arrColumns = array("Id"			=> array("Title" => "Id",				"SortField" => DO_Sales_Sale::ORDER_BY_SALE_ID),
							"Vendor"		=> array("Title" => "Group",			"SortField" => DO_Sales_Sale::ORDER_BY_SALE_ACCOUNT_VENDOR_ID),
							"SaleType"		=> array("Title" => "Type",				"SortField" => DO_Sales_Sale::ORDER_BY_SALE_TYPE_ID),
							"AccountName"	=> array("Title" => "Account",			"SortField" => DO_Sales_Sale::ORDER_BY_ACCOUNT_NAME),
							"ContactName"	=> array("Title" => "Contact",			"SortField" => DO_Sales_Sale::ORDER_BY_CONTACT_NAME),
							"Status"		=> array("Title" => "Status",			"SortField" => DO_Sales_Sale::ORDER_BY_SALE_STATUS_ID),
							"LastActionedOn"=> array("Title" => "Last Actioned",	"SortField" => DO_Sales_Sale::ORDER_BY_LAST_ACTIONED_ON),
							"CreatedBy"		=> array("Title" => "Dealer",			"SortField" => DO_Sales_Sale::ORDER_BY_CREATED_BY),
							"Actions"		=> array("Title" => "Actions",			"SortField" => NULL)
							);
		$intColumnCount = count($arrColumns);
		
		$arrStatusHistoryProps	= DO_Sales_SaleStatusHistory::getPropertyDataSourceMappings();
		$arrContactSaleProps	= DO_Sales_ContactSale::getPropertyDataSourceMappings();
		$arrSaleStatuses		= DO_Sales_SaleStatus::getAll();
		$arrSaleTypes			= DO_Sales_SaleType::getAll();
		$arrVendors				= DO_Sales_Vendor::getAll();
		
		// Build the body of the table
		if ($intTotalRecordCount > 0)
		{
			$strBodyRows = "";
			$bolAlt = FALSE;
			foreach ($arrSales as $objSale)
			{
				$objSaleAccount			= DO_Sales_SaleAccount::getForSale($objSale);
				$arrSaleStatusHistory	= DO_Sales_SaleStatusHistory::listForFkSaleStatusHistorySaleId($objSale, '"changedOn" DESC', 1);
				$objDealer				= DO_Sales_Dealer::getForId($objSale->createdBy);
				$arrContactSale			= DO_Sales_ContactSale::listForFkContactSaleSaleId($objSale, "({$arrContactSaleProps['contactAssociationTypeId']} = ". DO_Sales_ContactAssociationType::PRIMARY .") DESC", 1);
				$objContact				= (count($arrContactSale) == 1)? DO_Sales_Contact::getForId($arrContactSale[0]->id) : NULL;
				$objVendor				= $arrVendors[$objSaleAccount->vendorId];
				
				$strRowClass	= ($bolAlt)? "class='alt'" : "";
				$bolAlt			= !$bolAlt;
				
				$strId			= $objSale->id;
				
				$strVendor = htmlspecialchars($objVendor->name);
				
				$strAccountName = htmlspecialchars($objSaleAccount->businessName);
				
				if ($objSaleAccount->externalReference !== NULL && preg_match('/^Account.Id=\d+$/', $objSaleAccount->externalReference))
				{
					// The account is now in flex, include a link to it
					$strAccountLink = Href()->AccountOverview(intval(str_replace('Account.Id=', '', $objSaleAccount->externalReference)));
					$strAccountName = "<a href='$strAccountLink' title='Account Overview'>$strAccountName</a>";
				}
				
				if ($objContact !== NULL)
				{
					$strContactName = $objContact->firstName . (($objContact->middleNames !== NULL)? " {$objContact->middleNames}" : "") ." ". $objContact->lastName;
					$strContactName = htmlspecialchars($strContactName);
				}
				else
				{
					$strContactName = "";
				}
				
				$strSaleStatus		= htmlspecialchars($arrSaleStatuses[$objSale->saleStatusId]->name);
				$strSaleType		= htmlspecialchars($arrSaleTypes[$objSale->saleTypeId]->name);
				if (count($arrSaleStatusHistory) == 1)
				{
					$intChangedOn		= strtotime($arrSaleStatusHistory[0]->changedOn);
					$strLastActionedOn	= "<span title='". date("g:i:s a", $intChangedOn) ."'>". date("d-m-Y", $intChangedOn) ."</span>";
				}
				else
				{
					$strLastActionedOn = "?";
				}
				
				// Use the username for the dealer
				$strDealerName		= htmlspecialchars($objDealer->username);
				
				
				$strViewSaleLink = Href()->ViewSale($objSale->id);
				$strActions	= "<a href='$strViewSaleLink'>View</a>";
				
				$strBodyRows .= "
			<tr $strRowClass>
				<td>$strId</td>
				<td>$strVendor</td>
				<td>$strSaleType</td>
				<td>$strAccountName</td>
				<td>$strContactName</td>
				<td>$strSaleStatus</td>
				<td>$strLastActionedOn</td>
				<td>$strDealerName</td>
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
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Sales/ListSales/Last/?offset=$intFirstOffset'>First</a>";
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Sales/ListSales/Last/?offset=$intPreviousOffset'>Previous</a>";
		}
		if ($intCurrentOffset < $intNextOffset)
		{
			// Include pagination links to the next and final pages
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Sales/ListSales/Last/?offset=$intNextOffset'>Next</a>";
			$arrNavLinks[] = "<a href='". Flex::getUrlBase() . "reflex.php/Sales/ListSales/Last/?offset=$intLastOffset'>Last</a>";
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
				
				$strScript	= "document.location = '". Flex::getUrlBase() . "reflex.php/Sales/ListSales/Last/?sort[\\'{$arrColumn['SortField']}\\']=$strSortDirection'";
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
<table class='reflex highlight-rows' id='SalesListTable' name='SalesListTable'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				$strTitle
			</div>
			<div id='caption_options' name='caption_options'>
				<!-- INSERT FILTER OPTIONS HERE -->
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

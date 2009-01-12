<?php

class JSON_Handler_Account_Sales extends JSON_Handler
{
	public function buildAccountSalesPopup($intAccountId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		try
		{
			$bolUserHasSalesPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_SALES);
			
			$intAccountId	= intval($intAccountId);
			$objAccount		= Account::getForId($intAccountId);
			if ($objAccount === NULL)
			{
				throw new Exception("Account with id: $intAccountId, could not be found");
			}
			
			$arrSaleObjects = FlexSale::listForAccountId($intAccountId, "verified_on DESC, id DESC");
			
			$arrSaleTypes = Sale_Type::getAll();
			$arrSaleStatuses = DO_Sales_SaleStatus::getAll();
			
			$objCustomerGroup = Customer_Group::getForId($objAccount->customerGroup);
			
			// Since the history of the sale is stored on the sales data source, we have to use the current timestamp from this data source
			$strSalesCurrentTimestamp = Data_Source_Time::currentTimestamp(Data_Source::get(FLEX_DATABASE_CONNECTION_SALES));

			// Establish what the Earliest VerifiedOn timestamp can be, which is still within the cooling off period			
			if ($objCustomerGroup->coolingOffPeriod == NULL)
			{
				// There is no cooling off period
				$intMinVerifiedOnForCoolingOffPeriod = strtotime($strSalesCurrentTimestamp);
			}
			else
			{
				$intMinVerifiedOnForCoolingOffPeriod = strtotime("-{$objCustomerGroup->coolingOffPeriod} hour $strSalesCurrentTimestamp");
			}
			
			// Build the rows
			if (count($arrSaleObjects) == 0)
			{
				// The account has no sales associated with it
				$strBodyRows = "<tr><td colspan='7'>No Records</td></tr>";
			}
			else
			{
				$strBodyRows	= "";
				$bolAlt			= FALSE;
				foreach ($arrSaleObjects as $objSale)
				{
					$intVerifiedOn				= strtotime($objSale->verifiedOn);
					$strVerifiedOnFormatted		= date("d-m-Y g:i:s a", $intVerifiedOn);
					$intSaleId					= $objSale->getExternalReferenceValue();
					$strSaleType				= htmlspecialchars($arrSaleTypes[$objSale->saleTypeId]->name);
					$strRowClass				= ($bolAlt)? "class='alt'" : "";
					$bolAlt						= !$bolAlt;
					$doSale						= DO_Sales_Sale::getForId($intSaleId);
					if ($doSale === NULL)
					{
						throw new Exception("Can't find sale with id: $intSaleId");
					}
					
					$strCurrentStatus = htmlspecialchars($arrSaleStatuses[$doSale->saleStatusId]->name);
					
					if ($intVerifiedOn > $intMinVerifiedOnForCoolingOffPeriod && $doSale->saleStatusId != DO_Sales_SaleStatus::CANCELLED)
					{
						// The sale is still within its cooling off period
						$strCoolingOff = "Yes";
						// The cooling of period starts at the time of verification
						$strCoolingOffEndTimestamp	= date("d-m-Y g:i:s a", ($intVerifiedOn + $objCustomerGroup->coolingOffPeriod * 60 * 60));
					}	
					else
					{
						// The cooling off period has expired for the sale
						$strCoolingOff				= "No";
						$strCoolingOffEndTimestamp	= "";
					}
					
					$strViewLink	= Href()->ViewSale($intSaleId);
					$arrActions		= array();
					$arrActions[]	= "<a href='$strViewLink'>View</a>";
					$arrActions[]	= "<a onclick='JsAutoLoader.loadScript(\"javascript/sp/sale_history.js\", function(){SaleHistory.loadPopup($intSaleId);});'>History</a>";
					
					if ($intVerifiedOn > $intMinVerifiedOnForCoolingOffPeriod && $doSale->saleStatusId != DO_Sales_SaleStatus::CANCELLED)
					{
						// The sale is still within its cooling off period
						$arrActions[] = "<a onclick='JsAutoLoader.loadScript(\"javascript/sp/sale_cancellation_popup.js\", function(){SaleCancellationPopup.load($intSaleId);});'>Cancel</a>";
					}
					
					$strActions		= implode(" | ", $arrActions);
					
					$strBodyRows .= "
			<tr $strRowClass>
				<td>$intSaleId</td>
				<td>$strSaleType</td>
				<td>$strVerifiedOnFormatted</td>
				<td>$strCurrentStatus</td>
				<td>$strCoolingOff</td>
				<td>$strCoolingOffEndTimestamp</td>
				<td>$strActions</td>
			</tr>";
				}
			}
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody' style='padding:3px'>
	<div style='overflow:auto;max-height:25em;width:100%;'>
		<table class='reflex highlight-rows' id='AccountSalesTable' name='AccountSalesTable'>
			<thead>
				<tr>
					<th>Sale</th>
					<th>Type</th>
					<th>Verified&nbsp;On</th>
					<th>Status</th>
					<th>Cooling&nbsp;Off</th>
					<th>Cooling&nbsp;Off&nbsp;Ends</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				$strBodyRows
			</tbody>
		</table>
	</div>

	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' value='Close' onclick='Vixen.Popup.Close(this)'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>
</div>
";

			return array(	"success"		=> TRUE,
							"accountId"		=> $objAccount->id,
							"accountName"	=> htmlspecialchars($objAccount->getName()),
							"popupContent"	=> $strHtml
						);
		}
		catch (Exception $e)
		{
			return array(	"success"		=> FALSE,
							"errorMessage"	=> $e->getMessage()
						);
		}
	}
}

?>

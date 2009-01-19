<?php

class JSON_Handler_Sale extends JSON_Handler
{
	public function load($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = DO_Sales_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}
		return Sales_Portal::getStdClassForDOSale($sale);
	}
	
	public function submit($saleDetails)
	{
		return $this->confirm($saleDetails, true);
	}
	
	public function confirm($saleDetails, $bolValidateOnly=false)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		// WIP - MAKE SURE THE RECORD IS IN A SUITABLE STATE TO BE AMENDED

		// WIP - NEED TO SAVE THE SALE DETAILS!!!
		
		$dataSource = Data_Source::get();
		$dataSource->beginTransaction();
		
		if (!$saleDetails->id)
		{
			throw new Exception("No sale specified.");
		}
		
		try
		{
			$sale = DO_Sales_Sale::getForId($saleDetails->id);

			if ($sale == null)
			{
				throw new Exception("Unable to locate sale " . $saleDetails->id);
			}


			// Sale can only be amended if it is: -
			// submitted 
			// manual intervention
			if (!Sales_Portal_Sale::canBeAmended($sale))
			{
				throw new Exception("The sale cannot be amended at this time.");
			}

			$saleAccountDetails = $saleDetails->sale_account;

			$saleAccount = $sale->getSaleAccount();
			
			if (!$saleAccount)
			{
				throw new Exception("No sale account exists for the selected sale.");
			}
			
			// Remove any old direct debit details
			if ($saleAccount->billPaymentTypeId == 2)
			{
				if ($saleAccount->directDebitTypeId == 1)
				{
					$arrPaymentDetails = DO_Sales_SaleAccountDirectDebitBankAccount::listForSaleAccount($saleAccount);
				}
				else
				{
					$arrPaymentDetails = DO_Sales_SaleAccountDirectDebitCreditCard::listForSaleAccount($saleAccount);
				}
				foreach ($arrPaymentDetails as $objPaymentDetail)
				{
					$objPaymentDetail->delete;
				}
			}

			try
			{
				$saleAccount->abn = $saleAccountDetails->abn;
				$saleAccount->acn = $saleAccountDetails->acn;
				$saleAccount->addressLine1 = $saleAccountDetails->address_line_1;
				$saleAccount->addressLine2 = $saleAccountDetails->address_line_2;
				$saleAccount->billDeliveryTypeId = $saleAccountDetails->bill_delivery_type_id;
				$saleAccount->businessName = $saleAccountDetails->business_name;
				//$saleAccount->billPaymentTypeId = $saleAccountDetails->bill_payment_type_id;
				//$saleAccount->directDebitTypeId = $saleAccountDetails->direct_debit_type_id ? $saleAccountDetails->direct_debit_type_id : null;
				$saleAccount->postcode = $saleAccountDetails->postcode;
				$saleAccount->referenceId = $saleAccountDetails->reference_id;
				$saleAccount->stateId = $saleAccountDetails->state_id;
				$saleAccount->suburb = $saleAccountDetails->suburb;
				$saleAccount->tradingName = $saleAccountDetails->trading_name;
				$saleAccount->isValid(true);
				if (!$bolValidateOnly) 
				{
					$saleAccount->save($dealer->id);
				}
			}
			catch (DO_Validation_Exception $e)
			{
				throw new Exception($e->getMessage());
			}
			catch (Exception $e)
			{
				throw new Exception(($bolValidateOnly ? "The sale account details entered are invalid: " : "Failed to save sale account: ") . $e->getMessage());
			}
			/*
			if ($saleAccount->billPaymentTypeId == 2) // WIP - Code this properly! 2 = Direct Debit
			{
				try
				{
					switch($saleAccount->directDebitTypeId)
					{
					
						case 1: // WIP - Code this properly! 1 = Bank Account
							$directDebitDetails = $saleAccountDetails->sale_account_direct_debit_bank_account;
							$directDebit = new DO_Sales_SaleAccountDirectDebitBankAccount();
							$directDebit->saleAccountId = $bolValidateOnly ? 0 : $saleAccount->id;
							$directDebit->accountName = $directDebitDetails->account_name;
							$directDebit->accountNumber = $directDebitDetails->account_number;
							$directDebit->bankBsb = $directDebitDetails->bank_bsb;
							$directDebit->bankName = $directDebitDetails->bank_name;
							break;
							
						case 2: // WIP - Code this properly! 2 = Credit Card
							$directDebitDetails = $saleAccountDetails->sale_account_direct_debit_credit_card;
							$directDebit = new DO_Sales_SaleAccountDirectDebitCreditCard();
							$directDebit->saleAccountId = $bolValidateOnly ? 0 : $saleAccount->id;
							$directDebit->cardName = $directDebitDetails->card_name;
							$directDebit->cardNumber = $directDebitDetails->card_number;
							$directDebit->creditCardTypeId = $directDebitDetails->credit_card_type_id;
							$directDebit->cvv = $directDebitDetails->cvv;
							$directDebit->expiryMonth = $directDebitDetails->expiry_month;
							$directDebit->expiryYear = $directDebitDetails->expiry_year;
							break;
					}
					$directDebit->isValid(true);
					if (!$bolValidateOnly) 
					{
						$directDebit->save();
					}
				}
				catch (DO_Validation_Exception $e)
				{
					throw new Exception($e->getMessage());
				}
				catch (Exception $e)
				{
					throw new Exception(($bolValidateOnly ? "The direct debit details are invalid: " : "Failed to save direct debit details: ") . $e->getMessage());
				}
			}
			*/
			
			$tmpArrContactSales = DO_Sales_ContactSale::listForSale($sale);
			if (count($tmpArrContactSales) !== 1)
			{
				throw new Exception("Invalid sale setup - incorrect number of contacts: " . count($tmpArrContactSales));
			}

			$arrContactSales = array();
			foreach ($tmpArrContactSales as $contactSale)
			{
				$arrContactSales[$contactSale->contactId] = $contactSale;
			}
			
			$arrContactDetails = $saleDetails->contacts;
			foreach ($arrContactDetails as $contactDetails)
			{
				try
				{
					$new = false;
					if (array_key_exists($contactDetails->id, $arrContactSales))
					{
						$contact = $arrContactSales[$contactDetails->id]->getContact();
						unset($arrContactSales[$contactDetails->id]);
					}
					else
					{
						$contact = new DO_Sales_Contact();
						$contact->contactStatusId = 1; // WIP - Code this properly! 	1 = Active
						$new = true;
					}
					$contact->contactTitleId = $contactDetails->contact_title_id;
					$contact->dateOfBirth = $contactDetails->date_of_birth;
					$contact->firstName = $contactDetails->first_name;
					$contact->lastName = $contactDetails->last_name;
					$contact->middleNames = $contactDetails->middle_names;
					$contact->positionTitle = $contactDetails->position_title;
					//$contact->username = $contactDetails->username;
					//$contact->password = $contactDetails->password;
					$contact->isValid(true);
					if (!$bolValidateOnly) 
					{
						$contact->save();
					}
					
					if ($new)
					{
						$contactSale = new DO_Sales_ContactSale();
						$contactSale->saleId = $bolValidateOnly ? 0 : $sale->id;
						$contactSale->contactId = $bolValidateOnly ? 0 : $contact->id;
						$contactSale->contactAssociationTypeId = 1; // WIP - Code this properly! 1 = PRIMARY
						$contactSale->isValid(true);
						if (!$bolValidateOnly) 
						{
							$contactSale->save();
						}
					}
				}
				catch (DO_Validation_Exception $e)
				{
					throw new Exception($e->getMessage());
				}
				catch (Exception $e)
				{
					throw new Exception(($bolValidateOnly ? "The contact details entered are invalid: " : "Failed to save contact: ") . $e->getMessage());
				}

				$arrContactMethodDetails = $contactDetails->contact_methods;
				$tmpArrContactMethods = DO_Sales_ContactMethod::listForContact($contact);
				$arrContactMethods = array();
				foreach ($tmpArrContactMethods as $contactMethod)
				{
					$arrContactMethods[$contactMethod->contactMethodTypeId] = $contactMethod;
				}
				$hasPrimary = false;
				foreach ($arrContactMethodDetails as $contactMethodDetails)
				{
					try
					{
						if (array_key_exists($contactMethodDetails->contact_method_type_id, $arrContactMethods))
						{
							$contactMethod = $arrContactMethods[$contactMethodDetails->contact_method_type_id];
							// If the existing contact method should still be used...
							if ($contactMethodDetails->details != null || trim($contactMethodDetails->details))
							{
								// Remove it from the list of methods to be deleted
								unset($arrContactMethods[$contactMethodDetails->contact_method_type_id]);
							}
						}
						else
						{
							$contactMethod = new DO_Sales_ContactMethod();
							$contactMethod->contactId = $bolValidateOnly ? 0 : $contact->id;
							$contactMethod->contactMethodTypeId = $contactMethodDetails->contact_method_type_id;
						}
						
						$contactMethod->isPrimary = $contactMethodDetails->is_primary ? true : false;
						$contactMethod->details = $contactMethodDetails->details;
						try
						{
							$contactMethod->isValid(true);
							// Only save if not doing validation AND we actually have details to save
							if (!$bolValidateOnly && ($contactMethod->isPrimary || $contactMethod->details)) 
							{
									$contactMethod->save();
							}
						}
						catch (Exception $e)
						{
							if (!$contactMethod->details && !$contactMethod->isPrimary)
							{
								// Ignore the error - we don't need this record as it isn't primary!
								
								// WIP: We DO STILL NEED THE RECORD IF THEY HAVE OPTED FOR EBILL AND THIS IS THE EMAIL!!
								if ($saleAccount->billDeliveryTypeId == 2 && $contactMethod->contactMethodTypeId == 1) // WIP - Code this properly! billDeliveryTypeId 2 = EMAIL, contactMethodTypeId 1 = EMAIL 
								{
									throw new Exception("A valid email address must be entered when 'Email' is selected as the 'Bill Delivery Method'.");
								}
							}
							else
							{
								throw $e;
							}
						}
						
						$hasPrimary = $hasPrimary || $contactMethod->isPrimary;
					}
					catch (DO_Validation_Exception $e)
					{
						throw new Exception($e->getMessage());
					}
					catch (Exception $e)
					{
						throw new Exception(($bolValidateOnly ? "The contact details entered are invalid: " : "Failed to save contact method: ") . $e->getMessage());
					}

				}

				if (!$hasPrimary)
				{
					throw new Exception("No preferred contact method selected.");
				}

				if (!$bolValidateOnly)
				{
					foreach ($arrContactMethods as $unwantedContactMethod)
					{
						$unwantedContactMethod->delete();
					}
				}
			}
			
			if (!$bolValidateOnly)
			{
				foreach ($arrContactSales as $objContactSale)
				{
					$objContactSale->delete();
				}
			}

			$arrItemDetails = $saleDetails->items;

			if (!$arrItemDetails || !is_array($arrItemDetails) || !count($arrItemDetails))
			{
				throw new Exception("Unable to save a sale with no sale items. Try cancelling the sale instead.");
			}

			$tmpArrItems = DO_Sales_SaleItem::listForSale($sale);
			$arrItems = array();
			foreach ($tmpArrItems as $item)
			{
				$arrItems[$item->id] = $item;
			}
			
			foreach ($arrItemDetails as $itemDetails)
			{
				try
				{
					if (array_key_exists($itemDetails->id, $arrItems))
					{
						$item = $arrItems[$itemDetails->id];
						unset($arrItems[$itemDetails->id]);
						if ($item->productId != $itemDetails->product_id)
						{
							throw new Exception("You cannot change the product type of a sale item.");
						}
					}
					else
					{
						$item = new DO_Sales_SaleItem();
						$item->createdBy = $dealer->id;
						$item->productId = $itemDetails->product_id;
						$item->saleId = $bolValidateOnly ? 0 : $sale->id;
						$item->saleItemStatusId = DO_Sales_SaleItemStatus::SUBMITTED;
					}
					$item->isValid(true);
					if (!$bolValidateOnly) 
					{
						$item->save($dealer->id, 'New (original) sale item');
					}
				}
				catch (DO_Validation_Exception $e)
				{
					throw new Exception($e->getMessage());
				}
				catch (Exception $e)
				{
					throw new Exception(($bolValidateOnly ? "The sale item details entered are invalid: " : "Failed to save sale item: ") . $e->getMessage());
				}

				$module = $itemDetails->product_type_module;

				$moduleClassName = 'Product_Type_Module_'.$module;
				if (!class_exists($moduleClassName, true))
				{
					throw new Exception("No module for for product module type '$module'");
				}

				$objModule = new $moduleClassName();

				try
				{
					$objModule->saveProductDetailsForSaleItem($itemDetails->product_detail, $item, $bolValidateOnly);
				}
				catch (DO_Validation_Exception $e)
				{
					throw new Exception($e->getMessage());
				}
				catch (Exception $e)
				{
					throw new Exception(($bolValidateOnly ? "The details entered for sale item '$module' are invalid: " : "Failed to save '$module' product details for sale item: ") . $e->getMessage());
				}
			}
			
			if (!$bolValidateOnly)
			{
				foreach($arrItems as $removedItem)
				{
					$removedItem->cancel($dealer->id);
				}
			}

			if ($bolValidateOnly)
			{
				$dataSource->rollback();
			}
			else
			{
				$dataSource->commit();
			}
		}
		catch (Exception $e)
		{
			$dataSource->rollback();
			throw new Exception(($bolValidateOnly ? "The sale is invalid for the following reason(s):\n" : "The sale could not be saved for the following reason(s):\n") . $e->getMessage());
		}
		
		return $sale->id;
		
	}

	public function cancelSale($saleId, $strReason=NULL)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		//$sale = DO_Sales_Sale::getForId(intval($saleId));
		$sale = Sales_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}

		if (Sales_Portal_Sale::canBeCancelled($sale))
		{
			try
			{
				// Start Transactions for both the sales data source and the flex one
				$dsSales = $sale->getDataSource();
				$dsSales->beginTransaction();
				TransactionStart(FLEX_DATABASE_CONNECTION_DEFAULT);
				
				// This will create a system note if an account is associated with the sale
				$sale->cancel($dealer->id, $strReason);
				
				$dsSales->commit();
				TransactionCommit(FLEX_DATABASE_CONNECTION_DEFAULT);
				
				try
				{
					// Check if there was a flex account associated with this sale, and if so, alert the user that things should be done
					$objFlexSale = FlexSale::getForExternalReference("sale.id={$sale->id}");
					if ($objFlexSale !== NULL)
					{
						$strMessage = "Account {$objFlexSale->accountId} is associated with this sale, and may need manual actions performed on it such as reverse churns, service disconnections or even account closure.";
						return $strMessage;
					}
				}
				catch (Exception $e)
				{
					// Don't worry about it
				}
			}
			catch (Exception $e)
			{
				$dsSales->rollback();
				TransactionRollback(FLEX_DATABASE_CONNECTION_DEFAULT);
				throw new Exception("Cancelling the sale failed - ". $e->getMessage());
			}
		}
		else
		{
			throw new Exception("The sale cannot be cancelled at this time.");
		}
	}

	public function rejectSale($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = DO_Sales_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}

		// Sale can only be moved to rejected if it is :-
		// submitted
		if (Sales_Portal_Sale::canBeRejected($sale))
		{
			$sale->reject($dealer->id);
		}
		else
		{
			throw new Exception("The sale cannot be rejected at this time.");
		}
	}

	public function verifySale($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = DO_Sales_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}

		// Sale can only be moved to verified if it is :-
		// submitted
		if (Sales_Portal_Sale::canBeVerified($sale))
		{
			$sale->verify($dealer->id);
		}
		else
		{
			throw new Exception("The sale cannot be verified at this time.");
		}
	}


	// This is invoked via ajax
	public function listProductTypesForVendor($intVendorId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$intVendorId = intval($intVendorId);
		
		$list = DO_Sales_ProductType::getProductTypesForVendor($intVendorId);
		
		$arrModuleProductType = new stdClass();
		$arrModuleProductType->ids = array();
		$arrModuleProductType->labels = array();
		foreach($list as $instance) 
		{
			$arrModuleProductType->ids[] = $instance['module'];
			$arrModuleProductType->labels[] = $instance['name'];
		}
		return $arrModuleProductType;
	}
	
	// This is invoked via ajax
	public function listProductsForProductTypeModuleAndVendor($strProductTypeModule, $intVendorId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$productType = DO_Sales_ProductType::getForModule($strProductTypeModule);
		if (!$productType) throw new Exception('Invalid product type selected: ' . $strProductTypeModule);

		$intVendorId = intval($intVendorId);

		$intProductTypeId = $productType->id;
		
		$list = DO_Sales_Product::listProductIdAndNameForProductTypeIdAndVendorId($intProductTypeId, $intVendorId);
		
		$arrProductIdName = new stdClass();
		$arrProductIdName->ids = array();
		$arrProductIdName->labels = array();
		foreach($list as $instance) 
		{
			$arrProductIdName->ids[] = $instance['id'];
			$arrProductIdName->labels[] = $instance['name'];
		}
		return $arrProductIdName;
		
	}
	
	public function history($intSaleId)
	{
		$response = $this->buildHistoryPopup($intSaleId);
		if (!$response['Success'])
		{
			return $response;
		}
		$responseHTML = $response['PopupContent'];
		$responseHTML = str_replace("<input type='button' value='Close' onclick='Vixen.Popup.Close(this)'></input>", "<input type='button' value='Close' onclick='Sale.hideHistory()'></input>", $responseHTML);
		return $responseHTML;
	}
	
	// Builds the "Sale History" popup, which lists all status changes that the sale has gone through
	public function buildHistoryPopup($intSaleId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES);
		
		try
		{
			$intSaleId = intval($intSaleId);
			
			$doSale = DO_Sales_Sale::getForId($intSaleId);
			
			if ($doSale === NULL)
			{
				throw new Exception("Cannot find sale with id: $intSaleId");
			}
			
			// Retrieve the history
			$arrPropMap = DO_Sales_SaleStatusHistory::getPropertyDataSourceMappings();
			$strOrderBy = "{$arrPropMap['changedOn']} DESC, {$arrPropMap['id']} DESC";
			$arrSaleStatusHistoryObjects = DO_Sales_SaleStatusHistory::listForSale($doSale, $strOrderBy);
			
			// cache dealers
			$arrDealers = array();
			
			$arrStatuses = DO_Sales_SaleStatus::getAll();
			
			if (count($arrSaleStatusHistoryObjects) == 0)
			{
				// This shouldn't ever happen, because if the sale exists, then it should have at least 1 sale_status_history record
				$strBodyRows = "<tr><td colspan='4'>Now Records</td></tr>";
			}
			else
			{
				$strBodyRows	= "";
				$bolAlt			= FALSE;
				foreach ($arrSaleStatusHistoryObjects as $doHistoryRecord)
				{
					if (!array_key_exists($doHistoryRecord->changedBy, $arrDealers))
					{
						// We have not encountered this dealer yet.  Cache it
						$arrDealers[$doHistoryRecord->changedBy] = DO_Sales_Dealer::getForId($doHistoryRecord->changedBy);
					}

					$strRowClass	= ($bolAlt)? "class='alt'" : "";
					$bolAlt			= !$bolAlt;
					
					$strDealer = ($doHistoryRecord->changedBy == DO_Sales_Dealer::SYSTEM_DEALER_ID)? "System Process" : htmlspecialchars($arrDealers[$doHistoryRecord->changedBy]->username);
					
					$strStatus = htmlspecialchars($arrStatuses[$doHistoryRecord->saleStatusId]->name);
					$strStatusCssClass = "sale-status-". strtolower(str_replace(" ", "-", $arrStatuses[$doHistoryRecord->saleStatusId]->name));
					$strStatus = "<span class='$strStatusCssClass'>$strStatus</span>";
					
					$intTimestamp = strtotime($doHistoryRecord->changedOn);
					$strDate = date("d-m-Y", $intTimestamp);
					$strTime = date("g:i:s", $intTimestamp) ."&nbsp;". date("a", $intTimestamp);
					
					$strDescription = ($doHistoryRecord->description !== NULL)? htmlspecialchars($doHistoryRecord->description) : "";
					
					$strBodyRows .= "
			<tr $strRowClass>
				<td>$strDate</td>
				<td>$strTime</td>
				<td>$strStatus</td>
				<td>$strDescription</td>
				<td>$strDealer</td>
			</tr>";
				}
			}
			
			// Build contents for the popup
			$strHtml = "
<div id='PopupPageBody' style='padding:3px'>

	<div style='overflow:auto;max-height:25em;width:100%;'>
		<table class='reflex highlight-rows' id='SaleStatusHistoryTable' name='SaleStatusHistoryTable'>
			<thead>
				<tr>
					<th colspan='2'>Time</th>
					<th>Event</th>
					<th>Description</th>
					<th>Changed&nbsp;By</th>
				</tr>
			</thead>
			<tbody style='vertical-align:top'>
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

			return array(	"Success"		=> TRUE,
							"PopupContent"	=> $strHtml,
							"saleId"		=> $intSaleId
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}

	// Builds the data required of the Sale Cancellation popup
	public function buildSaleCancellationPopup($intSaleId)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES);
		
		try
		{
			$intSaleId = intval($intSaleId);
			
			$doSale = DO_Sales_Sale::getForId($intSaleId);
			
			if ($doSale === NULL)
			{
				throw new Exception("Cannot find sale with id: $intSaleId");
			}
			
			$strCoolingOffPeriodEndsOn = $doSale->getEndOfCoolingOffPeriodTimestamp();
			if ($strCoolingOffPeriodEndsOn === NULL || $strCoolingOffPeriodEndsOn <= Data_Source_Time::currentTimestamp($doSale->getDataSource()))
			{
				throw new Exception("Cooling off period has transpired for this sale");
			}
			
			$doSaleType							= DO_Sales_SaleType::getForId($doSale->saleTypeId);
			$doSaleStatus						= DO_Sales_SaleStatus::getForId($doSale->saleStatusId);
			$objSale							= new stdClass();
			$objSale->id						= $doSale->id;
			$objSale->saleType					= htmlspecialchars($doSaleType->name);
			$objSale->verifiedOn				= date("d-m-Y H:i:s", strtotime($doSale->getVerificationTimestamp()));
			$objSale->coolingOffPeriodEndsOn	= date("d-m-Y H:i:s", strtotime($strCoolingOffPeriodEndsOn));
			
			$objSale->status		= htmlspecialchars($doSaleStatus->name);
			$objSale->items			= array();
			$arrDoSaleItems			= DO_Sales_SaleItem::listForSale($doSale, "product_id ASC, sale_item_status_id DESC");
			$arrDoSaleItemStati		= DO_Sales_SaleItemStatus::getAll();
			
			$arrDoProductTypes		= DO_Sales_ProductType::listAll();
			$arrDoProductTypesIndexed	= array();
			foreach ($arrDoProductTypes as $doProductType)
			{
				$arrDoProductTypesIndexed[$doProductType->id] = $doProductType;
			}

			$strSaleItems = "";
			foreach ($arrDoSaleItems as $doSaleItem)
			{
				$doProduct						= DO_Sales_Product::getForId($doSaleItem->productId);
				$strModule 						= Product_Type_Module::getModuleClassNameForProduct($doProduct);
				$arrDoSaleItemStatusHistory		= DO_Sales_SaleItemStatusHistory::listForSaleItem($doSaleItem, "id DESC", 1);
				$doCurrentStatusDetails			= current($arrDoSaleItemStatusHistory);
				$objSaleItem					= new stdClass();
				$objSaleItem->id				= $doSaleItem->id;
				$objSaleItem->description		= call_user_func(array($strModule, "getSaleItemDescription"), $doSaleItem, TRUE, TRUE);
				$objSaleItem->status			= $arrDoSaleItemStati[$doCurrentStatusDetails->saleItemStatusId]->name;
				$objSaleItem->statusChangedOn	= date("d-m-Y H:i:s", strtotime($doCurrentStatusDetails->changedOn));
				$objSale->items[]				= $objSaleItem;
				
				$strSaleItems .= "
<tr>
	<td>{$objSaleItem->description}</td>
	<td>{$objSaleItem->status}</td>
	<td>{$objSaleItem->statusChangedOn}</td>
</tr>";
			}

			$strHtml = "
<div id='PopupPageBody' style='padding:3px'>
	<form id='SaleCancellationForm' name='SaleCancellationForm'>
		<div class='GroupedContent'>
			<table class='form-data'>
				<tr>
					<td class='title' style='width:20%'>Type of Sale</td>
					<td>{$objSale->saleType}</td>
				</tr>
				<tr>
					<td class='title'>Verified On</td>
					<td>{$objSale->verifiedOn}</td>
				</tr>
				<tr>
					<td class='title'>Cooling Off Period Ends</td>
					<td>{$objSale->coolingOffPeriodEndsOn}</td>
				</tr>
				<tr>
					<td class='title'>Products</td>
					<td>
						<div style='overflow:auto;max-height:10em;width:100%;'>
							<table style='width:100%'>
								$strSaleItems
							</table>
						</div>
					</td>
				</tr>
				<tr>
					<td class='title'>Reason for Cancellation</td>
					<td><textarea class='required' id='reason' name='reason' rows='3' style='width:100%'></textarea></td>
				</tr>
			</table>
		</div>
	
		<div style='padding-top:3px;height:auto:width:100%'>
			<div style='float:right'>
				<input type='button' value='Ok' id='okButton' name='okButton'></input>
				<input type='button' value='Close' id='cancelButton' name='cancelButton'></input>
			</div>
			<div style='clear:both;float:none'></div>
		</div>
	</form>

</div>
";

			return array(	"success"		=> TRUE,
							"sale"			=> $objSale,
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

	// This will run any of the sales reports 
	public function buildReport($strReportType, $objConstraints)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES_ADMIN);

		try
		{
			$strRenderMode = Sales_Report::RENDER_MODE_EXCEL;
			
			$objReportBuilder = Sales_Report::getNewReport($strReportType);

			$objReportBuilder->setConstraints($objConstraints);

			$intRecordCount = $objReportBuilder->buildReport();
	
			$strReport = $objReportBuilder->getReport(Sales_Report::RENDER_MODE_EXCEL);

			$arrRenderMode = Sales_Report::getRenderModeDetails($strRenderMode);
			
			$strFilename = strtolower(str_replace(" ", "_", $objReportBuilder->getDetailedReportName())) .".". $arrRenderMode['FileExtension'];
			
			// Store the report in the user's session
			$_SESSION['Sales']['Report'] = array(	'Content'		=> $strReport,
													'Filename'		=> $strFilename,
													'RenderMode'	=> $strRenderMode
												);
			return array(	"Success"			=> TRUE,
							"ReportLocation"	=> Href()->SalesReport($strReportType, TRUE),
							"RecordCount"		=> $intRecordCount
						);
		}
		catch (Exception $e)
		{
			return array(	
							"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}


}


?>

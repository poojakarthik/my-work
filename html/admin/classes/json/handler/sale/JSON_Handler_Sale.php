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
							unset($arrContactMethods[$contactMethodDetails->contact_method_type_id]);
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
							if (!$bolValidateOnly) 
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

				foreach ($arrContactMethods as $unwantedContactMethod)
				{
					$unwantedContactMethod->delete();
				}
			}
			
			foreach ($arrContactSales as $objContactSale)
			{
				$objContactSale->delete();
			}

			$arrItemDetails = $saleDetails->items;

			if (!$arrItemDetails || !is_array($arrItemDetails) || !count($arrItemDetails))
			{
				throw new Exception("Unable to save a sale with no sale items.");
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
			
			if ($bolValidateOnly)
			{
				foreach($arrItems as $removedItem)
				{
					$removedItem->cancel();
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

	public function cancelSale($saleId)
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

		// Sale can only be moved to cancelled if it is :-
		// submitted
		// rejected
		// manual intervention
		// provisioned
		// verified
		// i.e. pretty much any state except 'ready for provisioning'
		if (Sales_Portal_Sale::canBeCancelled($sale))
		{
			$sale->cancel($dealer->id);
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
}


?>

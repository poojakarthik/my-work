<?php

class Sales_Portal
{
	const APPLICATION_NAME_PORTAL = 'portal';
	
	const PERMISSION_PUBLIC 		= '0000000000000000000000000000000000000000000000000000000000000000';
	const PERMISSION_DEALER			= '1000000000000000000000000000000000000000000000000000000000000000'; 
	const PERMISSION_UPLINE_DEALER	= '0100000000000000000000000000000000000000000000000000000000000000';
	const PERMISSION_SUPER_ADMIN	= '1111111111111111111111111111111111111111111111111111111111111110';
	const PERMISSION_GOD			= '1111111111111111111111111111111111111111111111111111111111111111'; // 64 x '1'
	
	public static function getStdClassForDOSale(DO_Sales_Sale $sale)
	{
		$saleDetails = new stdClass();
		
		$saleDetails->id = $sale->id;
		$saleDetails->sale_type_id = $sale->saleTypeId;
		$saleDetails->sale_status_id = $sale->saleStatusId;
		$saleDetails->created_on = $sale->saleTypeId;
		$saleDetails->created_by = $sale->saleTypeId;
		$saleDetails->commission_paid_on = $sale->commissionPaidOn;
		
		$saleAccounts = DO_Sales_SaleAccount::listForSale($sale);
		$saleAccount = $saleAccounts[0];
		
		$saleDetails->sale_account = new stdClass();
		$saleDetails->sale_account->id = $saleAccount->id;
		$saleDetails->sale_account->state_id = $saleAccount->stateId;
		$saleDetails->sale_account->vendor_id = $saleAccount->vendorId;
		$saleDetails->sale_account->bill_delivery_type_id = $saleAccount->billDeliveryTypeId;
		$saleDetails->sale_account->bill_payment_type_id = $saleAccount->billPaymentTypeId;
		$saleDetails->sale_account->direct_debit_type_id = $saleAccount->directDebitTypeId;
		$saleDetails->sale_account->sale_account_direct_debit_credit_card_id = $saleAccount->saleAccountDirectDebitCreditCardId;
		$saleDetails->sale_account->sale_account_direct_debit_credit_card = null;
		$saleDetails->sale_account->sale_account_direct_debit_bank_account_id = $saleAccount->saleAccountDirectDebitBankAccountId;
		$saleDetails->sale_account->sale_account_direct_debit_bank_account = null;
		$saleDetails->sale_account->reference_id = $saleAccount->referenceId;
		$saleDetails->sale_account->business_name = $saleAccount->businessName;
		$saleDetails->sale_account->trading_name = $saleAccount->tradingName;
		$saleDetails->sale_account->abn = $saleAccount->abn;
		$saleDetails->sale_account->acn = $saleAccount->acn;
		$saleDetails->sale_account->address_line_1 = $saleAccount->addressLine1;
		$saleDetails->sale_account->address_line_2 = $saleAccount->addressLine2;
		$saleDetails->sale_account->suburb = $saleAccount->suburb;
		$saleDetails->sale_account->postcode = $saleAccount->postcode;
		
		$saleAccountDDCCs = DO_Sales_SaleAccountDirectDebitCreditCard::listForSaleAccount($saleAccount);
		if (count($saleAccountDDCCs))
		{
			$saleDetails->sale_account->sale_account_direct_debit_credit_card = new stdClass();
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->id = $saleAccountDDCCs[0]->id;
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->credit_card_type_id = $saleAccountDDCCs[0]->creditCardTypeId;
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->card_name = $saleAccountDDCCs[0]->cardName;
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->card_number = $saleAccountDDCCs[0]->cardNumber;
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->expiry_month = $saleAccountDDCCs[0]->expiryMonth;
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->expiry_year = $saleAccountDDCCs[0]->expiryYear;
			$saleDetails->sale_account->sale_account_direct_debit_credit_card->cvv = $saleAccountDDCCs[0]->cvv;
		}
		
		$saleAccountDDBAs = DO_Sales_SaleAccountDirectDebitBankAccount::listForSaleAccount($saleAccount);
		if (count($saleAccountDDBAs))
		{
			$saleDetails->sale_account->sale_account_direct_debit_bank_account = new stdClass();
			$saleDetails->sale_account->sale_account_direct_debit_bank_account->id = $saleAccountDDBAs[0]->id;
			$saleDetails->sale_account->sale_account_direct_debit_bank_account->bank_name = $saleAccountDDBAs[0]->bankName;
			$saleDetails->sale_account->sale_account_direct_debit_bank_account->bank_bsb = $saleAccountDDBAs[0]->bankBsb;
			$saleDetails->sale_account->sale_account_direct_debit_bank_account->account_number = $saleAccountDDBAs[0]->accountNumber;
			$saleDetails->sale_account->sale_account_direct_debit_bank_account->account_name = $saleAccountDDBAs[0]->accountName;
		}

		$contactSales = DO_Sales_ContactSale::listForSale($sale);
		
		$saleDetails->contacts = array();
		foreach ($contactSales as $contactSale)
		{
			$contact = $contactSale->getContact();
			$contactDetails = new stdClass();
			$contactDetails->id = $contact->id;
			$contactDetails->created_on = $contact->createdOn;
			$contactDetails->contact_title_id = $contact->contactTitleId;
			$contactDetails->contact_status_id = $contact->contactStatusId;
			$contactDetails->reference_id = $contact->referenceId;
			$contactDetails->first_name = $contact->firstName;
			$contactDetails->middle_names = $contact->middleNames;
			$contactDetails->last_name = $contact->lastName;
			$contactDetails->position_title = $contact->positionTitle;
			$contactDetails->username = $contact->username;
			$contactDetails->password = null;
			$contactDetails->date_of_birth = $contact->dateOfBirth;
			
			$contactMethods = DO_Sales_ContactMethod::listForContact($contact);
			
			$contactDetails->contact_methods = array();
			foreach ($contactMethods as $contactMethod)
			{
				$contactMethodDetails = new stdClass();
				$contactMethodDetails->id = $contactMethod->id;
				$contactMethodDetails->contact_method_type_id = $contactMethod->contactMethodTypeId;
				$contactMethodDetails->details = $contactMethod->details;
				$contactMethodDetails->is_primary = $contactMethod->isPrimary;

				$contactDetails->contact_methods[] = $contactMethodDetails;
			}

			$saleDetails->contacts[] = $contactDetails;
		}
		
		$saleItems = DO_Sales_SaleItem::listForSale($sale);
		
		$saleDetails->items = array();
		foreach ($saleItems as $saleItem)
		{
			$product = $saleItem->getProduct();
			$productType = $product->getProductType();
			
			$saleItemDetails = new stdClass();
			$saleItemDetails->id = $saleItem->id;
			$saleItemDetails->product_type_module = $productType->module;
			$saleItemDetails->product_id = $product->id;
			$saleItemDetails->product_name = $product->name;
			$saleItemDetails->product_type = $productType->name;
			$saleItemDetails->created_on = $saleItem->createdOn;
			$saleItemDetails->created_by = $saleItem->createdBy;
			$saleItemDetails->sale_item_status_id = $saleItem->saleItemStatusId;
			$saleItemDetails->commission_paid_on = $saleItem->commissionPaidOn;
			
			$moduleClassName = 'Product_Type_Module_'.$productType->module;
			if (!class_exists($moduleClassName, true))
			{
				throw new Exception("No module for for product module type '$module'");
			}
			$objModule = new $moduleClassName();
			$saleItemDetails->product_detail = $objModule->loadDataForSaleItem($saleItem);
			$saleDetails->items[] = $saleItemDetails;
		}
		
		//echo "/*\n\n";
		//var_export($saleDetails);
		//echo "\n\n*/";

		return $saleDetails;
	}

}

?>

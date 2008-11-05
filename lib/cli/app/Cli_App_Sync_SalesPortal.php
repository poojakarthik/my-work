<?php


class Cli_App_Sync_SalesPortal extends Cli
{
	const	SWITCH_TEST_RUN		= "t";
	const	SWITCH_MODE	 		= "m";

	function run()
	{
		try
		{
			$this->log("Starting.");

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			if ($arrArgs[self::SWITCH_TEST_RUN])
			{
				$this->log("Running in test mode. All changes will be rolled back.", TRUE);
			}
			
			// Load Flex Framework
			$this->requireOnce('lib/classes/Flex.php');
			Flex::load();
			
			switch (trim(strtoupper($arrArgs[self::SWITCH_MODE])))
			{
				case 'PUSH':
					$this->_pushAll();
					break;
				
				case 'PULL':
					$this->_pullAll();
					break;
				
				case 'SYNC':
					$this->_pushAll();
					$this->_pullAll();
					break;
				
				default:
					throw new Exception("'".trim(strtoupper($arrArgs[self::SWITCH_MODE]))."' is not a valid mode of operation!");
			}

			$this->log("Finished.");
			return 0;

		}
		catch(Exception $exception)
		{
			// We can now show the error message
			$this->showUsage($exception->getMessage());
			return 1;
		}
	}
	
	//---------------------------- PUSH OPERATIONS ---------------------------//
	// _pushAll()	-- Pushes all shared Data from Flex to the Sales Portal
	protected function _pushAll()
	{
		$this->_pushVendors();
		$this->_pushProducts();
		$this->_pushDealers();
	}
	
	// _pushVendors()	-- Synchronises the Flex.CustomerGroup table with SP Vendors 
	protected function _pushVendors()
	{
		$dsSalesPortal	= Data_Source::get('sales_portal');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for the Sales Portal
		$dsSalesPortal->beginTransaction();
		
		try
		{
			// Get list of Customer Groups from Flex
			$arrCustomerGroups	= Customer_Group::getAll();
			foreach ($dsSalesPortal as $objCustomerGroup)
			{
				// Does this Customer Group exist in the Sales Portal?
				$resVendors	= $dsSalesPortal->query("SELECT * FROM vendor WHERE id = {$objCustomerGroup->id}");
				if (PEAR::isError($resVendors))
				{
					throw new Exception($resVendors->getError()." :: ".$resVendors->getUserInfo());
				}
				elseif (!($arrVendor = $resVendors->fetchRow(MDB2_FETCHMODE_ASSOC)))
				{
					// No -- add it
					$resVendorInsert	= $dsSalesPortal->query("INSERT INTO vendor (id, name, description) VALUES ({$objCustomerGroup->id}, '{$objCustomerGroup->externalName}', '{$objCustomerGroup->externalName}');");
					if (PEAR::isError($resVendorInsert))
					{
						throw new Exception($resVendorInsert->getError()." :: ".$resVendorInsert->getUserInfo());
					}
				}
				else
				{
					// Yes -- update it
					$resVendorUpdate	= $dsSalesPortal->query("UPDATE vendor SET id = {$objCustomerGroup->id}, name = '{$objCustomerGroup->externalName}', description = '{$objCustomerGroup->externalName}' WHERE id = {$objCustomerGroup->id};");
					if (PEAR::isError($resVendorUpdate))
					{
						throw new Exception($resVendorUpdate->getError()." :: ".$resVendorUpdate->getUserInfo());
					}
				}
			}
			
			// All seems to have worked fine -- Commit the Transaction
			$dsSalesPortal->commit();
		}
		catch (Exception $eException)
		{
			// Rollback the Transaction & passthru the Exception
			$dsSalesPortal->rollback();
			throw $eException;
		}
	}
	
	// _pushProducts()	-- Synchronises the Flex Plans with SP Products 
	protected function _pushProducts()
	{
		$dsSalesPortal	= Data_Source::get('sales_portal');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for the Sales Portal
		$dsSalesPortal->beginTransaction();
		
		try
		{
			$qryQuery	= new Query();
			
			//-------------------------- RATE PLANS --------------------------//
			// Get list of Rate Plans from Flex
			$resRatePlans	= $qryQuery->Execute("SELECT * FROM RatePlan WHERE Archived IN (0, 1);");
			if ($resRatePlans === FALSE)
			{
				throw new Exception($qryQuery->Error());
			}
			else
			{
				while ($arrRatePlan = $resRatePlans->fetch_assoc())
				{
					// Determine the values
					$intProductVendor		= $arrRatePlan['customer_group'];
					$strProductName			= $arrRatePlan['Name'];
					$strProductDescription	= $arrRatePlan['Description'];
					$intProductType			= $this->_convertFlexToSalesPortal('service_type', $arrRatePlan['ServiceType']);
					$intProductStatus		= $this->_convertFlexToSalesPortal('plan_archived', $arrRatePlan['Archived']);
					
					// Does it already exist in the SP?
					$resProduct	= $dsSalesPortal->query("SELECT id FROM product WHERE reference = 'RatePlan.Id={$arrRatePlan['Id']}' LIMIT 1");
					if (PEAR::isError($resProduct))
					{
						throw new Exception($resProduct->getError()." :: ".$resProduct->getUserInfo());
					}
					if ($resProduct->numRows())
					{
						// Already Exists -- do an UPDATE
						$arrProduct			= $resProduct->fetchRow(MDB2_FETCHMODE_ASSOC);
						$strUpdateSQL		= "UPDATE product SET vendor_id = {$intProductVendor}, name = '{$strProductName}', description = '{$strProductDescription}', product_type_id = {$intProductType}, product_status_id = {$intProductStatus} " .
												"WHERE id = {$arrProduct['id']}";
						$resProductUpdate	= $dsSalesPortal->query($strUpdateSQL);
						if (PEAR::isError($resProductUpdate))
						{
							throw new Exception($resProductUpdate->getError()." :: ".$resProductUpdate->getUserInfo());
						}
					}
					else
					{
						// Doesn't Exist -- do an INSERT
						$strInsertSQL		= "INSERT INTO product (	vendor_id			, name					, description					, product_type_id	, product_status_id		, reference) VALUES " .
																	"(	{$intProductVendor}	, '{$strProductName}'	, '{$strProductDescription}'	, {$intProductType}	, {$intProductStatus}	, 'RatePlan.Id={$arrRatePlan['Id']}')";
						$resProductInsert	= $dsSalesPortal->query($strInsertSQL);
						if (PEAR::isError($resProductInsert))
						{
							throw new Exception($resProductInsert->getError()." :: ".$resProductInsert->getUserInfo());
						}
					}
				}
			}
			//----------------------------------------------------------------//
			
			// Other Product Definitions go here
			// TODO
			
			// All seems to have worked fine -- Commit the Transaction
			$dsSalesPortal->commit();
		}
		catch (Exception $eException)
		{
			// Rollback the Transaction & passthru the Exception
			$dsSalesPortal->rollback();
			throw $eException;
		}
	}
	
	// _pushDealers()	-- Synchronises the Flex Dealers with SP Dealers 
	protected function _pushDealers()
	{
		
		$dsSalesPortal	= Data_Source::get('sales_portal');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for the Sales Portal
		$dsSalesPortal->beginTransaction();
		
		try
		{
			$qryQuery	= new Query();
			
			// Get list of Dealers from Flex
			$resFlexDealers	= $qryQuery->Execute("SELECT * FROM dealer;");
			if ($resFlexDealers === FALSE)
			{
				throw new Exception($qryQuery->Error());
			}
			while ($arrFlexDealer = $resFlexDealers->fetch_assoc())
			{
				//-------------------------- DEALERS -------------------------//				
				// Does this Dealer exist in the Sales Portal?
				$resSPDealer	= $dsSalesPortal->query("SELECT id FROM dealer WHERE id = {$arrFlexDealer['id']} LIMIT 1");
				if (PEAR::isError($resSPDealer))
				{
					throw new Exception($resSPDealer->getError()." :: ".$resSPDealer->getUserInfo());
				}
				if (!($arrSPDealer = $resSPDealer->fetchRow(MDB2_FETCHMODE_ASSOC)))
				{
					// Doesn't exist -- INSERT
					$arrSPDealer	= $arrFlexDealer;
					
					$strInsertSQL	= "INSERT INTO dealer VALUES " .
										"( " .
										"	{$arrSPDealer['id']}, " .
										"	{$arrSPDealer['up_line_id']}," .
										"	'{$arrSPDealer['username']}'," .
										"	'{$arrSPDealer['password']}'," .
										"	{$arrSPDealer['can_verify']}," .
										"	'{$arrSPDealer['first_name']}'," .
										"	'{$arrSPDealer['last_name']}'," .
										"	{$arrSPDealer['title_id']}," .
										"	'{$arrSPDealer['business_name']}'," .
										"	'{$arrSPDealer['trading_name']}'," .
										"	'{$arrSPDealer['abn']}'," .
										"	{$arrSPDealer['abn_registered']}," .
										"	'{$arrSPDealer['address_line_1']}'," .
										"	'{$arrSPDealer['address_line_2']}'," .
										"	'{$arrSPDealer['suburb']}'," .
										"	{$arrSPDealer['state_id']}," .
										"	{$arrSPDealer['country_id']}," .
										"	'{$arrSPDealer['postcode']}'," .
										"	'{$arrSPDealer['postal_address_line_1']}'," .
										"	'{$arrSPDealer['postal_address_line_2']}'," .
										"	'{$arrSPDealer['postal_suburb']}'," .
										"	{$arrSPDealer['postal_state_id']}," .
										"	{$arrSPDealer['postal_country_id']}," .
										"	'{$arrSPDealer['postal_postcode']}'," .
										"	'{$arrSPDealer['phone']}'," .
										"	'{$arrSPDealer['mobile']}," .
										"	'{$arrSPDealer['fax']}'," .
										"	'{$arrSPDealer['email']}'," .
										"	{$arrSPDealer['commission_scale']}," .
										"	{$arrSPDealer['royalty_scale']}," .
										"	'{$arrSPDealer['bank_account_bsb']}'," .
										"	'{$arrSPDealer['bank_account_number']}'," .
										"	'{$arrSPDealer['bank_account_name']}'," .
										"	{$arrSPDealer['gst_registered']}," .
										"	'{$arrSPDealer['termination_date']}'," .
										"	{$arrSPDealer['dealer_status_id']}," .
										"	'{$arrSPDealer['created_on']}'" .
										");";
					$resDealerInsert	= $dsSalesPortal->query($strInsertSQL);
					if (PEAR::isError($resDealerInsert))
					{
						throw new Exception($resDealerInsert->getError()." :: ".$resDealerInsert->getUserInfo());
					}
				}
				else
				{
					// Does exist -- UPDATE
					$arrSPDealer	= $arrFlexDealer;
					
					$strUpdateSQL	= "UPDATE dealer SET " .
										"	up_line_id				= {$arrSPDealer['up_line_id']}," .
										"	username				= '{$arrSPDealer['username']}'," .
										"	password				= '{$arrSPDealer['password']}'," .
										"	can_verify				= {$arrSPDealer['can_verify']}," .
										"	first_name				= '{$arrSPDealer['first_name']}'," .
										"	last_name				= '{$arrSPDealer['last_name']}'," .
										"	title_id				= {$arrSPDealer['title_id']}," .
										"	business_name			= '{$arrSPDealer['business_name']}'," .
										"	trading_name			= '{$arrSPDealer['trading_name']}'," .
										"	abn						= '{$arrSPDealer['abn']}'," .
										"	abn_registered			= {$arrSPDealer['abn_registered']}," .
										"	address_line_1			= '{$arrSPDealer['address_line_1']}'," .
										"	address_line_2			= '{$arrSPDealer['address_line_2']}'," .
										"	suburb					= '{$arrSPDealer['suburb']}'," .
										"	state_id				= {$arrSPDealer['state_id']}," .
										"	country_id				= {$arrSPDealer['country_id']}," .
										"	postcode				= '{$arrSPDealer['postcode']}'," .
										"	postal_address_line_1	= '{$arrSPDealer['postal_address_line_1']}'," .
										"	postal_address_line_2	= '{$arrSPDealer['postal_address_line_2']}'," .
										"	postal_suburb			= '{$arrSPDealer['postal_suburb']}'," .
										"	postal_state_id			= {$arrSPDealer['postal_state_id']}," .
										"	postal_country_id		= {$arrSPDealer['postal_country_id']}," .
										"	postal_postcode			= '{$arrSPDealer['postal_postcode']}'," .
										"	phone					= '{$arrSPDealer['phone']}'," .
										"	mobile					= '{$arrSPDealer['mobile']}," .
										"	fax						= '{$arrSPDealer['fax']}'," .
										"	email					= '{$arrSPDealer['email']}'," .
										"	commission_scale		= {$arrSPDealer['commission_scale']}," .
										"	royalty_scale			= {$arrSPDealer['royalty_scale']}," .
										"	bank_account_bsb		= '{$arrSPDealer['bank_account_bsb']}'," .
										"	bank_account_number		= '{$arrSPDealer['bank_account_number']}'," .
										"	bank_account_name		= '{$arrSPDealer['bank_account_name']}'," .
										"	gst_registered			= {$arrSPDealer['gst_registered']}," .
										"	termination_date		= '{$arrSPDealer['termination_date']}'," .
										"	dealer_status_id		= {$arrSPDealer['dealer_status_id']}," .
										"	created_on				= '{$arrSPDealer['created_on']}' " .
										"WHERE id = {$arrSPDealer['id']};";
					$resDealerUpdate	= $dsSalesPortal->query($strUpdateSQL);
					if (PEAR::isError($resDealerUpdate))
					{
						throw new Exception($resDealerUpdate->getError()." :: ".$resDealerUpdate->getUserInfo());
					}
				}
				//------------------------------------------------------------//
			}
			
			//----------------------- DEALER >> PRODUCTS ---------------------//
			// Truncate the SP Table
			$resDealerProductTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_product");
			if (PEAR::isError($resDealerProductTruncate))
			{
				throw new Exception($resDealerProductTruncate->getError()." :: ".$resDealerProductTruncate->getUserInfo());
			}
			
			// Recreate the SP Table from Flex Data
			$resDealerRatePlan	= $qryQuery->Execute("SELECT * FROM dealer_rate_plan");
			if ($resDealerRatePlan === FALSE)
			{
				throw new Exception($resDealerRatePlan->Error());
			}
			while ($arrDealerRatePlan = $resDealerRatePlan->fetch_assoc())
			{
				// Insert an SP-equivalent record	
				$resDealerProductInsert	= $dsSalesPortal->query("INSERT INTO dealer_product (dealer_id, product_id) VALUES " .
																"({$arrDealerRatePlan['dealer_id']}, (SELECT id FROM product WHERE reference = '{$arrDealerRatePlan['rate_plan_id']}'))");
				if (PEAR::isError($resDealerProductInsert))
				{
					throw new Exception($resDealerProductInsert->getError()." :: ".$resDealerProductInsert->getUserInfo());
				}
			}
			//----------------------------------------------------------------//
			
			//---------------------- DEALER >> SALE TYPE ---------------------//
			// Truncate the SP Table
			$resDealerSaleTypeTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_sale_type");
			if (PEAR::isError($resDealerSaleTypeTruncate))
			{
				throw new Exception($resDealerSaleTypeTruncate->getError()." :: ".$resDealerSaleTypeTruncate->getUserInfo());
			}
			
			// Recreate the SP Table from Flex Data
			$resDealerSaleType	= $qryQuery->Execute("SELECT * FROM dealer_sale_type");
			if ($resDealerSaleType === FALSE)
			{
				throw new Exception($resDealerSaleType->Error());
			}
			while ($arrDealerSaleType = $resDealerSaleType->fetch_assoc())
			{
				// Insert an SP-equivalent record	
				$resDealerSaleTypeInsert	= $dsSalesPortal->query("INSERT INTO dealer_sale_type (dealer_id, sale_type_id) VALUES " .
																"({$arrDealerSaleType['dealer_id']}, {$arrDealerSaleType['sale_type_id']})");
				if (PEAR::isError($resDealerSaleTypeInsert))
				{
					throw new Exception($resDealerSaleTypeInsert->getError()." :: ".$resDealerSaleTypeInsert->getUserInfo());
				}
			}
			//----------------------------------------------------------------//
			
			//------------------------ DEALER >> VENDOR ----------------------//
			// Truncate the SP Table
			$resDealerVendorTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_vendor");
			if (PEAR::isError($resDealerVendorTruncate))
			{
				throw new Exception($resDealerVendorTruncate->getError()." :: ".$resDealerVendorTruncate->getUserInfo());
			}
			
			// Recreate the SP Table from Flex Data
			$resDealerCustomerGroup	= $qryQuery->Execute("SELECT * FROM dealer_customer_group");
			if ($resDealerCustomerGroup === FALSE)
			{
				throw new Exception($resDealerCustomerGroup->Error());
			}
			while ($arrDealerCustomerGroup = $resDealerCustomerGroup->fetch_assoc())
			{
				// Insert an SP-equivalent record	
				$resDealerVendorInsert	= $dsSalesPortal->query("INSERT INTO dealer_vendor (dealer_id, vendor_id) VALUES " .
																"({$arrDealerCustomerGroup['dealer_id']}, {$arrDealerCustomerGroup['customer_group_id']})");
				if (PEAR::isError($resDealerVendorInsert))
				{
					throw new Exception($resDealerVendorInsert->getError()." :: ".$resDealerVendorInsert->getUserInfo());
				}
			}
			//----------------------------------------------------------------//
			
			// All seems to have worked fine -- Commit the Transaction
			$dsSalesPortal->commit();
		}
		catch (Exception $eException)
		{
			// Rollback the Transaction & passthru the Exception
			$dsSalesPortal->rollback();
			throw $eException;
		}
	}
	//------------------------------------------------------------------------//
	
	//---------------------------- PULL OPERATIONS ---------------------------//
	// _pullAll()	-- Pulls all shared Data from the Sales Portal to Flex 
	protected function _pullAll()
	{
		$this->_pullSales();
	}
	
	// _pullSales()	-- Pulls all new Sales from the Sales Portal
	protected function _pullSales()
	{
		$dsSalesPortal	= Data_Source::get('sales_portal');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for both Databases
		$dsSalesPortal->beginTransaction();
		$dacFlex->TransactionStart();
		
		try
		{
			$insBankAccount	= new StatementInsert("DirectDebit");
			$insCreditCard	= new StatementInsert("CreditCard");
			
			// Get a list of New Sales from the SP
			// FIXME: When the sale_status.const_name field is added, use it instead of sale_status.name
			$resNewSales	= $dsSalesPortal->query("SELECT sale.* " .
													"FROM sale JOIN sale_status ON sale.sale_status_id = sale_status.id " .
													"WHERE sale_status.name = 'Ready For Provisioning'");
			if (PEAR::isError($resNewSales))
			{
				throw new Exception($resNewSales->getError()." :: ".$resNewSales->getUserInfo());
			}
			while ($arrSale = $resNewSales->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				// Is this for a new Account?
				if ($arrSale['sale_type_id'] === SALE_TYPE_NEW_CUSTOMER)
				{
					//--------------------- ACCOUNT GROUP --------------------//
					// Yes -- Create a new AccountGroup/Account for this Customer
					$objAccountGroup			= new Account_Group();
					$objAccountGroup->CreatedBy	= 0;
					$objAccountGroup->CreatedOn	= date("Y-m-d");
					$objAccountGroup->Archived	= 0;
					$objAccountGroup->save();
					//--------------------------------------------------------//
					
					//------------------------ ACCOUNT -----------------------//
					// Get sale_account Details for this Sale
					$resSaleAccount	= $dsSalesPortal->query("SELECT sale_account.*, state_name " .
															"FROM sale_account JOIN state ON state.id = sale_account.state_id  " .
															"WHERE sale_id = {$arrSale['id']} " .
															"LIMIT 1");
					if (PEAR::isError($resSaleAccount))
					{
						throw new Exception($resSaleAccount->getError()." :: ".$resSaleAccount->getUserInfo());
					}
					$arrSaleAccount	= $resSaleAccount->fetchRow(MDB2_FETCHMODE_ASSOC);
					
					$objAccount						= new Account();
					$objAccount->BusinessName		= $arrSaleAccount['business_name'];
					$objAccount->TradingName		= $arrSaleAccount['trading_name'];
					$objAccount->ABN				= $arrSaleAccount['abn'];
					$objAccount->ACN				= $arrSaleAccount['acn'];
					$objAccount->Address1			= $arrSaleAccount['address_line_1'];
					$objAccount->Address2			= $arrSaleAccount['address_line_2'];
					$objAccount->Suburb				= $arrSaleAccount['suburb'];
					$objAccount->Postcode			= $arrSaleAccount['postcode'];
					$objAccount->State				= $arrSaleAccount['state_name'];
					$objAccount->Country			= 'AU';
					$objAccount->BillingType		= $arrSaleAccount['business_name'];
					$objAccount->CustomerGroup		= $arrSaleAccount['vendor_id'];
					$objAccount->AccountGroup		= $objAccountGroup->Id;
					$objAccount->BillingFreq		= BILLING_DEFAULT_FREQ;
					$objAccount->BillingFreqType	= BILLING_DEFAULT_FREQ_TYPE;
					$objAccount->BillingMethod		= $this->_convertSalesPortalToFlex('billdeliverytype', $arrSaleAccount['bill_delivery_type_id']);
					
					$resPaymentTerms	= $qryQuery->Execute("SELECT * FROM payment_terms WHERE customer_group_id = {$objAccount->CustomerGroup} ORDER BY id DESC LIMIT 1");
					if ($resPaymentTerms === FALSE)
					{
						throw new Exception($resPaymentTerms->Error());
					}
					elseif ($arrPaymentTerms = $resPaymentTerms->fetch_assoc())
					{
						$objAccount->BillingDate	= $arrPaymentTerms['invoice_day'];
						$objAccount->PaymentTerms	= $arrPaymentTerms['payment_terms'];
					}
					else
					{
						$objAccount->BillingDate	= 1;
						$objAccount->PaymentTerms	= 14;
					}
					
					if ($arrSaleAccount['direct_debit_type_id'])
					{
						$objAccount->BillingType		= $this->_convertSalesPortalToFlex('directdebittype', $arrSaleAccount['direct_debit_type_id']);
					}
					else
					{
						$objAccount->BillingType		= BILLING_TYPE_ACCOUNT;
					}
					
					$objAccount->CreatedBy			= 0;
					$objAccount->CreatedOn			= date("Y-m-d");
					$objAccount->DisableDDR			= 0;
					$objAccount->DisableLatePayment	= 0;
					$objAccount->Sample				= 0;
					$objAccount->Archived			= 0;
					
					$objAccount->credit_control_status			= CREDIT_CONTROL_STATUS_UP_TO_DATE;
					$objAccount->last_automatic_invoice_action	= AUTOMATIC_INVOICE_ACTION_NONE;
					$objAccount->automatic_barring_status		= AUTOMATIC_BARRING_STATUS_NONE;
					$objAccount->vip							= 0;
					
					// Save the Account
					$objAccount->save();
					//--------------------------------------------------------//
					
					//---------------------- DIRECT DEBIT --------------------//
					switch ($arrSaleAccount['direct_debit_type_id'])
					{
						// Bank Account
						case 1:
							// Get additional Bank Account Details
							$resSPBankAccount	= $dsSalesPortal->query("SELECT * " .
																	"FROM sale_account_direct_debit_bank_account " .
																	"WHERE sale_account_id = {$arrSaleAccount['id']} " .
																	"LIMIT 1");
							if (PEAR::isError($resSPBankAccount))
							{
								throw new Exception($resSPBankAccount->getError()." :: ".$resSPBankAccount->getUserInfo());
							}
							$arrSPBankAccount	= $resSPBankAccount->fetchRow(MDB2_FETCHMODE_ASSOC);
							
							// Create new DirectDebit record
							$arrBankAccount	= array();
							$arrBankAccount['AccountGroup']		= $objAccount->AccountGroup;
							$arrBankAccount['BankName']			= $arrSPBankAccount['bank_name'];
							$arrBankAccount['BSB']				= $arrSPBankAccount['bank_bsb'];
							$arrBankAccount['AccountNumber']	= $arrSPBankAccount['account_number'];
							$arrBankAccount['AccountName']		= $arrSPBankAccount['account_name'];
							$arrBankAccount['Archived']			= 0;
							$arrBankAccount['created_on']		= date("Y-m-d");
							$arrBankAccount['employee_id']		= 0;
							$resBankAccountInsert	= $insBankAccount->Execute($arrBankAccount);
							if ($resBankAccountInsert === FALSE)
							{
								throw new Exception($insBankAccount->Error());
							}
							
							$objAccount->DirectDebit	= $resBankAccountInsert;
							break;
						
						// Credit Card
						case 2:
							// Get additional Credit Card Details
							$resSPCreditCard	= $dsSalesPortal->query("SELECT * " .
																	"FROM sale_account_direct_debit_credit_card " .
																	"WHERE sale_account_id = {$arrSaleAccount['id']} " .
																	"LIMIT 1");
							if (PEAR::isError($resSPCreditCard))
							{
								throw new Exception($resSPCreditCard->getError()." :: ".$resSPCreditCard->getUserInfo());
							}
							$arrSPCreditCard	= $resSPCreditCard->fetchRow(MDB2_FETCHMODE_ASSOC);
							
							// Create new CreditCard record
							$arrCreditCard	= array();
							$arrCreditCard['AccountGroup']		= $objAccount->AccountGroup;
							$arrCreditCard['BankName']			= $arrSPBankAccount['bank_name'];
							$arrCreditCard['BSB']				= $arrSPBankAccount['bank_bsb'];
							$arrCreditCard['AccountNumber']	= $arrSPBankAccount['account_number'];
							$arrCreditCard['AccountName']		= $arrSPBankAccount['account_name'];
							$arrCreditCard['Archived']			= 0;
							$arrCreditCard['created_on']		= date("Y-m-d");
							$arrCreditCard['employee_id']		= 0;
							$resCreditCardInsert	= $insCreditCard->Execute($arrCreditCard);
							if ($resCreditCardInsert === FALSE)
							{
								throw new Exception($insCreditCard->Error());
							}
							
							$objAccount->CreditCard	= $resCreditCardInsert;
							break;
					}
					
					// Finalise Account
					$objAccount->save();
					
					// Update the SP Sale Account Remote Reference
					// TODO
					//--------------------------------------------------------//
				}
				else
				{
					// We only support New Customer Sales at the moment
					throw new Exception(GetConstantDescription($arrSale['sale_type_id'], 'sale_type')." Sales are not supported by Flex!");
				}
				
				//------------------------ CONTACT -----------------------//
				// Get the new Contacts associated with this Sale
				$resNewContacts	= $dsSalesPortal->query("SELECT contact.*, contact_title.name AS contact_title_name, contact_sale.contact_association_type_id " .
														"(FROM contact JOIN contact_sale ON contact.id = contact_sale.contact_id JOIN sale ON sale.id = contact_sale.sale_id) LEFT JOIN contact_title ON contact_title.id = contact.contact_title_id " .
														"WHERE contact.contact_reference_id IS NULL");
				if (PEAR::isError($resNewContacts))
				{
					throw new Exception($resNewContacts->getError()." :: ".$resNewContacts->getUserInfo());
				}
				while ($arrSPContact = $resNewContacts->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					// Add this Contact to Flex
					$objContact	= new Contact();
					$objContact->AccountGroup		= $objAccount->AccountGroup;
					$objContact->Title				= $arrSPContact['contact_title_name'];
					$objContact->FirstName			= $arrSPContact['first_name'];
					$objContact->LastName			= $arrSPContact['last_name'];
					$objContact->DOB				= $arrSPContact['date_of_birth'];
					$objContact->JobTitle			= $arrSPContact['position_title'];
					$objContact->Account			= $objAccount->Id;
					$objContact->CustomerContact	= 0;
					$objContact->PassWord			= $arrSPContact['password'];
					$objContact->SessionId			= '';
					$objContact->SessionExpire		= '0000-00-00 00:00:00';
					$objContact->Archived			= 0;
					
					// Get the Contact Methods from SP
					$objContact->Phone				= '';
					$objContact->Mobile				= '';
					$objContact->Fax				= '';
					$objContact->Email				= NULL;
					$resContactMethod	= $dsSalesPortal->query("SELECT * " .
																"FROM contact_method " .
																"WHERE contact_id = {$arrSPContact['id']}");
					if (PEAR::isError($resContactMethod))
					{
						throw new Exception($resContactMethod->getError()." :: ".$resContactMethod->getUserInfo());
					}
					while ($arrSPContactMethod = $resContactMethod->fetchRow(MDB2_FETCHMODE_ASSOC))
					{
						switch ($arrSPContactMethod['contact_method_type_id'])
						{
							// Email
							case 1:
								$objContact->Email	= $arrSPContactMethod['details'];
								break;
								
							// Fax
							case 2:
								$objContact->Fax	= $arrSPContactMethod['details'];
								break;
								
							// Phone
							case 3:
								$objContact->Phone	= $arrSPContactMethod['details'];
								break;
								
							// Mobile
							case 4:
								$objContact->Mobile	= $arrSPContactMethod['details'];
								break;
						}
					}
					
					// Save the Flex Contact
					$objContact->save();
					
					// Update the SP Contact Remote Reference
					// TODO
					
					// Is it the Primary Contact for the Account?
					if ($arrSPContact['contact_association_type_id'])
					{
						$objAccount->PrimaryContact	= $objContact->Id;
						$objAccount->save();
					}
				}
				//--------------------------------------------------------//
			}
			
			// All seems to have worked fine -- Commit the Transaction
			$dsSalesPortal->commit();
			$dacFlex->TransactionCommit();
		}
		catch (Exception $eException)
		{
			// Rollback the Transaction & passthru the Exception
			$dsSalesPortal->rollback();
			$dacFlex->TransactionRollback();
			throw $eException;
		}
	}
	//------------------------------------------------------------------------//
	
	// _convertFlexToSalesPortal()	-- Converts Values from Flex to Sales Portal Forms
	protected function _convertFlexToSalesPortal($strType, $mixFlexValue)
	{
		static	$arrConversion	= array();
		
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales_portal');
		
		switch (str_replace('_', '', strtolower($strType)))
		{		
			case 'servicetype':
			case 'servicetypeid':
				// HACK: These should work, at least for now
				$arrConversion['servicetype']	= array(
														100 => 3,
														101	=> 2,
														102 => 1,
														103 => 4
													);
				
				return $arrConversion['servicetype'][(int)$mixFlexValue];
				break;
				
			case 'planarchived':
				// HACK: These should work, at least for now
				$arrConversion['planarchived']	= array(
															0	=> 1,
															1	=> 2
														);
				
				return $arrConversion['planarchived'][(int)$mixFlexValue];
				break;
			
			default:
				throw new Exception("Unknown Flex-to-SP Conversion Type: '{$strType}'!");
				break;
		}
	}
	
	// _convertSalesPortalToFlex()	-- Converts Values from Sales Portal to Flex Forms
	protected function _convertSalesPortalToFlex($strType, $mixFlexValue)
	{
		static	$arrConversion	= array();
		
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales_portal');
		
		$strType	= str_replace('_', '', strtolower($strType));
		switch ($strType)
		{		
			case 'billdeliverytype':
				// HACK: These should work, at least for now
				$arrConversion[$strType]	= array(
														1 	=> DELIVERY_METHOD_POST,
														2	=> DELIVERY_METHOD_EMAIL
													);
				
				return $arrConversion[$strType][(int)$mixFlexValue];
				break;
				
			case 'directdebittype':
				// HACK: These should work, at least for now
				$arrConversion[$strType]	= array(
														1 	=> BILLING_TYPE_DIRECT_DEBIT,
														2	=> BILLING_TYPE_CREDIT_CARD
													);
				
				return $arrConversion[$strType][(int)$mixFlexValue];
				break;
			
			default:
				throw new Exception("Unknown Flex-to-SP Conversion Type: '{$strType}'!");
				break;
		}
	}

	function getCommandLineArguments()
	{
		return array(

			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [performs full rollout and rollback (i.e. there should be no change)]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			self::SWITCH_MODE => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Synchronisation operation to perform [PUSH|PULL|SYNC]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("PUSH","PULL","SYNC"))'
			),
		
		);
	}
}


?>

<?php


class Cli_App_Sync_SalesPortal extends Cli
{
	const	SWITCH_TEST_RUN		= "t";
	const	SWITCH_MODE	 		= "m";
	const	SWITCH_ACTION 		= "a";
	
	const	SALES_PORTAL_SYSTEM_DEALER_ID	= 1;

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
			
			$strSpecificAction	= ucwords(trim(strtolower($arrArgs[self::SWITCH_ACTION])));
			$strMode			= trim(strtolower($arrArgs[self::SWITCH_MODE]));
			switch ($strMode)
			{
				case 'push':
				case 'pull':
					$this->{'_'.$strMode.$strSpecificAction}();
					break;
				
				case 'sync':
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
			$this->log($exception->__toString());
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
		$this->log("\t* Pushing Customer Groups/Vendors from Flex to the Sales Portal...");
		
		$dsSalesPortal	= Data_Source::get('sales');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for the Sales Portal
		$dsSalesPortal->beginTransaction();
		
		try
		{
			$this->log("\t\t* Retrieving list of Flex Customer Groups...");
			
			// Get list of Customer Groups from Flex
			$arrCustomerGroups	= Customer_Group::getAll();
			foreach ($arrCustomerGroups as $objCustomerGroup)
			{
				$this->log("\t\t\t+ Id #{$objCustomerGroup->id} ({$objCustomerGroup->externalName})...");
				
				// Does this Customer Group exist in the Sales Portal?
				$resVendors	= $dsSalesPortal->query("SELECT * FROM vendor WHERE id = {$objCustomerGroup->id}");
				if (PEAR::isError($resVendors))
				{
					throw new Exception($resVendors->getMessage()." :: ".$resVendors->getUserInfo());
				}
				elseif (!($arrVendor = $resVendors->fetchRow(MDB2_FETCHMODE_ASSOC)))
				{
					$this->log("\t\t\t+ Does not exist, adding...");
					
					// No -- add it
					$resVendorInsert	= $dsSalesPortal->query("INSERT INTO vendor (id, name, description) VALUES ({$objCustomerGroup->id}, '{$objCustomerGroup->externalName}', '{$objCustomerGroup->externalName}');");
					if (PEAR::isError($resVendorInsert))
					{
						throw new Exception($resVendorInsert->getMessage()." :: ".$resVendorInsert->getUserInfo());
					}
				}
				else
				{
					$this->log("\t\t\t+ Already exists, updating...");
					
					// Yes -- update it
					$resVendorUpdate	= $dsSalesPortal->query("UPDATE vendor SET id = {$objCustomerGroup->id}, name = '{$objCustomerGroup->externalName}', description = '{$objCustomerGroup->externalName}' WHERE id = {$objCustomerGroup->id};");
					if (PEAR::isError($resVendorUpdate))
					{
						throw new Exception($resVendorUpdate->getMessage()." :: ".$resVendorUpdate->getUserInfo());
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
		$this->log("\t* Pushing Products from Flex to the Sales Portal...");
		
		$dsSalesPortal	= Data_Source::get('sales');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for the Sales Portal
		$dsSalesPortal->beginTransaction();
		
		try
		{
			$qryQuery	= new Query();
			
			$this->log("\t\t* Getting list of Rate Plans from Flex...");
			
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
					$this->log("\t\t\t+ Id# {$arrRatePlan['Id']} ({$arrRatePlan['Name']})...");
					
					// Determine the values
					$intProductVendor		= $arrRatePlan['customer_group'];
					$strProductName			= $arrRatePlan['Name'];
					$strProductDescription	= $arrRatePlan['Description'];
					$intProductType			= $this->_convertFlexToSalesPortal('servicetype', $arrRatePlan['ServiceType']);
					$intProductStatus		= $this->_convertFlexToSalesPortal('planarchived', $arrRatePlan['Archived']);
					
					// Does it already exist in the SP?
					$resProduct	= $dsSalesPortal->query("SELECT id FROM product WHERE reference = 'RatePlan.Id={$arrRatePlan['Id']}' LIMIT 1");
					if (PEAR::isError($resProduct))
					{
						throw new Exception($resProduct->getMessage()." :: ".$resProduct->getUserInfo());
					}
					if ($resProduct->numRows())
					{
						$this->log("\t\t\t\t+ Already exists, updating...");
						
						// Already Exists -- do an UPDATE
						$arrProduct			= $resProduct->fetchRow(MDB2_FETCHMODE_ASSOC);
						$strUpdateSQL		= "UPDATE product SET vendor_id = {$intProductVendor}, name = '{$strProductName}', description = '{$strProductDescription}', product_type_id = {$intProductType}, product_status_id = {$intProductStatus} " .
												"WHERE id = {$arrProduct['id']}";
						$resProductUpdate	= $dsSalesPortal->query($strUpdateSQL);
						if (PEAR::isError($resProductUpdate))
						{
							throw new Exception($resProductUpdate->getMessage()." :: ".$resProductUpdate->getUserInfo());
						}
					}
					else
					{
						$this->log("\t\t\t\t+ Does not exist, adding...");
						
						// Doesn't Exist -- do an INSERT
						$strInsertSQL		= "INSERT INTO product (	vendor_id			, name					, description					, product_type_id	, product_status_id		, reference) VALUES " .
																	"(	{$intProductVendor}	, '{$strProductName}'	, '{$strProductDescription}'	, {$intProductType}	, {$intProductStatus}	, 'RatePlan.Id={$arrRatePlan['Id']}')";
						$resProductInsert	= $dsSalesPortal->query($strInsertSQL);
						if (PEAR::isError($resProductInsert))
						{
							throw new Exception($resProductInsert->getMessage()." :: ".$resProductInsert->getUserInfo());
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
		$this->log("\t* Pushing Dealers from Flex to the Sales Portal...");
		
		$dsSalesPortal	= Data_Source::get('sales');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for the Sales Portal
		$dsSalesPortal->beginTransaction();
		
		try
		{
			$qryQuery	= new Query();
			
			$this->log("\t\t* Getting list of Flex Dealers...");
			
			// Get list of Dealers from Flex
			$resFlexDealers	= $qryQuery->Execute("SELECT * FROM dealer;");
			if ($resFlexDealers === FALSE)
			{
				throw new Exception($qryQuery->Error());
			}
			while ($arrFlexDealer = $resFlexDealers->fetch_assoc())
			{
				$this->log("\t\t\t+ Id #{$arrFlexDealer['id']} ({$arrFlexDealer['first_name']} {$arrFlexDealer['last_name']})...");
				
				//-------------------------- DEALERS -------------------------//		
				// Escape values
				foreach ($arrFlexDealer as $strField=>$mixValue)
				{
					$arrFlexDealer[$strField]	= self::_toDBValue($mixValue);
				}
				
				// Does this Dealer exist in the Sales Portal?
				$resSPDealer	= $dsSalesPortal->query("SELECT id FROM dealer WHERE id = {$arrFlexDealer['id']} LIMIT 1");
				if (PEAR::isError($resSPDealer))
				{
					throw new Exception($resSPDealer->getMessage()." :: ".$resSPDealer->getUserInfo());
				}
				if (!($arrSPDealer = $resSPDealer->fetchRow(MDB2_FETCHMODE_ASSOC)))
				{
					$this->log("\t\t\t\t+ Doesn't exit, adding...");
					
					// Doesn't exist -- INSERT
					$arrSPDealer	= $arrFlexDealer;
					
					$strInsertSQL	= "INSERT INTO dealer VALUES " .
										"( " .
										"	{$arrSPDealer['id']}, " .
										"	{$arrSPDealer['up_line_id']}," .
										"	{$arrSPDealer['username']}," .
										"	{$arrSPDealer['password']}," .
										"	{$arrSPDealer['can_verify']}," .
										"	{$arrSPDealer['first_name']}," .
										"	{$arrSPDealer['last_name']}," .
										"	{$arrSPDealer['title_id']}," .
										"	{$arrSPDealer['business_name']}," .
										"	{$arrSPDealer['trading_name']}," .
										"	{$arrSPDealer['abn']}," .
										"	{$arrSPDealer['abn_registered']}," .
										"	{$arrSPDealer['address_line_1']}," .
										"	{$arrSPDealer['address_line_2']}," .
										"	{$arrSPDealer['suburb']}," .
										"	{$arrSPDealer['state_id']}," .
										"	{$arrSPDealer['country_id']}," .
										"	{$arrSPDealer['postcode']}," .
										"	{$arrSPDealer['postal_address_line_1']}," .
										"	{$arrSPDealer['postal_address_line_2']}," .
										"	{$arrSPDealer['postal_suburb']}," .
										"	{$arrSPDealer['postal_state_id']}," .
										"	{$arrSPDealer['postal_country_id']}," .
										"	{$arrSPDealer['postal_postcode']}," .
										"	{$arrSPDealer['phone']}," .
										"	{$arrSPDealer['mobile']}," .
										"	{$arrSPDealer['fax']}," .
										"	{$arrSPDealer['email']}," .
										"	{$arrSPDealer['commission_scale']}," .
										"	{$arrSPDealer['royalty_scale']}," .
										"	{$arrSPDealer['bank_account_bsb']}," .
										"	{$arrSPDealer['bank_account_number']}," .
										"	{$arrSPDealer['bank_account_name']}," .
										"	{$arrSPDealer['gst_registered']}," .
										"	{$arrSPDealer['termination_date']}," .
										"	{$arrSPDealer['dealer_status_id']}," .
										"	{$arrSPDealer['created_on']}" .
										");";
					$resDealerInsert	= $dsSalesPortal->query($strInsertSQL);
					if (PEAR::isError($resDealerInsert))
					{
						throw new Exception($resDealerInsert->getMessage()." :: ".$resDealerInsert->getUserInfo());
					}
				}
				else
				{
					$this->log("\t\t\t\t+ Already exits, updating...");
					// Does exist -- UPDATE
					$arrSPDealer	= $arrFlexDealer;
					
					$strUpdateSQL	= "UPDATE dealer SET " .
										"	up_line_id				= {$arrSPDealer['up_line_id']}," .
										"	username				= {$arrSPDealer['username']}," .
										"	password				= {$arrSPDealer['password']}," .
										"	can_verify				= {$arrSPDealer['can_verify']}," .
										"	first_name				= {$arrSPDealer['first_name']}," .
										"	last_name				= {$arrSPDealer['last_name']}," .
										"	title_id				= {$arrSPDealer['title_id']}," .
										"	business_name			= {$arrSPDealer['business_name']}," .
										"	trading_name			= {$arrSPDealer['trading_name']}," .
										"	abn						= {$arrSPDealer['abn']}," .
										"	abn_registered			= {$arrSPDealer['abn_registered']}," .
										"	address_line_1			= {$arrSPDealer['address_line_1']}," .
										"	address_line_2			= {$arrSPDealer['address_line_2']}," .
										"	suburb					= {$arrSPDealer['suburb']}," .
										"	state_id				= {$arrSPDealer['state_id']}," .
										"	country_id				= {$arrSPDealer['country_id']}," .
										"	postcode				= {$arrSPDealer['postcode']}," .
										"	postal_address_line_1	= {$arrSPDealer['postal_address_line_1']}," .
										"	postal_address_line_2	= {$arrSPDealer['postal_address_line_2']}," .
										"	postal_suburb			= {$arrSPDealer['postal_suburb']}," .
										"	postal_state_id			= {$arrSPDealer['postal_state_id']}," .
										"	postal_country_id		= {$arrSPDealer['postal_country_id']}," .
										"	postal_postcode			= {$arrSPDealer['postal_postcode']}," .
										"	phone					= {$arrSPDealer['phone']}," .
										"	mobile					= {$arrSPDealer['mobile']}," .
										"	fax						= {$arrSPDealer['fax']}," .
										"	email					= {$arrSPDealer['email']}," .
										"	commission_scale		= {$arrSPDealer['commission_scale']}," .
										"	royalty_scale			= {$arrSPDealer['royalty_scale']}," .
										"	bank_account_bsb		= {$arrSPDealer['bank_account_bsb']}," .
										"	bank_account_number		= {$arrSPDealer['bank_account_number']}," .
										"	bank_account_name		= {$arrSPDealer['bank_account_name']}," .
										"	gst_registered			= {$arrSPDealer['gst_registered']}," .
										"	termination_date		= {$arrSPDealer['termination_date']}," .
										"	dealer_status_id		= {$arrSPDealer['dealer_status_id']}," .
										"	created_on				= {$arrSPDealer['created_on']} " .
										"WHERE id = {$arrSPDealer['id']};";
					$resDealerUpdate	= $dsSalesPortal->query($strUpdateSQL);
					if (PEAR::isError($resDealerUpdate))
					{
						throw new Exception($resDealerUpdate->getMessage()." :: ".$resDealerUpdate->getUserInfo());
					}
				}
				//------------------------------------------------------------//
			}
			
			//----------------------- DEALER >> PRODUCTS ---------------------//
			$this->log("\t\t* Recreating Dealers >> Products relationships...");
			// Truncate the SP Table
			$resDealerProductTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_product");
			if (PEAR::isError($resDealerProductTruncate))
			{
				throw new Exception($resDealerProductTruncate->getMessage()." :: ".$resDealerProductTruncate->getUserInfo());
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
					throw new Exception($resDealerProductInsert->getMessage()." :: ".$resDealerProductInsert->getUserInfo());
				}
			}
			//----------------------------------------------------------------//
			
			//---------------------- DEALER >> SALE TYPE ---------------------//
			$this->log("\t\t* Recreating Dealers >> Sale Types relationships...");
			// Truncate the SP Table
			$resDealerSaleTypeTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_sale_type");
			if (PEAR::isError($resDealerSaleTypeTruncate))
			{
				throw new Exception($resDealerSaleTypeTruncate->getMessage()." :: ".$resDealerSaleTypeTruncate->getUserInfo());
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
					throw new Exception($resDealerSaleTypeInsert->getMessage()." :: ".$resDealerSaleTypeInsert->getUserInfo());
				}
			}
			//----------------------------------------------------------------//
			
			//------------------------ DEALER >> VENDOR ----------------------//
			$this->log("\t\t* Recreating Dealers >> Vendors relationships...");
			// Truncate the SP Table
			$resDealerVendorTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_vendor");
			if (PEAR::isError($resDealerVendorTruncate))
			{
				throw new Exception($resDealerVendorTruncate->getMessage()." :: ".$resDealerVendorTruncate->getUserInfo());
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
					throw new Exception($resDealerVendorInsert->getMessage()." :: ".$resDealerVendorInsert->getUserInfo());
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
		$this->log("\t* Pulling Sales from the Sales Portal to Flex...");
		
		$dsSalesPortal	= Data_Source::get('sales');
		$dacFlex		= DataAccess::getDataAccess();
		
		// Start a transaction for both Databases
		$dsSalesPortal->beginTransaction();
		$dacFlex->TransactionStart();
		
		try
		{
			$qryQuery		= new Query();
			$insBankAccount	= new StatementInsert("DirectDebit");
			$insCreditCard	= new StatementInsert("CreditCard");
			
			$this->log("\t\t* Getting a list of New Sales from the Sales Portal...");
			
			// Get a list of New Sales from the SP
			// FIXME: When the sale_status.const_name field is added, use it instead of sale_status.name
			$resNewSales	= $dsSalesPortal->query("SELECT sale.* " .
													"FROM sale JOIN sale_status ON sale.sale_status_id = sale_status.id " .
													"WHERE sale_status.name = 'Ready For Provisioning'");
			if (PEAR::isError($resNewSales))
			{
				throw new Exception($resNewSales->getMessage()." :: ".$resNewSales->getUserInfo());
			}
			while ($arrSale = $resNewSales->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$this->log("\t\t* Sale Id #{$arrSale['id']}...");
				
				// Created a new Savepoint for this Sale
				$strSaleSavePoint	= "Flex_SP_Pull_Sale_{$arrSale['id']}";
				if ($qryQuery->Execute("SAVEPOINT {$strSaleSavePoint}") === FALSE)
				{
					throw new Exception($qryQuery->Error());
				}
				$resCreateSavepoint	= $dsSalesPortal->query("SAVEPOINT {$strSaleSavePoint}");
				if (PEAR::isError($resCreateSavepoint))
				{
					throw new Exception($resCreateSavepoint->getMessage()." :: ".$resCreateSavepoint->getUserInfo());
				}
				
				try
				{
					// Is this for a new Account?
					if ($arrSale['sale_type_id'] == 1)
					{
						$this->log("\t\t\t+ Creating new Account...");
						
						//--------------------- ACCOUNT GROUP --------------------//
						// Yes -- Create a new AccountGroup/Account for this Customer
						$objAccountGroup			= new Account_Group();
						$objAccountGroup->CreatedBy	= 0;
						$objAccountGroup->CreatedOn	= $arrSale['created_on'];
						$objAccountGroup->Archived	= 0;
						$objAccountGroup->save();
						//--------------------------------------------------------//
						
						//------------------------ ACCOUNT -----------------------//
						// Get sale_account Details for this Sale
						$resSaleAccount	= $dsSalesPortal->query("SELECT sale_account.*, state.name AS state_name " .
																"FROM sale_account JOIN state ON state.id = sale_account.state_id  " .
																"WHERE sale_id = {$arrSale['id']} " .
																"LIMIT 1");
						if (PEAR::isError($resSaleAccount))
						{
							throw new Exception($resSaleAccount->getMessage()." :: ".$resSaleAccount->getUserInfo());
						}
						$arrSaleAccount	= $resSaleAccount->fetchRow(MDB2_FETCHMODE_ASSOC);
						
						$objAccount						= new Account();
						$objAccount->BusinessName		= ($arrSaleAccount['business_name']) ? $arrSaleAccount['business_name'] : '';
						$objAccount->TradingName		= ($arrSaleAccount['trading_name']) ? $arrSaleAccount['trading_name'] : '';
						$objAccount->ABN				= ($arrSaleAccount['abn']) ? $arrSaleAccount['abn'] : '';
						$objAccount->ACN				= ($arrSaleAccount['acn']) ? $arrSaleAccount['acn'] : '';
						$objAccount->Address1			= $arrSaleAccount['address_line_1'];
						$objAccount->Address2			= ($arrSaleAccount['address_line_2']) ? $arrSaleAccount['address_line_2'] : '';
						$objAccount->Suburb				= $arrSaleAccount['suburb'];
						$objAccount->Postcode			= $arrSaleAccount['postcode'];
						$objAccount->State				= $arrSaleAccount['state_name'];
						$objAccount->Country			= 'AU';
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
						$objAccount->CreatedOn			= $arrSale['created_on'];
						$objAccount->DisableDDR			= 0;
						$objAccount->DisableLatePayment	= 0;
						$objAccount->DisableLateNotices	= 0;
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
								$this->log("\t\t\t\t+ Adding Bank Account...");
								// Get additional Bank Account Details
								$resSPBankAccount	= $dsSalesPortal->query("SELECT * " .
																		"FROM sale_account_direct_debit_bank_account " .
																		"WHERE sale_account_id = {$arrSaleAccount['id']} " .
																		"LIMIT 1");
								if (PEAR::isError($resSPBankAccount))
								{
									throw new Exception($resSPBankAccount->getMessage()." :: ".$resSPBankAccount->getUserInfo());
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
								$arrBankAccount['created_on']		= $arrSale['created_on'];
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
								$this->log("\t\t\t\t+ Adding Credit Card...");
								// Get additional Credit Card Details
								$resSPCreditCard	= $dsSalesPortal->query("SELECT * " .
																		"FROM sale_account_direct_debit_credit_card " .
																		"WHERE sale_account_id = {$arrSaleAccount['id']} " .
																		"LIMIT 1");
								if (PEAR::isError($resSPCreditCard))
								{
									throw new Exception($resSPCreditCard->getMessage()." :: ".$resSPCreditCard->getUserInfo());
								}
								$arrSPCreditCard	= $resSPCreditCard->fetchRow(MDB2_FETCHMODE_ASSOC);
								
								// Create new CreditCard record
								$arrCreditCard	= array();
								$arrCreditCard['AccountGroup']	= $objAccount->AccountGroup;
								$arrCreditCard['CardType']		= $arrSPCreditCard['credit_card_type_id'];
								$arrCreditCard['Name']			= $arrSPCreditCard['card_name'];
								$arrCreditCard['CardNumber']	= $arrSPCreditCard['card_number'];
								$arrCreditCard['ExpMonth']		= str_pad($arrSPCreditCard['expiry_month'], 2, '0', STR_PAD_LEFT);
								$arrCreditCard['ExpYear']		= (string)$arrSPCreditCard['expiry_year'];
								$arrCreditCard['CVV']			= $arrSPCreditCard['cvv'];
								$arrCreditCard['Archived']		= 0;
								$arrCreditCard['created_on']	= $arrSale['created_on'];
								$arrCreditCard['employee_id']	= 0;
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
						
						$this->log("\t\t\t\t+ Updating Sales Portal Remote Reference to {$objAccount->Id}...");
						// Update the SP Sale Account Remote Reference
						$resSPSaleAccount	= $dsSalesPortal->query("UPDATE sale_account SET reference_id = {$objAccount->Id} WHERE id = {$arrSaleAccount['id']}");
						if (PEAR::isError($resSPSaleAccount))
						{
							throw new Exception($resSPSaleAccount->getMessage()." :: ".$resSPSaleAccount->getUserInfo());
						}
						//--------------------------------------------------------//
					}
					else
					{
						// We only support New Customer Sales at the moment
						throw new Exception("'{$arrSale['sale_type_id']}' Sales are not supported by Flex!");
					}
					
					$this->log("\t\t\t* Getting list of new Contacts...");
					//-------------------------- CONTACT -------------------------//
					// Get the new Contacts associated with this Sale
					$resNewContacts	= $dsSalesPortal->query("SELECT contact.*, contact_title.name AS contact_title_name, contact_sale.contact_association_type_id " .
															"FROM (contact JOIN contact_sale ON contact.id = contact_sale.contact_id JOIN sale ON sale.id = contact_sale.sale_id) LEFT JOIN contact_title ON contact_title.id = contact.contact_title_id " .
															"WHERE contact.contact_reference_id IS NULL");
					if (PEAR::isError($resNewContacts))
					{
						throw new Exception($resNewContacts->getMessage()." :: ".$resNewContacts->getUserInfo());
					}
					while ($arrSPContact = $resNewContacts->fetchRow(MDB2_FETCHMODE_ASSOC))
					{
						$this->log("\t\t\t\t+ Adding a new Contact for Account #{$objAccount->Id}...");
						
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
						$objContact->PassWord			= ($arrSPContact['password']) ? $arrSPContact['password'] : '';
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
							throw new Exception($resContactMethod->getMessage()." :: ".$resContactMethod->getUserInfo());
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
						$this->log("\t\t\t\t\t+ Updating Sales Portal Remote Reference to {$objContact->Id}...");
						$resSPContact	= $dsSalesPortal->query("UPDATE contact SET reference_id = {$objContact->Id} WHERE id = {$arrSPContact['id']}");
						if (PEAR::isError($resSPContact))
						{
							throw new Exception($resSPContact->getMessage()." :: ".$resSPContact->getUserInfo());
						}
						
						// Is it the Primary Contact for the Account?
						if ($arrSPContact['contact_association_type_id'] === 1)
						{
							$this->log("\t\t\t\t\t+ Setting as {$objAccount->Id}'s Primary Contact...");
							$objAccount->PrimaryContact	= $objContact->Id;
							$objAccount->save();
						}
					}
					//------------------------------------------------------------//
					
					//------------------------ SALE ITEMS ------------------------//
					$this->log("\t\t\t* Getting list of Items sold...");
					
					// Get all items that were sold
					$resSPSaleItems	= $dsSalesPortal->query("SELECT sale_item.*, product.product_type_id, product_type.product_category_id " .
															"FROM sale_item JOIN product ON sale_item.product_id = product.id JOIN product_type ON product_type.id = product.product_type_id " .
															"WHERE sale_id = {$arrSale['id']} AND sale_item_status_id = 5");
					if (PEAR::isError($resSPSaleItems))
					{
						throw new Exception($resSPSaleItems->getMessage()." :: ".$resSPSaleItems->getUserInfo());
					}
					while ($arrSPSaleItem = $resSPSaleItems->fetchRow(MDB2_FETCHMODE_ASSOC))
					{
						$this->log("\t\t\t\t* Adding Sale Item #{$arrSPSaleItem['id']}...");
						
						// Create a Savepoint for this Sale Item
						$strSaleItemSavePoint	= "Flex_SP_Pull_Sale_Item_{$arrSPSaleItem['id']}";
						if ($qryQuery->Execute("SAVEPOINT {$strSaleItemSavePoint}") === FALSE)
						{
							throw new Exception($qryQuery->Error());
						}
						$resCreateSavepoint	= $dsSalesPortal->query("SAVEPOINT {$strSaleItemSavePoint}");
						if (PEAR::isError($resCreateSavepoint))
						{
							throw new Exception($resCreateSavepoint->getMessage()." :: ".$resCreateSavepoint->getUserInfo());
						}
						
						try
						{
							// What Category of Product is the item?
							switch ($arrSPSaleItem['product_category_id'])
							{
								// Service
								case 1:
									$this->log("\t\t\t\t\t+ Adding new Service to Account {$objAccount->Id}...");
									
									// Create a new Service
									$objService	= new Service();
									$objService->Account		= $objAccount->Id;
									$objService->AccountGroup	= $objAccount->AccountGroup;
									
									// Defaults
									$objService->Indial100			= 0;
									$objService->CappedCharge		= 0;
									$objService->UncappedCharge		= 0;
									$objService->CreatedBy			= 0;
									$objService->NatureOfCreation	= SERVICE_CREATION_NEW;
									$objService->ForceInvoiceRender	= 0;
									$objService->Status				= SERVICE_ACTIVE;
									$objService->Dealer				= $arrSale['dealer_id'];
									$objService->Cost				= 0;
									
									// Get the Sale Status Date
									$resSPSaleItemStatusDate	= $dsSalesPortal->query("SELECT changed_on " .
																						"FROM sale_item_status_history " .
																						"WHERE sale_item_id = {$arrSPSaleItem['id']} " .
																						"ORDER BY changed_on DESC " .
																						"LIMIT 1");
									if (PEAR::isError($resSPSaleItemStatusDate))
									{
										throw new Exception($resSPSaleItemStatusDate->getMessage()." :: ".$resSPSaleItemStatusDate->getUserInfo());
									}
									$arrSPSaleItemStatusDate	= $resSPSaleItemStatusDate->fetchRow(MDB2_FETCHMODE_ASSOC);
									$objService->CreatedOn		= $arrSPSaleItemStatusDate['changed_on'];
									
									// Additional Details -- What product type is this?
									$insAdditionalDetails	= NULL;
									$arrAdditionalDetails	= NULL;
									switch ($arrSPSaleItem['product_type_id'])
									{
										// Land Line
										case 1:
											// Get the additional Land Line details
											$insAdditionalDetails	= new StatementInsert("ServiceAddress");
											$resSPLandLineDetails	= $dsSalesPortal->query("SELECT * " .
																							"FROM sale_item_service_landline " .
																							"WHERE sale_item_id = {$arrSPSaleItem['id']} " .
																							"LIMIT 1");
											if (PEAR::isError($resSPLandLineDetails))
											{
												throw new Exception($resSPLandLineDetails->getMessage()." :: ".$resSPLandLineDetails->getUserInfo());
											}
											$arrSPLandLineDetails	= $resSPLandLineDetails->fetchRow(MDB2_FETCHMODE_ASSOC);
											
											// Service Details
											$objService->FNN			= $arrSPLandLineDetails['fnn'];
											$objService->ServiceType	= SERVICE_TYPE_LAND_LINE;
											$objService->Indial100		= $arrSPLandLineDetails['is_indial_100'];
											$objService->ELB			= $arrSPLandLineDetails['has_extension_level_billing'];
											
											// Service Address Details
											$arrAdditionalDetails	= array();
											$arrAdditionalDetails['AccountGroup']	= $objService->AccountGroup;
											$arrAdditionalDetails['Account']		= $objService->Account;
											$arrAdditionalDetails['Service']		= $objService->Id;
											$arrAdditionalDetails['BillName']		= $arrSPLandLineDetails['bill_name'];
											$arrAdditionalDetails['BillAddress1']	= $arrSPLandLineDetails['bill_address_line_1'];
											$arrAdditionalDetails['BillAddress2']	= $arrSPLandLineDetails['bill_address_line_2'];
											$arrAdditionalDetails['BillLocality']	= $arrSPLandLineDetails['bill_locality'];
											$arrAdditionalDetails['BillPostcode']	= $arrSPLandLineDetails['bill_name'];
											
											$arrAdditionalDetails['ServiceAddressType']			= $this->_salesPortalEnum('landline_service_address_type', $arrSPLandLineDetails['landline_service_address_type_id']);
											$arrAdditionalDetails['ServiceAddressTypeNumber']	= $arrSPLandLineDetails['service_address_type_number'];
											$arrAdditionalDetails['ServiceAddressTypeSuffix']	= $arrSPLandLineDetails['service_address_type_suffix'];
											$arrAdditionalDetails['ServiceStreetNumberStart']	= $arrSPLandLineDetails['service_street_number_start'];
											$arrAdditionalDetails['ServiceStreetNumberEnd']		= $arrSPLandLineDetails['service_street_number_end'];
											$arrAdditionalDetails['ServiceStreetNumberSuffix']	= $arrSPLandLineDetails['service_street_number_suffix'];
											$arrAdditionalDetails['ServiceStreetName']			= $arrSPLandLineDetails['service_street_name'];
											$arrAdditionalDetails['ServiceStreetType']			= $this->_salesPortalEnum('landline_service_street_type', $arrSPLandLineDetails['landline_service_street_type_id']);
											$arrAdditionalDetails['ServiceStreetTypeSuffix']	= $this->_salesPortalEnum('landline_service_street_type_suffix', $arrSPLandLineDetails['landline_service_street_type_suffix_id']);
											$arrAdditionalDetails['ServicePropertyName']		= $arrSPLandLineDetails['service_property_name'];
											$arrAdditionalDetails['ServiceLocality']			= $arrSPLandLineDetails['service_locality'];
											$arrAdditionalDetails['ServiceState']				= $this->_salesPortalEnum('landline_service_state', $arrSPLandLineDetails['landline_service_state_id'], 'code');
											$arrAdditionalDetails['ServicePostcode']			= $arrSPLandLineDetails['service_postcode'];
											
											switch ($arrSPLandLineDetails['landline_type_id'])
											{
												// Residential
												case 1:
													$arrAdditionalDetails['Residential']		= 1;
													$arrAdditionalDetails['EndUserTitle']		= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['EndUserGivenName']	= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['EndUserFamilyName']	= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['EndUserCompanyName']	= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['DateOfBirth']		= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['Employer']			= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['Occupation']			= $arrSPLandLineDetails['bill_name'];
													break;
												
												// Business
												case 2:
													$arrAdditionalDetails['Residential']		= 0;
													$arrAdditionalDetails['EndUserCompanyName']	= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['ABN']				= $arrSPLandLineDetails['bill_name'];
													$arrAdditionalDetails['TradingName']		= $arrSPLandLineDetails['bill_name'];
													break;
													
												default:
													throw new Exception("'{$arrSPLandLineDetails['landline_type_id']}' is not a valid Land Line Type!");
													break;
											}
											break;
											
										// Mobile
										case 2:
											// Get the additional Mobile details
											$insAdditionalDetails	= new StatementInsert("ServiceMobileDetail");
											$resSPMobileDetails		= $dsSalesPortal->query("SELECT * " .
																							"FROM sale_item_service_mobile " .
																							"WHERE sale_item_id = {$arrSPSaleItem['id']} " .
																							"LIMIT 1");
											if (PEAR::isError($resSPMobileDetails))
											{
												throw new Exception($resSPMobileDetails->getMessage()." :: ".$resSPMobileDetails->getUserInfo());
											}
											$arrSPMobileDetails		= $resSPMobileDetails->fetchRow(MDB2_FETCHMODE_ASSOC);
											
											// Service Details
											$objService->FNN			= $arrSPMobileDetails['fnn'];
											$objService->ServiceType	= SERVICE_TYPE_MOBILE;
											
											// Mobile Details
											$arrAdditionalDetails	= array();
											$arrAdditionalDetails['AccountGroup']	= $objService->AccountGroup;
											$arrAdditionalDetails['Account']		= $objService->Account;
											$arrAdditionalDetails['Service']		= $objService->Id;
											$arrAdditionalDetails['SimPUK']			= ($arrSPMobileDetails['sim_puk']) ? $arrSPMobileDetails['sim_puk'] : '';
											$arrAdditionalDetails['SimESN']			= '';
											$arrAdditionalDetails['SimState']		= ($arrSPMobileDetails['sim_state_id']) ? $this->_salesPortalEnum('state', $arrSPMobileDetails['sim_state_id'], 'code') : '';
											$arrAdditionalDetails['DOB']			= ($arrSPMobileDetails['dob']) ? $arrSPMobileDetails['dob'] : '0000-00-00';
											$arrAdditionalDetails['Comments']		= ($arrSPMobileDetails['comments']) ? $arrSPMobileDetails['comments'] : '';
											break;
											
										// ADSL
										case 3:
											// Get the additional ADSL details
											$resSPADSLDetails		= $dsSalesPortal->query("SELECT * " .
																							"FROM sale_item_service_inbound " .
																							"WHERE sale_item_id = {$arrSPSaleItem['id']} " .
																							"LIMIT 1");
											if (PEAR::isError($resSPADSLDetails))
											{
												throw new Exception($resSPADSLDetails->getMessage()." :: ".$resSPADSLDetails->getUserInfo());
											}
											$arrSPADSLDetails		= $resSPADSLDetails->fetchRow(MDB2_FETCHMODE_ASSOC);
											
											// Service Details
											$objService->FNN			= $arrSPADSLDetails['fnn'];
											$objService->ServiceType	= SERVICE_TYPE_ADSL;
											break;
											
										// Inbound
										case 4:
											// Get the additional Inbound details
											$insAdditionalDetails	= new StatementInsert("ServiceInboundDetail");
											$resSPInboundDetails	= $dsSalesPortal->query("SELECT * " .
																							"FROM sale_item_service_inbound " .
																							"WHERE sale_item_id = {$arrSPSaleItem['id']} " .
																							"LIMIT 1");
											if (PEAR::isError($resSPInboundDetails))
											{
												throw new Exception($resSPInboundDetails->getMessage()." :: ".$resSPInboundDetails->getUserInfo());
											}
											$arrSPInboundDetails	= $resSPInboundDetails->fetchRow(MDB2_FETCHMODE_ASSOC);
											
											// Service Details
											$objService->FNN			= $arrSPInboundDetails['fnn'];
											$objService->ServiceType	= SERVICE_TYPE_INBOUND;
											
											// Mobile Details
											$arrAdditionalDetails	= array();
											$arrAdditionalDetails['Service']		= $objService->Id;
											$arrAdditionalDetails['AnswerPoint']	= ($arrSPInboundDetails['answer_point']) ? $arrSPInboundDetails['answer_point'] : '';
											$arrAdditionalDetails['Complex']		= $arrSPInboundDetails['has_complex_configuration'];
											$arrAdditionalDetails['Configuration']	= $arrSPInboundDetails['configuration'];
											break;
									}
									$this->log("\t\t\t\t\t\t* FNN: {$objService->FNN}");
									$this->log("\t\t\t\t\t\t* Service Type: {$objService->ServiceType}");
									
									// Is the FNN in use already?
									$this->log("\t\t\t\t\t\t* Checking if FNN is in use...");
									if (IsFNNInUse($objService->FNN, $objService->Indial100, $objService->CreatedOn))
									{
										$this->log("\t\t\t\t\t\t\t! FNN is in use!  Aborting Sale...");
										throw new Exception_Sale_Product_Manual_Intervention("The FNN {$objService->FNN} is already in use.  Please close the existing Service in Flex, or revoke the sale if it a duplicate.");
									}
									
									// Save the Service
									$objService->save();
									
									// Extension Level Billing
									if ($objService->Indial100 && $objService->ELB)
									{
										$this->log("\t\t\t\t\t\t+ Enabling Extension Level Billing...");
										$GLOBALS['fwkFramework']->EnableELB($objService->Id);
									}
									
									// Set Service Plan
									$resProduct	= $dsSalesPortal->query("SELECT reference FROM product WHERE id = {$arrSPSaleItem['product_id']}");
									if (PEAR::isError($resProduct))
									{
										throw new Exception($resProduct->getMessage()." :: ".$resProduct->getUserInfo());
									}
									$arrProduct		= $resProduct->fetchRow(MDB2_FETCHMODE_ASSOC);
									$arrRatePlanId	= explode('=', $arrProduct['reference']);
									$objRatePlan	= new Rate_Plan(Array('Id'=>(int)$arrRatePlanId[1]));
									$this->log("\t\t\t\t\t\t+ Setting Plan to '{$objRatePlan->Name}'...");									
									$objService->changePlan($objRatePlan);
									
									// Perform Automatic Provisioning
									$this->log("\t\t\t\t\t\t+ Automatically Provisioning...");
									if ($objService->ServiceType === SERVICE_TYPE_LAND_LINE)
									{
										// Full Service
										$this->log("\t\t\t\t\t\t\t+ Adding Full Service Request...");
										$objFullServiceRequest	= new Provisioning_Request();
										$objFullServiceRequest->AccountGroup		= $objService->AccountGroup;
										$objFullServiceRequest->Account				= $objService->Account;
										$objFullServiceRequest->Service				= $objService->Id;
										$objFullServiceRequest->FNN					= $objService->FNN;
										$objFullServiceRequest->Employee			= 0;
										$objFullServiceRequest->Carrier				= $objRatePlan->CarrierFullService;
										$objFullServiceRequest->Type				= PROVISIONING_TYPE_FULL_SERVICE;
										$objFullServiceRequest->RequestedOn			= date("Y-m-d H:i:s");
										$objFullServiceRequest->AuthorisationDate	= $objService->CreatedOn;
										$objFullServiceRequest->Status				= REQUEST_STATUS_WAITING;
										$objFullServiceRequest->save();
										
										// Preselection
										$this->log("\t\t\t\t\t\t\t+ Adding Preselection Request...");
										$objPreselectionRequest	= new Provisioning_Request();
										$objPreselectionRequest->AccountGroup		= $objService->AccountGroup;
										$objPreselectionRequest->Account			= $objService->Account;
										$objPreselectionRequest->Service			= $objService->Id;
										$objPreselectionRequest->FNN				= $objService->FNN;
										$objPreselectionRequest->Employee			= 0;
										$objPreselectionRequest->Carrier			= $objRatePlan->CarrierPreselection;
										$objPreselectionRequest->Type				= PROVISIONING_TYPE_PRESELECTION;
										$objPreselectionRequest->RequestedOn		= date("Y-m-d H:i:s");
										$objPreselectionRequest->AuthorisationDate	= $objService->CreatedOn;
										$objPreselectionRequest->Status				= REQUEST_STATUS_WAITING;
										$objPreselectionRequest->save();
									}
									else
									{
										$this->log("\t\t\t\t\t\t\t! Automatic Provisioning is not supported for ".GetConstantDescription($objService->ServiceType, 'service_type')."s");
									}
									break;
								
								// Unknown
								default:
									throw new Exception("Product Category '{$arrSPSaleItem['product_category_id']}' is unsupported by Flex!");
							}
							
							// Set Item Status to Provisioned
							$this->_updateSaleItemStatus($arrSPSaleItem['id'], 'Provisioned');
						
							// All seems to have worked, release Sale Item the savepoints
							if ($qryQuery->Execute("RELEASE SAVEPOINT {$strSaleItemSavePoint}") === FALSE)
							{
								throw new Exception($qryQuery->Error());
							}
							$resReleaseSavepoint	= $dsSalesPortal->query("RELEASE SAVEPOINT {$strSaleItemSavePoint}");
							if (PEAR::isError($resReleaseSavepoint))
							{
								throw new Exception($resReleaseSavepoint->getMessage()." :: ".$resReleaseSavepoint->getUserInfo());
							}
						}
						catch(Exception_Sale_Product_Manual_Intervention $eException)
						{
							$this->log("\t\t\t\t\t\t\t! Setting Sale Product Status to Manual Intervention...");
							
							// There was an issue with one of the Items which needs manual intervention to resolve
							$this->_updateSaleItemStatus($arrSPSaleItem['id'], 'Manual Intervention', $eException->getMessage());
							
							// Revoke the whole Sale
							throw $eException;
						}
					}
					//------------------------------------------------------------//
					
					// Set Sale Status to Provisioned
					$this->_updateSaleStatus($arrSale['id'], 'Provisioned');
					
					// All seems to have worked, release the Sale savepoints
					if ($qryQuery->Execute("RELEASE SAVEPOINT {$strSaleSavePoint}") === FALSE)
					{
						throw new Exception($qryQuery->Error());
					}
					$resReleaseSavepoint	= $dsSalesPortal->query("RELEASE SAVEPOINT {$strSaleSavePoint}");
					if (PEAR::isError($resReleaseSavepoint))
					{
						throw new Exception($resReleaseSavepoint->getMessage()." :: ".$resReleaseSavepoint->getUserInfo());
					}
				}
				catch (Exception_Sale_Manual_Intervention $eException)
				{
					// Rollback to the savepoint for this Sale
					if ($qryQuery->Execute("ROLLBACK TO {$strSaleSavePoint}") === FALSE)
					{
						throw new Exception($qryQuery->Error());
					}
					$resRollbackSavepoint	= $dsSalesPortal->query("ROLLBACK TO {$strSaleSavePoint}");
					if (PEAR::isError($resRollbackSavepoint))
					{
						throw new Exception($resRollbackSavepoint->getMessage()." :: ".$resRollbackSavepoint->getUserInfo());
					}
					
					// There was an issue with the Sale which needs manual intervention to resolve
					$this->_updateSaleStatus($arrSale['id'], 'Manual Intervention', $eException->getMessage());
				}
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
	protected static function _convertFlexToSalesPortal($strType, $mixFlexValue)
	{
		static	$arrConversion	= array();
		
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales');
		
		$strType	= str_replace('_', '', strtolower($strType));
		switch ($strType)
		{		
			case 'servicetype':
				// HACK: These should work, at least for now
				$arrConversion[$strType]	= array(
														100 => 3,
														101	=> 2,
														102 => 1,
														103 => 4
													);
				
				return $arrConversion[$strType][(int)$mixFlexValue];
				break;
				
			case 'planarchived':
				// HACK: These should work, at least for now
				$arrConversion[$strType]	= array(
														0	=> 1,
														1	=> 2
													);
				
				return $arrConversion[$strType][(int)$mixFlexValue];
				break;
			
			default:
				throw new Exception("Unknown Flex-to-SP Conversion Type: '{$strType}'!");
				break;
		}
	}
	
	// _convertSalesPortalToFlex()	-- Converts Values from Sales Portal to Flex Forms
	protected static function _convertSalesPortalToFlex($strType, $mixFlexValue)
	{
		static	$arrConversion	= array();
		
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales');
		
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
				
			case 'producttype':
				// HACK: These should work, at least for now
				$arrConversion[$strType]	= array(
														3	=> 100,
														2	=> 101,
														1	=> 102,
														4	=> 103
													);
				
				return $arrConversion[$strType][(int)$mixFlexValue];
				break;
			
			default:
				throw new Exception("Unknown Flex-to-SP Conversion Type: '{$strType}'!");
				break;
		}
	}
	
	// _salesPortalEnum()	-- Gets the Name/Description for a given table/id pair from the Sales Portal
	private static function _salesPortalEnum($strTable, $intId, $strMode='name')
	{
		static	$arrEnumerations	= array();
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales');
		
		// If it isn't cached, then retrieve the enumeration data
		if (!isset($arrEnumerations[$strTable]))
		{
			$resSPEnumeration	= $dsSalesPortal->query("SELECT * FROM {$strTable};");
			if (PEAR::isError($resSPEnumeration))
			{
				throw new Exception($resSPEnumeration->getMessage()." :: ".$resSPEnumeration->getUserInfo());
			}
			else
			{
				while ($arrPair = $resSPEnumeration->fetchRow())
				{
					$arrEnumerations[$strTable][$arrPair['id']]	= $arrPair;
				}
			}
		}
		
		// Return the Value
		return $arrEnumerations[$strTable][$arrPair['id']][$strMode];
	}
	
	// _updateSaleHistory()	-- Updates the Sale Status (and History) in the Sales Portal
	private static function _updateSaleStatus($intSPSaleId, $mixNewStatus, $strReason=NULL)
	{
		// Save the new Status
		$strSaleStatus	= (is_int($mixNewStatus)) ? $mixNewStatus : "(SELECT id FROM sale_status WHERE name = '{$mixNewStatus}')";
		$resSaleUpdate	= $dsSalesPortal->query("UPDATE sale " .
												"SET sale_status_id = {$strSaleStatus} " .
												"WHERE id = {$intSPSaleItemId} " .
												"LIMIT 1");
		if (PEAR::isError($resSaleUpdate))
		{
			throw new Exception($resSaleUpdate->getMessage()." :: ".$resSaleUpdate->getUserInfo());
		}
		
		// Update the History
		$strReasonSQL			= ($strReason === NULL) ? 'NULL' : "'".str_replace("'", "\\'", $strReason)."'";
		$resSaleStatusInsert	= $dsSalesPortal->query("INSERT INTO sale_status_history (sale_id, sale_status_id, changed_on, changed_by, description) " .
															"SELECT id, sale_status_id, DEFAULT, ".self::SALES_PORTAL_SYSTEM_DEALER_ID.", {$strReasonSQL} FROM sale WHERE id = {$intSPSaleId}");
		if (PEAR::isError($resSaleStatusInsert))
		{
			throw new Exception($resSaleStatusInsert->getMessage()." :: ".$resSaleStatusInsert->getUserInfo());
		}
	}
	
	// _updateSaleItemHistory()	-- Updates the Sale Item Status (and History) in the Sales Portal
	private static function _updateSaleItemStatus($intSPSaleItemId, $mixNewStatus, $strReason=NULL)
	{
		// Save the new Status
		$strSaleItemStatus	= (is_int($mixNewStatus)) ? $mixNewStatus : "(SELECT id FROM sale_item_status WHERE name = '{$mixNewStatus}')";
		$resSaleItemUpdate	= $dsSalesPortal->query("UPDATE sale_item " .
													"SET sale_item_status_id = {$strSaleItemStatus} " .
													"WHERE id = {$intSPSaleItemId} " .
													"LIMIT 1");
		if (PEAR::isError($resSaleItemUpdate))
		{
			throw new Exception($resSaleItemUpdate->getMessage()." :: ".$resSaleItemUpdate->getUserInfo());
		}
		
		// Update the History
		$strReasonSQL				= ($strReason === NULL) ? 'NULL' : "'".str_replace("'", "\\'", $strReason)."'";
		$resSaleItemStatusInsert	= $dsSalesPortal->query("INSERT INTO sale_item_status_history (sale_item_id, sale_item_status_id, changed_on, changed_by, description) " .
															"SELECT id, sale_item_status_id, DEFAULT, ".self::SALES_PORTAL_SYSTEM_DEALER_ID.", {$strReasonSQL} FROM sale_item WHERE id = {$intSPSaleItemId}");
		if (PEAR::isError($resSaleItemStatusInsert))
		{
			throw new Exception($resSaleItemStatusInsert->getMessage()." :: ".$resSaleItemStatusInsert->getUserInfo());
		}
	}
	
	// _toDBValue()	-- Converts a variable into its DB-compatible form
	private static function _toDBValue($mixValue)
	{
		// Is it NULL?
		if ($mixValue === NULL)
		{
			$mixValue	= 'NULL';
		}
		else
		{
			// Escape, etc
			switch (gettype($mixValue))
			{
				case 'string':
					$mixValue	= "'".str_replace("'", "\\'", $mixValue)."'";
					break;
			}
		}
		return $mixValue;
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
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Synchronisation operation to perform [PUSH|PULL|SYNC]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("PUSH","PULL","SYNC"))'
			),
			self::SWITCH_ACTION => array(
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "Specific action to perform (eg. Dealers, Sales)[optional, default is to perform all actions assoctiated with the specified Sync Operation Mode]",
				self::ARG_DEFAULT		=> 'ALL',
				self::ARG_VALIDATION	=> 'Cli::_validInArray(strtoupper("%1$s"), array("DEALERS","PRODUCTS","VENDORS","SALES","ALL"))'
			),
		
		);
	}
}

class Exception_Sale_Manual_Intervention extends Exception {}
class Exception_Sale_Product_Manual_Intervention extends Exception_Sale_Manual_Intervention {}
?>

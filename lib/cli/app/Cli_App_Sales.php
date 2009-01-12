<?php


class Cli_App_Sales extends Cli
{
	const	SWITCH_TEST_RUN		= "t";
	const	SWITCH_MODE	 		= "m";
	const	SWITCH_ACTION 		= "a";
	
	const	SALES_PORTAL_SYSTEM_DEALER_ID	= 1;
	
	const	PROVISIONING_AMNESTY_HOURS		= 72;

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
				
				case 'provision':
					$this->_automaticProvisioning();
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
	
	// This is so the _pushAll method can be called externally
	public static function pushAll()
	{
		$objSyncSales = new self();
		$objSyncSales->_pushAll();
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

				$strCoolingOffPeriod = ($objCustomerGroup->coolingOffPeriod !== NULL)? $objCustomerGroup->coolingOffPeriod : "NULL";
				
				// Does this Customer Group exist in the Sales Portal?
				$resVendors	= $dsSalesPortal->query("SELECT id FROM vendor WHERE id = {$objCustomerGroup->id}", Array('integer'));
				if (PEAR::isError($resVendors))
				{
					throw new Exception($resVendors->getMessage()." :: ".$resVendors->getUserInfo());
				}
				elseif (!($arrVendor = $resVendors->fetchRow(MDB2_FETCHMODE_ASSOC)))
				{
					$this->log("\t\t\t+ Does not exist, adding...");
					
					// No -- add it
					$resVendorInsert	= $dsSalesPortal->query("INSERT INTO vendor (id, name, description, cooling_off_period) VALUES ({$objCustomerGroup->id}, '{$objCustomerGroup->externalName}', '{$objCustomerGroup->externalName}', $strCoolingOffPeriod);");
					if (PEAR::isError($resVendorInsert))
					{
						throw new Exception($resVendorInsert->getMessage()." :: ".$resVendorInsert->getUserInfo());
					}
				}
				else
				{
					$this->log("\t\t\t+ Already exists, updating...");
					
					// Yes -- update it
					$resVendorUpdate	= $dsSalesPortal->query("UPDATE vendor SET id = {$objCustomerGroup->id}, name = '{$objCustomerGroup->externalName}', description = '{$objCustomerGroup->externalName}', cooling_off_period = $strCoolingOffPeriod WHERE id = {$objCustomerGroup->id};");
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
					$resProduct	= $dsSalesPortal->query("SELECT id FROM product WHERE external_reference = 'RatePlan.Id={$arrRatePlan['Id']}' LIMIT 1", Array('integer'));
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
						$strInsertSQL		= "INSERT INTO product (	vendor_id			, name					, description					, product_type_id	, product_status_id		, external_reference) VALUES " .
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
				$resSPDealer	= $dsSalesPortal->query("SELECT id FROM dealer WHERE id = {$arrFlexDealer['id']} LIMIT 1", Array('integer'));
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
										"	{$arrSPDealer['created_on']}," .
										"	{$arrSPDealer['clawback_period']}" .
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
										"	created_on				= {$arrSPDealer['created_on']}," .
										"	clawback_period			= {$arrSPDealer['clawback_period']} " .
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
			$resDealerProductTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_product; ALTER SEQUENCE dealer_product_id_seq RESTART WITH 1;");
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
																"({$arrDealerRatePlan['dealer_id']}, (SELECT id FROM product WHERE external_reference = 'RatePlan.Id={$arrDealerRatePlan['rate_plan_id']}'))");
				if (PEAR::isError($resDealerProductInsert))
				{
					throw new Exception($resDealerProductInsert->getMessage()." :: ".$resDealerProductInsert->getUserInfo());
				}
			}
			//----------------------------------------------------------------//
			
			//---------------------- DEALER >> SALE TYPE ---------------------//
			$this->log("\t\t* Recreating Dealers >> Sale Types relationships...");
			// Truncate the SP Table
			$resDealerSaleTypeTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_sale_type; ALTER SEQUENCE dealer_sale_type_id_seq RESTART WITH 1;");
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
			$resDealerVendorTruncate	= $dsSalesPortal->query("TRUNCATE TABLE dealer_vendor; ALTER SEQUENCE dealer_vendor_id_seq RESTART WITH 1;");
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
		
		$strPullDatetime	= Data_Source_Time::currentTimestamp();
		$intPullDatetime	= strtotime($strPullDatetime);
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
													"WHERE sale_status.name = 'Awaiting Dispatch'");
			if (PEAR::isError($resNewSales))
			{
				throw new Exception($resNewSales->getMessage()." :: ".$resNewSales->getUserInfo());
			}
			while ($arrSPSale = $resNewSales->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$this->log("\t\t* Sale Id #{$arrSPSale['id']}...");
				
				// Created a new Savepoint for this Sale
				$strSaleSavePoint	= "Flex_SP_Pull_Sale_{$arrSPSale['id']}";
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
					$arrContacts	= Array();
					$arrServices	= Array();
					
					// Is this for a new Account?
					if ($arrSPSale['sale_type_id'] == 1)
					{
						$this->log("\t\t\t+ Creating new Account...");
						
						//--------------------- ACCOUNT GROUP --------------------//
						// Yes -- Create a new AccountGroup/Account for this Customer
						$objAccountGroup			= new Account_Group();
						$objAccountGroup->CreatedBy	= Employee::SYSTEM_EMPLOYEE_ID;
						$objAccountGroup->CreatedOn	= $strPullDatetime;
						$objAccountGroup->Archived	= ACCOUNT_STATUS_ACTIVE;
						$objAccountGroup->save();
						//--------------------------------------------------------//
						
						//------------------------ ACCOUNT -----------------------//
						// Get sale_account Details for this Sale
						$resSaleAccount	= $dsSalesPortal->query("SELECT sale_account.*, state.code AS state_name " .
																"FROM sale_account JOIN state ON state.id = sale_account.state_id  " .
																"WHERE sale_id = {$arrSPSale['id']} " .
																"LIMIT 1");
						if (PEAR::isError($resSaleAccount))
						{
							throw new Exception($resSaleAccount->getMessage()." :: ".$resSaleAccount->getUserInfo());
						}
						$arrSPSaleAccount	= $resSaleAccount->fetchRow(MDB2_FETCHMODE_ASSOC);
						$this->log("\t\t\t\t* SP Sale Account Id #{$arrSPSaleAccount['id']}...");
						
						$objAccount						= new Account();
						$objAccount->BusinessName		= ($arrSPSaleAccount['business_name']) ? $arrSPSaleAccount['business_name'] : '';
						$objAccount->TradingName		= ($arrSPSaleAccount['trading_name']) ? $arrSPSaleAccount['trading_name'] : '';
						$objAccount->ABN				= ($arrSPSaleAccount['abn']) ? $arrSPSaleAccount['abn'] : '';
						$objAccount->ACN				= ($arrSPSaleAccount['acn']) ? $arrSPSaleAccount['acn'] : '';
						$objAccount->Address1			= $arrSPSaleAccount['address_line_1'];
						$objAccount->Address2			= ($arrSPSaleAccount['address_line_2']) ? $arrSPSaleAccount['address_line_2'] : '';
						$objAccount->Suburb				= $arrSPSaleAccount['suburb'];
						$objAccount->Postcode			= $arrSPSaleAccount['postcode'];
						$objAccount->State				= $arrSPSaleAccount['state_name'];
						$objAccount->Country			= 'AU';
						$objAccount->CustomerGroup		= $arrSPSaleAccount['vendor_id'];
						$objAccount->AccountGroup		= $objAccountGroup->Id;
						$objAccount->BillingFreq		= BILLING_DEFAULT_FREQ;
						$objAccount->BillingFreqType	= BILLING_DEFAULT_FREQ_TYPE;
						$objAccount->BillingMethod		= $this->_convertSalesPortalToFlex('billdeliverytype', $arrSPSaleAccount['bill_delivery_type_id']);
						
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
						
						if ($arrSPSaleAccount['direct_debit_type_id'])
						{
							$objAccount->BillingType		= $this->_convertSalesPortalToFlex('directdebittype', $arrSPSaleAccount['direct_debit_type_id']);
						}
						else
						{
							$objAccount->BillingType		= BILLING_TYPE_ACCOUNT;
						}
						
						$objAccount->CreatedBy			= Employee::SYSTEM_EMPLOYEE_ID;
						$objAccount->CreatedOn			= $strPullDatetime;
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
						switch ($arrSPSaleAccount['direct_debit_type_id'])
						{
							// Bank Account
							case 1:
								$this->log("\t\t\t\t+ Adding Bank Account...");
								// Get additional Bank Account Details
								$resSPBankAccount	= $dsSalesPortal->query("SELECT * " .
																		"FROM sale_account_direct_debit_bank_account " .
																		"WHERE sale_account_id = {$arrSPSaleAccount['id']} " .
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
								$arrBankAccount['created_on']		= $arrSPSale['created_on'];
								$arrBankAccount['employee_id']		= Employee::SYSTEM_EMPLOYEE_ID;
								$resBankAccountInsert	= $insBankAccount->Execute($arrBankAccount);
								if ($resBankAccountInsert === FALSE)
								{
									throw new Exception($insBankAccount->Error());
								}
								
								$objAccount->DirectDebit	= $insBankAccount->intInsertId;
								break;
							
							// Credit Card
							case 2:
								$this->log("\t\t\t\t+ Adding Credit Card...");
								// Get additional Credit Card Details
								$resSPCreditCard	= $dsSalesPortal->query("SELECT * " .
																		"FROM sale_account_direct_debit_credit_card " .
																		"WHERE sale_account_id = {$arrSPSaleAccount['id']} " .
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
								$arrCreditCard['created_on']	= $arrSPSale['created_on'];
								$arrCreditCard['employee_id']	= Employee::SYSTEM_EMPLOYEE_ID;
								$resCreditCardInsert	= $insCreditCard->Execute($arrCreditCard);
								if ($resCreditCardInsert === FALSE)
								{
									throw new Exception($insCreditCard->Error());
								}
								
								$objAccount->CreditCard	= $insCreditCard->intInsertId;
								break;
						}
						
						// Finalise Account
						$objAccount->save();
						
						$this->log("\t\t\t\t+ Updating Sales Portal Remote Reference to {$objAccount->Id}...");
						// Update the SP Sale Account Remote Reference
						$resSPSaleAccount	= $dsSalesPortal->query("UPDATE sale_account SET external_reference = 'Account.Id={$objAccount->Id}' WHERE id = {$arrSPSaleAccount['id']}");
						if (PEAR::isError($resSPSaleAccount))
						{
							throw new Exception($resSPSaleAccount->getMessage()." :: ".$resSPSaleAccount->getUserInfo());
						}
						//--------------------------------------------------------//
					}
					else
					{
						// We only support New Customer Sales at the moment
						throw new Exception("'{$arrSPSale['sale_type_id']}' Sales are not supported by Flex!");
					}
					
					// Get the date on which the Sale was Verified
					$resVerifiedOn	= $dsSalesPortal->query("SELECT changed_on " .
															"FROM sale_status_history " .
															"WHERE sale_id = {$arrSPSale['id']} AND sale_status_id = 2 " .
															"ORDER BY id DESC " .
															"LIMIT 1");
					if (PEAR::isError($resVerifiedOn))
					{
						throw new Exception($resVerifiedOn->getMessage()." :: ".$resVerifiedOn->getUserInfo());
					}
					$arrVerifiedOn = $resVerifiedOn->fetchRow(MDB2_FETCHMODE_ASSOC);
					
					// Create a new Sale record in Flex
					$objSale	= new Sale();
					$objSale->external_reference	= "sale.id={$arrSPSale['id']}";
					$objSale->account_id			= $objAccount->Id;
					$objSale->verified_on			= $arrVerifiedOn['changed_on'];
					$objSale->sale_type_id			= $arrSPSale['sale_type_id'];
					$objSale->save();
					
					$objSale->intCouldntComplete	= 0;
					
					$this->log("\t\t\t* Getting list of new Contacts...");
					//-------------------------- CONTACT -------------------------//
					// Get the new Contacts associated with this Sale
					$resNewContacts	= $dsSalesPortal->query("SELECT contact.*, contact_title.name AS contact_title_name, contact_sale.contact_association_type_id " .
															"FROM (contact JOIN contact_sale ON contact.id = contact_sale.contact_id JOIN sale ON sale.id = contact_sale.sale_id) LEFT JOIN contact_title ON contact_title.id = contact.contact_title_id " .
															"WHERE contact.external_reference IS NULL AND contact_status_id = 1 AND contact_sale.sale_id = {$arrSPSale['id']}");
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
						$objContact->DOB				= ($arrSPContact['date_of_birth']) ? $arrSPContact['date_of_birth'] : '';
						$objContact->JobTitle			= ($arrSPContact['position_title']) ? $arrSPContact['position_title'] : '';
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
									$objContact->Email	= trim($arrSPContactMethod['details']);
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
						
						// Is there already a Contact with this Email Address?
						if ($objContact->Email && Contact::isEmailInUse($objContact->Email))
						{
							$this->log("\t\t\t\t\t! Contact's Email Address is already in use!  Aborting Sale...");
							throw new Exception_Sale_Manual_Intervention("Contact {$objContact->FirstName} {$objContact->LastName}'s specified Email Address ({$objContact->Email}) is already in use!");
						}
						
						// Save the Flex Contact
						$objContact->save();
						
						// Update the SP Contact Remote Reference
						$this->log("\t\t\t\t\t+ Updating Sales Portal Remote Reference to {$objContact->Id}...");
						$resSPContact	= $dsSalesPortal->query("UPDATE contact SET external_reference = 'Contact.Id={$objContact->Id}' WHERE id = {$arrSPContact['id']}");
						if (PEAR::isError($resSPContact))
						{
							throw new Exception($resSPContact->getMessage()." :: ".$resSPContact->getUserInfo());
						}
						
						// Is it the Primary Contact for the Account?
						if ($arrSPContact['contact_association_type_id'] == 1)
						{
							$this->log("\t\t\t\t\t+ Setting as {$objAccount->Id}'s Primary Contact...");
							$objAccount->PrimaryContact	= $objContact->Id;
							$objAccount->save();
						}
						
						$arrContacts[]	= $objContact;
					}
					//------------------------------------------------------------//
					
					//------------------------ SALE ITEMS ------------------------//
					$this->log("\t\t\t* Getting list of Items sold...");
					
					// Get all items that were sold
					$resSPSaleItems	= $dsSalesPortal->query("SELECT sale_item.*, product.product_type_id, product_type.product_category_id " .
															"FROM sale_item JOIN product ON sale_item.product_id = product.id JOIN product_type ON product_type.id = product.product_type_id " .
															"WHERE sale_id = {$arrSPSale['id']} AND sale_item_status_id = 5");
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
									$objService->Status				= SERVICE_PENDING;
									$objService->Dealer				= $arrSPSaleItem['created_by'];
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
									$objService->CreatedOn		= $strPullDatetime;
									
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
											$objService->Indial100		= ($arrSPLandLineDetails['is_indial_100'] === 't') ? TRUE : FALSE;
											$objService->ELB			= ($arrSPLandLineDetails['has_extension_level_billing'] === 't') ? TRUE : FALSE;
											
											// Service Address Details
											$arrAdditionalDetails	= array();
											$arrAdditionalDetails['AccountGroup']	= $objService->AccountGroup;
											$arrAdditionalDetails['Account']		= $objService->Account;
											$arrAdditionalDetails['Service']		= $objService->Id;
											$arrAdditionalDetails['BillName']		= $arrSPLandLineDetails['bill_name'];
											$arrAdditionalDetails['BillAddress1']	= $arrSPLandLineDetails['bill_address_line_1'];
											$arrAdditionalDetails['BillAddress2']	= $arrSPLandLineDetails['bill_address_line_2'];
											$arrAdditionalDetails['BillLocality']	= $arrSPLandLineDetails['bill_locality'];
											$arrAdditionalDetails['BillPostcode']	= $arrSPLandLineDetails['bill_postcode'];
											
											$arrAdditionalDetails['ServiceAddressType']			= ($arrSPLandLineDetails['landline_service_address_type_id'] !== NULL)? $this->_salesPortalEnum('landline_service_address_type', $arrSPLandLineDetails['landline_service_address_type_id'], 'code') : NULL;
											$arrAdditionalDetails['ServiceAddressTypeNumber']	= $arrSPLandLineDetails['service_address_type_number'];
											$arrAdditionalDetails['ServiceAddressTypeSuffix']	= $arrSPLandLineDetails['service_address_type_suffix'];
											$arrAdditionalDetails['ServiceStreetNumberStart']	= $arrSPLandLineDetails['service_street_number_start'];
											$arrAdditionalDetails['ServiceStreetNumberEnd']		= $arrSPLandLineDetails['service_street_number_end'];
											$arrAdditionalDetails['ServiceStreetNumberSuffix']	= $arrSPLandLineDetails['service_street_number_suffix'];
											$arrAdditionalDetails['ServiceStreetName']			= $arrSPLandLineDetails['service_street_name'];
											$arrAdditionalDetails['ServiceStreetType']			= ($arrSPLandLineDetails['landline_service_street_type_id'])? $this->_salesPortalEnum('landline_service_street_type', $arrSPLandLineDetails['landline_service_street_type_id'], 'code') : NULL;
											$arrAdditionalDetails['ServiceStreetTypeSuffix']	= ($arrSPLandLineDetails['landline_service_street_type_suffix_id'])? $this->_salesPortalEnum('landline_service_street_type_suffix', $arrSPLandLineDetails['landline_service_street_type_suffix_id'], 'code') : NULL;
											$arrAdditionalDetails['ServicePropertyName']		= $arrSPLandLineDetails['service_property_name'];
											$arrAdditionalDetails['ServiceLocality']			= $arrSPLandLineDetails['service_locality'];
											$arrAdditionalDetails['ServiceState']				= $this->_salesPortalEnum('landline_service_state', $arrSPLandLineDetails['landline_service_state_id'], 'code');
											$arrAdditionalDetails['ServicePostcode']			= $arrSPLandLineDetails['service_postcode'];
											
											switch ($arrSPLandLineDetails['landline_type_id'])
											{
												// Residential
												case 1:
													$arrResidentialLandlineDetails	= $dsSalesPortal->queryRow(	"SELECT * ".
																												"FROM sale_item_service_landline_residential ".
																												"WHERE sale_item_service_landline_id = {$arrSPLandLineDetails['id']}",
																												NULL, MDB2_FETCHMODE_ASSOC);
													if (PEAR::isError($arrResidentialLandlineDetails))
													{
														throw new Exception($arrResidentialLandlineDetails->getMessage()." :: ".$arrResidentialLandlineDetails->getUserInfo());
													}
													elseif ($arrResidentialLandlineDetails === NULL)
													{
														throw new Exception("Could not find sale_item_service_landline_residential record relating to the sale_item_service_landline record having id: {$arrSPLandLineDetails['id']}");
													}
													
													$arrAdditionalDetails['Residential']		= 1;
													$arrAdditionalDetails['EndUserTitle']		= $this->_salesPortalEnum('landline_end_user_title', $arrResidentialLandlineDetails['landline_end_user_title_id'], 'code');
													$arrAdditionalDetails['EndUserGivenName']	= $arrResidentialLandlineDetails['end_user_given_name'];
													$arrAdditionalDetails['EndUserFamilyName']	= $arrResidentialLandlineDetails['end_user_family_name'];
													$arrAdditionalDetails['DateOfBirth']		= str_replace("-", "", $arrResidentialLandlineDetails['end_user_dob']);
													$arrAdditionalDetails['Employer']			= $arrResidentialLandlineDetails['end_user_employer'];
													$arrAdditionalDetails['Occupation']			= $arrResidentialLandlineDetails['end_user_occupation'];
													break;
												
												// Business
												case 2:
													$arrBusinessLandlineDetails	= $dsSalesPortal->queryRow(	"SELECT * ".
																											"FROM sale_item_service_landline_business ".
																											"WHERE sale_item_service_landline_id = {$arrSPLandLineDetails['id']}",
																											NULL, MDB2_FETCHMODE_ASSOC);
													if (PEAR::isError($arrBusinessLandlineDetails))
													{
														throw new Exception($arrBusinessLandlineDetails->getMessage()." :: ".$arrBusinessLandlineDetails->getUserInfo());
													}
													elseif ($arrBusinessLandlineDetails === NULL)
													{
														throw new Exception("Could not find sale_item_service_landline_business record relating to the sale_item_service_landline record having id: {$arrSPLandLineDetails['id']}");
													}
													$arrAdditionalDetails['Residential']		= 0;
													$arrAdditionalDetails['EndUserCompanyName']	= $arrBusinessLandlineDetails['company_name'];
													$arrAdditionalDetails['ABN']				= $arrBusinessLandlineDetails['abn'];
													$arrAdditionalDetails['TradingName']		= $arrBusinessLandlineDetails['trading_name'];
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
											
											// Service Details (Mobiles are automatically set to ACTIVE)
											$objService->FNN			= $arrSPMobileDetails['fnn'];
											$objService->ServiceType	= SERVICE_TYPE_MOBILE;
											
											// If it's a New mobile, it will have already been provisioned, so set to ACTIVE
											if ($arrSPMobileDetails['service_mobile_origin_id'] == 1)
											{
												$objService->Status			= SERVICE_ACTIVE;
											}
											
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
											
											// Current Account Information
											$strChurnDetails						= ($arrSPMobileDetails['current_provider']) ? "\nCurrent Provider\t\t: {$arrSPMobileDetails['current_provider']}" : '';
											$strChurnDetails						.= ($arrSPMobileDetails['current_account_number']) ? "\nCurrent Account Number\t: {$arrSPMobileDetails['current_account_number']}" : '';
											$arrAdditionalDetails['Comments']		.= ($strChurnDetails) ? "\n{$strChurnDetails}" : '';
											$arrAdditionalDetails['Comments']		= trim($arrAdditionalDetails['Comments']);
											break;
											
										// ADSL
										case 3:
											// Get the additional ADSL details
											$resSPADSLDetails		= $dsSalesPortal->query("SELECT * " .
																							"FROM sale_item_service_adsl " .
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
											$arrAdditionalDetails['Complex']		= ($arrSPInboundDetails['has_complex_configuration'] === 't') ? TRUE : FALSE;;
											$arrAdditionalDetails['Configuration']	= ($arrSPInboundDetails['configuration']) ? $arrSPInboundDetails['configuration'] : '';
											break;
									}
									
									// Was an FNN provided? (new Mobile Services won't have an MSN)
									if ($objService->FNN === NULL)
									{
										$this->log("\t\t\t\t\t\t\t! No FNN has been provided!  Aborting Sale...");
										throw new Exception_Sale_Product_Manual_Intervention("This Service does not have an FNN assigned.  Please complete any manual provisioning required to determine the FNN, and reset to Awaiting Dispatch.");
									}
									
									$this->log("\t\t\t\t\t\t* FNN: {$objService->FNN}");
									$this->log("\t\t\t\t\t\t* Service Type: {$objService->ServiceType}");
									
									// Is the FNN in use already?
									$this->log("\t\t\t\t\t\t* Checking if FNN is in use... ({$objService->FNN}; {$objService->Indial100}; {$objService->CreatedOn})");
									$mixFNNInUse	= IsFNNInUse($objService->FNN, $objService->Indial100, $objService->CreatedOn);
									if (is_string($mixFNNInUse))
									{
										throw new Exception($mixFNNInUse);
									}
									elseif ($mixFNNInUse)
									{
										$this->log("\t\t\t\t\t\t\t! FNN is in use!  Aborting Sale...");
										throw new Exception_Sale_Product_Manual_Intervention("The FNN {$objService->FNN} is already in use.  Please close the existing Service in Flex, or revoke the sale if it is a duplicate.");
									}
									
									// Save the Service
									$objService->save();
									
									// Add in the Addition Details
									if ($insAdditionalDetails)
									{
										$arrAdditionalDetails['Service']	= $objService->Id;
										if ($insAdditionalDetails->Execute($arrAdditionalDetails) === FALSE)
										{
											throw new Exception($insAdditionalDetails->Error());
										}
									}
									
									// Extension Level Billing
									if ($objService->Indial100 && $objService->ELB)
									{
										$this->log("\t\t\t\t\t\t+ Enabling Extension Level Billing...");
										$GLOBALS['fwkFramework']->EnableELB($objService->Id);
									}
									
									// Set Service Plan
									$resProduct	= $dsSalesPortal->query("SELECT external_reference FROM product WHERE id = {$arrSPSaleItem['product_id']}");
									if (PEAR::isError($resProduct))
									{
										throw new Exception($resProduct->getMessage()." :: ".$resProduct->getUserInfo());
									}
									$arrProduct		= $resProduct->fetchRow(MDB2_FETCHMODE_ASSOC);
									$arrRatePlanId	= explode('=', $arrProduct['external_reference']);
									$objRatePlan	= new Rate_Plan(Array('Id'=>(int)$arrRatePlanId[1]), TRUE);
									$this->log("\t\t\t\t\t\t+ Setting Plan to '{$objRatePlan->Name}'...");									
									$objService->changePlan($objRatePlan);
									
									$objService->objRatePlan	= $objRatePlan;
									
									$arrServices[]	= $objService;
									break;
								
								// Unknown
								default:
									throw new Exception("Product Category '{$arrSPSaleItem['product_category_id']}' is unsupported by Flex!");
							}
							
							// Create a new Sale_Item record in Flex
							$objSaleItem	= new Sale_Item();
							$objSaleItem->external_reference	= "sale_item.id={$arrSPSaleItem['id']}";
							$objSaleItem->sale_id				= $objSale->id;
							switch ($arrSPSaleItem['product_category_id'])
							{
								// Service
								case 1:
									$objSaleItem->service_id	= $objService->Id;
									break;
								
								// Unknown
								default:
									throw new Exception("Product Category '{$arrSPSaleItem['product_category_id']}' is unsupported by Flex!");
							}
							$objSaleItem->save();
							
							// Set Item Status
							$this->_updateSaleItemStatus($arrSPSaleItem['id'], 'Dispatched');
							if ($objService->Status === SERVICE_ACTIVE)
							{
								$this->_updateSaleItemStatus($arrSPSaleItem['id'], 'Completed');
							}
							else
							{
								// There is at least one non-Completed Item in this Sale
								$objSale->intCouldntComplete++;
							}
						
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
							
							// There was an issue with one of the Items which needs manual intervention to resolve
							$this->_updateSaleItemStatus($arrSPSaleItem['id'], 'Manual Intervention', $eException->getMessage());
							
							// Revoke the whole Sale
							throw $eException;
						}
					}
					//------------------------------------------------------------//
					
					// Add System Note detailing Account Creation
					if ((int)$objSale->sale_type_id == 1)
					{
						// New Sale
						$objAccountCreationNote	= new Note();
						$objAccountCreationNote->AccountGroup	= $objAccount->AccountGroup;
						$objAccountCreationNote->Account		= $objAccount->Account;
						$objAccountCreationNote->Employee		= Employee::SYSTEM_EMPLOYEE_ID;
						$objAccountCreationNote->Datetime		= $strPullDatetime;
						$objAccountCreationNote->NoteType		= Note::SYSTEM_NOTE_TYPE_ID;
						
						$strNote  = "This Account has been created from the Sales Portal with the following details:\n\n" .
									"Sale Reference ID: {$arrSPSale['id']}\n";
						
						if (count($arrContacts))
						{
							$strNote	.= "\nContacts:\n";
							foreach ($arrContacts as $objContact)
							{
								$strNote	.= "\t{$objContact->FirstName} {$objContact->LastName} " . (($objAccount->PrimaryContact == $objContact->Id) ? "(Primary Contact)" : '') . "\n";								
							}
						}
						
						$strNote .= "\nServices:\n";
						foreach ($arrServices as $objService)
						{
							foreach ($arrContacts as $objContact)
							{
								$strNote	.= "\t{$objService->FNN} (".trim($objService->objRatePlan->Name).")\n";								
							}
						}
						
						$objAccountCreationNote->Note			= trim($strNote);
						$objAccountCreationNote->save();
					}
					
					// Set Sale Status
					$this->_updateSaleStatus($arrSPSale['id'], 'Dispatched');
					if (!$objSale->intCouldntComplete)
					{
						$this->_updateSaleStatus($arrSPSale['id'], 'Completed');
					}
					
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
					// There was an issue with the Sale which needs manual intervention to resolve
					$this->_updateSaleStatus($arrSPSale['id'], 'Manual Intervention', $eException->getMessage());
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
				while ($arrPair = $resSPEnumeration->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					$arrEnumerations[$strTable][$arrPair['id']]	= $arrPair;
				}
			}
		}
		
		// Return the Value
		return $arrEnumerations[$strTable][$intId][$strMode];
	}
	
	// _updateSaleHistory()	-- Updates the Sale Status (and History) in the Sales Portal
	private static function _updateSaleStatus($intSPSaleId, $mixNewStatus, $strReason=NULL)
	{
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales');
		
		// Save the new Status
		$strSaleStatus	= (is_int($mixNewStatus)) ? $mixNewStatus : "(SELECT id FROM sale_status WHERE name = '{$mixNewStatus}')";
		$resSaleUpdate	= $dsSalesPortal->query("UPDATE sale " .
												"SET sale_status_id = {$strSaleStatus} " .
												"WHERE id = {$intSPSaleId}");
		if (PEAR::isError($resSaleUpdate))
		{
			throw new Exception($resSaleUpdate->getMessage()." :: ".$resSaleUpdate->getUserInfo());
		}
		
		$strCurrentTimestamp = Data_Source_Time::currentTimestamp($dsSalesPortal);
		
		// Update the History
		$strReasonSQL			= ($strReason === NULL) ? 'NULL' : "'".str_replace("'", "\\'", $strReason)."'";
		$resSaleStatusInsert	= $dsSalesPortal->query("INSERT INTO sale_status_history (sale_id, sale_status_id, changed_by, changed_on, description) " .
															"SELECT id, sale_status_id, ".self::SALES_PORTAL_SYSTEM_DEALER_ID.", '{$strCurrentTimestamp}', {$strReasonSQL} FROM sale WHERE id = {$intSPSaleId}");
		if (PEAR::isError($resSaleStatusInsert))
		{
			throw new Exception($resSaleStatusInsert->getMessage()." :: ".$resSaleStatusInsert->getUserInfo());
		}
	}
	
	// _updateSaleItemHistory()	-- Updates the Sale Item Status (and History) in the Sales Portal
	private static function _updateSaleItemStatus($intSPSaleItemId, $mixNewStatus, $strReason=NULL)
	{
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales');
		
		// Save the new Status
		$strSaleItemStatus	= (is_int($mixNewStatus)) ? $mixNewStatus : "(SELECT id FROM sale_item_status WHERE name = '{$mixNewStatus}')";
		
		$resSaleItemUpdate	= $dsSalesPortal->query("UPDATE sale_item " .
													"SET sale_item_status_id = {$strSaleItemStatus} " .
													"WHERE id = {$intSPSaleItemId}");
		if (PEAR::isError($resSaleItemUpdate))
		{
			throw new Exception($resSaleItemUpdate->getMessage()." :: ".$resSaleItemUpdate->getUserInfo());
		}
		
		$strCurrentTimestamp = Data_Source_Time::currentTimestamp($dsSalesPortal);
		
		// Update the History
		$strReasonSQL				= ($strReason === NULL) ? 'NULL' : "'".str_replace("'", "\\'", $strReason)."'";
		$resSaleItemStatusInsert	= $dsSalesPortal->query("INSERT INTO sale_item_status_history (sale_item_id, sale_item_status_id, changed_by, changed_on, description) " .
															"SELECT id, sale_item_status_id, ".self::SALES_PORTAL_SYSTEM_DEALER_ID.", '{$strCurrentTimestamp}', {$strReasonSQL} FROM sale_item WHERE id = {$intSPSaleItemId}");
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
	
	// _automaticProvisioning()	: checks to see if there are any Sales that are ready to be automatically provisioned
	private function _automaticProvisioning()
	{
		$this->log("\t* Performing Automatic Provisioning...");
		
		$dsSalesPortal	= Data_Source::get('sales');
		$dacFlex		= DataAccess::getDataAccess();
		
		$strCurrentTimestamp = Data_Source_Time::currentTimestamp($dsSalesPortal);
		
		// The provisioning process may find services that are flagged as pending activation, but their associated SaleItem has been cancelled.
		// These need to be manually cancelled, or at the very least, brought to the attention of administrative staff
		$arrServicesToCancel = array();
		
		// All Services, other than Landlines, currently need manual provisioning
		// These need to be brought to the attention of administrative staff
		$arrServicesNeedingManualProvisioning = array();
		
		// Start a transaction for both Databases
		$dsSalesPortal->beginTransaction();
		$dacFlex->TransactionStart();
		
		try
		{
			$qryQuery	= new Query();
			
			// Get the list of Services which are Pending Activation, originated from the Sales Portal, and have exceeded the Provisioning Amnesty Period
			// Note that this list can reference sale items that have since been cancelled, and thus should not be auto provisioned
			$selPendingServices	= new StatementSelect(	"Service INNER JOIN sale_item ON sale_item.service_id = Service.Id INNER JOIN sale ON sale_item.sale_id = sale.id",
														"Service.*, sale_id AS flex_sale_id, sale.verified_on, sale_item.id AS flex_sale_item_id",
														"Service.Status = ".SERVICE_PENDING." AND NOW() > ADDDATE(sale.verified_on, INTERVAL ".self::PROVISIONING_AMNESTY_HOURS." HOUR)");
														
			// Get list of sale items that currently have a status of dispatched, and are now eligible for auto provisioning (this will not retrieve sale items that have been cancelled)
			$resSaleItems = $dsSalesPortal->queryAll(	"SELECT si.id AS id ".
														"FROM sale_item AS si INNER JOIN sale_item_status_history AS sish ON si.id = sish.sale_item_id ".
														"WHERE si.sale_item_status_id = ". DO_Sales_SaleItemStatus::DISPATCHED ." AND sish.sale_item_status_id = ". DO_Sales_SaleItemStatus::VERIFIED ." AND sish.changed_on < (NOW() - INTERVAL '". self::PROVISIONING_AMNESTY_HOURS ." HOUR');",
														array("integer"), MDB2_FETCHMODE_ASSOC);
			
			// This will store an array of Sales_Sale objects, which have been updated (have had sale items provisioned)
			$arrProvisionedSales = array();
			
			if (PEAR::isError($resSaleItems))
			{
				throw new Exception($resSaleItems->getMessage()." :: ".$resSaleItems->getUserInfo());
			}
			
			$arrSPEligibleSaleItems = array();
			foreach ($resSaleItems as $arrSaleItem)
			{
				$arrSPEligibleSaleItems[$arrSaleItem['id']] = $arrSaleItem;
			}
			
			if ($selPendingServices->Execute() === FALSE)
			{
				throw new Exception();
			}

			while ($arrService = $selPendingServices->Fetch())
			{
				$objService	= new Service($arrService);
				
				$objFlexSaleItem = FlexSaleItem::getForId($arrService['flex_sale_item_id'], TRUE);

				// Check that the service's sale item is currently flagged as being dispatched (as opposed to cancelled or completed, although the only alternative should be cancelled)
				if (!array_key_exists($objFlexSaleItem->getExternalReferenceValue(), $arrSPEligibleSaleItems))
				{
					// The Sale Item associated with this pending service must have been cancelled, but has not yet been 'Closed' in flex
					$arrServicesToCancel[] = Array(	'Service'		=> $objService,
													'FlexSaleItem'	=> $objFlexSaleItem
												);
					
					// Move on to the next service
					continue;
				}

				$objSale		= Sales_Sale::getForFlexSaleId($objFlexSaleItem->saleId, TRUE);
				$objRatePlan	= $objService->getCurrentPlan();

				$this->log("\t\t+ {$objService->FNN}...");
				$this->log("\t\t\t* Sale\t: {$objSale->id} ...");
				$this->log("\t\t\t* Sale Item\t: ". $objFlexSaleItem->getExternalReferenceValue() ." ...");
				$this->log("\t\t\t* Rate Plan\t: {$objRatePlan->Name} ...");
				
				// Create 
				switch ($objService->ServiceType)
				{
					case SERVICE_TYPE_LAND_LINE:
						// Full Service
						$this->log("\t\t\t+ Adding Full Service Request...");
						$objFullServiceRequest	= new Provisioning_Request();
						$objFullServiceRequest->AccountGroup		= $objService->AccountGroup;
						$objFullServiceRequest->Account				= $objService->Account;
						$objFullServiceRequest->Service				= $objService->Id;
						$objFullServiceRequest->FNN					= $objService->FNN;
						$objFullServiceRequest->Employee			= 0;
						$objFullServiceRequest->Carrier				= $objRatePlan->CarrierFullService;
						$objFullServiceRequest->Type				= PROVISIONING_TYPE_FULL_SERVICE;
						$objFullServiceRequest->RequestedOn			= Data_Source_Time::currentTimestamp();
						$objFullServiceRequest->AuthorisationDate	= $objService->CreatedOn;
						$objFullServiceRequest->Status				= REQUEST_STATUS_WAITING;
						$objFullServiceRequest->save();
						
						// Preselection
						$this->log("\t\t\t+ Adding Preselection Request...");
						$objPreselectionRequest	= new Provisioning_Request();
						$objPreselectionRequest->AccountGroup		= $objService->AccountGroup;
						$objPreselectionRequest->Account			= $objService->Account;
						$objPreselectionRequest->Service			= $objService->Id;
						$objPreselectionRequest->FNN				= $objService->FNN;
						$objPreselectionRequest->Employee			= 0;
						$objPreselectionRequest->Carrier			= $objRatePlan->CarrierPreselection;
						$objPreselectionRequest->Type				= PROVISIONING_TYPE_PRESELECTION;
						$objPreselectionRequest->RequestedOn		= Data_Source_Time::currentTimestamp();
						$objPreselectionRequest->AuthorisationDate	= $arrService['verified_on'];
						$objPreselectionRequest->Status				= REQUEST_STATUS_WAITING;
						$objPreselectionRequest->save();
						break;
					
					default:
						// This case will only occur if a Service is of a type that must be manually provisioned, and the service has not yet been set to ACTIVE in Flex
						
						// These services will require manual Provisioning
						$arrServicesNeedingManualProvisioning[] = Array('Service'		=> $objService,
																		'FlexSaleItem'	=> $objFlexSaleItem
																		);
						continue;
						//throw new Exception("Service of Type '".GetConstantDescription($objService->ServiceType, 'service_type')."' are not automatically provisioned by Flex!");
						//break;
				}
				
				// Set this Service to Active in Flex
				$objService->Status	= SERVICE_ACTIVE;
				$objService->save();
				
				// Set the Sale Item to Completed in the Sales Portal
				$this->_updateSaleItemStatus($objFlexSaleItem->getExternalReferenceValue(), DO_Sales_SaleItemStatus::COMPLETED);
				
				if (!array_key_exists($objSale->id, $arrProvisionedSales))
				{
					$arrProvisionedSales[$objSale->id] = $objSale;
				}
				
			}
			
			// Finalise Sales/Accounts
			// Accounts don't need to be finalised as they are set to ACTIVE as soon as the sale is imported
			foreach ($arrProvisionedSales as $objSale)
			{
				$objSale->setCompletedOrCancelledBasedOnSaleItems();
			}
			
			//TODO! I should email the list of Services that should be cancelled, and the list of services that still need to be manually provisioned to the interested parties
			

			// All seems to have worked fine -- Commit the Transactions
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
				self::ARG_DESCRIPTION	=> "Synchronisation operation to perform [PUSH|PULL|SYNC|PROVISION]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("PUSH","PULL","SYNC","PROVISION"))'
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

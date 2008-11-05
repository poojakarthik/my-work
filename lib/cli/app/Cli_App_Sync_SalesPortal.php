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
		// TODO
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

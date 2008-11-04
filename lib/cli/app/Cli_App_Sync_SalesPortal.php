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
				$resVendors	= $dsSalesPortal->query("SELECT * FROM vendor WHERE reference = '{$objCustomerGroup->externalName}' LIMIT 1");
				if (PEAR::isError($resVendors))
				{
					throw new Exception($resVendors->getError)." :: ".$resVendors->getUserInfo();
				}
				elseif (!($arrVendor = $resVendors->fetchRow(MDB2_FETCHMODE_ASSOC)))
				{
					// No -- add it
					$resVendorInsert	= $dsSalesPortal->query("INSERT INTO vendor (name, description, reference) VALUES ('{$objCustomerGroup->externalName}', '{$objCustomerGroup->externalName}', '{$objCustomerGroup->id}');");
					if (PEAR::isError($resVendorInsert))
					{
						throw new Exception($resVendorInsert->getError)." :: ".$resVendorInsert->getUserInfo();
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
					$intProductVendor		= $this->_convertFlexToSalesPortal('customer_group', $arrRatePlan['customer_group']);
					$strProductName			= $arrRatePlan['Name'];
					$strProductDescription	= $arrRatePlan['Description'];
					$intProductType			= $this->_convertFlexToSalesPortal('service_type', $arrRatePlan['ServiceType']);
					$intProductStatus		= $this->_convertFlexToSalesPortal('plan_archived', $arrRatePlan['Archived']);
					
					// Does it already exist in the SP?
					$resProduct	= $dsSalesPortal->query("SELECT id FROM product WHERE reference = 'RatePlan.Id={$arrRatePlan['Id']}' LIMIT 1");
					if (PEAR::isError($resProduct))
					{
						throw new Exception($resProduct->getError)." :: ".$resProduct->getUserInfo();
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
							throw new Exception($resProductUpdate->getError)." :: ".$resProductUpdate->getUserInfo();
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
							throw new Exception($resProductInsert->getError)." :: ".$resProductInsert->getUserInfo();
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
		// TODO
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
		static	$dsSalesPortal;
		$dsSalesPortal	= (isset($dsSalesPortal)) ? $dsSalesPortal : Data_Source::get('sales_portal');
		
		switch (str_replace('_', '', strtolower($strType)))
		{
			case 'vendor':
			case 'vendorid':
			case 'customergroup':
			case 'customergroupid':
				static	$arrCustomerGroupVendor;
				if (!isset($arrCustomerGroupVendor))
				{
					$resVendor	= $dsSalesPortal->query("SELECT id, reference FROM vendor");
					if (PEAR::isError($resVendor))
					{
						throw new Exception($resVendor->getError)." :: ".$resVendor->getUserInfo();
					}
					$arrCustomerGroupVendor	= array();
					while ($arrVendor = $resVendor->fetchRow(MDB2_FETCHMODE_ASSOC))
					{
						$arrReference	= explode('=', $arrVendor['reference']);
						$arrCustomerGroupVendor[(int)$arrReference[1]]	= $arrVendor['id'];
					}
				}
				return $arrCustomerGroupVendor[(int)$mixFlexValue];
				break;
				
			case 'servicetype':
			case 'servicetypeid':
				// HACK: These should work, at least for now
				$arrServiceTypeProductType	= array(
														100 => 3,
														101	=> 2,
														102 => 1,
														103 => 4
													);
				
				return $arrServiceTypeProductType[(int)$mixFlexValue];
				break;
				
			case 'planarchived':
				// HACK: These should work, at least for now
				$arrPlanArchivedProductStatus	= array(
															0	=> 1,
															1	=> 2
														);
				
				return $arrPlanArchivedProductStatus[(int)$mixFlexValue];
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

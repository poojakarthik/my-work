<?php

class JSON_Handler_Contract_ManageBreached extends JSON_Handler
{

	// Waives the Contract Fees for a given ServiceRatePlan
	public function waive($intContractId)
	{
		try
		{
			// Waive the fees
			DataAccess::getDataAccess()->TransactionStart();
			
			// Build a Contract object using the passed ServiceRatePlan.Id
			$objServiceRatePlan	= new Service_Rate_Plan(Array('Id'=>$intContractId), TRUE);
			$objServiceRatePlan->contract_breach_fees_charged_on	= date("Y-m-d H:i:s");
			$objServiceRatePlan->contract_breach_fees_employee_id	= Flex::getUserId();
			$objServiceRatePlan->save();
			
			// If no exceptions were thrown, then everything worked
			DataAccess::getDataAccess()->TransactionCommit();
			
			return array(
							"Success"		=> TRUE,
							"ContractId"	=> $intContractId,
						);
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			// Send an Email to Devs
			SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ContractId"	=> $intContractId,
							"ErrorMessage"	=> 'ERROR'
						);
		}
	}

	// Waives the Contract Fees for a given ServiceRatePlan
	public function apply($intContractId, $fltPayoutPercentage, $fltPayoutFee, $fltExitFee)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			// Waive the fees
			DataAccess::getDataAccess()->TransactionStart();
			
			// Build a Contract object using the passed ServiceRatePlan.Id
			$objServiceRatePlan	= new Service_Rate_Plan(Array('Id'=>$intContractId), TRUE);
			$objService			= new Service(Array('Id'=>$objServiceRatePlan->Service), TRUE);
			
			// Add Exit Fee Adjustment
			$fltExitFee	= round((float)$fltExitFee, 2);
			if ($fltExitFee > 0.0)
			{
				// Get the ChargeType data for Early Exit Fees
				$objExitChargeType	= Charge_Type::getContractExitFee();
				
				// Create the Charge
				$objExitCharge	= new Charge();
				
				$objExitCharge->AccountGroup		= $objService->AccountGroup;
				$objExitCharge->Account				= $objService->Account;
				$objExitCharge->Service				= $objService->Id;
				$objExitCharge->CreatedBy			= Flex::getUserId();
				$objExitCharge->CreatedOn			= date("Y-m-d");
				$objExitCharge->ApprovedBy			= Flex::getUserId();
				$objExitCharge->ChargeType			= $objExitChargeType->ChargeType;
				$objExitCharge->charge_type_id		= $objExitChargeType->Id;
				$objExitCharge->Description			= $objExitChargeType->Description;
				$objExitCharge->ChargedOn			= $objExitCharge->CreatedOn;
				$objExitCharge->Nature				= NATURE_DR;
				$objExitCharge->Amount				= $fltExitFee;
				$objExitCharge->Notes				= '';
				$objExitCharge->Status				= CHARGE_APPROVED;
				$objExitCharge->global_tax_exempt	= FALSE;
				
				$objExitCharge->save();
				
				// Set the appropriate field in the ServiceRatePlan
				$objServiceRatePlan->exit_fee_charge_id	= $objExitCharge->Id;
			}
			
			// Add Payout Adjustment
			$fltPayoutFee	= round((float)$fltPayoutFee, 2);
			if ($fltPayoutFee > 0.0)
			{
				// Get the ChargeType data for Early Exit Fees
				$objPayoutChargeType	= Charge_Type::getContractPayoutFee();
				
				// Create the Charge
				$objPayoutCharge	= new Charge();
				
				$objPayoutCharge->AccountGroup		= $objService->AccountGroup;
				$objPayoutCharge->Account			= $objService->Account;
				$objPayoutCharge->Service			= $objService->Id;
				$objPayoutCharge->CreatedBy			= Flex::getUserId();
				$objPayoutCharge->CreatedOn			= date("Y-m-d");
				$objPayoutCharge->ApprovedBy		= Flex::getUserId();
				$objPayoutCharge->ChargeType		= $objPayoutChargeType->ChargeType;
				$objPayoutCharge->charge_type_id	= $objPayoutChargeType->Id;
				$objPayoutCharge->Description		= $objPayoutChargeType->Description;
				$objPayoutCharge->ChargedOn			= $objPayoutCharge->CreatedOn;
				$objPayoutCharge->Nature			= NATURE_DR;
				$objPayoutCharge->Amount			= $fltPayoutFee;
				$objPayoutCharge->Notes				= '';
				$objPayoutCharge->Status			= CHARGE_APPROVED;
				$objPayoutCharge->global_tax_exempt	= FALSE;
				
				$objPayoutCharge->save();
				
				// Set the appropriate field in the ServiceRatePlan
				$objServiceRatePlan->contract_payout_charge_id	= $objPayoutCharge->Id;
			}
			
			// Update the ServiceRatePlan Details
			$objServiceRatePlan->contract_breach_fees_charged_on	= date("Y-m-d H:i:s");
			$objServiceRatePlan->contract_breach_fees_employee_id	= Flex::getUserId();
			$objServiceRatePlan->contract_payout_percentage			= round((float)$fltPayoutPercentage, 2);
			$objServiceRatePlan->save();
			
			// If no exceptions were thrown, then everything worked
			DataAccess::getDataAccess()->TransactionCommit();
			
			return array(
						"Success"		=> TRUE,
						"ContractId"	=> $intContractId,
						);
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			// Send an Email to Devs
			SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"		=> FALSE,
							"ContractId"	=> $intContractId,
							"ErrorMessage"	=> 'ERROR'
						);
		}
	}
}

?>

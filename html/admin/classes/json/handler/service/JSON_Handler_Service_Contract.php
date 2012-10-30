<?php
class JSON_Handler_Service_Contract extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	
	public function getDetails($iServiceRatePlanId) {
		$oServiceRatePlan = Service_Rate_Plan::getForId((int)$iServiceRatePlanId);
		$oService = Service::getForId($oServiceRatePlan->Service);
		$oServiceType = Service_Type::getForId($oService->ServiceType);
		$oRatePlan = Rate_Plan::getForId($oServiceRatePlan->RatePlan);
		$oContractStatus = Contract_Status::getForId($oServiceRatePlan->contract_status_id);
		$oAccount = Account::getForId($oService->Account);
		$oCustomerGroup = Customer_Group::getForId($oAccount->CustomerGroup);

		$oContractBreachReason = ($oServiceRatePlan->contract_breach_reason_id) ? Contract_Breach_Reason::getForId($oServiceRatePlan->contract_breach_reason_id) : null;
		$oContractBreachFeesEmployee = ($oServiceRatePlan->contract_breach_fees_employee_id) ? Employee::getForId($oServiceRatePlan->contract_breach_fees_employee_id) : null;

		$oContractPayoutCharge = ($oServiceRatePlan->contract_payout_charge_id) ? Charge::getForId($oServiceRatePlan->contract_payout_charge_id) : null;
		$oContractPayoutChargeType = ($oContractPayoutCharge && $oContractPayoutCharge->charge_type_id) ? Charge_Type::getForId($oContractPayoutCharge->charge_type_id) : null;

		$oExitFeeCharge = ($oServiceRatePlan->exit_fee_charge_id) ? Charge::getForId($oServiceRatePlan->exit_fee_charge_id) : null;
		$oExitFeeChargeType = ($oExitFeeCharge && $oExitFeeCharge->charge_type_id) ? Charge_Type::getForId($oExitFeeCharge->charge_type_id) : null;

		// Return relevant data
		return array(
			'id' => $oServiceRatePlan->Id,
			// Account
			'account_id' => $oService->Account,
			'account' => array(
				'id' => $oAccount->Id,
				'account_name' => $oAccount->BusinessName,
				'customer_group_id' => $oAccount->CustomerGroup,
				'customer_group' => array(
					'id' => $oCustomerGroup->Id,
					'internal_name' => $oCustomerGroup->internal_name,
					'primary_colour' => $oCustomerGroup->customer_primary_color
				)
			),
			// Service
			'service_id' => $oServiceRatePlan->Service,
			'service' => array(
				'id' => $oService->Id,
				'service_identifier' => $oService->FNN,
				'service_type_id' => $oService->ServiceType,
				'service_type' => array(
					'id' => $oServiceType->id,
					'name' => $oServiceType->name,
					'description' => $oServiceType->description,
					'constant' => $oServiceType->const_name
				)
			),
			// Plan
			'rate_plan_id' => $oServiceRatePlan->RatePlan,
			'rate_plan' => array(
				'id' => $oRatePlan->Id,
				'name' => $oRatePlan->Name,
				'contract_term' => $oRatePlan->ContractTerm,
				'minimum_charge' => $oRatePlan->MinMonthly,
				'contract_exit_fee' => $oRatePlan->contract_exit_fee,
				'contract_payout_percentage' => $oRatePlan->contract_payout_percentage
			),
			// Contract
			'contract_start_datetime' => $oServiceRatePlan->StartDatetime,
			'contract_end_datetime' => $oServiceRatePlan->EndDatetime,
			'contract_scheduled_end_datetime' => $oServiceRatePlan->contract_scheduled_end_datetime,
			'contract_effective_end_datetime' => $oServiceRatePlan->contract_effective_end_datetime,
			'contract_term_remaining' => $oServiceRatePlan->contractMonthsRemaining(),
			'contract_status_id' => $oServiceRatePlan->contract_status_id,
			'contract_status' => array(
				'id' => $oContractStatus->id,
				'name' => $oContractStatus->name,
				'constant' => $oContractStatus->const_name
			),
			// Contract Breach
			'contract_breach_reason_id' => $oServiceRatePlan->contract_breach_reason_id,
			'contract_breach_reason' => ((!$oContractBreachReason) ? null : array(
				'id' => $oContractBreachReason->id,
				'name' => $oContractBreachReason->name,
				'constant' => $oContractBreachReason->const_name
			)),
			'contract_breach_reason_description' => $oServiceRatePlan->contract_breach_reason_description,
			// Contract Payout
			'contract_payout_recommended' => $oServiceRatePlan->calculateContractPayout(),
			'contract_payout_percentage' => $oServiceRatePlan->contract_payout_percentage,
			'contract_payout_charge_id' => $oServiceRatePlan->contract_payout_charge_id,
			'contract_payout_charge' => ((!$oContractPayoutCharge) ? null : array(
				'id' => $oContractPayoutCharge->id,
				'charge_type_id' => $oContractPayoutCharge->charge_type_id,
				'charge_type' => array(
					'id' => $oContractPayoutChargeType->Id,
					'charge_type' => $oContractPayoutChargeType->ChargeType
				),
				'amount' => Rate::roundtoCurrencyStandard($oContractPayoutCharge->Amount),
				'charge_status_id' => $oContractPayoutCharge->Status,
				'charge_status' => array(
					'id' => $oContractPayoutCharge->Status,
					'name' => GetConstantDescription($oContractPayoutCharge->Status, 'ChargeStatus'),
					'constant' => GetConstantName($oContractPayoutCharge->Status, 'ChargeStatus')
				)
			)),
			// Contract Exit Fee
			'exit_fee_recommended' => $oServiceRatePlan->calculateContractExitFee(),
			'exit_fee_charge_id' => $oServiceRatePlan->exit_fee_charge_id,
			'exit_fee_charge' => ((!$oExitFeeCharge) ? null : array(
				'id' => $oExitFeeCharge->id,
				'charge_type_id' => $oExitFeeCharge->charge_type_id,
				'charge_type' => array(
					'id' => $oExitFeeChargeType->Id,
					'charge_type' => $oExitFeeChargeType->ChargeType
				),
				'amount' => Rate::roundtoCurrencyStandard($oExitFeeCharge->Amount),
				'charge_status_id' => $oExitFeeCharge->Status,
				'charge_status' => array(
					'id' => $oExitFeeCharge->Status,
					'name' => GetConstantDescription($oExitFeeCharge->Status, 'ChargeStatus'),
					'constant' => GetConstantName($oExitFeeCharge->Status, 'ChargeStatus')
				)
			)),
			// Contract Breach Fees Audit
			'contract_breach_fees_datetime' => $oServiceRatePlan->contract_breach_fees_charged_on,
			'contract_breach_fees_employee_id' => $oServiceRatePlan->contract_breach_fees_employee_id,
			'contract_breach_fees_employee' => ((!$oContractBreachFeesEmployee) ? null : array(
				'id' => $oContractBreachFeesEmployee->Id,
				'first_name' => $oContractBreachFeesEmployee->FirstName,
				'last_name' => $oContractBreachFeesEmployee->LastName
			)),
			'contract_breach_fees_reason' => $oServiceRatePlan->contract_breach_fees_reason
		);
	}

}
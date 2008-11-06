<?php

//----------------------------------------------------------------------------//
// Dealer_Rate_Plan
//----------------------------------------------------------------------------//
/**
 * Dealer_Rate_Plan
 *
 * Models a dealer_rate_plan record
 *
 * Models a dealer_rate_plan record
 *
 * @class	Dealer_Rate_Plan
 */
class Dealer_Rate_Plan
{
	//------------------------------------------------------------------------//
	// getRatePlanForDealer
	//------------------------------------------------------------------------//
	/**
	 * getRatePlanForDealer()
	 *
	 * Returns array of RatePlan ids that the dealer can sell on behalf of
	 * 
	 * Returns array of RatePlan ids that the dealer can sell on behalf of
	 *
	 * @param		int		$intDealerId	id of the dealer
	 * @return		array of RatePlan ids	
	 * @method
	 */
	public static function getRatePlansForDealer($intDealerId)
	{
		static $selDealerRatePlan;
		if (!isset($selDealerRatePlan))
		{
			$selDealerRatePlan = new StatementSelect("dealer_rate_plan", self::getColumns(), "dealer_id = <DealerId>");
		}
		
		if ($selDealerRatePlan->Execute(Array("DealerId"=>$intDealerId)) === FALSE)
		{
			throw new Exception("Failed to retrieve records from the Dealer_Rate_Plan table for dealer with id: $intDealerId - ". $selDealerRatePlan->Error());
		}
		
		$arrRecordSet = $selDealerRatePlan->FetchAll();
		
		$arrRatePlanIds = Array();
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrRatePlanIds[$arrRecord['ratePlanId']] = $arrRecord['ratePlanId'];
		}
		
		return $arrRatePlanIds;
	}
	
	//------------------------------------------------------------------------//
	// setRatePlansForDealer
	//------------------------------------------------------------------------//
	/**
	 * setRatePlansForDealer()
	 *
	 * Set which RatePlans a Dealer can sell on behalf of
	 * 
	 * Set which RatePlans a Dealer can sell on behalf of
	 *
	 * @param		int		$intDealerId		id of the dealer
	 * @param		array	$arrRatePlanIds		array of RatePlan ids
	 * @return		void	
	 * @method
	 */
	public static function setRatePlansForDealer($intDealerId, $arrRatePlanIds)
	{
		// Delete existing records in the Dealer_Rate_Plan table
		$qryQuery		= new Query();
		$intDealerId	= intval($intDealerId);
		
		if (!$qryQuery->Execute("DELETE FROM dealer_rate_plan WHERE dealer_id = $intDealerId;"))
		{
			throw new Exception("Failed to set RatePlans for Dealer: $intDealerId.  (Removal of old dealer - RatePlan associations failed) - " . $qryQuery->Error());
		}
		
		// Reset the auto incrementing counter for the dealer_rate_plan table
		// (This really isn't worth doing, as it is only effective if the dealer is the last one to have records added to the dealer_rate_plan table)
		
		// Insert records into the dealer_rate_plan table
		$arrData = array(	"dealer_id"		=> $intDealerId,
							"rate_plan_id"	=> NULL
						);
		$insDealerRatePlan = new StatementInsert("dealer_rate_plan", $arrData);
		foreach ($arrRatePlanIds as $intRatePlanId)
		{
			$arrData['rate_plan_id'] = $intRatePlanId;
			if ($insDealerRatePlan->Execute($arrData) === FALSE)
			{
				throw new Exception("Failed to set RatePlans for Dealer: $intDealerId. (inserting record into dealer_rate_plan table failed) - ". $insDealerRatePlan->Error());
			}
		}
	}
	

	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the table
	 * 
	 * Returns array defining the columns of the table
	 *
	 * @return		array	
	 * @method
	 */
	protected static function getColumns()
	{
		return Array(
						"id"				=> "id",
						"dealerId"			=> "dealer_id",
						"ratePlanId"		=> "rate_plan_id"
					);
	}
}

?>

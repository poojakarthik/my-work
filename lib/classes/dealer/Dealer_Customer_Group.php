<?php

//----------------------------------------------------------------------------//
// Dealer_Customer_Group
//----------------------------------------------------------------------------//
/**
 * Dealer_Customer_Group
 *
 * Models a dealer_customer_group record
 *
 * Models a dealer_customer_group record
 *
 * @class	Dealer_Customer_Group
 */
class Dealer_Customer_Group
{
	//------------------------------------------------------------------------//
	// getCustomerGroupsForDealer
	//------------------------------------------------------------------------//
	/**
	 * getCustomerGroupsForDealer()
	 *
	 * Returns array of customer group ids that the dealer can sell on behalf of
	 * 
	 * Returns array of customer group ids that the dealer can sell on behalf of
	 *
	 * @param		int		$intDealerId	id of the dealer
	 * @return		array of customer group ids	
	 * @method
	 */
	public static function getCustomerGroupsForDealer($intDealerId)
	{
		static $selDealerCustomerGroup;
		if (!isset($selDealerCustomerGroup))
		{
			$selDealerCustomerGroup = new StatementSelect("dealer_customer_group", self::getColumns(), "dealer_id = <DealerId>");
		}
		
		if ($selDealerCustomerGroup->Execute(Array("DealerId"=>$intDealerId)) === FALSE)
		{
			throw new Exception("Failed to retrieve records from the dealer_customer_group table for dealer with id: $intDealerId - ". $selDealerCustomerGroup->Error());
		}
		
		$arrRecordSet = $selDealerCustomerGroup->FetchAll();
		
		$arrCustomerGroupIds = Array();
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrCustomerGroupIds[$arrRecord['customerGroupId']] = $arrRecord['customerGroupId'];
		}
		
		return $arrCustomerGroupIds;
	}
	
	//------------------------------------------------------------------------//
	// setCustomerGroupsForDealer
	//------------------------------------------------------------------------//
	/**
	 * setCustomerGroupsForDealer()
	 *
	 * Set which CustomerGroups a Dealer can sell on behalf of
	 * 
	 * Set which CustomerGroups a Dealer can sell on behalf of
	 *
	 * @param		int		$intDealerId	id of the dealer
	 * @param		array	$arrCustomerGroupIds	array of CustomerGroup ids
	 * @return		void	
	 * @method
	 */
	public static function setCustomerGroupsForDealer($intDealerId, $arrCustomerGroupIds)
	{
		// Delete existing records in the dealer_customer_group table
		$qryQuery		= new Query();
		$intDealerId	= intval($intDealerId);
		
		if (!$qryQuery->Execute("DELETE FROM dealer_customer_group WHERE dealer_id = $intDealerId;"))
		{
			throw new Exception("Failed to set CustomerGroups for Dealer: $intDealerId.  (Removal of old dealer - customer group associations failed) - " . $qryQuery->Error());
		}
		
		// Reset the auto incrementing counter for the dealer_customer_group table
		// (This really isn't worth doing, as it is only effective if the dealer is the last one to have records added to the dealer_customer_group table)
		
		// Insert records into the dealer_customer_group table
		$arrData = array(	"dealer_id"			=> $intDealerId,
							"customer_group_id"	=> NULL
						);
		$insDealerCustomerGroup = new StatementInsert("dealer_customer_group", $arrData);
		foreach ($arrCustomerGroupIds as $intCustomerGroupId)
		{
			$arrData['customer_group_id'] = $intCustomerGroupId;
			if ($insDealerCustomerGroup->Execute($arrData) === FALSE)
			{
				throw new Exception("Failed to set CustomerGroups for Dealer: $intDealerId. (inserting record into dealer_customer_group table failed) - ". $insDealerCustomerGroup->Error());
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
						"customerGroupId"	=> "customer_group_id"
					);
	}
}

?>

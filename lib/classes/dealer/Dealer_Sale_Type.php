<?php

//----------------------------------------------------------------------------//
// Dealer_Sale_Type
//----------------------------------------------------------------------------//
/**
 * Dealer_Sale_Type
 *
 * Models a dealer_sale_type record
 *
 * Models a dealer_sale_type record
 *
 * @class	Dealer_Sale_Type
 */
class Dealer_Sale_Type
{
	//------------------------------------------------------------------------//
	// getSaleTypeForDealer
	//------------------------------------------------------------------------//
	/**
	 * getSaleTypeForDealer()
	 *
	 * Returns array of SaleType ids that the dealer can perform sales of
	 * 
	 * Returns array of SaleType ids that the dealer can perform sales of
	 *
	 * @param		int		$intDealerId	id of the dealer
	 * @return		array of SaleType ids	
	 * @method
	 */
	public static function getSaleTypesForDealer($intDealerId)
	{
		static $selDealerSaleType;
		if (!isset($selDealerSaleType))
		{
			$selDealerSaleType = new StatementSelect("dealer_sale_type", self::getColumns(), "dealer_id = <DealerId>");
		}
		
		if ($selDealerSaleType->Execute(Array("DealerId"=>$intDealerId)) === FALSE)
		{
			throw new Exception("Failed to retrieve records from the Dealer_Sale_Type table for dealer with id: $intDealerId - ". $selDealerSaleType->Error());
		}
		
		$arrRecordSet = $selDealerSaleType->FetchAll();
		
		$arrSaleTypeIds = Array();
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrSaleTypeIds[$arrRecord['saleTypeId']] = $arrRecord['saleTypeId'];
		}

		return $arrSaleTypeIds;
	}
	
	//------------------------------------------------------------------------//
	// setSaleTypesForDealer
	//------------------------------------------------------------------------//
	/**
	 * setSaleTypesForDealer()
	 *
	 * Set which SaleTypes a Dealer can sell on behalf of
	 * 
	 * Set which SaleTypes a Dealer can sell on behalf of
	 *
	 * @param		int		$intDealerId		id of the dealer
	 * @param		array	$arrSaleTypeIds		array of SaleType ids
	 * @return		void	
	 * @method
	 */
	public static function setSaleTypesForDealer($intDealerId, $arrSaleTypeIds)
	{
		// Delete existing records in the Dealer_Sale_Type table
		$qryQuery		= new Query();
		$intDealerId	= intval($intDealerId);
		
		if (!$qryQuery->Execute("DELETE FROM dealer_sale_type WHERE dealer_id = $intDealerId;"))
		{
			throw new Exception("Failed to set SaleTypes for Dealer: $intDealerId.  (Removal of old dealer - SaleType associations failed) - " . $qryQuery->Error());
		}
		
		// Reset the auto incrementing counter for the dealer_sale_type table
		// (This really isn't worth doing, as it is only effective if the dealer is the last one to have records added to the dealer_sale_type table)
		
		// Insert records into the dealer_sale_type table
		$arrData = array(	"dealer_id"		=> $intDealerId,
							"sale_type_id"	=> NULL
						);
		$insDealerSaleType = new StatementInsert("dealer_sale_type", $arrData);
		foreach ($arrSaleTypeIds as $intSaleTypeId)
		{
			$arrData['sale_type_id'] = $intSaleTypeId;
			if ($insDealerSaleType->Execute($arrData) === FALSE)
			{
				throw new Exception("Failed to set SaleTypes for Dealer: $intDealerId. (inserting record into dealer_sale_type table failed) - ". $insDealerSaleType->Error());
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
						"saleTypeId"		=> "sale_type_id"
					);
	}
}

?>

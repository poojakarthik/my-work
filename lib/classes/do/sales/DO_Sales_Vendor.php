<?php

class DO_Sales_Vendor extends DO_Sales_Base_Vendor
{
	protected static $_cache = NULL;
	
	// Returns array of all the objects, with the id of the records as the key
	public static function getAll($bolForceRefresh=FALSE)
	{
		if (!isset(self::$_cache) || $bolForceRefresh)
		{
			$arrProps		= self::getPropertyDataSourceMappings();
			$arrStatuses	= self::getFor(NULL, TRUE, "{$arrProps['name']} ASC");
			self::$_cache	= array();
			foreach ($arrStatuses as $objStatus)
			{
				self::$_cache[$objStatus->id] = $objStatus;
			}
			 
		}
		
		return self::$_cache;
	}
	
	public static function getForId($intId)
	{
		$arrStatuses = self::getAll();
		return (array_key_exists($intId, $arrStatuses))? $arrStatuses[$intId] : NULL;
	}

	// THIS should not be used.  Instead use DO_Sales_Dealer->getVendors()
	/*
	 * function getListOfVendors()
	 *
	 * Returns an array, builds a list of vendors.
	 */
	static function getListOfVendors($intDealerId)
	{

		$dataSource = self::getDataSource();

		$strSQL = "SELECT id,description 
		FROM vendor 
		WHERE id IN (SELECT vendor_id FROM dealer_vendor WHERE dealer_id = '$intDealerId')";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to build a list of vendors: " . $result->getMessage());

		}

		$arrVendorList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrVendorList;

	}

}

?>
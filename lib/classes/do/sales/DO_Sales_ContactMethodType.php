<?php

class DO_Sales_ContactMethodType extends DO_Sales_Base_ContactMethodType
{
	// These constants should directly relate to the records of this table
	const EMAIL		= 1;
	const FAX		= 2;
	const PHONE		= 3;
	const MOBILE	= 4;
	

	private static $_arrCache = null;
	
	/**
	 * listAll()
	 *
	 * Returns an array of DO_Sales_ContactMethodType objects representing each record of the contact_method_type table
	 * The keys for the array are the associated id of each record
	 * This list is cached
	 *
	 * @return	array of DO_Sales_ContactMethodType objects		array keys are the relevent contact_method_type.id values
	 * @method
	 */
	public static function listAll($bolForceRefresh=false)
	{
		if (self::$_arrCache === null || $bolForceRefresh)
		{
			self::$_arrCache = array();
			$arrContactMethodTypesNotIndexed = self::getFor(null, true, 'name ASC');
			foreach ($arrContactMethodTypesNotIndexed as $doContactMethodType)
			{
				self::$_arrCache[$doContactMethodType->id] = $doContactMethodType;
			}
		}
		return self::$_arrCache;
	}

	/*
	 * function getContactMethodTypesList()
	 *
	 * Returns an array of contact methods from the contact_method_type table.
	 */
	static function getContactMethodTypesList()
	{

		$dataSource = self::getDataSource();

		$strSQL = "SELECT id,description
		FROM contact_method_type
		ORDER BY description";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to get contact methods: " . $result->getMessage());
		}

		$arrContactMethodTypesList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		return $arrContactMethodTypesList;

	}
	
	// Sanitises contact_method.details, based on the contact_method.contact_method_type_id
	// This functionality is located here as opposed to the DO_Sales_ContactMethod class, because sanitation of this property (contact_method.details) is dependent on its 
	// ContactMethodType.  And the reason why this method isn't static is because theoretically the sanitizing of the details could be dependent on specific values stored in 
	// the contact_method_type record, that this object relates to, much like the credit_card_type table and associated class. (although that is currently not the case for this class and table) 
	public function cleanContactMethodDetails($strDetails)
	{
		switch ($this->id)
		{
			case self::EMAIL:
				return DO_SalesSanitation::cleanEmailAddress($strDetails);

			case self::FAX:
			case self::PHONE:
			case self::MOBILE:
				return DO_SalesSanitation::cleanFNN($strDetails);

			default:
				throw new Exception("ContactMethodType: '{$this->name}' does not support cleaning details");
		}
	}
	
	public function isValidContactMethodDetails($strDetails)
	{
		switch ($this->id)
		{
			case self::EMAIL:
				return DO_SalesValidation::isValidEmailAddress($strDetails);

			case self::FAX:
			case self::PHONE:
				return DO_SalesValidation::isValidLandlineFNN($strDetails);

			case self::MOBILE:
				return DO_SalesValidation::isValidMobileMSN($strDetails);
		
			default:
				throw new Exception("ContactMethodType: '{$this->name}' does not support validating details");
		}
	}
}

?>
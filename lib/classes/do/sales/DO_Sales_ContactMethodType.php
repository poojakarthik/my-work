<?php

class DO_Sales_ContactMethodType extends DO_Sales_Base_ContactMethodType
{

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

}

?>
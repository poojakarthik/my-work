<?php

class DO_Sales_ContactTitle extends DO_Sales_Base_ContactTitle
{

	static function listAll()
	{
		return self::getFor(null, true);
	}

	/*
	 * function getSalutationList()
	 *
	 * Returns an array of titles from the contact_title table.
	 */
	static function getSalutationList()
	{

		$dataSource = self::getDataSource();

		$strSQL = "SELECT id,description
		FROM contact_title
		ORDER BY description";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to get titles: " . $result->getMessage());
		}

		$arrSalutationList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		return $arrSalutationList;

	}

}

?>
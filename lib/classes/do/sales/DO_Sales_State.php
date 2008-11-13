<?php

class DO_Sales_State extends DO_Sales_Base_State
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}

	/*
	 * function getStatesList()
	 *
	 * Returns an array of states from the state table.
	 */
	static function getStatesList()
	{

		$dataSource = self::getDataSource();

		$strSQL = "SELECT id,description
		FROM state
		ORDER BY description";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to Returns an array of states: " . $result->getMessage());

		}

		$arrStatesList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrStatesList;

	}

}

?>
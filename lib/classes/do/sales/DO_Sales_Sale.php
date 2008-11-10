<?php

class DO_Sales_Sale extends DO_Sales_Base_Sale
{
	const SEARCH_FOR_BUSINESS_NAME = "blah";
	
	const ORDER_BY_CONTACT_NAME		= "contactName";
	const ORDER_BY_ACCOUNT_NAME		= "accountName";
	const ORDER_BY_DEALER_ID		= "createdBy";
	const ORDER_BY_SALE_ID			= "id";
	const ORDER_BY_LAST_ACTIONED_ON	= "lastActionedOn";
	
	
	private static $_arrPaginationDetails = array(	"TotalRecordCount"	=> NULL,
													"PageRecordCount"	=> NULL,
													"CurrentOffset"		=> NULL,
													"FirstOffset"		=> NULL,
													"PreviousOffset"	=> NULL,
													"NextOffset"		=> NULL,
													"LastOffset"		=> NULL
												);
	
	// Performs a sale search based on lots of different things
	public static function searchFor($arrFilter=NULL, $arrSort=NULL, $intLimit=NULL, $intOffset=NULL)
	{
		$arrWhereParts		= array();
		$arrOrderByParts	= array();
		

		// Build WHERE clause
		// TODO! fix this
		if (is_array($arrFilter))
		{
			foreach ($arrFilter as $strColumn=>$arrStyle)
			{
				if (!array_key_exists($strColumn, $arrColumns))
				{
					// The column doesn't exist in the dealer table
					continue;
				}
				$strColumnType = $arrColumnTypes[$strColumn];
				
				switch ($arrStyle['Comparison'])
				{
					case '!=':
					case '=':
						if ($arrStyle['Value'] === NULL || (is_array($arrStyle['Value']) && empty($arrStyle['Value'])))
						{
							$strNegate = ($arrStyle['Comparison'] == '!=')? "NOT" : "";
							$arrWhereParts[] = $arrColumns[$strColumn] ." IS $strNegate NULL";
						}
						elseif (is_array($arrStyle['Value']))
						{
							$arrValues = array();
							foreach ($arrStyle['Value'] as $mixValue)
							{
								// prepare the values for being used in sql code
								switch ($strColumnType)
								{
									case 'integer':
									case 'boolean':
										$arrValues[] = intval($mixValue);
										break;
									case 'text':
										$arrValues[] = "'". $objDB->EscapeString($mixValue) ."'";
										break;
									default:
										throw new exception(__CLASS__ ."::". __METHOD__ ." - don't know how to handle data type, '$strColumnType'");
								}
							}
							
							$strNegate = ($arrStyle['Comparison'] == '!=')? "NOT" : "";
							$arrWhereParts[] = $arrColumns[$strColumn] ." $strNegate IN (". implode(", ", $arrValues) .")";
						}
						else
						{
							// prepare the values for being used in sql code
							switch ($strColumnType)
							{
								case 'integer':
								case 'boolean':
									$mixValue = intval($arrStyle['Value']);
									break;
								case 'text':
									$mixValue = "'". $objDB->EscapeString($arrStyle['Value']) ."'";
									break;
								default:
									throw new exception(__CLASS__ ."::". __METHOD__ ." - don't know how to handle data type, '$strColumnType'");
							}
							$strComparison = ($arrStyle['Comparison'] == '!=')? "!=" : "=";
							$arrWhereParts[] = $arrColumns[$strColumn] ." $strComparison $mixValue";
						}
						break;
				}
			}
		}
		
		$strWhere = (count($arrWhereParts) > 0)? implode(" AND ", $arrWhereParts) : NULL;
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				switch ($strColumn)
				{
					case ORDER_BY_CONTACT_NAME:
					case ORDER_BY_ACCOUNT_NAME:
					case ORDER_BY_DEALER_ID:
					case ORDER_BY_SALE_ID:
					case ORDER_BY_LAST_ACTIONED_ON:
						$arrOrderByParts[] = $strColumn . ($bolAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__CLASS__ ."::". __METHOD__ ." - Illegal sorting identifier: $strColumn");
						break;
				}
			}
		}
		$strOrderBy = (count($arrOrderByParts) > 0)? implode(", ", $arrOrderByParts) : NULL;
		
		// Build SELECT statement
		$strSaleTableName				= self::getDataSourceObjectName();
		$arrSaleTableProps				= self::getDataSourcePropertyMappings();
		$strSaleAccountTableName		= DO_Sales_SaleAccount::getDataSourceObjectName();
		$arrSaleAccountTableProps		= DO_Sales_SaleAccount::getDataSourcePropertyMappings();
		$strSaleStatusHistoryTableName	= DO_Sales_SaleStatusHistory::getDataSourceObjectName();
		$arrSaleStatusHistoryTableProps	= DO_Sales_SaleStatusHistory::getDataSourcePropertyMappings();
		$strContactSaleTableName		= DO_Sales_ContactSale::getDataSourceObjectName();
		$arrContactSaleTableProps		= DO_Sales_ContactSale::getDataSourcePropertyMappings();
		$strContactTableName			= DO_Sales_Contact::getDataSourceObjectName();
		$arrContactTableProps			= DO_Sales_Contact::getDataSourcePropertyMappings();
		
		
		
		$dataSource = DO_Sales_Sale::getDataSource();

		if (PEAR::isError($results = $dataSource->query($strSQL)))
		{
			throw new Exception('Failed to load records for ' . __CLASS__ . ' :: ' . $results->getMessage());
		}

		$details = $results->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		
		return self::getFor($strWhere, $strOrderBy, $intLimit, $intOffset);
	}

	public static function getPaginationDetails()
	{
		return self::$_arrPaginationDetails;
	}

	
}

?>
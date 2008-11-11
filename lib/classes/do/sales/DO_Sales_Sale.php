<?php

class DO_Sales_Sale extends DO_Sales_Base_Sale
{
	const SEARCH_FOR_VENDOR_ID		= 1;
	const SEARCH_FOR_SALE_TYPE		= 2;
	const SEARCH_FOR_SALE_STATUS	= 3;
	const SEARCH_FOR_DEALER_ID		= 4;
	const SEARCH_FOR_MANAGER_ID		= 5; // includes sales from all dealers under the manager
	
	
	const ORDER_BY_CONTACT_NAME		= "contact_name";
	const ORDER_BY_ACCOUNT_NAME		= "account_name";
	const ORDER_BY_SALE_ID			= "sale_id";
	const ORDER_BY_LAST_ACTIONED_ON	= "last_actioned_on";
	const ORDER_BY_SALE_STATUS_ID	= "sale|sale_status_id";
	const ORDER_BY_CREATED_BY		= "sale|created_by";
	const ORDER_BY_CREATED_ON		= "sale|created_on";
	const ORDER_BY_SALE_TYPE_ID		= "sale|sale_type_id";
	const ORDER_BY_SALE_ACCOUNT_VENDOR_ID = "sale_account|vendor_id";
	
	
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
		// TODO!
		$strWhereClause = "";
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				switch ($strColumn)
				{
					case self::ORDER_BY_CONTACT_NAME:
					case self::ORDER_BY_ACCOUNT_NAME:
					case self::ORDER_BY_SALE_ID:
					case self::ORDER_BY_LAST_ACTIONED_ON:
					case self::ORDER_BY_SALE_STATUS_ID:
					case self::ORDER_BY_CREATED_BY:
					case self::ORDER_BY_CREATED_ON:
					case self::ORDER_BY_SALE_TYPE_ID:
					case self::ORDER_BY_SALE_ACCOUNT_VENDOR_ID:
						$arrOrderByParts[] = str_replace('|', '.', $strColumn) . ($bolAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__METHOD__ ." - Illegal sorting identifier: $strColumn");
						break;
				}
			}
		}
		
		$strOrderByClause = (count($arrOrderByParts) > 0)? "ORDER BY ". implode(", ", $arrOrderByParts) : NULL;
		
		// Build LIMIT clause
		if ($intLimit !== NULL)
		{
			$strLimitClause = "LIMIT ". intval($intLimit);
			if ($intOffset !== NULL)
			{
				$strLimitClause .= " OFFSET ". intval($intOffset);
			}
			else
			{
				$intOffset = 0;
			}
		}
		else
		{
			$strLimitClause = "";
		}
		
		// Build SELECT statement
		$strSaleTableName	= self::getDataSourceObjectName();
		$arrSaleTableProps	= self::getPropertyDataSourceMappings();
		$strSaleId			= $strSaleTableName .".". self::getDataSourceIdName();
		$strSaleSaleStatus	= $strSaleTableName .".". $arrSaleTableProps["saleStatusId"];
		$strSaleCreatedBy	= $strSaleTableName .".". $arrSaleTableProps["createdBy"];
		$strSaleCreatedOn	= $strSaleTableName .".". $arrSaleTableProps["createdOn"];
		$strSaleSaleTypeId	= $strSaleTableName .".". $arrSaleTableProps["saleTypeId"];
		
		$strSaleAccountTableName	= DO_Sales_SaleAccount::getDataSourceObjectName();
		$arrSaleAccountTableProps	= DO_Sales_SaleAccount::getPropertyDataSourceMappings();
		$strSaleAccountSaleId		= $strSaleAccountTableName .".". $arrSaleAccountTableProps["saleId"];
		$strSaleAccountBusinessName	= $strSaleAccountTableName .".". $arrSaleAccountTableProps["businessName"];
		$strSaleAccountTradingName	= $strSaleAccountTableName .".". $arrSaleAccountTableProps["tradingName"];
		
		$strSaleStatusHistoryTableName	= DO_Sales_SaleStatusHistory::getDataSourceObjectName();
		$arrSaleStatusHistoryTableProps	= DO_Sales_SaleStatusHistory::getPropertyDataSourceMappings();
		$strSaleStatusHistorySaleId		= $strSaleStatusHistoryTableName .".". $arrSaleStatusHistoryTableProps["saleId"];
		$strSaleStatusHistoryChangedOn	= $strSaleStatusHistoryTableName .".". $arrSaleStatusHistoryTableProps["changedOn"];
		
		$strContactSaleTableName				= DO_Sales_ContactSale::getDataSourceObjectName();
		$arrContactSaleTableProps				= DO_Sales_ContactSale::getPropertyDataSourceMappings();
		$strContactSaleSaleId					= $strContactSaleTableName .".". $arrContactSaleTableProps["saleId"];
		$strContactSaleContactAssociationTypeId	= $strContactSaleTableName .".". $arrContactSaleTableProps["contactAssociationTypeId"];
		$strContactSaleContactId				= $strContactSaleTableName .".". $arrContactSaleTableProps["contactId"];
		
		$strContactTableName	= DO_Sales_Contact::getDataSourceObjectName();
		$arrContactTableProps	= DO_Sales_Contact::getPropertyDataSourceMappings();
		$strContactId			= $strContactTableName .".". DO_Sales_Contact::getDataSourceIdName();
		$strContactFirstName	= $strContactTableName .".". $arrContactTableProps["firstName"];
		$strContactLastName		= $strContactTableName .".". $arrContactTableProps["lastName"];
		$strContactMiddleNames	= $strContactTableName .".". $arrContactTableProps["middleNames"];
		
		$intContactAssociationTypePrimary = DO_Sales_ContactAssociationType::PRIMARY;
		
		$strFromClause = "FROM $strSaleTableName 
INNER JOIN $strSaleAccountTableName ON $strSaleId = $strSaleAccountSaleId
INNER JOIN (	SELECT $strSaleStatusHistorySaleId, MAX($strSaleStatusHistoryChangedOn) AS last_actioned_on
				FROM $strSaleStatusHistoryTableName
				GROUP BY $strSaleStatusHistorySaleId
				) AS $strSaleStatusHistoryTableName ON $strSaleId = $strSaleStatusHistorySaleId
LEFT JOIN $strContactSaleTableName ON ($strSaleId = $strContactSaleSaleId AND $strContactSaleContactAssociationTypeId = $intContactAssociationTypePrimary)
LEFT JOIN $strContactTableName ON ($strContactId = $strContactSaleContactId)
";

		// Create query to find out how many rows there are in total
		$strRowCountQuery = "SELECT COUNT($strSaleId) as row_count $strFromClause $strWhereClause;";
		
		// Create proper query
		$strQuery = "SELECT $strSaleId AS sale_id,
COALESCE($strSaleAccountBusinessName, $strSaleAccountTradingName) AS account_name,
($strContactFirstName || COALESCE(' ' || $strContactMiddleNames, '') || ' ' || $strContactLastName) AS contact_name, 
$strSaleStatusHistoryTableName.last_actioned_on AS last_actioned_on
$strFromClause $strWhereClause $strOrderByClause $strLimitClause;";
		
		$dataSource = self::getDataSource();

		// Execute the query
		if (PEAR::isError($results = $dataSource->query($strQuery)))
		{
			throw new Exception('Failed to retreive records for '. __METHOD__ ." - using query: $strQuery - ". $results->getMessage());
		}

		$arrRecordSet = $results->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		$arrSales = array();
		foreach ($arrRecordSet as $arrRecord)
		{
			// I don't think the recordset will have things properly type cast, so do it here
			$intSaleId = intval($arrRecord['sale_id']);
			$arrSales[$intSaleId] = self::getForId($intSaleId);
		}

		// Perform Pagination Calculations
		if (PEAR::isError($results = $dataSource->query($strRowCountQuery)))
		{
			throw new Exception('Failed to calculate row count for '. __METHOD__ ." - ". $results->getMessage());
		}

		$intTotalRows = intval($results->fetchOne());
		
		if ($intLimit === NULL)
		{
			// All records were retrieved
			self::$_arrPaginationDetails = array(	"TotalRecordCount"	=> $intTotalRows,
													"PageRecordCount"	=> count($arrSales),
													"CurrentOffset"		=> 0,
													"FirstOffset"		=> 0,
													"PreviousOffset"	=> 0,
													"NextOffset"		=> 0,
													"LastOffset"		=> 0
												);
		}
		else
		{
			$intTotalRecordCount	= $intTotalRows;
			$intPageRecordCount		= count($arrSales);
			$intCurrentOffset		= $intOffset;
			$intFirstOffset			= 0;
			$intPreviousOffset		= max($intCurrentOffset - $intLimit, 0);
			$intLastOffset			= max(floor(($intTotalRecordCount - 1) / $intLimit) * $intLimit, 0);
			$intNextOffset			= min($intCurrentOffset + $intLimit, $intLastOffset);
			
			self::$_arrPaginationDetails = array(	"TotalRecordCount"	=> $intTotalRecordCount,
													"PageRecordCount"	=> $intPageRecordCount,
													"CurrentOffset"		=> $intCurrentOffset,
													"FirstOffset"		=> $intFirstOffset,
													"PreviousOffset"	=> $intPreviousOffset,
													"NextOffset"		=> $intNextOffset,
													"LastOffset"		=> $intLastOffset
												);
		}
		
		return $arrSales;
	}

	public static function getPaginationDetails()
	{
		return self::$_arrPaginationDetails;
	}

	
}

?>
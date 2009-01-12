<?php
//----------------------------------------------------------------------------//
// Sales_Report
//----------------------------------------------------------------------------//
/**
 * Sales_Report
 *
 * Encapsulates Sales Reporting functionality
 *
 * Encapsulates Sales Reporting functionality
 *
 * @class	Sales_Report
 */
class Sales_Report
{
	const REPORT_TYPE_COMMISSIONS		= "Commissions";
	const REPORT_TYPE_OUTSTANDING_SALES	= "Outstanding";
	const REPORT_TYPE_SALE_ITEM_SUMMARY	= "SaleItemSummary";
	const REPORT_TYPE_SALE_ITEM_STATUS	= "SaleItemStatus";
	const REPORT_TYPE_SALE_ITEM_HISTORY	= "SaleItemHistory";
	const REPORT_TYPE_SALE_HISTORY		= "SaleHistory";
	
	private static $arrReportTypes = array(		self::REPORT_TYPE_COMMISSIONS		=> array(	"Name"			=> "Commissions Report",
																								"Description"	=> "Commissions Report"
																							),
												self::REPORT_TYPE_OUTSTANDING_SALES	=> array(	"Name"			=> "Outstanding Sales Report",
																								"Description"	=> "Outstanding Sales Report"
																							),
												self::REPORT_TYPE_SALE_ITEM_SUMMARY	=> array(	"Name"			=> "Sale Item Summary Report",
																								"Description"	=> "Sale Item Summary Report"
																							),
												self::REPORT_TYPE_SALE_ITEM_STATUS	=> array(	"Name"			=> "Sale Item Status Report",
																								"Description"	=> "Sale Item Status Report"
																							),
												self::REPORT_TYPE_SALE_ITEM_HISTORY	=> array(	"Name"			=> "Sale Item History Report",
																								"Description"	=> "Sale Item History Report"
																							),
												self::REPORT_TYPE_SALE_HISTORY		=> array(	"Name"			=> "Sale History Report",
																								"Description"	=> "Sale History Report"
																							)
											);
	
	public static function getReportTypes()
	{
		return self::$arrReportTypes;
	}
	
}
 
?>

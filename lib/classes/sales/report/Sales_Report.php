<?php
//----------------------------------------------------------------------------//
// Sales_Report
//----------------------------------------------------------------------------//
/**
 * Sales_Report
 *
 * Base class for Sales Report classes, which will define specific Sales Reports
 *
 * Base class for Sales Report classes, which will define specific Sales Reports
 *
 * @class	Sales_Report
 * @abstract
 */
abstract class Sales_Report
{
	const REPORT_TYPE_COMMISSIONS		= "Commissions";
	const REPORT_TYPE_OUTSTANDING_SALES	= "Outstanding";
	const REPORT_TYPE_SALE_ITEM_SUMMARY	= "SaleItemSummary";
	const REPORT_TYPE_SALE_ITEM_STATUS	= "SaleItemStatus";
	const REPORT_TYPE_SALE_ITEM_HISTORY	= "SaleItemHistory";
	const REPORT_TYPE_SALE_HISTORY		= "SaleHistory";
	
	const RENDER_MODE_IN_PAGE_HTML	= "InPageHTML";
	const RENDER_MODE_HTML			= "HTML";
	const RENDER_MODE_XML			= "XML";
	const RENDER_MODE_CSV			= "CSV";
	const RENDER_MODE_EXCEL			= "Excel";
	
	protected static $_arrReportTypes = array(	self::REPORT_TYPE_COMMISSIONS		=> array(	"Name"			=> "Commissions Report",
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
	
	protected static $_arrRenderModes = array(	self::RENDER_MODE_IN_PAGE_HTML	=> array(	"Name"				=> "In Page HTML",
																							"Description"		=> "In Page HTML",
																							"MimeType"			=> "text/html",
																							"FileExtension"		=> NULL
																						),
												self::RENDER_MODE_HTML	=> array(	"Name"				=> "HTML",
																					"Description"		=> "HTML",
																					"MimeType"			=> "text/html",
																					"FileExtension"		=> "html"
																				),
												self::RENDER_MODE_XML	=> array(	"Name"				=> "XML",
																					"Description"		=> "XML",
																					"MimeType"			=> "application/xml",
																					"FileExtension"		=> "xml"
																				),
												self::RENDER_MODE_CSV	=> array(	"Name"				=> "CSV",
																					"Description"		=> "CSV",
																					"MimeType"			=> "text/csv",
																					"FileExtension"		=> "csv"
																				),
												self::RENDER_MODE_EXCEL	=> array(	"Name"				=> "Excel",
																					"Description"		=> "Excel",
																					"MimeType"			=> "application/excel",
																					"FileExtension"		=> "xls"
																				)
											);
	
	// Returns an array defining all the report types
	public static function getReportTypes()
	{
		return self::$_arrReportTypes;
	}
	
	// Returns the details of a specific Sales ReportType as an associated array
	public static function getReportTypeDetails($strReportType)
	{
		if (array_key_exists($strReportType, self::$_arrReportTypes))
		{
			return self::$_arrReportTypes[$strReportType];
		}
		throw new Exception(__METHOD__ ." - Invalid ReportType: $strReportType");
	}
	
	// Returns an array defining all render modes
	public static function getRenderModes()
	{
		return self::$_arrRenderModes;
	}

	// Returns the details of a specific RenderMode as an associated array
	public static function getRenderModeDetails($strRenderMode)
	{
		if (array_key_exists($strRenderMode, self::$_arrRenderModes))
		{
			return self::$_arrRenderModes[$strRenderMode];
		}
		throw new Exception(__METHOD__ ." - Invalid RenderMode: $strRenderMode");
	}

	
	// Returns a specific object of one of the Sales_Report extended classes, specific to $strReportType
	public static function getNewReport($strReportType)
	{
		// Check that $strReportType is a valid report type
		if (!array_key_exists($strReportType, self::$_arrReportTypes))
		{
			throw new Exception(__METHOD__ ." - Unknown report type: $strReportType");
		}
		
		$strReportClassName = __CLASS__ ."_". $strReportType;

		if (class_exists($strReportClassName))
		{
			return new $strReportClassName;
		}
		else
		{
			throw new Exception(__METHOD__ ." - Cannot find report class: $strReportClassName");
		}
	}
	
	// Sets the constraints for the report (and validates them)
	abstract public function setConstraints($objConstraints);
	
	// Generates the report
	// This will return the number of records in the report
	abstract public function buildReport();
	
	// Retrieves the report, in the RenderMode specified, assuming the report can be rendered in this mode
	abstract public function getReport($strRenderMode);
	
	// Returns an array defining the allowable RenderModes for the specific report type
	abstract public function getAllowableRenderModes();
	
	// Returns detailed report name, possibly based on the constraints of the report
	abstract public function getDetailedReportName();
}
 
?>

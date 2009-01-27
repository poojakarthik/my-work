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
	const REPORT_TYPE_OUTSTANDING_SALES	= "OutstandingSales";
	const REPORT_TYPE_SALE_ITEM_SUMMARY	= "SaleItemSummary";
	const REPORT_TYPE_SALE_ITEM_STATUS	= "SaleItemStatus";
	const REPORT_TYPE_SALE_ITEM_HISTORY	= "SaleItemHistory";
	const REPORT_TYPE_SALE_HISTORY		= "SaleHistory";
	
	const RENDER_MODE_IN_PAGE_HTML	= "InPageHTML";
	const RENDER_MODE_HTML			= "HTML";
	const RENDER_MODE_XML			= "XML";
	const RENDER_MODE_CSV			= "CSV";
	const RENDER_MODE_EXCEL			= "Excel";
	
	// This should be set to the REPORT_TYPE_ constant specific to the Sales_Report extended class
	protected $_reportType;
	
	// This should store the ids of all the allowable render modes specific to the Sales_Report extended class
	protected $_arrAllowableRenderModes = array();

	// Stores the report data, with each element in the array representing a row in the resultant report table
	protected $_arrReportData;
	
	// This should store a detailed description of what the specific report does
	protected $_strDescription = "INSERT REPORT DESCRIPTION HERE";

	// Store the columns of _arrReportData to include in the report, and their labels
	// All these columns are recorded for each record of the report
	protected $_arrColumns;

	// This will store the columns to actually use when rendering the report
	protected $_arrSelectedColumns;
	
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
																							)
												/* self::REPORT_TYPE_SALE_ITEM_HISTORY	=> array(	"Name"			=> "Sale Item History Report",
																								"Description"	=> "Sale Item History Report"
																							),
												self::REPORT_TYPE_SALE_HISTORY		=> array(	"Name"			=> "Sale History Report",
																								"Description"	=> "Sale History Report"
																							)
												*/
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
		throw new Exception("Invalid ReportType: $strReportType");
	}
	
	// Returns an array defining all render modes
	public static function getAllRenderModes()
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
		throw new Exception("Invalid RenderMode: $strRenderMode");
	}

	
	// Returns a specific object of one of the Sales_Report extended classes, specific to $strReportType
	public static function getNewReport($strReportType)
	{
		// Check that $strReportType is a valid report type
		if (!array_key_exists($strReportType, self::$_arrReportTypes))
		{
			throw new Exception("Unknown report type: $strReportType");
		}
		
		$strReportClassName = __CLASS__ ."_". $strReportType;

		if (class_exists($strReportClassName))
		{
			return new $strReportClassName;
		}
		else
		{
			throw new Exception("Cannot find report class: $strReportClassName");
		}
	}
	
	// Sets the constraints for the report (and validates them)
	abstract public function setConstraints($objConstraints);
	
	// Generates the report
	// This will return the number of records in the report
	abstract public function buildReport();
	
	// Returns detailed report name, possibly based on the constraints of the report
	abstract public function getDetailedReportName();

	// Returns an array defining the allowable RenderModes for the specific report type
	// The array will just store the RenderMode constants
	public function getAllowableRenderModes()
	{
		return $this->_arrAllowableRenderModes;
	}

	public function getDescription()
	{
		return $this->_strDescription;
	}

	// Returns array of allowable columns for the report
	// The key will be the column constant, and the value will be a description of the field
	public function getAllowableColumns()
	{
		return $this->_arrColumns;
	}
	
	// $arrColumns is an array of the column constants (note that this is not an associative array)
	public function setColumns($arrColumns)
	{
		// Make sure it's an array and there's something in it
		if (!(is_array($arrColumns) && count($arrColumns)))
		{
			throw new Exception("Invalid columns declaration");
		}
		
		$this->_arrSelectedColumns = array();
		foreach ($arrColumns as $column)
		{
			if (!array_key_exists($column, $this->_arrColumns))
			{
				throw new Exception("Invalid column, '$column', for ". self::$_arrReportTypes[$this->_reportType]['Name']);
			}
			$this->_arrSelectedColumns[] = $column;
		}
	}

	// Retrieves the report, in the RenderMode specified, assuming the report can be rendered in this mode
	public function getReport($strRenderMode)
	{
		if (!isset($this->_arrSelectedColumns))
		{
			// Specific columns have not been declared
			$this->_arrSelectedColumns = array_keys($this->_arrColumns);
		}

		if (!in_array($strRenderMode, $this->_arrAllowableRenderModes))
		{
			throw new Exception("Invalid Render Mode, '$strRenderMode', for ". self::$_arrReportTypes[$this->_reportType]['Name']);
		}
		
		switch ($strRenderMode)
		{
			case Sales_Report::RENDER_MODE_EXCEL:
				$strReport = $this->_translateToExcel();
				break;
				
			case Sales_Report::RENDER_MODE_CSV:
				$strReport = $this->_translateToCSV();
				break;
				
			default:
				throw new Exception("Unknown Render Mode '$strRenderMode'");
				break;
		}
		
		return $strReport;
	}
	
	protected function _translateToCSV()
	{
		$resTempFile = tmpfile();
		
		if ($resTempFile === FALSE)
		{
			throw new Exception("Error creating temporary csv file");
		}
		
		// Build the header record
		$arrHeader = array();
		foreach ($this->_arrSelectedColumns as $column)
		{
			$arrHeader[] = $this->_arrColumns[$column];
		}
		
		if (fputcsv($resTempFile, $arrHeader) === FALSE)
		{
			throw new Exception("Error writing header row to temporary csv file");
		}
		
		// Now write each of the rows
		foreach ($this->_arrReportData as $arrDetails)
		{
			$arrRow = array();
			foreach ($this->_arrSelectedColumns as $column)
			{
				$arrRow[] = $arrDetails[$column];
			}
			
			if (fputcsv($resTempFile, $arrRow) === FALSE)
			{
				throw new Exception("Error writing row to temporary csv file");
			}
		}
		
		// Move the file pointer to the begining of the file
		if (rewind($resTempFile) === FALSE)
		{
			throw new Exception("Error rewinding temporary csv file");
		}

		$strBuffer = "";
		$strReport = "";
		while (($strBuffer = fread($resTempFile, 100000)) != '')
		{
			$strReport .= $strBuffer;
		}
		if ($strBuffer === FALSE)
		{
			throw new Exception("Error reading temporary csv file");
		}
		
		fclose($resTempFile);
		
		return $strReport;
	}

	// Converts _arrReportData into Excel markup (Not real excel format, but instead a very simple html markup, which excel accepts)
	protected function _translateToExcel()
	{
		// Build the header row
		$strHeaderRow = "";
		foreach ($this->_arrSelectedColumns as $column)
		{
			$strHeaderRow .= "\t\t\t\t\t<th>". htmlspecialchars($this->_arrColumns[$column]) ."</th>\n";
		}
		
		// Build the rows
		$strRows = "";
		foreach ($this->_arrReportData as $arrDetails)
		{
			$strRow = "";
			foreach ($this->_arrSelectedColumns as $column)
			{
				$strRow .= "\t\t\t\t\t<td>". htmlspecialchars($arrDetails[$column]). "</td>\n";
			}
			
			$strRows .= "\t\t\t\t<tr>\n$strRow\t\t\t\t</tr>\n";
		}
		
		$arrRenderMode	= Sales_Report::getRenderModeDetails(Sales_Report::RENDER_MODE_EXCEL);
		$strMimeType	= $arrRenderMode['MimeType'];

		// Put it all together
		$strReport = "<html>
	<head>
		<meta http-equiv=\"content-type\" content=\"$strMimeType\">
	</head>
	<body>
		<table border=\"1\">
			<thead>
				<tr>
$strHeaderRow
				</tr>
			</thead>
			<tbody>
$strRows
			</tbody>
		</table>
	</body>
</html>";

		return $strReport;
	}

}
 
?>

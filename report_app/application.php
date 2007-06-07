<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		report
 * @author		Rich 'Waste' Davis
 * @version		7.06
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ApplicationReport
//----------------------------------------------------------------------------//
/**
 * ApplicationReport
 *
 * Reporting application
 *
 * Reporting application
 *
 *
 * @prefix		app
 *
 * @package		report
 * @class		ApplicationReport
 */
 class ApplicationReport extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationReport
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		$this->_rptReport	= new Report("Report Report (wtfmate) for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au", TRUE, "dispatch@voiptelsystems.com.au");
		
		// Statements
		$this->_selReports		= new StatementSelect("DataReportSchedule", "*", "Status = ".REPORT_WAITING);
		$this->_selDataReport	= new StatementSelect("DataReport", "Name, SQLTable, SQLSelect, SQLWhere, SQLGroupBy", "Id = <DataReport>");
		
		$arrColumns = Array();
		$arrColumns['Status']		= NULL;
		$arrColumns['GeneratedOn']	= new MySQLFunction("NOW()");
		$this->_ubiReport		= new StatementUpdateById("DataReportSchedule", $arrColumns);
	}
	
	//------------------------------------------------------------------------//
	// Execute
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Execute the application
	 *
	 * Execute the application
	 *
	 * @return	array		
	 *
	 * @method
	 */
 	function Execute()
 	{
		// Get all REPORT_WAITING Reports
		$intTotal	= $this->_selReports->Execute();
		$intPassed	= 0;
		while ($arrReport = $this->_selReports->Fetch())
		{
			// Report
			$this->_rptReport->AddMessage(" + Generating Report #{$arrReport['Id']}...\t\t\t", FALSE);
			
			// Get DataReport Details
			$this->_selDataReport->Execute($arrReport);
			$arrDataReport = $this->_selDataReport->Fetch();
			
			// Prepare Columns and Where Array
			$arrAliases	= unserialize($arrReport['SQLSelect']);
			$arrColumns = Array();			
			foreach ($arrAliases as $strAlias)
			{
				$arrColumns[$strAlias] = $arrDataReport[$strAlias];
			}
			$arrWhere	= unserialize();
			
			// Instanciate & Run Data Report
			$selReportSelect = new StatementSelect($arrDataReport['SQLTable'], $arrColumns, $arrDataReport['SQLWhere']);
			$selReport->Execute();
			$arrData = $selReport->FetchAll();
			
			// Export Data
			switch ($arrReport['RenderTarget'])
			{
				case REPORT_TARGET_CSV:
					$arrReport['Status'] = ($strFile = $this->_ExportCSV($arrData, $arrDataReport['Name'])) ? REPORT_GENERATED : REPORT_GENERATE_FAILED;
					break;
				
				case REPORT_TARGET_XLS:
					$arrReport['Status'] = ($strFile = $this->_ExportXLS($arrData, $arrDataReport['Name'], $arrReport)) ? REPORT_GENERATED : REPORT_GENERATE_FAILED;
					break;
					
				default:
					$arrReport['Status'] = REPORT_BAD_RENDER_TARGET;
			}
			
			// Email report
			if ($arrReport['Status'] == REPORT_GENERATED)
			{
				if (!$this->_selEmployee->Execute(Array('Id' => $arrReport['Employee'])))
				{
					// No valid email address (this shouldn't happen!)
					$arrReport['Status'] = REPORT_EMAIL_FAILED;
				}
				else
				{
					// Email the Report
					$arrEmployee = $this->_selEmployee->Fetch();
					
					// Generate Content
					$strContent	=	"Dear {$arrEmployee['FirstName']},\n\n" .
									"Attached is the viXen Data Report ({$arrDataReport['Name']}) you requested on {$arrReport['CreatedOn']}.\n\n" .
									"Pablo\nYellow Billing Mascot";
					
		 			$arrHeaders = Array	(
						'From'		=> "reports@yellowbilling.com.au",
						'Subject'	=> "{$arrDataReport['Name']} requested on {$arrReport['CreatedOn']}"
					);
					
		 			$mimMime	= new Mail_mime("\n");
		 			$mimMime->setTXTBody($strContent);
		 			$mimMime->addAttachment($strFile, 'application/x-msexcel');
		 			
					$strBody	= $mimMime->get();
					$strHeaders	= $mimMime->headers($arrHeaders);
		 			$emlMail	=& Mail::factory('mail');
		 			
		 			// Send the email
		 			if (!$emlMail->send($arrEmployee['Email'], $strHeaders, $strBody))
		 			{
		 				$arrReport['Status'] = REPORT_EMAIL_FAILED;
		 			}
				}
			}
			
			// Update the Schedule entry
			$arrReport['GeneratedOn']	= new MySQLFunction("NOW()");
			$this->_ubiReport->Execute($arrReport);
			
			// Report based on Status
			switch ($arrReport['Status'])
			{
				case REPORT_GENERATED:
					$this->_rptReport->AddMessage("[   OK   ]");
					$intPassed++;
					break;
					
				case REPORT_EMAIL_FAILED:
					$this->_rptReport->AddMessage("[ FAILED ]\n\tReason: Email attempt failed");
					break;
					
				case REPORT_BAD_RENDER_TARGET:
					$this->_rptReport->AddMessage("[ FAILED ]\n\tReason: Invalid Render Target Specified");
					break;
					
				case REPORT_GENERATE_FAILED:
					$this->_rptReport->AddMessage("[ FAILED ]\n\tReason: Generation Attempt Failed");
					break;
				
				default:
					$this->_rptReport->AddMessage("[ FAILED ]");
					break;
			}
		}
		
		return Array('Total' => $intTotal, 'Passed' => $intPassed);
	}
	
	
	//------------------------------------------------------------------------//
	// _ExportCSV
	//------------------------------------------------------------------------//
	/**
	 * _ExportCSV()
	 *
	 * Exports a MySQL resultset to a CSV document
	 *
	 * Exports a MySQL resultset to a CSV document
	 *
	 * @param	array	$arrData	MySQL resultset to generate from
	 * @param	string	$strName	Name of the Report
	 *
	 * @return	string				Path to the file generated
	 *
	 * @method
	 */
 	private function _ExportCSV($arrData, $strReportName)
 	{
 		// Open file
 		$strPath		= "/home/vixen_upload/datareport/$strName - ".date("d/M/Y h:i:s A").".csv";
 		$ptrFile		= fopen($strPath, "w");
 		$strDelimiter	= ';';
		
 		// Set column headers
 		$arrColumns		= array_keys($arrData[0]);
 		foreach ($arrColumns as $strColumn)
 		{
 			fwrite("\"$strColumn\";");
 		}
 		fwrite("\n");
 		
 		// Write the data
 		foreach ($arrData as $arrRow)
 		{
 			foreach ($arrRow as $arrField)
 			{
 				fwrite("\"{$arrField['Value']}\";");
 			}
 			fwrite("\n");
 		}
 		
 		fclose($ptrFile);
 		
 		return $strPath;
 	}
	
	
	//------------------------------------------------------------------------//
	// _ExportXLS
	//------------------------------------------------------------------------//
	/**
	 * _ExportXLS()
	 *
	 * Exports a MySQL resultset to an XLS document
	 *
	 * Exports a MySQL resultset to an XLS document
	 *
	 * @param	array	$arrData	MySQL resultset to generate from
	 * @param	string	$strName	Name of the Report
	 * @param	array	$arrReport	Report data
	 *
	 * @return	string				Path to the file generated
	 *
	 * @method
	 */
 	private function _ExportXLS($arrData, $strReportName, $arrReport)
 	{
 		$strPath		= "/home/vixen_upload/datareport/$strName - ".date("d/M/Y h:i:s A").".xls";
 		
		// Generate Excel 5 Workbook
		$wkbWorkbook = new Spreadsheet_Excel_Writer($strPath);
		$wksWorksheet =& $wkbWorkbook->addWorksheet();
		
		// Set up formatting styles
		$arrFormat = $this->_InitExcelFormats($wkbWorkbook);
		
		// Add in the title row
 		$arrColumns	= array_keys($arrData[0]);
 		foreach ($arrColumns as $intKey=>$strColumn)
 		{
 			$wksWorksheet->write(0, $intKey+1, $strColumn, $arrFormat['Title']);
 		}
		
		// Add in data rows
		$arrSQLSelect	= unserialize($arrReport['SQLSelect']);
		$arrExcelCols	= Array();
		$intRow			= 0;
		foreach ($arrData as $intRow=>$arrRow)
		{
			$intCol = 1;
			foreach ($arrRow as $strName=>$mixField)
			{
				$arrExcelCols[$strName]['Col'] = $intCol;
				
				// If an output type is specified then use it, else 'best guess'
				switch ($arrSQLSelect[$strName]['Type'])
				{
					case EXCEL_TYPE_CURRENCY:
						$wksWorksheet->writeNumber($intRow+1, $intCol, (float)$mixField	, $arrFormat['Currency']);
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['CurrencyTotal'];
						break;
						
					case EXCEL_TYPE_INTEGER:
						$wksWorksheet->writeNumber($intRow+1, $intCol, (int)$mixField	, $arrFormat['Integer']);
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['IntegerTotal'];
						break;
						
					case EXCEL_TYPE_PERCENTAGE:
						$wksWorksheet->writeNumber($intRow+1, $intCol, (int)$mixField	, $arrFormat['Percentage']);
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['PercentageTotal'];
						break;
					
					// Best Guess
					default:
						if (is_int($mixField['Value']))
						{
							// Integer
							$wksWorksheet->write($intRow+1, $intCol, $mixField, $arrFormat['Integer']);
							$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['IntegerTotal'];
						}
						elseif (IsValidFNN($mixField))
						{
							// FNN
							$wksWorksheet->write($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
						}
						else
						{
							// Just a string
							$wksWorksheet->writeString($intRow+1, $intCol, $mixField);
						}
				}
				$intCol++;
			}
		}
		
		// Draw a Horizontal Line to separate totals from data
		$wksWorksheet->writeString($intRow+2, 0, "Grand Totals:", $arrFormat['TotalText']);
		for ($intCol = 1; $intCol < count($arrExcelCols); $intCol++)
		{
			$wksWorksheet->writeString($intRow+2, $intCol, "", $arrFormat['TotalText']);
		}
		
		// Add totals, if specified
		foreach ($arrSQLSelect as $strName=>$arrField)
		{
			// Calculate Cell Range
			$strCellStart	= Spreadsheet_Excel_Writer::rowcolToCell(2					, $arrExcelCols[$strName]['Col']);
			$strCellEnd		= Spreadsheet_Excel_Writer::rowcolToCell(count($arrData)+2	, $arrExcelCols[$strName]['Col']);
			
			// Construct the Excel Function
			switch ($arrField['Total'])
			{
				case EXCEL_TOTAL_SUM:
	 				// Standard SUM
	 				$wksWorksheet->writeFormula($intRow + 2, $arrExcelCols[$strName]['Col'], "=SUM($strCellStart:$strCellEnd)", $arrExcelCols[$strName]['TotalFormat']);
					break;
					
				case EXCEL_TOTAL_AVG:
	 				// Standard AVG
	 				$wksWorksheet->writeFormula($intRow + 2, $arrExcelCols[$strName]['Col'], "=AVG($strCellStart:$strCellEnd)", $arrExcelCols[$strName]['TotalFormat']);
					break;
					
				// TODO: More specific cases?
					
				default:
					// Do we even have a total?
					if (is_string($arrField['Total']))
					{
						// A custom formula, based off other totals
						$strFunction = $arrField['Total'];
						foreach ($arrSQLSelect as $strSubName=>$arrSubField)
						{
							$strCell	= Spreadsheet_Excel_Writer::rowcolToCell($intRow + 2, $arrExcelCols[$strSubName]['Col']);
							$strFunction = str_replace("<$strSubName>", $strCell, $strFunction);
						}
					}
			}
		}
		
		// Send the XLS file
		$wkbWorkbook->close();
 		
 		// return the path
 		return $strPath;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// _InitExcelFormats
	//------------------------------------------------------------------------//
	/**
	 * _InitExcelFormats()
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * @param	Spreadsheet_Excel_Writer	$wkbWorkbook	Workbook to create formats for
	 *
	 * @return	array										Associative Array of Formats
	 *
	 * @method
	 */
 	private function _InitExcelFormats($wkbWorkbook)
 	{		
 		$arrFormat = Array();
 		
 		// Integer format (make sure it doesn't show exponentials for large ints)
		$fmtInteger =& $wkbWorkbook->addFormat();
		$fmtInteger->setNumFormat('00');
		$arrFormat['Integer']		= $fmtInteger;
		
		// Bold Text
		$fmtBold		= $wkbWorkbook->addFormat();
		$fmtBold->setBold();
		$arrFormat['TextBold']		= $fmtBold;
		
		// Title Row
		$fmtTitle =& $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		$fmtTitle->setFgColor(22);
		$fmtTitle->setBorder(1);
		$arrFormat['Title']			= $fmtTitle;
		
		// Total Text Cell
		$fmtTotalText	= $wkbWorkbook->addFormat();
		$fmtTotalText->setTopColor('black');
		$fmtTotalText->setTop(1);
		$arrFormat['TotalText']		= $fmtTotalText;
		
		
		
		// Currency
		$fmtCurrency	= $wkbWorkbook->addFormat();
		$fmtCurrency->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$arrFormat['Currency']		= $fmtCurrency;
		
		// Bold Currency
		$fmtCurrencyBold	= $wkbWorkbook->addFormat();
		$fmtCurrencyBold->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$fmtCurrencyBold->setBold();
		$arrFormat['CurrencyBold']	= $fmtCurrencyBold;
		
		// Total Currency
		$fmtTotal		= $wkbWorkbook->addFormat();
		$fmtTotal->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$fmtTotal->setBold();
		$fmtTotal->setTopColor('black');
		$fmtTotal->setTop(1);
		$arrFormat['CurrencyBold']	= $fmtTotal;
		
		
		
		// Percentage
		$fmtPercentage	= $wkbWorkbook->addFormat();
		$fmtPercentage->setNumFormat('0.00%;-0.00%');
		$arrFormat['Percentage']	= $fmtPercentage;
		
		// Bold Percentage
		$fmtPCBold		= $wkbWorkbook->addFormat();
		$fmtPCBold->setNumFormat('0.00%;-0.00%');
		$fmtPCBold->setBold();
		$arrFormat['PercentageBold']	= $fmtPCBold;
		
		// Total Percentage
		$fmtPCTotal		= $wkbWorkbook->addFormat();
		$fmtPCTotal->setNumFormat('0.00%;-0.00%');
		$fmtPCTotal->setBold();
		$fmtPCTotal->setTopColor('black');
		$fmtPCTotal->setTop(1);
		$arrFormat['PercentageBold']	= $fmtPCTotal;
		
		
		
		// FNN
		$fmtFNN			= $wkbWorkbook->addFormat();
		$fmtFNN->setNumFormat('0000000000');
		$arrFormat['FNN']				= $fmtFNN;
		
		return $arrFormat; 		
 	}
 }


?>

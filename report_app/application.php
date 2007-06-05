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
			
			// Instanciate & Run Data Report
			$arrColumns = unserialize($arrDataReport['SQLColumns']);
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
 			foreach ($arrRow as $mixValue)
 			{
 				fwrite("\"$mixValue\";");
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
		$fmtTitle =& $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		$fmtTitle->setFgColor(22);
		$fmtTitle->setBorder(1);
		
		// Currency format
		$fmtCurrency =& $wkbWorkbook->addFormat();
		$fmtCurrency->setNumFormat('$#,##0.00;-$#,##0.00');
		
		// Integer format (make sure it doesn't show exponentials for large ints)
		$fmtInteger =& $wkbWorkbook->addFormat();
		$fmtInteger->setNumFormat('00');
		
		// Add in the title row
 		$arrColumns	= array_keys($arrData[0]);
 		foreach ($arrColumns as $intKey=>$strColumn)
 		{
 			$wksWorksheet->write(0, $intKey, $strColumn, $fmtTitle);
 		}
		
		// Add in remaining rows
		foreach ($arrData as $intRow=>$arrRow)
		{
			$intCol = 0;
			foreach ($arrRow as $mixField)
			{
				if (preg_match('/^\d+\.\d+$/misU', $mixField))
				{
					// Currency/float
					$wksWorksheet->write($intRow+1, $intCol, $mixField, $fmtCurrency);
				}
				elseif (is_int($mixField))
				{
					// Integer
					$wksWorksheet->write($intRow+1, $intCol, (int)$mixField, $fmtInteger);
				}
				else
				{
					$wksWorksheet->writeString($intRow+1, $intCol, $mixField);
				}
				$intCol++;
			}
		}
		
		// TODO: Add totals, if specified
		// use $wksWorksheet->writeFormula
		
		// Send the XLS file
		$wkbWorkbook->close();
 		
 		return $strPath;
 	}
 }


?>

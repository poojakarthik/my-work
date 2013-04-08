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
		
		$this->_rptReport	= new Report("Report Report (wtfmate) for ".date("Y-m-d H:i:s"), "rdavis@ybs.net.au", (bool)$arrConfig['Display'], "dispatch@yellowbilling.com.au");
		$this->_rptReport->AddMessage(MSG_HORIZONTAL_RULE);
		
		// Statements
		$this->_selReports		= new StatementSelect("DataReportSchedule", "*", "Status = ".REPORT_WAITING);
		$this->_selDataReport	= new StatementSelect("DataReport", "*", "Id = <DataReport>");
		$this->_selEmployee		= new StatementSelect("Employee", "*", "Id = <Id>");
		
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
			$arrValues	= unserialize($arrDataReport['SQLSelect']);
			$arrColumns = Array();
			foreach ($arrAliases as $strAlias)
			{
				$arrColumns["'$strAlias'"] = $arrValues[$strAlias]['Value'];
			}
			$arrWhere	= unserialize($arrReport['SQLWhere']);
			
			// Instanciate & Run Data Report
			$selReport = new StatementSelect($arrDataReport['SQLTable'], $arrColumns, $arrDataReport['SQLWhere'], NULL, NULL, $arrDataReport['SQLGroupBy']);
			
			//Debug($arrWhere);
			//die;
			
			if (($intResultCount = $selReport->Execute($arrWhere)) === FALSE)
			{
				Debug($selReport->Error());
				Debug($selReport->_strQuery);
			}
			$arrData = $selReport->FetchAll();
				Debug($selReport->_strQuery);
			
			//Debug($arrData);
			//Debug($arrDataReport);
			//Debug($arrWhere);
			
			// Export Data
			if ($intResultCount)
			{
				switch ($arrReport['RenderTarget'])
				{
					case REPORT_TARGET_CSV:
						$arrDataReport['Overrides']['Extension'] = ($arrDataReport['Overrides']['Extension']) ? $arrDataReport['Overrides']['Extension'] : 'csv';
						$arrReport['Status'] = ($arrReturn = $this->ExportCSV($arrData, $arrDataReport, $arrReport)) ? REPORT_GENERATED : REPORT_GENERATE_FAILED;
						$strFile = $arrReturn['FileName'];
						$strMime = 'text/csv';
						break;
					
					case REPORT_TARGET_XLS:
						$arrDataReport['Overrides']['Extension'] = ($arrDataReport['Overrides']['Extension']) ? $arrDataReport['Overrides']['Extension'] : 'xls';
						$arrReport['Status'] = ($strFile = $this->ExportXLS($arrData, $arrDataReport, $arrReport)) ? REPORT_GENERATED : REPORT_GENERATE_FAILED;
						$strMime = 'application/x-msexcel';
						break;
						
					default:
						$arrReport['Status'] = REPORT_BAD_RENDER_TARGET;
				}
			}
			else
			{
				// If there are no results, treat it as a success
				$arrReport['Status'] = REPORT_GENERATED;
			}
			//Debug($strFile);
			//die;
			
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
		 			$mimMime	= new Mail_mime("\n");
					$strContent	=	"Dear {$arrEmployee['FirstName']},\n\n";
					
					if ($intResultCount)
					{
						$strContent .= "Attached is the viXen Data Report ({$arrDataReport['Name']}) you requested on {$arrReport['CreatedOn']}.";
					}
					else
					{
						$strContent .= "There were no results for your requested Data Report ({$arrDataReport['Name']}).  Please try a different set of constraints.";
					}
					$strContent 	.= "\n\nPablo\nYellow Billing Mascot";
					
		 			$arrHeaders = Array	(
						'From'		=> "reports@yellowbilling.com.au",
						'Subject'	=> "{$arrDataReport['Name']} requested on {$arrReport['CreatedOn']}"
					);
					
		 			$mimMime->setTXTBody($strContent);
		 			
		 			// Add attachment if there are any results
		 			if ($intResultCount)
					{
		 				$mimMime->addAttachment($strFile, $strMime);
					}
		 			
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
	// ApplyPostSelectProcesses
	//------------------------------------------------------------------------//
	/**
	 * ApplyPostSelectProcesses()
	 *
	 * Applies and configurred PostSelectProcesses to the data in a report
	 *
	 * Applies and configurred PostSelectProcesses to the data in a report
	 *
	 * @param	array	$arrData				MySQL resultset to generate from
	 * @param	array	$arrDataReport			Report data

	 * @return	void							The passed $arrData array is updated directly
	 *
	 * @method
	 */
	function ApplyPostSelectProcesses(&$arrData, &$arrDataReport)
	{
		// Check for any post-select data modifications such as Decrypt of str_replace
		if ($arrDataReport['PostSelectProcess'])
		{
			// Unserialize the array...
			$arrPostSelectProcesses = unserialize($arrDataReport['PostSelectProcess']);

			// If there are any post process functions to be run...
			if (count($arrPostSelectProcesses))
			{
				// For each tuple...
				foreach ($arrData as $intIndex => $arrTuple)
				{
					// And for each post process function
					foreach ($arrPostSelectProcesses as $strColumnName => $strFunctionName)
					{
						// Chuck a wobbler if the post process function does not exist
						if (!function_exists($strFunctionName))
						{
							throw new Exception("Report requires undefined post processing function '$strFunctionName'.");
						}
						// If the column exists in the tuple..
						if (array_key_exists($strColumnName, $arrTuple))
						{
							// Set the new value to be the return value from the function on the current value
							$arrData[$intIndex][$strColumnName] = call_user_func($strFunctionName, $arrData[$intIndex][$strColumnName]);
						}
					}
				}
			}
		}
	}

	
	
	//------------------------------------------------------------------------//
	// ExportCSV
	//------------------------------------------------------------------------//
	/**
	 * ExportCSV()
	 *
	 * Exports a MySQL resultset to a CSV document
	 *
	 * Exports a MySQL resultset to a CSV document
	 *
	 * @param	array	$arrData				MySQL resultset to generate from
	 * @param	array	$arrReport				Report data
	 * @param	array	$arrReportParameters	DataReport parameters stored in DataReportSchedule
	 * @param	boolean	$bolSave				TRUE	: Save the file to a temporary location
	 * 											FALSE	: Send the file to the browser
	 *
	 * @return	array							Array containing the filename/path of the report and
	 * 											the raw data for the report
	 *
	 * @method
	 */
 	function ExportCSV($arrData, $arrReport, $arrReportParameters, $bolSave = TRUE)
 	{
		// Apply any post select processing
		$this->ApplyPostSelectProcesses($arrData, $arrReport);

		// Check for overrides
		$arrReport['Overrides']	= ($arrReport['Overrides']) ? unserialize($arrReport['Overrides']) : Array();
		//Debug($arrReport['Overrides']);
		$arrReport['Overrides']['NoTitles']		= ($arrReport['Overrides']['NoTitles'])		? $arrReport['Overrides']['NoTitles']	: FALSE;
		
		$strName = $this->_MakeFileName($arrReport, $arrReportParameters);
 		
 		// Saving?
 		if ($bolSave)
 		{
	 		// Open file
	 		$sBaseDir		= FILES_BASE_PATH."upload/datareport/";
 			@mkdir($sBaseDir, 0777, true);
 			$strPath		= "$sBaseDir$strName";
	 		$ptrFile		= fopen($strPath, "w");
 		}
 		else
 		{
 			$strPath = $strName;
 		}
 		$strDelimiter	= ($arrReport['Overrides']['Delimiter'])		? $arrReport['Overrides']['Delimiter']	: ';';
 		$strEnclose		= (isset($arrReport['Overrides']['Enclose']))	? $arrReport['Overrides']['Enclose']	: '"';
 		
 		//Debug($strDelimiter);
 		//Debug($strEnclose);
		
 		// Set column headers
 		if (!$arrReport['Overrides']['NoTitles'])
 		{
	 		$arrColumns		= array_keys($arrData[0]);
	 		foreach ($arrColumns as $strColumn)
	 		{
	 			$strReturn .= ($bolSave) ? fwrite($ptrFile, "{$strEnclose}$strColumn{$strEnclose}$strDelimiter") : "{$strEnclose}$strColumn{$strEnclose}$strDelimiter";
	 		}
	 		$strReturn	= (strrpos($strReturn, $strDelimiter) === (strlen($strReturn) - 1)) ? substr($strReturn, 0, -1) : $strReturn;
	 		
	 		$strReturn .= ($bolSave) ? fwrite($ptrFile, "\n") : "\n";
 		}
 		
 		// Write the data
 		foreach ($arrData as $arrRow)
 		{
 			$arrLine = Array();
 			foreach ($arrRow as $mixField)
 			{
 				$arrLine[]	= "{$strEnclose}{$mixField}{$strEnclose}";
 				//$strReturn .= ($bolSave) ? fwrite($ptrFile, "{$strEnclose}{$mixField}{$strEnclose}$strDelimiter") : "{$strEnclose}{$mixField}{$strEnclose}$strDelimiter";
 			}
 			$strReturn .= ($bolSave) ? fwrite($ptrFile, implode($strDelimiter, $arrLine)."\n") : implode($strDelimiter, $arrLine)."\n";
 		}
 		
 		//Debug($strReturn);
 		//die;
 		
 		$arrReturn = Array();
 		$arrReturn['Output']		= $strReturn;
 		$arrReturn['FileName']		= $strPath;
 		return $arrReturn;
 	}
	
	
	//------------------------------------------------------------------------//
	// ExportXLS
	//------------------------------------------------------------------------//
	/**
	 * ExportXLS()
	 *
	 * Exports a MySQL resultset to an XLS document
	 *
	 * Exports a MySQL resultset to an XLS document
	 *
	 * @param	array	$arrData				MySQL resultset to generate from
	 * @param	array	$arrReport				Report data
	 * @param	array	$arrReportParameters	DataReport parameters stored in DataReportSchedule
	 * @param	boolean	$bolSave				TRUE	: Save the file to a temporary location
	 * 											FALSE	: Send the file to the browser
	 *
	 * @return	string							Path to the file generated
	 *
	 * @method
	 */
 	function ExportXLS($arrData, $arrReport, $arrReportParameters, $bolSave = TRUE)
 	{
		// Apply any post select processing
		$this->ApplyPostSelectProcesses($arrData, $arrReport);

		// Generate Excel 5 Workbook
 		$strFileName = $this->_MakeFileName($arrReport, $arrReportParameters);
		require_once FLEX_BASE_PATH.'/lib/PHPExcel/Classes/PHPExcel.php';
		require_once FLEX_BASE_PATH.'/lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
 		
 		// Saving?
 		if ($bolSave) {
 			$sBaseDir = FILES_BASE_PATH."upload/datareport/";
 			@mkdir($sBaseDir, 0777, true);
 			$strPath = "$sBaseDir$strFileName";
			// Create a new PHPExcel object
			$wkbWorkbook = new PHPExcel($strPath);
 		} else {
			// Create a new PHPExcel object
			$wkbWorkbook = new PHPExcel();
			$strPath = $strFileName;
			header("Content-Type: application/ms-excel");
			header("Content-Disposition: attachment;filename='{$strFileName}'");
			header("Cache-Control: max-age=0");
		}

 		// Add the worksheet
 		$wksWorksheet	= $wkbWorkbook->createSheet();
		$arrFormat		= $this->_InitExcelFormats($wkbWorkbook);
		$intRow = 1;
		$intCol = 1;

		// Add in the title row
 		$arrColumns	= array_keys($arrData[0]);
 		foreach ($arrColumns as $intKey=>$strValue) {
 			$strCol = PHPExcel_Cell::stringFromColumnIndex($intCol);
 			$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $strValue);
			$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Title']);
 			$intCol++;
 		}
		
		// Add in data rows
		$arrSQLSelect	= unserialize($arrReport['SQLSelect']);
		//Debug($arrSQLSelect);
		$arrExcelCols	= Array();

		foreach ($arrData as $iKey=>$arrRow) {
			$intRow++;
			$intCol=1;
			foreach ($arrRow as $strName=>$mixField) {
				$arrExcelCols[$strName]['Col'] = $intCol;
 				$strCol = PHPExcel_Cell::stringFromColumnIndex($intCol);
				
				// Is this field a function?
				if ($strFunction = $arrSQLSelect[$strName]['Function']) {
					foreach ($arrSQLSelect as $strSubName=>$arrSubField) {
						$strCellColLetter = PHPExcel_Cell::stringFromColumnIndex($arrExcelCols[$strSubName]['Col']);
						$strCell = "{$strCellColLetter}{$intRow}";
						$strFunction = str_replace("<$strSubName>", $strCell, $strFunction);
					}
					$mixField = $strFunction;
					//Debug($mixField);
				}

				// If an output type is specified then use it, else 'best guess'
				switch ($arrSQLSelect[$strName]['Type']) {

					case EXCEL_TYPE_CURRENCY:
						if (!$arrSQLSelect[$strName]['Function']) {
				 			$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, (float)$mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Currency']);
							//$wksWorksheet->writeNumber($intRow+1, $intCol, (float)$mixField, $arrFormat['Currency']);
						} else {
				 			$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Currency']);
							//$wksWorksheet->writeFormula($intRow+1, $intCol, $mixField, $arrFormat['Currency']);
						}
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['CurrencyTotal'];
						break;
						
					case EXCEL_TYPE_INTEGER:
						if (!$arrSQLSelect[$strName]['Function']) {
				 			$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, (int)$mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Integer']);
							//$wksWorksheet->writeNumber($intRow+1, $intCol, (int)$mixField, $arrFormat['Integer']);
						} else {
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Integer']);
							//$wksWorksheet->writeFormula($intRow+1, $intCol, $mixField, $arrFormat['Integer']);
						}
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['IntegerTotal'];
						break;
						
					case EXCEL_TYPE_PERCENTAGE:
						if (!$arrSQLSelect[$strName]['Function']) {
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, (float)$mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Percentage']);
							//$wksWorksheet->writeNumber($intRow+1, $intCol, (float)$mixField	, $arrFormat['Percentage']);
						} else {
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Percentage']);
							//$wksWorksheet->writeFormula($intRow+1, $intCol, $mixField, $arrFormat['Percentage']);
						}
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['PercentageTotal'];
						break;
						
					case EXCEL_TYPE_FNN:
						$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
						$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['FNN']);
						//$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
						break;
					
					// Best Guess
					default:
						if (IsValidFNN($mixField)) {
							// FNN
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['FNN']);
							//$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
						} elseif (is_int($mixField['Value'])) {
							// Integer
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['Integer']);
							//$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['Integer']);
							$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['IntegerTotal'];
						} elseif (IsValidFNN($mixField)) {
							// FNN
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['FNN']);
							//$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
						} else {
							// Just a string
							$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, $mixField);
							//$wksWorksheet->writeString($intRow+1, $intCol, $mixField);
						}
				}
				$intCol++;
			}
		}
		
		// Do we have any Totals?
		$bolTotals = FALSE;
		foreach ($arrSQLSelect as $strName=>$arrField) {
			$bolTotals = ($arrField['Total']) ? TRUE : $bolTotals;
		}
		

		$intCol=0;
 		$strCol = PHPExcel_Cell::stringFromColumnIndex($intCol);
 		$intRow++;

		if ($bolTotals) {
			// Draw a Horizontal Line to separate totals from data
			$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, "Grand Totals");
			$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['TotalText']);
			for ($intCol = 1; $intCol <= count($arrExcelCols); $intCol++) {
 				$strCol = PHPExcel_Cell::stringFromColumnIndex($intCol);
				$wkbWorkbook->getActiveSheet()->setCellValueByColumnAndRow($intCol, $intRow, "");
				$wkbWorkbook->getActiveSheet()->getStyle("{$strCol}{$intRow}")->applyFromArray($arrFormat['TotalText']);
			}
			
			// Add totals, if specified
			foreach ($arrSQLSelect as $strName=>$arrField) {
				if (!$arrField['Total']) {
					continue;
				}
				
				// Calculate Cell Range
 				$strCol = PHPExcel_Cell::stringFromColumnIndex($arrExcelCols[$strName]['Col']);
				$strCellStart	= $strCol . "1";
				$strCellEnd		= $strCol . count($arrData);
				// Construct the Excel Function
				switch ($arrField['Total']) {
					case EXCEL_TOTAL_SUM:
		 				// Standard SUM
						$wkbWorkbook->getActiveSheet()->setCellValue("{$strCol}{$intRow}", "=SUM({$strCellStart}:{$strCellEnd})");
						break;
						
					case EXCEL_TOTAL_AVG:
		 				// Standard AVG
						$wkbWorkbook->getActiveSheet()->setCellValue("{$strCol}{$intRow}", "=AVG({$strCellStart}:{$strCellEnd})");
						break;
					// TODO: More specific cases?
					default:
						// Do we even have a total?
						if (is_string($arrField['Total'])) {
							// A custom formula, based off other totals
							$strFunction = $arrField['Total'];
							foreach ($arrSQLSelect as $strSubName=>$arrSubField) {
 								$strCol = PHPExcel_Cell::stringFromColumnIndex($arrExcelCols[$strName]['Col']);
								$strCell = "{$strCol}{$intRow}";
								$strFunction = str_replace("<$strSubName>", $strCell, $strFunction);
							}
							$strFunction = (substr($strFunction, 0, 1) == "=") ? $strFunction : "=$strFunction";
							$wkbWorkbook->getActiveSheet()->setCellValue("{$strCol}{$intRow}", "{$strFunction}");
							//Debug($strFunction);
						}
				}
			}
		}

		$oWriter = PHPExcel_IOFactory::createWriter($wkbWorkbook, 'Excel5');
		$oWriter->save("{$strPath}");		
 		chmod($strPath, 0777);
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
 	private function _InitExcelFormats($wkbWorkbook) {
 		$aFormat = array(
			'Integer' => array(
				'numberformat' => array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER
				)
			),
			'IntegerBold' => array(
				'numberformat' => array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER
				),
				'font' => array(
					'bold' => true
				)
			),
			'IntegerTotal' => array(
				'numberformat' => array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER
				),
				'font' => array(
					'bold' => true
				),
				'borders' => array(
					'top' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('argb' => '000000')
					)
				)
			),
			'TextBold' => array(
				'font' => array(
					'bold' => true
				)
			),
			'Title' => array(
				'font' => array(
					'bold' => true
				),
				'borders' => array(
					'outline' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('argb' => '000000')
					)
				),
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => array(
						'rgb' => 'D3D3D3',
					)
				)
			),
			'TotalText' => array(
				'font' => array(
					'bold' => true
				),
				'borders' => array(
					'top' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('argb' => '000000')
					) 
				)
			),
			'Currency' => array(
				'numberformat' => array(
					'code' => '$#,##0.00;$#,##0.00 CR'
				)
			),
			'CurrencyBold' => array(
				'font' => array(
					'bold' => true
				),
				'numberformat' => array(
					'code' => '$#,##0.00;$#,##0.00 CR'
				)
			),
			'CurrencyTotal' => array(
				'font' => array(
					'bold' => true
				),
				'numberformat' => array(
					'code' => '$#,##0.00;$#,##0.00 CR'
				),
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '000000')
				)
			),
			'Percentage' => array(
				'numberformat' => array(
					'code' => '0.00%;[red]-0.00%'
				)
			),
			'PercentageBold' => array(
				'font' => array(
					'bold' => true
				),
				'numberformat' => array(
					'code' => '0.00%;-0.00%'
				)
			),
			'PercentageTotal' => array(
				'font' => array(
					'bold' => true
				),
				'numberformat' => array(
					//'code' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00
					'code' => '0.00%;-0.00%'
				),
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '000000')
				)
			),
			'FNN' => array(
				'numberformat' => array(
					'code' => '0000000000'
				)
			)
		);

		return $aFormat;

 	}
 	
 	
 	
	//------------------------------------------------------------------------//
	// _MakeFileName
	//------------------------------------------------------------------------//
	/**
	 * _MakeFileName()
	 *
	 * Generates the file name to save the report as
	 *
	 * Generates the file name to save the report as
	 *
	 * @param	array	$arrReport				Report data
	 * @param	array	$arrReportParameters	DataReport parameters stored in DataReportSchedule
	 *
	 * @return	string							File name
	 *
	 * @method
	 */
 	private function _MakeFileName($arrReport, $arrReportParameters)
 	{
 		$arrReport['SQLSelect'] = unserialize($arrReport['SQLSelect']);
 		$arrReport['SQLFields'] = unserialize($arrReport['SQLFields']);
 		$arrReportParameters['SQLSelect'] = unserialize($arrReportParameters['SQLSelect']);
 		$arrReportParameters['SQLWhere'] = unserialize($arrReportParameters['SQLWhere']);
 		
 		if ($arrReport['FileName'])
 		{
 			// Parse Filename Template
 			$arrTemplate = Array();
 			$strFileName = $arrReport['FileName'];
 			
 			//Debug($strFileName);
 			
 			//Debug($arrTemplate);
 			
 			preg_match_all("/<([\d\w\s\:\(\)]+)>/misU", $strFileName, $arrTemplate, PREG_SET_ORDER);
 			
 			foreach ($arrTemplate as $arrMatch)
 			{
 				// Get the value we want
 				$arrVariable = explode('::', $arrMatch[1]);
 				//Debug($arrMatch[0]);
 				
				if (count($arrVariable) == 2)
				{
					// Using namespace, need to do a DB Select
					$arrDBSelect = $arrReport['SQLFields'][$arrVariable[0]]['DBSelect'];
					$selVariable = new StatementSelect($arrDBSelect['Table'], $arrDBSelect['Columns'], $arrDBSelect['Where'] . " AND {$arrDBSelect['Columns'][Value]} = <Value>", $arrDBSelect['OrderBy'], $arrDBSelect['Limit'], $arrDBSelect['GroupBy']);
					$selVariable->Execute(Array('Value' => $arrReportParameters['SQLWhere'][$arrVariable[0]]));
					$arrVariableData = $selVariable->Fetch();
					$strVariable = $arrVariableData[$arrVariable[1]];
				}
				elseif (stripos($arrVariable[0], "()") !== FALSE)
				{
					// Special Function
					switch (strtoupper($arrVariable[0]))
					{
						case 'DATE()':
							$strVariable	= date("d/m/Y");
							break;
						
						case 'DATETIME()':
							$strVariable	= date("d/m/Y H:i:s");
							break;
						
						case 'TIME()':
							$strVariable	= date("H:i:s");
							break;
						
						default:
							$strVariable	= $arrMatch[0];
					}
				}
				else
				{
					// Standard Variable
					$strVariable = trim($arrReportParameters['SQLWhere'][$arrVariable[0]], '%');
				}
				
				$strFileName = str_replace($arrMatch[0], $strVariable, $strFileName);
 			}
 		}
 		else
 		{
 			$strFileName = $arrReport['Name'];
 			$strFileName .= " - " . date("d M Y h:i:s A");
 		}
 		
 		// Turn forward slashes into _
 		$strFileName	= preg_replace('/\//', '_', $strFileName);
 		
 		// Add file extension
 		if ($arrReport['Overrides']['Extension'])
 		{
 			$strFileName .= '.' . $arrReport['Overrides']['Extension'];
 		}
 		else
 		{
	 		switch ($arrReportParameters['RenderTarget'])
	 		{
	 			case REPORT_TARGET_XLS:
	 				$strFileName .= ".xls";
	 				break;
	 			
	 			case REPORT_TARGET_CSV:
	 				$strFileName .= ".csv";
	 				break;
	 		}
 		}
 		
 		return $strFileName;
 	}
 }


?>

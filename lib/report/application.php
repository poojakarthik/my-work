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
		
		$this->_rptReport	= new Report("Report Report (wtfmate) for ".date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au", (bool)$arrConfig['Display'], "dispatch@voiptelsystems.com.au");
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
	 		$strPath		= FILES_BASE_PATH."upload/datareport/$strName";
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
 		$arrReturn['Output']	= $strReturn;
 		$arrReturn['FileName']	= $strPath;
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
 		
 		// Saving?
 		if ($bolSave)
 		{
 			$strPath		= FILES_BASE_PATH."upload/datareport/$strFileName";
			$wkbWorkbook	= new Spreadsheet_Excel_Writer($strPath);
 		}
 		else
 		{
			$wkbWorkbook	= new Spreadsheet_Excel_Writer();
			$strPath		= $strFileName;
			$wkbWorkbook->send($strFileName);
 		}
 		
 		// Add the worksheet
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
		//Debug($arrSQLSelect);
		$arrExcelCols	= Array();
		$intRow			= 0;
		foreach ($arrData as $intRow=>$arrRow)
		{
			$intCol = 1;
			foreach ($arrRow as $strName=>$mixField)
			{
				$arrExcelCols[$strName]['Col'] = $intCol;
				
				// Is this field a function?
				if ($strFunction = $arrSQLSelect[$strName]['Function'])
				{
					foreach ($arrSQLSelect as $strSubName=>$arrSubField)
					{
						$strCell	= Spreadsheet_Excel_Writer::rowcolToCell($intRow+1, $arrExcelCols[$strSubName]['Col']);
						$strFunction = str_replace("<$strSubName>", $strCell, $strFunction);
					}
					$mixField = $strFunction;
					//Debug($mixField);
				}
				
				// If an output type is specified then use it, else 'best guess'
				switch ($arrSQLSelect[$strName]['Type'])
				{
					case EXCEL_TYPE_CURRENCY:
						if (!$arrSQLSelect[$strName]['Function'])
						{
							$wksWorksheet->writeNumber($intRow+1, $intCol, (float)$mixField, $arrFormat['Currency']);
						}
						else
						{
							$wksWorksheet->writeFormula($intRow+1, $intCol, $mixField, $arrFormat['Currency']);
						}
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['CurrencyTotal'];
						break;
						
					case EXCEL_TYPE_INTEGER:
						if (!$arrSQLSelect[$strName]['Function'])
						{
							$wksWorksheet->writeNumber($intRow+1, $intCol, (int)$mixField, $arrFormat['Integer']);
						}
						else
						{
							$wksWorksheet->writeFormula($intRow+1, $intCol, $mixField, $arrFormat['Integer']);
						}
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['IntegerTotal'];
						break;
						
					case EXCEL_TYPE_PERCENTAGE:
						if (!$arrSQLSelect[$strName]['Function'])
						{
							$wksWorksheet->writeNumber($intRow+1, $intCol, (float)$mixField	, $arrFormat['Percentage']);
						}
						else
						{
							$wksWorksheet->writeFormula($intRow+1, $intCol, $mixField, $arrFormat['Percentage']);
						}
						$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['PercentageTotal'];
						break;
						
					case EXCEL_TYPE_FNN:
						$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
						break;
					
					// Best Guess
					default:
						if (IsValidFNN($mixField))
						{
							// FNN
							$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
						}
						elseif (is_int($mixField['Value']))
						{
							// Integer
							$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['Integer']);
							$arrExcelCols[$strName]['TotalFormat'] = $arrFormat['IntegerTotal'];
						}
						elseif (IsValidFNN($mixField))
						{
							// FNN
							$wksWorksheet->writeNumber($intRow+1, $intCol, $mixField, $arrFormat['FNN']);
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
		
		// Do we have any Totals?
		$bolTotals = FALSE;
		foreach ($arrSQLSelect as $strName=>$arrField)
		{
			$bolTotals = ($arrField['Total']) ? TRUE : $bolTotals;
		}
		
		if ($bolTotals)
		{
			// Draw a Horizontal Line to separate totals from data
			$wksWorksheet->writeString($intRow+2, 0, "Grand Totals", $arrFormat['TotalText']);
			for ($intCol = 1; $intCol <= count($arrExcelCols); $intCol++)
			{
				$wksWorksheet->writeString($intRow+2, $intCol, "", $arrFormat['TotalText']);
			}
			
			// Add totals, if specified
			foreach ($arrSQLSelect as $strName=>$arrField)
			{
				if (!$arrField['Total'])
				{
					continue;
				}
				
				// Calculate Cell Range
				$strCellStart	= Spreadsheet_Excel_Writer::rowcolToCell(1					, $arrExcelCols[$strName]['Col']);
				$strCellEnd		= Spreadsheet_Excel_Writer::rowcolToCell(count($arrData)	, $arrExcelCols[$strName]['Col']);
				
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
							$strFunction = (substr($strFunction, 0, 1) == "=") ? $strFunction : "=$strFunction";
							$wksWorksheet->writeFormula($intRow + 2, $arrExcelCols[$strName]['Col'], $strFunction, $arrExcelCols[$strName]['TotalFormat']);
							//Debug($strFunction);
						}
				}
			}
		}
		
		// Save the XLS file, CHMOD, return path
		$wkbWorkbook->close();
		if ($bolSave)
		{
 			chmod($strPath, 0777);
		}
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
		$fmtInteger->setNumFormat('0');
		$arrFormat['Integer']		= $fmtInteger;
		
 		// Bold Integer format (make sure it doesn't show exponentials for large ints)
		$fmtIntegerBold =& $wkbWorkbook->addFormat();
		$fmtIntegerBold->setNumFormat('0');
		$fmtIntegerBold->SetBold();
		$arrFormat['IntegerBold']		= $fmtIntegerBold;
		
 		// Total Integer format (make sure it doesn't show exponentials for large ints)
		$fmtIntegerTotal =& $wkbWorkbook->addFormat();
		$fmtIntegerTotal->setNumFormat('0');
		$fmtIntegerTotal->setBold();
		$fmtIntegerTotal->setTopColor('black');
		$fmtIntegerTotal->setTop(1);
		$arrFormat['IntegerTotal']		= $fmtIntegerTotal;
		
		
		
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
		$fmtTotalText->setBold();
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
		$arrFormat['CurrencyTotal']	= $fmtTotal;
		
		
		
		// Percentage
		$fmtPercentage	= $wkbWorkbook->addFormat();
		$fmtPercentage->setNumFormat('0.00%;[red]-0.00%');
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
		$arrFormat['PercentageTotal']	= $fmtPCTotal;
		
		
		
		// FNN
		$fmtFNN			= $wkbWorkbook->addFormat();
		$fmtFNN->setNumFormat('0000000000');
		$arrFormat['FNN']				= $fmtFNN;
		
		return $arrFormat; 		
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
 		
 		//Debug($strFileName);
 		
 		return $strFileName;
 	}
 }


?>

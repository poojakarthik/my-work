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
	 * @return			VOID
	 *
	 * @method
	 */
 	function Execute()
 	{
		// Get all REPORT_WAITING Reports
		$this->_selReports->Execute();
		while ($arrReport = $this->_selReports->Fetch())
		{
			// Report
			// TODO
			
			// Get DataReport Details
			$this->_selDataReport->Execute($arrReport);
			$arrDataReport = $this->_selDataReport->Fetch();
			
			// Instanciate & Run Data Report
			$arrColumns = unserialize($arrDataReport['SQLColumns']);
			$selReportSelect = new StatementSelect($arrDataReport['SQLTable'], $arrColumns, $arrDataReport['SQLWhere']);
			$selReport->Execute();
		}
	}
 }


?>

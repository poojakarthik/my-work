<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_BUG;
	
	// call application
	require ('config/application.php');
	
	// Pull documentation information
	$docDocumentation->Explain ('Bugs');
	
	$objWhere = new VixenWhere();
	
	// only create WHERE for fields actually entered
	// OR remove null where's in db_access code

	if ($_POST['CreatedOnStartYear'] <> '' && $_POST['CreatedOnEndYear'] <> '')
	{
		$createdDate = Array(		date('Y-m-d H-i-s', strtotime($_POST['CreatedOnStartYear'] . 				"-" . $_POST['CreatedOnStartMonth'] . "-" . $_POST['CreatedOnStartDay'])),
									date('Y-m-d H-i-s', strtotime(strval(intval($_POST['CreatedOnEndYear'])+1) . "-" . $_POST['CreatedOnEndMonth'] . "-" . $_POST['CreatedOnEndDay'])));
	}
	if ($_POST['ClosedOnStartYear'] <> '' && $_POST['ClosedOnEndYear'] <> '')
	{
		$closedDate = Array(		date('Y-m-d H-i-s', strtotime($_POST['ClosedOnStartYear'] . 				"-" . $_POST['ClosedOnStartMonth'] . "-" . $_POST['ClosedOnStartDay'])),
									date('Y-m-d H-i-s', strtotime(strval(intval($_POST['ClosedOnEndYear'])+1) . "-" . $_POST['ClosedOnEndMonth'] . "-" . $_POST['ClosedOnEndDay'])));
	}
		
	$objWhere->AddAnd('BugReport.CreatedOn', ($createdDate[0] > date('Y-m-d',strtotime('1980-01-01'))) ? $createdDate : NULL	, "BETWEEN");
	$objWhere->AddAnd('BugReport.ClosedOn', ($closedDate[0] > date('Y-m-d',strtotime('1980-01-01'))) ? $closedDate : NULL		, "BETWEEN");
	$objWhere->AddAnd('BugReport.CreatedBy',($_POST['CreatedBy']) 	? $_POST['CreatedBy'] 	: NULL);
	$objWhere->AddAnd('AssignedTo', 		($_POST['AssignedTo']) 	? $_POST['AssignedTo'] 	: NULL);
	$objWhere->AddAnd('Status', 			($_POST['Status']) 		? $_POST['Status'] 		: NULL);
	$objWhere->AddAnd('PageName', 			($_POST['PageName']) 	? $_POST['PageName'] 	: NULL, WHERE_SEARCH);
	
	$objWhere->AddAnd(Array('BugReport.Comment', 'Resolution'), ($_POST['Search']) ? $_POST['Search'] : NULL, WHERE_SEARCH);
	
	
	//TODO!Sean! searching the comments, bring back each bug only once (bugs.php)
	//$objWhere->AddAnd(Array('BugReport.Comment', 'Resolution', 'BugReportComment.Comment'), ($_POST['Search']) ? $_POST['Search'] : NULL, WHERE_SEARCH);
	//$objWhere->AddOr(Array('BugReport.Comment', 'Resolution', 'BugReportComment.Comment'), 		$_POST['Search'], "LIKE");
	
	// -- example code for passing multiple where clauses
	//$objWhere->WhereArray();	
	/*
	$objWhere2 = new Vixen_where();
	$objWhere2->AddAnd('CreatedBy', 		$_POST['CreatedBy']);
	$objWhere2->AddAnd('AssignedTo', 	$_POST['AssignedTo']);
	// $objWhere is of type VixenWhere
	$objWhere->AddAnd($objWhere2);
	*/
	
	// -- various where debugging code
	//Debug($objWhere);
	//Debug($objWhere->WhereArray());
	//Debug(explode("<", $objWhere->WhereString()));
	//Debug($objWhere->WhereString());
	
	// Start a new Bug Search
	$Style->attachObject (new Bug_list(
		$objWhere,
		isset ($_POST ['rangePage']) ? $_POST ['rangePage'] : 1, 
		isset ($_POST ['rangeLength']) ? $_POST ['rangeLength'] : 30));
		
			
	// Get list of employees that have created bugs.

	$arrCreatedByEmployeeColumns = Array();
	$arrCreatedByEmployeeColumns['CreatedById']		= "DISTINCT Employee.Id";
	$arrCreatedByEmployeeColumns['CreatedByName']	= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
	
	$strCreatedByEmployeeTables = 'BugReport JOIN Employee ON (BugReport.CreatedBy = Employee.Id)';
	
	$selCreatedByEmployee = new StatementSelect($strCreatedByEmployeeTables, $arrCreatedByEmployeeColumns);
	$intCount = $selCreatedByEmployee->Execute ($arrCreatedByEmployeeColumns);
	$arrCreatedByEmployeeResults = $selCreatedByEmployee->FetchAll ($this);
	$GLOBALS['Style']->InsertDOM($arrCreatedByEmployeeResults, 'CreatedByEmployees');

	// Get list of employees that are assigned to bugs.
	
	$arrAssignedToEmployeeColumns = Array();
	$arrAssignedToEmployeeColumns['AssignedToId']	= "DISTINCT Employee.Id";
	$arrAssignedToEmployeeColumns['AssignedToName']	= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
	
	$strAssignedToEmployeeTables = 'BugReport JOIN Employee ON (BugReport.AssignedTo = Employee.Id)';
	
	$selAssignedToEmployee = new StatementSelect($strAssignedToEmployeeTables, $arrAssignedToEmployeeColumns);
	$intCount = $selAssignedToEmployee->Execute ($arrAssignedToEmployeeColumns);
	$arrAssignedToEmployeeResults = $selAssignedToEmployee->FetchAll ($this);	
	$GLOBALS['Style']->InsertDOM($arrAssignedToEmployeeResults, 'AssignedToEmployees');
	
	// Get all the statuses
	
	$arrStatusesColumns = Array();
	$arrStatusesColumns['StatusId']	= "DISTINCT BugReport.Status";
	
	$strStatusesTables = 'BugReport';
	
	$selStatuses = new StatementSelect($strStatusesTables, $arrStatusesColumns);
	$intCount = $selStatuses->Execute ($arrStatusesColumns);
	$arrStatusesResults = $selStatuses->FetchAll ($this);	
	
	foreach ($arrStatusesResults as $intKey=>$arrResult)
		{
			$arrStatusesResults[$intKey]['Status'] = GetConstantDescription($arrStatusesResults[$intKey]['StatusId'], 'BugStatus');
		}
	//Returning the search keywords, to display on the page
	if ($_POST)
	{
		//Debug($_POST);die;
		$arrSearchTerms = array();
		$arrSearchTerms = $_POST;
		$GLOBALS['Style']->InsertDOM($arrSearchTerms, 'SearchTerms');	
	}
	
	//$arrStatusesResults['Status'] = GetConstantDescription($arrStatusesResults['StatusId'], 'BugStatus');
	$GLOBALS['Style']->InsertDOM($arrStatusesResults, 'Statuses');
	
	
	// Output the Result
	$Style->Output ('xsl/content/bug/list.xsl');
	
?>

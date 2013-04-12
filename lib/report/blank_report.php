<?php
// Blank Data Report Definition
$arrDataReport = array(
	'Name' => '',
	'FileName' => NULL,
	// A description of the report
	'Summary' => '',
	/*
	 * 	Priviledges
	 *		This number represents the employee permissions required to be able to execute the report.
	 *		To specify it you can the following php constants (numeric value in parentheses):
	 *			PERMISSION_PUBLIC (1)
	 *			PERMISSION_ADMIN (159)
	 *			PERMISSION_OPERATOR (4)
	 *			PERMISSION_SALES (8)
	 *			PERMISSION_ACCOUNTS (16)
	 *			PERMISSION_RATE_MANAGEMENT (32)
	 *			PERMISSION_CREDIT_MANAGEMENT (64)
	 *			PERMISSION_OPERATOR_VIEW (128)
	 *			PERMISSION_OPERATOR_EXTERNAL (256)
	 *			PERMISSION_CUSTOMER_GROUP_ADMIN (512)
	 *			PERMISSION_KB_USER (1024)
	 *			PERMISSION_KB_ADMIN_USER (2048)
	 *			PERMISSION_SALES_ADMIN (4096)
	 *			PERMISSION_PROPER_ADMIN (8192)
	 *			PERMISSION_SUPER_ADMIN (2147483647)
	 *			PERMISSION_DEBUG (2147483648)
	 *			PERMISSION_GOD (140737488355327)
	 *		They can be OR'd by using the | character (e.g. PERMISSION_ADMIN | PERMISSION_OPERATOR)
	 */
	'Priviledges' => PERMISSION_PUBLIC,
	// Date Format: yyyy-mm-dd
	'CreatedOn' => '',
	// Shouldn't need to change this one
	'Documentation' => serialize(array(
		'0' => 'DataReport'
	)),
	// FROM clause
	'SQLTable' => '',
	/*
	 *	SELECT fields
	 *		The key to the array is the alias. 
	 *		The 'Value' property is the actual query clause (e.g. a.Id)
	 *		The 'Type' property is optional and can be used to define how the selected value will be treated in excel, use the following php constants: 
	 *			EXCEL_TYPE_CURRENCY (500)
	 *			EXCEL_TYPE_INTEGER  (501)
	 *			EXCEL_TYPE_PERCENTAGE (502)
	 *			EXCEL_TYPE_FNN (503)
	 */
	'SQLSelect' => serialize(array(
		/* Delete me -- an example 
		'Account' => array(
			'Value' => 'a.Id',
			'Type' => EXCEL_TYPE_INTEGER
		)*/
	)),
	// WHERE clause
	'SQLWhere' => '',
	/*
	 *	SQLFields
	 *		This is where you can define the input parameters.
	 *		The array key is the query placeholder. To reference the parameter in the query use <Placeholder>.
	 *		- 'Type' is one of the following: dataInteger, dataString, dataBoolean, dataFloat, dataDate or dataDatetime.
	 *		- Leave 'Documentation-Entity' as 'DataReport'.
	 *		- 'Documentation-Field' is the name of the parameter as it will appear in the user interface
	 */
	'SQLFields' => serialize(array(
		/* Delete me -- an example 
		'StartDate' => array(
			'Type' => 'dataDate',
			'Documentation-Entity' => 'DataReport',
			'Documentation-Field' => 'Start Date'
		)*/
	)),
	// GROUP BY clause
	'SQLGroupBy' => '',
	// This defines how the report executes, whether it runs on the spot (REPORT_RENDER_INSTANT) or is run in the background and the result is sent via email (REPORT_RENDER_EMAIL)
	'RenderMode' => REPORT_RENDER_INSTANT,
	// Leave this null to allow the option of csv or excel as the output format
	'RenderTarget' => NULL,
	// Leave null
	'Overrides' => NULL,
	// Leave null
	'PostSelectProcess' => NULL,
	/* 	data_report_status_id
	 * 		The status of the report
	 *			DATA_REPORT_STATUS_DRAFT -- will only show in the user interface for those with enough permission (?). can still be edited.
	 *			DATA_REPORT_STATUS_ACTIVE -- can be viewed/run via the user interface. cannot be edited anymore.
	 *			DATA_REPORT_STATUS_INACTIVE -- will not show in the user interface. cannot be edited anymore
	 */
	'data_report_status_id' => DATA_REPORT_STATUS_DRAFT
);
?>
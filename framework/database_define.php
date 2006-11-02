<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// database_define
//----------------------------------------------------------------------------//
/**
 * database_define
 *
 * Defines database tables for use in the application
 *
 * Defines database tables for use in the application
 *
 * @file		database_define.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */



//----------------------------------------------------------------------------//
// database table define format
//----------------------------------------------------------------------------//
 /*
	$arrDefine['Name']		= "";			// Table name
	$arrDefine['Type']		= "MYISAM";		// optional Table type, defaults to	'MYISAM'
	$arrDefine['Id']		= "Id";			// optional Primary auto index column name, defaults to	'Id'
	
	$arrDefine['Index'][] 		= "";		// optional array of index column names
	$arrDefine['Unique'][] 		= "";		// optional array of unique column names
	
	// DO NOT Define the ID column here ! it will be added automatically
	$arrDefine['Column'][$strName]['Type'] 			= "";			// Validation type: s, i etc
	$arrDefine['Column'][$strName]['SqlType'] 		= "";			// Sql Type: Varchar(5), Int etc
	$arrDefine['Column'][$strName]['Null'] 			= TRUE|FALSE;	// optional, defaults to FALSE (NOT NULL)
	$arrDefine['Column'][$strName]['Default'] 		= "";			// optional default value
	$arrDefine['Column'][$strName]['Attributes'] 	= "";			// optional attributes
 */



//----------------------------------------------------------------------------//
// skeleton table define
//----------------------------------------------------------------------------//
/*
	// clean reused temporary array
	unset($arrDefine);
	
	// Define Table
	$arrDefine['Name']		= "";
	$arrDefine['Type']		= "MYISAM";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
	
	// Define Columns
	$strName = "";
		$arrDefine['Column'][$strName]['Type'] 			= "";
		$arrDefine['Column'][$strName]['SqlType'] 		= "";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['Attributes'] 	= "";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$define['Name']] = $define;
	
*/

		
		
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Account
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Account";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "BusinessName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "TradingName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ABN";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(20)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ACN";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(20)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Address1";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Address2";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Suburb";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Postcode";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "State";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(3)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Country";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(2)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "BillingType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CustomerGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreditCard";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "LastBilled";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "BillingDate";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "1";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "BillingFreq";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "1";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "BillingFreqType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "BillingMethod";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: AccountGroup
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "AccountGroup";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "CreatedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ManagedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: CDR
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "CDR";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "FNN";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CDRFilename";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Carrier";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CarrierRef";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Source";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Destination";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StartDatetime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "EndDatetime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Units";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Cost";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Status";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CDR";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "DestinationText";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "DestinationCode";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(3)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecordType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ServiceType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Charge";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Rate";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "NormalisedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Invoice";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "SequenceNo";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Charge
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Charge";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Invoice";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ChargeType";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ChargedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Nature";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Amount";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: ChargeType
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "ChargeType";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "ChargeType";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Nature";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Fixed";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Amount";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Contact
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Contact";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Customer";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Title";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(31)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "FirstName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "LastName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "DOB";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "JobTitle";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Email";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CustomerContact";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Phone";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(15)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Mobile";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(15)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Fax";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(15)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "UserName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(31)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "PassWord";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "SessionID";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "SessionExpire";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: CreditCard
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "CreditCard";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Customer";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CardType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Name";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CardNumber";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(20)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ExpMonth";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(4) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ExpYear";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(4) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CVV";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(3)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Employee
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Employee";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "FirstName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "LastName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "UserName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(21)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "PassWord";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: ErrorLog
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "ErrorLog";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "UserName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(21)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ErrorMessage";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ErrorCode";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "File";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Line";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Trace";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "AbendedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Bug";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: FileDownload
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "FileDownload";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "FileName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Location";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Carrier";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CollectedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ImportedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Status";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "FileType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: FileImport
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "FileImport";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "FileName";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Location";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Carrier";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ImportedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "NormalisedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Status";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "FileType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "SHA1";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Invoice
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Invoice";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "DueOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "SettledOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Credits";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Debits";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Total";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Tax";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Balance";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: InvoicePayment
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "InvoicePayment";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Invoice";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Payment";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Amount";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Note
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Note";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Note";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Contact";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Employee";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Datetime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Payment
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Payment";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "PaidOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "PaymentType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Amount";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "TXNReference";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(100)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "EnteredBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Rate
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Rate";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Name";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecordType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ServiceType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StdUnits";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StdRatePerUnit";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StdFlagfall";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StdPercentage";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StdMinCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ExsUnits";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ExsRatePerUnit";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ExsFlagfall";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ExsPercentage";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StartTime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "time";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataTime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "EndTime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "time";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataTime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Monday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Tuesday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Wednesday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Thursday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Friday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Saturday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Sunday";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Destination";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(3)";
		$arrDefine['Column'][$strName]['Null'] 			= TRUE;
		$arrDefine['Column'][$strName]['Default'] 		= null;
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CapUnits";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CapCost";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CapUsage";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CapLimit";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Prorate";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Fleet";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Uncapped";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RateGroup
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RateGroup";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Name";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecordType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ServiceType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RateGroupRate
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RateGroupRate";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "RateGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Rate";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RatePlan
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RatePlan";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Name";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ServiceType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "MinMonthly";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ChargeCap";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "UsageCap";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RatePlanRateGroup
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RatePlanRateGroup";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "RatePlan";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RateGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RatePlanRecurringCharge
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RatePlanRecurringCharge";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "RatePlan";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecurringCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RecurringCharge
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RecurringCharge";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ChargeType";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Nature";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "date";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecurringFreqType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecurringDate";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "MinCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecursionCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CancellationFee";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Continuable";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "TotalPaid";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "TotalRecursions";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "mediumint(9) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: RecurringChargeType
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "RecurringChargeType";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "ChargeType";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Description";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Nature";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Fixed";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecurringFreqType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecurringDate";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "MinCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecursionCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CancellationFee";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Continuable";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: Service
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "Service";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "FNN";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ServiceType";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "MinMonthly";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "ChargeCap";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "UsageCap";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "AccountGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "Account";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CappedCharges";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "UncappedCharges";
		$arrDefine['Column'][$strName]['Type'] 			= "d";
		$arrDefine['Column'][$strName]['SqlType'] 		= "float";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: ServiceRateGroup
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "ServiceRateGroup";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RateGroup";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "StartDatetime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "EndDatetime";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: ServiceRatePlan
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "ServiceRatePlan";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RatePlan";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
						
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: ServiceRecurringCharge
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "ServiceRecurringCharge";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
						
	// Define Columns
	$strName = "Service";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "RecurringCharge";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedBy";
		$arrDefine['Column'][$strName]['Type'] 			= "i";
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
								
	// Define Columns
	$strName = "CreatedOn";
		$arrDefine['Column'][$strName]['Type'] 			= "s";
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime";
		$arrDefine['Column'][$strName]['Null'] 			= FALSE;
		$arrDefine['Column'][$strName]['Default'] 		= "";
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime";
		
	// Save Table Define
	$_GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
				
	
?>

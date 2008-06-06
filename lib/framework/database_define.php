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
 * @version		8.5 
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
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
 	
*/ 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Account
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "TradingName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ABN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "ABN"; 
		 
				 
				 
	// Define Columns 
	$strName = "ACN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "ACN"; 
		 
				 
				 
	// Define Columns 
	$strName = "Address1"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Address2"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Suburb"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Postcode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "State"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(3)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Country"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(2)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillingType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PrimaryContact"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CustomerGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreditCard"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "DirectDebit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastBilled"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillingDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "1"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillingFreq"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "1"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillingFreqType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillingMethod"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PaymentTerms"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "DisableDDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "DisableLatePayment"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "DisableLateNotices"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LatePaymentAmnesty"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Sample"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: AccountGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "AccountGroup"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "ManagedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: AccountLetterLog
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "AccountLetterLog"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Invoice"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LetterType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: BugReport
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "BugReport"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "AssignedTo"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ClosedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "PageName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "PageDetails"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SerialisedGET"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SerialisedPOST"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Comment"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Resolution"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: BugReportComment
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "BugReportComment"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "BugReport"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Comment"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CDR
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "File"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierRef"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Source"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Destination"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Units"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Cost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(32767)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "DestinationCode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecordType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Charge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Rate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "NormalisedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "RatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(32)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SequenceNo"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Credit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CDRCreditLink
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CDRCreditLink"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CreditCDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "DebitCDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CDRInvoiced
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CDRInvoiced"; 
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
		 
				 
				 
	// Define Columns 
	$strName = "File"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierRef"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Source"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Destination"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Units"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Cost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(32767)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "DestinationCode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecordType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Charge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Rate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "NormalisedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "RatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SequenceNo"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Credit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Carrier
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Carrier"; 
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
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CarrierModule
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CarrierModule"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Module"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(512)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FrequencyType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Frequency"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastSentOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EarliestDelivery"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Active"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CarrierModuleConfig
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CarrierModuleConfig"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CarrierModule"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1024)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Value"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1024)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Charge
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Charge"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "ApprovedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ChargeType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ChargedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Nature"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Amount"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Invoice"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Notes"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "LinkType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LinkId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ChargeType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Nature"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Fixed"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Amount"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ConditionalContexts
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ConditionalContexts"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Object"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Property"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Operator"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Value"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Context"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Config
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Config"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Application"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Module"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Value"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ConfigConstant
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ConfigConstant"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "ConstantGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Value"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Editable"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Deletable"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ConfigConstantGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ConfigConstantGroup"; 
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
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Special"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Extendable"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Contact
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Contact"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Title"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FirstName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "DOB"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "JobTitle"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Email"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CustomerContact"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Phone"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Mobile"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Fax"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "UserName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(31)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "PassWord"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SessionId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SessionExpire"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CostCentre
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CostCentre"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CreditCard
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CreditCard"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CardType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CardNumber"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(100)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExpMonth"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(2)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExpYear"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CVV"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: CustomerGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "CustomerGroup"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "InternalName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExternalName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OutboundEmail"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DataReport
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DataReport"; 
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
		 
				 
				 
	// Define Columns 
	$strName = "FileName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Summary"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "mediumtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Priviledges"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Documentation"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLTable"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLSelect"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLWhere"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLFields"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLGroupBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "PostSelectProcess"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RenderMode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "RenderTarget"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Overrides"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DataReportSchedule
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DataReportSchedule"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "DataReport"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "GeneratedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLSelect"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLWhere"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLOrder"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SQLLimit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RenderTarget"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Destination
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Destination"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Code"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Context"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DestinationTranslation
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DestinationTranslation"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Code"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierCode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DirectDebit
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DirectDebit"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "BankName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BSB"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(6)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountNumber"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(9)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DocumentResource
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DocumentResource"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CustomerGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "OriginalFilename"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileContent"; 
		$arrDefine['Column'][$strName]['Type'] 			= "b"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "mediumblob"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= ""; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DocumentResourceType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DocumentResourceType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "PlaceHolder"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "PermissionRequired"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "TagSignature"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DocumentResourceTypeFileType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DocumentResourceTypeFileType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "ResourceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DocumentTemplate
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DocumentTemplate"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CustomerGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "TemplateType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "TemplateSchema"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Version"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Source"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "mediumtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EffectiveOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "ModifiedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastUsedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DocumentTemplateSchema
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DocumentTemplateSchema"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "TemplateType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Version"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Sample"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "mediumtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: DocumentTemplateType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DocumentTemplateType"; 
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
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Documentation
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Documentation"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Entity"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Field"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Label"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Title"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(100)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Employee
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "LastName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "UserName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(31)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "PassWord"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Phone"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Mobile"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Extension"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(15)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Email"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "DOB"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SessionId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SessionExpire"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Session"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Karma"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PabloSays"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Privileges"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: EmployeeAccountAudit
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "EmployeeAccountAudit"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Contact"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RequestedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ErrorLog
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "ErrorMessage"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ErrorCode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "File"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Line"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Trace"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "AbendedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Bug"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: FileDownload
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Location"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CollectedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "ImportedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: FileExport
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "FileExport"; 
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
		 
				 
				 
	// Define Columns 
	$strName = "Location"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExportedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "SHA1"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: FileImport
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Location"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ImportedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "NormalisedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "SHA1"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(40)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: FileType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "FileType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Extension"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "MIMEType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Invoice
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Invoice"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "DueOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SettledOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Credits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Debits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Total"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Tax"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TotalOwing"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Balance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Disputed"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountBalance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "DeliveryMethod"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: InvoiceOutput
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "InvoiceOutput"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Data"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: InvoiceOutputArchive
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "InvoiceOutputArchive"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Data"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: InvoicePayment
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "InvoicePayment"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Payment"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Amount"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: InvoiceRun
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "InvoiceRun"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillingDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceCount"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillCost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillRated"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillInvoiced"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillTax"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "BalanceData"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CDRArchivedState"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: InvoiceTemp
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "InvoiceTemp"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "DueOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SettledOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Credits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Debits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Total"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Tax"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TotalOwing"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Balance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Disputed"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountBalance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "DeliveryMethod"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: LetterTemplate
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "LetterTemplate"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "CustomerGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LetterType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Template"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: LetterTemplateVar
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "LetterTemplateVar"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "LetterTemplate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Value"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: MasterInstructions
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "MasterInstructions"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Datetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Instruction"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Command"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: MasterState
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "MasterState"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Datetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "State"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Note
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Contact"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Datetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "NoteType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: NoteType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "NoteType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "TypeLabel"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BorderColor"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(6)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BackgroundColor"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(6)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "TextColor"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(6)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Payment
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Payment"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PaidOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "PaymentType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Amount"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TXNReference"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(100)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OriginType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "OriginId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EnteredBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Payment"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "File"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "SequenceNo"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Balance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Payment_bk
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Payment_bk"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PaidOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "PaymentType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Amount"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TXNReference"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(100)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OriginType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "OriginId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EnteredBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Payment"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "File"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "SequenceNo"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Balance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Process
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Process"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "ProcessType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PID"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "WaitDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Output"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(32767)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProcessPriority
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProcessPriority"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "ProcessWaiting"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ProcessRunning"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "WaitMode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "AlertEmail"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(256)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProcessType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProcessType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(256)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Command"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1024)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "WorkingDirectory"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1024)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Debug"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(3) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProvisioningExport
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProvisioningExport"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Location"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Reason"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Sequence"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProvisioningLog
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProvisioningLog"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Request"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Direction"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Date"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProvisioningRequest
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProvisioningRequest"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FNN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierRef"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileExport"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Response"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RequestedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "AuthorisationDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SentOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastUpdated"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProvisioningRequest_bk
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProvisioningRequest_bk"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FNN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierRef"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileExport"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Response"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RequestedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "AuthorisationDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SentOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastUpdated"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProvisioningResponse
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProvisioningResponse"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FNN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierRef"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileImport"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Raw"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EffectiveDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Request"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ImportedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ProvisioningTranslation
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ProvisioningTranslation"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Context"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierCode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1024)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1024)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Rate
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecordType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PassThrough"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "StdUnits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "StdRatePerUnit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(17,8)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "StdFlagfall"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "StdPercentage"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "StdMarkup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(17,8)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "StdMinCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExsUnits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExsRatePerUnit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(17,8)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExsFlagfall"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExsPercentage"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExsMarkup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(17,8)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartTime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "time"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataTime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndTime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "time"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataTime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Monday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Tuesday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Wednesday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Thursday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Friday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Saturday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Sunday"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Destination"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CapUnits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CapCost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "CapUsage"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CapLimit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Prorate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Fleet"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Uncapped"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RateGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecordType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Fleet"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CapLimit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RateGroupRate
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "RateGroupRate"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "RateGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Rate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RatePlan
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Shared"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "MinMonthly"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "InAdvance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ChargeCap"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "UsageCap"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierFullService"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierPreselection"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ContractTerm"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RatePlanRateGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "RatePlanRateGroup"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "RatePlan"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RateGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RatePlanRecurringChargeType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "RatePlanRecurringChargeType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "RatePlan"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringChargeType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RecordType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "RecordType"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Code"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Context"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Required"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Itemised"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "GroupId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "DisplayType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RecordTypeTranslation
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "RecordTypeTranslation"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Code"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierCode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RecurringCharge
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "RecurringCharge"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ApprovedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ChargeType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(10)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Nature"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastChargedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringFreqType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringFreq"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "MinCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecursionCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "CancellationFee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Continuable"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "PlanCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "UniqueCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "TotalCharged"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TotalRecursions"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "mediumint(9) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: RecurringChargeType
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
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
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Nature"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "enum('DR','CR')"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Fixed"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringFreqType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringFreq"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "MinCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecursionCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "CancellationFee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Continuable"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "PlanCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "UniqueCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Request
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Request"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RequestType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RequestDateTime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "ExportFile"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Sequence"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "GainDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "LossDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Service
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Service"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "EtechId"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FNN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Indial100"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CostCentre"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CappedCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "UncappedCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
	// Define Columns 
	$strName = "NatureOfCreation"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
				 
				 
	// Define Columns 
	$strName = "ClosedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "ClosedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
	// Define Columns 
	$strName = "NatureOfClosure"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierPreselect"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "EarliestCDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LatestCDR"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LineStatus"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "LineStatusDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "PreselectionStatus"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "PreselectionStatusDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "ForceInvoiceRender"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(3) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
	// Define Columns 
	$strName = "LastOwner"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
				 
	// Define Columns 
	$strName = "NextOwner"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "400"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceAddress
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceAddress"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Residential"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillAddress1"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillAddress2"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillLocality"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(23)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "BillPostcode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndUserTitle"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndUserGivenName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndUserFamilyName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndUserCompanyName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "DateOfBirth"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(8)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employer"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Occupation"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ABN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "ABN"; 
		 
				 
				 
	// Define Columns 
	$strName = "TradingName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceAddressType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(3)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceAddressTypeNumber"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(5)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceAddressTypeSuffix"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(2)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceStreetNumberStart"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(5)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceStreetNumberEnd"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(5)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceStreetNumberSuffix"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceStreetName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceStreetType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceStreetTypeSuffix"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(2)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServicePropertyName"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceLocality"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(30)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServiceState"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(3)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "ServicePostcode"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceExtension
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceExtension"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Name"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RangeStart"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RangeEnd"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CostCentre"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Archived"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceInboundDetail
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceInboundDetail"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "AnswerPoint"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Complex"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "tinyint(1)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataBoolean"; 
		 
				 
				 
	// Define Columns 
	$strName = "Configuration"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceMobileDetail
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceMobileDetail"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "SimPUK"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SimESN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(15)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "SimState"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(3)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "DOB"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Comments"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceRateGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceRateGroup"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RateGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Active"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "1"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceRatePlan
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceRatePlan"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RatePlan"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "StartDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "EndDatetime"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastChargedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Active"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "1"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceRecurringCharge
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceRecurringCharge"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecurringCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedBy"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceTotal
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceTotal"; 
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
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RatePlan"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "CappedCost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "UncappedCost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "CappedCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "UncappedCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TotalCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Credit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Debit"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "PlanCharge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= "0.0000"; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceTypeTotal
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "ServiceTypeTotal"; 
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
		 
				 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "RateGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RecordType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Cost"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Charge"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Units"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Records"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: Tip
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "Tip"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "TipType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "TipText"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: UIAppDocumentation
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "UIAppDocumentation"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Object"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Property"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Context"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "ValidationRule"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "longtext"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "InputType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OutputType"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Label"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OutputLabel"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OutputMask"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Class"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: UIAppDocumentationOptions
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "UIAppDocumentationOptions"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "Object"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Property"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(50)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Context"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Value"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "OutputLabel"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "InputLabel"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: UnitelFundedFNNs
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "UnitelFundedFNNs"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "id"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FNN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Date"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "PaidDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: _InvoiceTemp
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "_InvoiceTemp"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CreatedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "DueOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SettledOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "Credits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Debits"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Total"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Tax"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "TotalOwing"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Balance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "Disputed"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "AccountBalance"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "decimal(13,4)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataFloat"; 
		 
				 
				 
	// Define Columns 
	$strName = "DeliveryMethod"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "InvoiceRun"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(13)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	 
 	
	//----------------------------------------------------------------------------// 
	// Table: _ProvisioningRequest
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "_ProvisioningRequest"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= ""; 
		 
		 
				 
	// Define Columns 
	$strName = "AccountGroup"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Account"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Service"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "FNN"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "char(25)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "Employee"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Carrier"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Type"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(10) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "CarrierRef"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "varchar(255)"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "FileExport"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Response"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
				 
				 
	// Define Columns 
	$strName = "Description"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "text"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataString"; 
		 
				 
				 
	// Define Columns 
	$strName = "RequestedOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "AuthorisationDate"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "date"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDate"; 
		 
				 
				 
	// Define Columns 
	$strName = "SentOn"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "LastUpdated"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= TRUE; 
		$arrDefine['Column'][$strName]['Default'] 		= null; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 
		 
				 
				 
	// Define Columns 
	$strName = "Status"; 
		$arrDefine['Column'][$strName]['Type'] 			= "i"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "int(11)"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 
					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	



	 
 	
	//----------------------------------------------------------------------------// 
	// Table: ServiceRateGroup
	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset($arrDefine);
	 
	$arrDefine['Name']		= "DATABASE_VERSION"; 
	$arrDefine['Type']		= "InnoDB"; 
	$arrDefine['Id']		= "Id"; 
	$arrDefine['Index'][] 		= ""; 
	$arrDefine['Unique'][] 		= "";
 
	// Define Columns 
	$strName = "VERSION"; 
		$arrDefine['Column'][$strName]['Type'] 			= "d"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "bigint(20) unsigned"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataInteger"; 
		 

	// Define Columns 
	$strName = "ROLLED_OUT_DATE"; 
		$arrDefine['Column'][$strName]['Type'] 			= "s"; 
		$arrDefine['Column'][$strName]['SqlType'] 		= "datetime"; 
		$arrDefine['Column'][$strName]['Null'] 			= FALSE; 
		$arrDefine['Column'][$strName]['Default'] 		= ""; 
		$arrDefine['Column'][$strName]['ObLib'] 		= "dataDatetime"; 

					 
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 


?>
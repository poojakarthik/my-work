<pre>
<?
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import
//----------------------------------------------------------------------------//
/**
 * import
 *
 * Writes the database_define.php file
 *
 * Writes the database_define.php file
 *
 * @file		import.php
 * @language	PHP
 * @package		import
 * @author		Bashkim 'Bash' Isai, Rich 'Waste' Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// !!!! USAGE !!!
//----------------------------------------------------------------------------//
/*
 * Use the GET variable "l" to specify the location of your database_define.php
 * You must include the trailing "/", and must not include the file basename.
 * 
 * Example URL:  "http://localhost/vixen/import/import.php?l=/home/vixen/framework/"
 * 				 This will produce a file called "database_define.php" at "/home/vixen/framework/"
 *  
 */
	
	
	// Define basic file format
	$strFileContents = "<?php\n".
"//----------------------------------------------------------------------------// 
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
 * @version		".str_replace("0", "", date("y.m"))." 
 * @copyright	2006 VOIPTEL Pty Ltd 
 * @license		NOT FOR EXTERNAL DISTRIBUTION 
 * 
 */ 
 
 
 
//----------------------------------------------------------------------------// 
// database table define format 
//----------------------------------------------------------------------------// 
 /* 
	\$arrDefine['Name']		= \"\";			// Table name 
	\$arrDefine['Type']		= \"MYISAM\";		// optional Table type, defaults to	'MYISAM' 
	\$arrDefine['Id']		= \"Id\";			// optional Primary auto index column name, defaults to	'Id' 
 	
	\$arrDefine['Index'][] 		= \"\";		// optional array of index column names 
	\$arrDefine['Unique'][] 		= \"\";		// optional array of unique column names 
 	
	// DO NOT Define the ID column here ! it will be added automatically 
	\$arrDefine['Column'][\$strName]['Type'] 			= \"\";			// Validation type: s, i etc 
	\$arrDefine['Column'][\$strName]['SqlType'] 		= \"\";			// Sql Type: Varchar(5), Int etc 
	\$arrDefine['Column'][\$strName]['Null'] 			= TRUE|FALSE;	// optional, defaults to FALSE (NOT NULL) 
	\$arrDefine['Column'][\$strName]['Default'] 		= \"\";			// optional default value 
	\$arrDefine['Column'][\$strName]['Attributes'] 	= \"\";			// optional attributes 
 */ 
 
 
 
//----------------------------------------------------------------------------// 
// skeleton table define 
//----------------------------------------------------------------------------// 
/* 
	// clean reused temporary array 
	unset(\$arrDefine); 
 	
	// Define Table 
	\$arrDefine['Name']		= \"\"; 
	\$arrDefine['Type']		= \"MYISAM\"; 
	\$arrDefine['Id']		= \"Id\";  
	\$arrDefine['Index'][] 		= \"\"; 
	\$arrDefine['Unique'][] 		= \"\"; 
 	
	// Define Columns 
	\$strName = \"\"; 
		\$arrDefine['Column'][\$strName]['Type'] 			= \"\"; 
		\$arrDefine['Column'][\$strName]['SqlType'] 		= \"\"; 
		\$arrDefine['Column'][\$strName]['Null'] 			= FALSE; 
		\$arrDefine['Column'][\$strName]['Default'] 		= \"\"; 
		\$arrDefine['Column'][\$strName]['Attributes'] 	= \"\"; 
 		
	// Save Table Define 
	\$GLOBALS['arrDatabaseTableDefine'][\$arrDefine['Name']] = \$arrDefine; 
 	
*/ 
	";
	
	$_DATABASE = "vixen";
	
	$MySQL_Link = mysql_connect ("10.11.12.13", "bash", "bash");
	
	$_TABLES = mysql_list_tables ($_DATABASE);
	
	while ($_TABLE = mysql_fetch_row ($_TABLES)) {
		$_FIELDS = mysql_query ("SHOW COLUMNS FROM " . $_DATABASE . "." . $_TABLE [0]);
	
		$strFileContents.= " 
 	
	//----------------------------------------------------------------------------// 
	// Table: ".$_TABLE [0] . "\n".
"	//----------------------------------------------------------------------------// 
	 
 
 		
 		
	unset(\$arrDefine);
	 
	\$arrDefine['Name']		= \"".$_TABLE[0]."\"; 
	\$arrDefine['Type']		= \"InnoDB\"; 
	\$arrDefine['Id']		= \"Id\"; 
	\$arrDefine['Index'][] 		= \"\"; 
	\$arrDefine['Unique'][] 		= \"\"; 
		 
		";
		
		while ($_FIELD = mysql_fetch_assoc ($_FIELDS)) {
			if ($_FIELD ['Field'] != "Id") {
	
				if (preg_match ("/char/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataString";
				}
				
				if (preg_match ("/text/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataString";
				}
				
				if (preg_match ("/date/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataDate";
				}
				
				if (preg_match ("/time/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataTime";
				}
				
				if (preg_match ("/datetime/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataDatetime";
				}

				if (preg_match ("/int/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "i";
					$_FIELD ['ObLib'] = "dataInteger";
				}
				
				if (preg_match ("/tinyint\(1\)/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "i";
					$_FIELD ['ObLib'] = "dataBoolean";
				}

				if (preg_match ("/float/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "d";
					$_FIELD ['ObLib'] = "dataFloat";
				}
				
				if (preg_match ("/decimal/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "d";
					$_FIELD ['ObLib'] = "dataFloat";
				}
				
				if (preg_match ("/enum/", $_FIELD ['Type'])) {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataString";
				}
				
				if ($_FIELD ['Field'] == "ABN") {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "ABN";
				}
				
				if ($_FIELD ['Field'] == "ACN") {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "ACN";
				}
				
				if ($_TABLE [0] == "Employee" && $_FIELD ['Field'] == "Session") {
					$_FIELD ['RefType'] = "s";
					$_FIELD ['ObLib'] = "dataString";
				}
				
				$strFileContents .= " 
				 
	// Define Columns 
	\$strName = \"".$_FIELD ['Field']."\"; 
		\$arrDefine['Column'][\$strName]['Type'] 			= \"".$_FIELD ['RefType']."\"; 
		\$arrDefine['Column'][\$strName]['SqlType'] 		= \"".$_FIELD ['Type']."\"; 
		\$arrDefine['Column'][\$strName]['Null'] 			= ".(($_FIELD ['Null'] === "YES") ? "TRUE" : "FALSE")."; 
		\$arrDefine['Column'][\$strName]['Default'] 		= ".(($_FIELD ['Default'] === null) ? "null" : "\"" . $_FIELD ['Default'] . "\"")."; 
		\$arrDefine['Column'][\$strName]['ObLib'] 		= \"".$_FIELD ['ObLib']."\"; 
		 
				";
			}
		}
	$strFileContents .= "	 
	// Save Table Define
	\$GLOBALS['arrDatabaseTableDefine'][\$arrDefine['Name']] = \$arrDefine; 
	";
	
	}
	
	$strFileContents .= "\n\n?>";
	
	// Write to file
	if ($_GET['l'])
	{
		$strFileLocation = $_GET['l']."database_define.php";
	}
	else
	{
		$strFileLocation = "../framework/database_define.php";
	}
	
	if (file_exists($strFileLocation))
	{
		// Delete backup file if one exists
		if (file_exists($strFileLocation.".bak"))
		{
			echo "Deleting old backup file...\t".$strFileLocation.".bak\n";
			unlink($strFileLocation.".bak");
		}
		
		echo "Making backup file...\t\t".$strFileLocation.".bak\n";
		// Make a backup
		rename($strFileLocation, $strFileLocation.".bak");
	}
	echo "Writing new file...\t\t".$strFileLocation."\n";
	try
	{
		$ptrFile = fopen($strFileLocation, "w");
		fwrite($ptrFile, $strFileContents);
		fclose($ptrFile);
	}
	catch (Exception $e)
	{
		echo "Unable to write file.\n\n".$e;
		die();
	}
	
	chmod ($strFileLocation, 0777);
	chmod ($strFileLocation . ".bak", 0777);
	
	echo "File successfully written!";
	
	?>
</pre>

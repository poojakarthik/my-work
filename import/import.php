<pre>
<?
	
	$_DATABASE = "bash";
	
	$MySQL_Link = mysql_connect ("10.11.12.13", "bash", "bash");
	
	$_TABLES = mysql_list_tables ($_DATABASE);
	
	while ($_TABLE = mysql_fetch_row ($_TABLES)) {
		$_FIELDS = mysql_query ("SHOW COLUMNS FROM " . $_DATABASE . "." . $_TABLE [0]);
		
		?>
		
	unset($arrDefine);
	
	//----------------------------------------------------------------------------//
	// Table: <?=$_TABLE [0] . "\n"?>
	//----------------------------------------------------------------------------//
	
	$arrDefine['Name']		= "<?=$_TABLE [0]?>";
	$arrDefine['Type']		= "InnoDB";
	$arrDefine['Id']		= "Id";
	$arrDefine['Index'][] 		= "";
	$arrDefine['Unique'][] 		= "";
		
		<?
		
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
				
				?>
				
	// Define Columns
	$strName = "<?=$_FIELD ['Field']?>";
		$arrDefine['Column'][$strName]['Type'] 			= "<?=$_FIELD ['RefType']?>";
		$arrDefine['Column'][$strName]['SqlType'] 		= "<?=$_FIELD ['Type']?>";
		$arrDefine['Column'][$strName]['Null'] 			= <?=(($_FIELD ['Null'] === "YES") ? "TRUE" : "FALSE")?>;
		$arrDefine['Column'][$strName]['Default'] 		= <?=(($_FIELD ['Default'] === null) ? "null" : "\"" . $_FIELD ['Default'] . "\"")?>;
		$arrDefine['Column'][$strName]['ObLib'] 		= "<?=$_FIELD ['ObLib']?>";
		
				<?
			}
		}
	?>	
	// Save Table Define
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine;
	<?
	}
	
?>

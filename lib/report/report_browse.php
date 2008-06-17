<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Changes the old Data Reports to match the new format
//----------------------------------------------------------------------------//

// load application
require_once('require.php');

$selReports = new StatementSelect("DataReport", "*");
$selReports->Execute();

// Styles
$strTH = "width:10%;border:1px solid";
$strTD = "border:1px solid";

echo	"<html><head><title>Data Reports on ".DATABASE_URL."</title></head>\n" .
		"<body>\n";
		
while ($arrReport = $selReports->Fetch())
{
	$arrReport['Documentation'] = unserialize($arrReport['Documentation']);
	$arrReport['SQLSelect'] = unserialize($arrReport['SQLSelect']);
	$arrReport['SQLFields'] = unserialize($arrReport['SQLFields']);
	Debug($arrReport);
	continue;
	
	echo "<table width='100%' style='border: 1px solid'>\n";
	
	// Id
	echo "<tr><th style='$strTH'>Id</th><td style='$strTD'>{$arrReport['Id']}</td></tr>\n";
	
	// Name
	echo "<tr><th style='$strTH'>Name</th><td style='$strTD'>{$arrReport['Name']}</td></tr>\n";
	
	// Summary
	echo "<tr><th style='$strTH'>Summary</th><td style='$strTD'>{$arrReport['Summary']}</td></tr>\n";
	
	// SQLTable
	echo "<tr><th style='$strTH'>FROM Clause</th><td style='$strTD'>{$arrReport['SQLTable']}</td></tr>\n";
	
	// Columns
	echo "<tr><th style='$strTH'>Columns</th><td style='$strTD'>\n";
	$arrColumns = unserialize($arrReport['SQLSelect']);
	foreach ($arrColumns as $strCol=>$arrCol)
	{
		echo "<table width='100%'>\n";
		echo "<tr><th style='$strTH'>$strCol</th><td style='$strTD'>\n";
		
		echo "<b>Value:</b> {$arrCol['Value']}<br />\n";
		echo ($arrCol['Function']) ? "<b>Function:</b> {$arrCol['Function']}<br />\n" : "";
		echo ($arrCol['Type']) ? "<b>Type:</b> {$arrCol['Type']}<br />\n" : "";
		echo ($arrCol['Total']) ? "<b>Total:</b> {$arrCol['Total']}<br />\n" : "";
		
		Debug($arrCol);
		
		echo "</td></table>\n";
	}
	echo "</td></tr>\n";
	
	// SQLWhere
	// TODO
	
	// SQLFields
	// TODO
	
	// SQLGroupBy
	// TODO
	
	// RenderMode
	// TODO
	
	echo "</table><br />\n";
}
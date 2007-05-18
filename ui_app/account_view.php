<?php

// of Dbo->Account->Id->Render($templatetype)
function Render($templatetype)
{

/*
	// $templatetype = label;
	$strTag = $this->GetHTMLTag($templatetype);
	// $strTag = ""
	
	
	$myTarget = $this->Dbo->Account->Id->Value;
	$newTag = strReplace($strTag, "[location]", "localhost/intran...../account_view.php?Id=" . $myTarget);
	$newTag = strReplace($newTage, "[pagename]", "View Account");
	echo $newTag;*/
	
	
	
	
	// $templatetype = input;
	// lookup database definition to see what type to use
	$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	$myType = $GLOBALS['arrDatabaseTableDefine']['Account']['Column']['Id']['DisplayType'];
	$strTag = $this->GetHTMLTag($templatetype,$myType);
	
	// $strTag = "<input name=[name] class='input-wide-string'>[value]</input>"
	// what we want:
	// 	<input name='Account.Id' class='input-wide-string'>100012345</input>
	
	// we need some code somewhere to replace place-holders with actual values
	// in the example below, how do we figure out account.id is what we want?
	$strTag = strReplace($strTag, "[name]", 'account.id');
	$strTag = strReplace($strTag, "[value]", $this->Value);
	echo $strTag;
	
}



?>

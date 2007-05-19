<?php
// This is the file which handles the HTML Element Templates
// I am unsure where it should reside, it is kinda part of the framework, but
// not entirely.
class HTMLTemplate
{
	/*$arrParams['Definition'] 	= $arrType;
	$arrParams['Template'] 		= $strTemplateType;
	$arrParams['Value'] 		= $this->Value;
	$arrParams['Name'] 			= $this->Name;
	$arrParams['Valid'] 		= $this->Valid;
	$arrParams['Required'] 		= $bolRequired;*/
	function Input($arrParams)
	{
		echo "<td>";
		echo "<input name='account.id' value='{$arrParams['Value']}'></input>";
		echo "</td>\r\n";
	}
	
	function Label($arrParams)
	{
		echo "<td>";
		echo "<div id='{$arrParams['Name']}'>{$arrParams['Value']}</div>";
		echo "</td>\r\n";
	}
	
	function Other($arrParams)
	{
		echo "<td>";
		echo "<div id='{$arrParams['Name']}'>{$arrParams['Value']}</div>";
		echo "</td>\r\n";
	
	}
	// function which handles any method calls which dont exist
    function __call($strMethodName, $arrMethodParams)
    {
		$arrParams = $arrMethodParams[0];
		echo "<td>";
		echo "<div id='$strMethodName():{$arrParams['Name']}'>{$arrParams['Value']}</div>";
		echo "</td>\r\n";
    }
}


?>

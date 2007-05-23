<?php
// This is the file which handles the HTML Element Templates
// I am unsure where it should reside, it is kinda part of the framework, but
// not entirely.
class HTMLElements
{
	/*$arrParams['Definition'] 	= $arrType;
	$arrParams['Template'] 		= $strTemplateType;
	$arrParams['Value'] 		= $this->Value;
	$arrParams['Name'] 			= $this->Name;
	$arrParams['Valid'] 		= $this->Valid;
	$arrParams['Required'] 		= $bolRequired;*/
	function Input($arrParams)
	{
		// get documentation for label
		$strDocumentation = explode(".",$arrParams['Name']);
		
		// work out the class to use
		if (!$arrParams['Definition']['Class'])
		{
			$arrParams['Definition']['Class'] = CLASS_DEFAULT; // Default
		}
		$strClass = $arrParams['Definition']['Class']."Input"; // DefaultInput
		if ($arrParams['Valid'] === FALSE)
		{
			$strClass .= "Invalid"; // DefaultInputInvalid
		}
		
		echo "<td>";
		echo "$strDocumentation[1]:";
		echo "</td>";
		echo "<td>";
		echo "<input name='account.id' value='{$arrParams['Value']}' class='$strClass'></input>";
		echo "</td>";
	}
	
	function Label($arrParams)
	{

		// get documentation for label
		$strDocumentation = explode(".",$arrParams['Name']);
		
		echo "<td>";
		echo "$strDocumentation[1]:";
		echo "</td>";
		echo "<td>";
		//echo "<input name='account.id' value='{$arrParams['Value']}' class='input-string' style='text-align:right'></input>";
		echo "<div id='{$arrParams['Name']}' class='right'>{$arrParams['Value']}</div>";
		echo "</td>";
	}
	
	function Other($arrParams)
	{
		echo "<td>";
		// get documentation for label
		$strDocumentation = explode(".",$arrParams['Name']);
		echo "$strDocumentation[1]:";
		echo "</td>";
		echo "<td>";
		echo "<div id='{$arrParams['Name']}' class='right'>{$arrParams['Value']}</div>";
		echo "</td>";
	
	}
	// function which handles any method calls which dont exist
    function __call($strMethodName, $arrMethodParams)
    {
		$arrParams = $arrMethodParams[0];
		echo "<td>";
		echo "<div id='$strMethodName():{$arrParams['Name']}'>{$arrParams['Value']}</div>";
		echo "</td>";
    }
}


?>

<?php


//---------------
// framework.php
//---------------

// of Dbo->Account->Id->Render($templatetype)
function dboRender($strTemplateType, $bolRequired)
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
	//$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	//$arrType = $GLOBALS['arrDatabaseTableDefine']['Account']['Column']['Id'];
	
	$arrType = Array();
	$arrType['Class'] 	= 'input-string-valid';
	
	$arrParams = Array();
	/*$arrParams['Definition'] 	= $arrType;
	$arrParams['Template'] 		= $templatetype;
	$arrParams['Value'] 		= $this->Value;
	$arrParams['Name'] 			= $this->Name;
	$arrParams['Valid'] 		= $this->Valid;
	$arrParams['Required'] 		= $bolRequired;*/
		
	$arrParams['Definition'] 	= $arrType;
	$arrParams['Template'] 		= $strTemplateType;
	$arrParams['Value'] 		= '100012345';
	$arrParams['Name'] 			= 'account.id';
	$arrParams['Valid'] 		= TRUE;
	$arrParams['Required'] 		= $bolRequired;
	
	RenderHTMLTemplate($arrParams);
	
	// $strTag = "<input name=[name] class='input-wide-string'>[value]</input>"
	// what we want:
	// 	<input name='Account.Id' class='input-wide-string'>100012345</input>
	
	
}

class Page
{

	private $strPageName;
	private $strPageLayout;
	private $arrObjects;
	private $intColumns;

	function SetName($strName)
	{
		//var_dump($this);
		echo "<br />";
		$this->strPageName = $strName;
	}
	
	function SetLayout($strLayout)
	{
		$this->strPageLayout = $strLayout;
	}
	
	function AddObject($name,$column,$id)
	{
		$myObject = Array();
		$myObject['Name'] = $name;
		$myObject['Column'] = $column;
		$myObject['Id'] = $id;
		$this->arrObjects[0] = $myObject;

	}
	
	function Render()
	{
		
		// this echo will be replaced by a page template
		// and a header template
		echo "<html>\r\n<head>\r\n";
		echo "<link rel='stylesheet' type='text/css' href='default.css' />\r\n";
		echo "</head>\r\n<body>\r\n";
		//var_dump($this->Page->arrObjects);
		foreach($this->Page->arrObjects as $myObject)
		{
			//Name = 'AccountDetails';
			require_once('html_object_templates/account.php');
			AccountDetails::objRender();
		}
		// this echo will be replaced by a page-end template
		echo "</body>\r\n</html>";
	}

}


?>

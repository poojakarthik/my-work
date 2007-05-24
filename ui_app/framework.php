<?php


//---------------
// framework.php
//---------------

// Add this to the 'real' PropertyToken class
class PropertyToken
{

	function RenderInput($bolRequired=NULL, $strContext=NULL)
	{
		// Build up parameters for RenderHTMLTemplate()
		//TODO!Interface-kids!Actually do this
		
		RenderHTMLTemplate($arrParams);
	}

	function RenderOutput($bolRequired=NULL, $strContext=NULL)
	{
		// Build up parameters for RenderHTMLTemplate()
		//TODO!Interface-kids!Actually do this
		
		RenderHTMLTemplate($arrParams);
	}

}


// of Dbo()->Account->Id->RenderInput($bolRequired, $strContext)
// Dbo()->Object->Property->Render([$bolRequired], [$strContext]);
//DEPRECIATED
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
	/*
	$arrParams['Object'] 		= $this->object;		// 'Account'
	$arrParams['Property'] 		= $this->property;		// 'Id'
	$arrParams['Context'] 		= $this->context;		// DEFAULT
	$arrParams['Definition'] 	= $;					// definition array
	$arrParams['Value'] 		= $this->Value;			// '1000123456'
	$arrParams['Valid']			= $;					// TRUE
	$arrParams['Required'] 		= $bolRequired;			// TRUE
	
	$arrDefinition['ValidationRule']	= $;			// VALID_EMAIL
	$arrDefinition['InputType']	= $;					// 
	$arrDefinition['OutputType']	= $;				//
	$arrDefinition['Label']	= $;						//
	$arrDefinition['InputOptions']	= $;				//
	$arrDefinition['OutputOptions']	= $;				// ['-1'] = "blah <value> blah"
														// ['0']  = "blah bleh blah"
	$arrDefinition['DefaultOutput']	= $;				// "Do not charge for <value> months"
	$arrDefinition['OutputMask']	= $;				// 
	
	*/
		
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
	private $arrObjects = Array();
	private $intColumns;

	function __construct()
	{
		$this->arrObjects = Array();
	}
	
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
	
	function AddObject($strName, $intColumn, $strId=NULL)
	{
		// set UID for this object
		if ($strId)
		{
			// check if this object already exists and die (or something) if it does
		}
		else
		{
			$strId = uniqid();
		}
		
		// set the class name
		$strClassName = "HtmlTemplate$strName";
		
		// set up the object
		$objObject = Array();
		$objObject['Name'] = $strName;
		$objObject['Column'] = $intColumn;
		$objObject['Object'] = new $strClassName;
		$this->arrObjects[$strId] = $objObject;
		
		// return the object id
		return $strId;
	}
	
	function Render()
	{
		// load required layout
		require_once(TEMPLATE_BASE_DIR."layout_template/" . strtolower($this->strPageLayout) . ".php");
	}

}


?>

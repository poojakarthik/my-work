<?php

function __autoload($strClassName)
{
	//no exceptions can be thrown, so an errors will be fatal
	require_once("apptemplate/" . strtolower($strClassName) . ".php");

}
//---------------
// functions.php
//---------------
require_once('html_elements.php');

// RenderHTMLTemplate will be one of the global functions of the framework, which
// accepts a set of definitions, and creates a html tag from them.
function RenderHTMLTemplate($arrParams)
{
	/*$arrParams['Definition'] 	= $arrType;
	$arrParams['Template'] 		= $strTemplateType;
	$arrParams['Value'] 		= $this->Value;
	$arrParams['Name'] 			= $this->Name;
	$arrParams['Valid'] 		= $this->Valid;
	$arrParams['Required'] 		= $bolRequired;*/
	
	// problem with using method overloading:
	// it only works if the class has been instantiated first,
	// cant be used without
	
	$rah = new HTMLTemplate;
	$rah->$arrParams['Template']($arrParams);
	//HTMLTemplate::$arrParams['Template']($arrParams);

}

function LoadPage($strPageName)
{
	require_once("php_page_templates/" . strtolower($strPageName) . ".php");
}

//------------------------
// application_loader.php
//------------------------

class Application
{


	function Load($strTemplateName)
	{
		//split name
		$arrThing = explode ('.', $strTemplateName);
		$Type = $arrThing[0];
		$Name = $arrThing[1];
		//Get user details (inc Permissions)
		//$this->Dbo->Session->AuthenticatedEmployee->GetDetails();
		/*???can't this be done in the framework at the same time you build the Dbo object of 	variables
		--at this stage we ahavent defined this anywhere, needs to be somewhere\
		-- could be here or lower level*/
	
		//Create AppTemplate Object
		$objAppTemplate = new $Type;	// new Account
		//Run AppTemplate
		$objAppTemplate->{$Name}();		// ->View()
	
		//Add context menu
		//Add in JS & CSS
		//Add in documentation
	
		// RENDER
		//$this->Page->Render();
		/*
		??? call the render function of the page object
		??? decide where context, doco and js/css go, and also include breadcrumbs
		*/
	}
}

$myApplication = new Application;

//---------------
// framework.php
//---------------

// of Dbo->Account->Id->Render($templatetype)
function Render($strTemplateType, $bolRequired)
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
	$arrType = $GLOBALS['arrDatabaseTableDefine']['Account']['Column']['Id'];
	// $arrType = Template -> WIDE_INPUT
	
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
	
	function SetName($strName)
	{
	
	}
	
	function SetLayout($strLayout)
	{
	
	}

}
/*
Render('Input',TRUE);
Render('Label',TRUE);
Render('Other',TRUE);
Render('Select',TRUE);
*/

?>

<?php

require_once('functions.php');
require_once('framework.php');
$myApplication = new Application;

function __autoload($strClassName)
{
	//no exceptions can be thrown, so an errors will be fatal
	require_once("apptemplate/" . strtolower($strClassName) . ".php");

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
	
		/*
		//Create AppTemplate Object
		$this->objAppTemplate = new $Type;	// new Account
		//Run AppTemplate
		$this->objAppTemplate->{$Name}();		// ->View()
		*/
		
		Account::View();
		
		//Add context menu
		//Add in JS & CSS
		//Add in documentation
	
		// RENDER
		Page::Render();
		/*
		??? call the render function of the page object
		??? decide where context, doco and js/css go, and also include breadcrumbs
		*/
	}
	
	function LoadPage($strPageName)
	{
		//var_dump($myApplication);
		$this->Page = new Page;
		require_once("php_page_templates/" . strtolower($strPageName) . ".php");
		//echo $myApplication->Page;
	}
}



?>

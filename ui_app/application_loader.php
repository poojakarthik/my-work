<?php

// Definitions

Define ('TEMPLATE_BASE_DIR', "");
Define ('TEMPLATE_STYLE_DIR', "style_template/");

Define ('COLUMN_ONE'	, 1);
Define ('COLUMN_TWO'	, 2);
Define ('COLUMN_THREE'	, 3);
Define ('COLUMN_FOUR'	, 4);

require_once('functions.php');
require_once('framework.php');
$myApplication = new Application;

function __autoload($strClassName)
{
	// Class Names must be like;
	// app_template/account.php			AppTemplateAccount
	// html_template/account_view.php	HtmlTemplateAccountView
	// html_template/cdr_view.php		HtmlTemplateCdrView
	
	// if the class is a template
	
		// split on 'Template'	HtmlTemplateCdrView
				// $strFolder 		= 'html_template'
				// $strFileIndex	= 'cdrview'
		// build the folder name (eg. html_template)
		// get a directory listing (if we don't have it already)
		// make a directory listing array $array['cdrview'] = 'cdr_view.php'
				// $filename = $array[$strFileIndex]
		// save the directory listing array to globals
		// check the array to find the file name
		// use a constant to specify the absolute path to the template base dir
		// build the class path
	
	// else
		// nothing for now
	
	$arrClassName = explode("template", $strClassName);
	$strClassPath = $arrClassName[0] . "_template";
	
	if (unset($arrAvailableFiles[$strClassPath]))
	{
		$arrAvailableFiles[$strClassPath] = new array();
		
		// add each filename in the $strClassPath directory to a list and check if 
		// the desired class name can be found
		
		
	}	
	$arrAvailableFiles[$strClassPath] = glob(
	
	// try and load the class file
	if ($strClassPath)
	{
		require_once($strClassPath);
	}
	
	//remove this once the function works
		if (substr($strClassName, 0, 11) == 'AppTemplate')
		{
			include_once(TEMPLATE_BASE_DIR."app_template/".strtolower(substr($strClassName, 11)).".php");
		}
		elseif (substr($strClassName, 0, 12) == 'HtmlTemplate')
		{
			include_once(TEMPLATE_BASE_DIR."html_template/".strtolower(substr($strClassName, 12)).".php");
		}
}

//------------------------
// application_loader.php
//------------------------

class Application
{

	function Load($strTemplateName)
	{
		//split template name
		$arrTemplate 	= explode ('.', $strTemplateName);
		$strClass 		= 'AppTemplate'.$arrTemplate[0];
		$strMethod 		= $arrTemplate[1];
		
		//Get user details (inc Permissions)
		//$this->Dbo->Session->AuthenticatedEmployee->GetDetails();
		/*???can't this be done in the framework at the same time you build the Dbo object of 	variables
		--at this stage we ahavent defined this anywhere, needs to be somewhere\
		-- could be here or lower level*/
	
		
		
		//Create AppTemplate Object
		$this->objAppTemplate = new $strClass;
		
		//Run AppTemplate
		$this->objAppTemplate->{$strMethod}();
		
		//Add context menu
		//Add in JS & CSS
		//Add in documentation
	
		// Render Page
		$this->objAppTemplate->Page->Render();
		
		/*
		??? call the render function of the page object
		??? decide where context, doco and js/css go, and also include breadcrumbs
		*/
	}
	
}

class ApplicationTemplate extends BaseTemplate
{
	
	function LoadPage($strPageName)
	{
		// create new page object
		$this->Page = new Page;
		
		// load required page
		require_once(TEMPLATE_BASE_DIR."page_template/" . strtolower($strPageName) . ".php");
	}
}

class PageTemplate extends BaseTemplate
{

}

class HtmlTemplate extends BaseTemplate
{

}

class LayoutTemplate extends BaseTemplate
{

}


class BaseTemplate
{

}


?>

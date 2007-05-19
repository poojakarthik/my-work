<?php
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


?>

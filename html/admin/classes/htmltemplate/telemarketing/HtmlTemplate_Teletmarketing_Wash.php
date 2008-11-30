<?php

class HtmlTemplate_Telemarketing_Wash extends FlexHtmlTemplate
{
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		//$this->LoadJavascript("telemarketing_wash");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->Admin();
		BreadCrumb()->SetCurrentPage("Wash Proposed Dialler List");
	}

	public function Render()
	{
		// Render the containing DIV
		echo	"<div class='Page'> \n" .
				"	Test" .
				"</div>";
		
	}
}

?>
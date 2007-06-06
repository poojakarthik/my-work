<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// knowledge_base_doc_view.php
//----------------------------------------------------------------------------//
/**
 * knowledge_base_doc_view
 *
 * HTML Template for the knowledge base doc view HTML object
 *
 * HTML Template for the knowledge base doc view HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all information relating to a knowledge base document and can be embedded in
 * various Page Templates
 *
 * @file		knowledge_base_doc_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateKnowledgeBaseDocView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateKnowledgeBaseDocView
 *
 * HTML Template class for the knowledge base doc view HTML object
 *
 * HTML Template class for the knowledge base doc view HTML object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateKnowledgeBaseDocView
 * @extends	HtmlTemplate
 */
 
 
class HtmlTemplateBreadCrumb extends HtmlTemplate
{
	function __construct()
	{
		// Load all java script specific to the page here
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{	
		echo "<div Id='VixenBreadCrumb' Class='BreadCrumbMenu'>\n	";
		foreach (DBO()->BreadCrumb AS $objProperty)
		{
			echo " / <a href ='".$objProperty->Value."'>".$objProperty->Label."</a>";
		}
		echo "\n</div>\n";
	}
}

?>

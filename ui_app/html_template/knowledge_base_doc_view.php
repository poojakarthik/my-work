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
 
 
class HtmlTemplateKnowledgeBaseDocView extends HtmlTemplate
{
	function __construct()
	{
		// I don't currently know if this is necessary for this page
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
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
		// output the information about the document
		// This should eventually utilise the RenderOutput methods of the PropertyToken class
		// however display data (relating to the knowledge base) is not currently stored in the
		// UIAppDocumentation table of the database
		//echo "<br><b>Article Id: </b>" . DBO()->KnowledgeBase->Id->Value ."\n";
		echo "<table border='5'>\n";
		DBO()->KnowledgeBase->Id->RenderOutput();
		DBO()->KnowledgeBase->Title->RenderOutput();
		DBO()->KnowledgeBase->Abstract->RenderOutput();
		DBO()->KnowledgeBase->Content->RenderOutput();
		echo "</table>\n";
		
		
		// check that an author could be found
		if (!DBO()->Author->IsInvalid())
		{
			// the author was found
			echo "<br><b>Created by: </b>" . DBO()->Author->FirstName->Value . " " . DBO()->Author->LastName->Value ."\n";
		}
		else
		{
			// the author could not be found so just output their employee id
			echo "<br><b>Created by: </b>employee id ". DBO()->KnowledgeBase->CreatedBy->Value . " (unknown)\n";
		}
		
		
		echo "<br><b>Created on: </b>" . DBO()->KnowledgeBase->CreatedOn->Value ."\n";
		
		echo "<br><b>Last updated: </b>". DBO()->KnowledgeBase->LastUpdated->Value ."\n";
		
		// check that an authoriser could be found
		if (DBO()->Authoriser->IsValid())
		{
			// the authoriser was found
			echo "<br><b>Authorised by: </b>" . DBO()->Authoriser->FirstName->Value . " " . DBO()->Authoriser->LastName->Value ."\n";
		}
		else
		{
			// the authoriser could not be found so just output their employee id
			echo "<br><b>Authorised by: </b>employee id ". DBO()->KnowledgeBase->AuthorisedBy->Value . " (unknown)\n";
		}
		echo "<br><b>Authorised on: </b>" . DBO()->KnowledgeBase->AuthorisedOn->Value ."\n";
		
		// Output links to all related documents
		// have the link label be the id and title of the document  "title (id:123)"
		
		foreach (DBL()->KnowledgeBase AS $dboKnowledgeBase)
		{
			echo "<br><b>Related Article: </b>";
			echo "<A href='knowledge_base_doc_view.php?KnowledgeBase_Id=". $dboKnowledgeBase->Id->Value ."'>".$dboKnowledgeBase->Title->Value . " (doc id: ". $dboKnowledgeBase->Id->Value .")"."</A>" ."\n";
		}
		
		
	}
}

?>

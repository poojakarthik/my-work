<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// knowledge_base
//----------------------------------------------------------------------------//
/**
 * knowledge_base
 *
 * contains all ApplicationTemplate extended classes relating to Knowledge Base functionality
 *
 * contains all ApplicationTemplate extended classes relating to Knowledge Base functionality
 *
 * @file		knowledge_base.php
 * @language	PHP
 * @package		framework
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateKnowledgeBase
//----------------------------------------------------------------------------//
/**
 * AppTemplateKnowledgeBase
 *
 * The AppTemplateKnowledgeBase class
 *
 * The AppTemplateKnowledgeBase class.  This incorporates all logic for all pages
 * relating to the knowledge base.
 *
 * @package	ui_app
 * @class	AppTemplateKnowledgeBase
 * @extends	ApplicationTemplate
 */
class AppTemplateKnowledgeBase extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// ViewDocument
	//------------------------------------------------------------------------//
	/**
	 * ViewDocument()
	 *
	 * Performs the logic for the knowledge_base_doc_view.php webpage
	 * 
	 * Performs the logic for the knowledge_base_doc_view.php webpage
	 *
	 * @return		bool
	 * @method
	 *
	 */
	function ViewDocument()
	{
		// Should probably check user authorization here
		//TODO!include user authorisation

		// retrieve the requested document...
		// The contents of $_GET is set up in the DBO() object within submitted_data::Get() which has already
		// been called within Application::Load
		// however the actual DBO()->...->Load() method is not run
		// This is only set up for the GET variables defined in the format "Object_Property=value"
		// ie knowledge_base_doc_view.php?KnowledgeBase_Id=1
		if (!DBO()->KnowledgeBase->Load())
		{
			// the document was not specified so display an appropriate error message and return them to the document selection page
			//$this->LoadPage('knowledge_base_doc_select');
			//echo("<br> The document requested could not be found");
			DBO()->Error->Message = "The document requested could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// retrieve all related documents (relationships between documents are defined in the table KnowledgeBaseLink)
		DBL()->KnowledgeBase->Where->Set("Id IN (SELECT ArticleRight FROM KnowledgeBaseLink WHERE ArticleLeft = <Id>)
										OR
										Id IN (SELECT ArticleLeft FROM KnowledgeBaseLink WHERE ArticleRight = <Id>)", 
										Array('Id'=>DBO()->KnowledgeBase->Id->Value));
		DBL()->KnowledgeBase->SetColumns(Array("Id", "Title"));
		DBL()->KnowledgeBase->Load();

		// Load the name of the employee who created the KnowledgeBase document
		DBO()->Author->Id = DBO()->KnowledgeBase->CreatedBy->Value;
		DBO()->Author->SetTable("Employee");
		DBO()->Author->Load();
		
		// Load the name of the employee who authorised the KnowledgeBase document
		DBO()->Authoriser->Id = DBO()->KnowledgeBase->AuthorisedBy->Value;
		DBO()->Authoriser->SetTable("Employee");
		DBO()->Authoriser->Load();
		
		// All data relating to the document has been retrieved from the database so now load the page template
		$this->LoadPage('knowledge_base_doc_view');
		
		// context menu
		ContextMenu()->Level_1->Level_2->View_Account(1);
		
		// add to breadcrumb menu
		BreadCrumb()->ViewAccount(1000006574);
		BreadCrumb()->ViewService(1, '0787321549');
		
		
		return TRUE;
	}
}
?>

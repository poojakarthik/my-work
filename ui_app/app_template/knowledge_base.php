<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//



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

		// retrieve the requested document...
		// The contents of $_GET is set up in the DBO() object within submitted_data::Get() which has already
		// been called within Application::Load
		// however the actual DBO()->...->Load() method is not run
		// This is only set up for the GET variables defined in the format "Object_Property=value"
		// ie knowledge_base_doc_view.php?KnowledgeBase_Id=1
		if (!DBO()->KnowledgeBase->Load())
		{
			// the document was not specified so display an appropriate error message and return them to the document selection page
			$this->LoadPage('knowledge_base_doc_select');
			return FALSE;
		}
		
		// retrieve all related documents (relationships between documents are defined in the table KnowledgeBaseLink)
		DBL()->KnowledgeBase->Where->Set("Id IN (SELECT ArticleRight FROM KnowledgeBaseLink WHERE ArticleLeft = <Id>)
										OR
										Id IN (SELECT ArticleLeft FROM KnowledgeBaseLink WHERE ArticleRight = <Id>)", 
										Array('Id'=>DBO()->KnowledgeBase->Id->Value));
		DBL()->KnowledgeBase->_arrColumns = Array("Id", "Title");
		DBL()->KnowledgeBase->Load();
		
		// Load the name of the employee who created the KnowledgeBase document
		DBO()->Author->Id = DBO()->KnowledgeBase->CreatedBy->Value;
		DBO()->Author->_strTable = "Employee";
		if (!DBO()->Author->Load())
		{
			// could not find the author in the Emplyee table
			// should probably set DBO()->Author to be invalid
		}
		
		// Load the name of the employee who authorised the KnowledgeBase document
		DBO()->Authoriser->Id = DBO()->KnowledgeBase->AuthorisedBy->Value;
		DBO()->Authoriser->_strTable = "Employee";
		if (!DBO()->Authoriser->Load())
		{
			// could not find the Authoriser in the Emplyee table
			// should probably set DBO()->Authoriser to be invalid
		}
		
		// All data relating to the document has been retrieved from the database so now load the page template
		$this->LoadPage('knowledge_base_doc_view');
		
		return TRUE;
	}
}
?>

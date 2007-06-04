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
echo "THIS IS WITHIN THE APPLICATION TEMPLATE";

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
		DBL()->KnowledgeBaseLink->Where->Set("ArticleLeft = <Id> OR ArticleRight = <Id>", Array('Id'=>DBO()->KnowledgeBase->Id->Value));
		DBL()->KnowledgeBaseLink->Load();
			
		// The document has been retrieved from the database
		$this->LoadPage('knowledge_base_doc_view');
		
		return TRUE;
	}
}
?>

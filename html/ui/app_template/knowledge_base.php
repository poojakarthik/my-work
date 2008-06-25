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
	// ViewArticle
	//------------------------------------------------------------------------//
	/**
	 * ViewArticle()
	 *
	 * Performs the logic for the knowledge_base_view_article.php webpage
	 * 
	 * Performs the logic for the knowledge_base_view_article.php webpage
	 *
	 * @return		bool
	 * @method
	 *
	 */
	function ViewArticle()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolIsAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// context menu
		//ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Employee_Console();		
		ContextMenu()->Contact_Retrieve->Service->Add_Service(DBO()->Account->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->Edit_Service(DBO()->Service->Id->Value);		
		ContextMenu()->Contact_Retrieve->Service->Change_Plan(DBO()->Service->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->Change_of_Lessee(DBO()->Service->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);	

		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Invoice_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->View_Service_Notes(DBO()->Service->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Service_Note(DBO()->Service->Id->Value);
		if ($bolIsAdminUser)
		{
			// User must have admin permissions to view the Administrative Console
			ContextMenu()->Admin_Console();
		}
		ContextMenu()->Logout();
		
		// breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->Knowledge_Base();
		BreadCrumb()->SetCurrentPage("View Article");
		
		// retrieve the requested document
		if (!DBO()->KnowledgeBase->Load())
		{
			DBO()->Error->Message = "The document requested could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		$strId = DBO()->KnowledgeBase->Id->Value;
		$strId = str_pad($strId, 7, "kb00000", STR_PAD_LEFT);
		DBO()->KnowledgeBase->ArticleId = $strId;
		
		// retrieve all related documents (relationships between documents are defined in the table KnowledgeBaseLink)
		DBL()->KnowledgeBase->Where->Set("Id IN (SELECT ArticleRight FROM KnowledgeBaseLink WHERE ArticleLeft = <Id>)
										OR
										Id IN (SELECT ArticleLeft FROM KnowledgeBaseLink WHERE ArticleRight = <Id>)", 
										Array('Id'=>DBO()->KnowledgeBase->Id->Value));
		DBL()->KnowledgeBase->SetColumns("Id, Title, Abstract, CreatedOn, LastUpdated");
		DBL()->KnowledgeBase->Load();

		// Set the properly formatted Id for each article
		foreach (DBL()->KnowledgeBase as $dboArticle)
		{
			$strId = $dboArticle->Id->Value;
			$strId = str_pad($strId, 7, "kb00000", STR_PAD_LEFT);
			$dboArticle->ArticleId = $strId;
		}

		// All data relating to the document has been retrieved from the database so now load the page template
		$this->LoadPage('knowledge_base_doc_view');
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ListArticles
	//------------------------------------------------------------------------//
	/**
	 * ListArticles()
	 *
	 * Performs the logic for the knowledge_base_list_articles.php webpage
	 * 
	 * Performs the logic for the knowledge_base_list_articles.php webpage
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ListArticles()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolIsAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// context menu
		//ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Employee_Console();		
		ContextMenu()->Contact_Retrieve->Service->Add_Service(DBO()->Account->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->Edit_Service(DBO()->Service->Id->Value);		
		ContextMenu()->Contact_Retrieve->Service->Change_Plan(DBO()->Service->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->Change_of_Lessee(DBO()->Service->Id->Value);	
		ContextMenu()->Contact_Retrieve->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);	

		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Invoice_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Make_Payment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->Add_Recurring_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->View_Service_Notes(DBO()->Service->Id->Value);
		ContextMenu()->Contact_Retrieve->Notes->Add_Service_Note(DBO()->Service->Id->Value);
		if ($bolIsAdminUser)
		{
			// User must have admin permissions to view the Administrative Console
			ContextMenu()->Admin_Console();
		}
		ContextMenu()->Logout();
		
		// breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SetCurrentPage("Knowledge Base");
		
		
		// Setup all DBO and DBL objects required for the page
		DBL()->KnowledgeBase->SetColumns("Id, Title, Abstract, CreatedOn, LastUpdated");
		DBL()->KnowledgeBase->OrderBy("Id");
		if (!DBL()->KnowledgeBase->Load())
		{
			DBO()->Error->Message = "The list of Knowledge Base articles could not be retrieved from the database";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Set the properly formatted Id for each article
		foreach (DBL()->KnowledgeBase as $dboArticle)
		{
			$strId = $dboArticle->Id->Value;
			$strId = str_pad($strId, 7, "kb00000", STR_PAD_LEFT);
			$dboArticle->ArticleId = $strId;
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('knowledge_base_list_articles');

		return TRUE;
	}
	
	
}
?>

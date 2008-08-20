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
 * Page Template for the knowledge_base_doc_view webpage
 *
 * Page Template for the knowledge_base_doc_view webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
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


// set the page title
$this->Page->SetName("Article: ". DBO()->KnowledgeBase->ArticleId->Value);

$this->Page->SetLayout('1Column');

// add the Knowledge Base Document View HTML template object to this page 
$this->Page->AddObject('KnowledgeBaseDocView', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

// If there are related articles then display them in a table
if (DBL()->KnowledgeBase->RecordCount() > 0)
{
	$this->Page->AddObject('KnowledgeBaseArticleList', COLUMN_ONE, HTML_CONTEXT_RELATED_ARTICLES);
}


?>

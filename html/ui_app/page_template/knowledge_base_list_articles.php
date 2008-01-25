<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// knowledge_base_list_articles.php
//----------------------------------------------------------------------------//
/**
 * knowledge_base_list_articles
 *
 * Page Template for the knowledge_base_list_articles webpage
 *
 * Page Template for the knowledge_base_list_articles webpage
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		knowledge_base_list_articles.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// set the page title
$this->Page->SetName('Knowledge Base');

// set the layout template for the page.
$this->Page->SetLayout('1Column');

// add HTML template objects to this page 
$this->Page->AddObject('KnowledgeBaseArticleList', COLUMN_ONE, HTML_CONTEXT_DEFAULT);


?>

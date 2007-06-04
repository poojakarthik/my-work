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
 * which has already been instantiated
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

echo "<br>THIS IS WITHIN THE PAGE TEMPLATE";

// set the page title
$this->Page->SetName('Knowledge Base Document Viewer');

// set the layout template for the page.  
// Note that this does not actually include this "layout template" file yet.  
// I imagine it is included during $this->Page->Render();
$strLayout = '1Column';
$this->Page->SetLayout($strLayout);

// add the Knowledge Base Document View HTML template object to this page 
$this->Page->AddObject('KnowledgeBaseDocView', COLUMN_ONE);


?>

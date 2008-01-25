<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// <Class>_<MethodName>
//----------------------------------------------------------------------------//
/**
 * <Class>_<MethodName>
 *
 * Page Template for the <Class>_<MethodName> page
 *
 * Page Template for the <Class>_<MethodName> page
 * This file specifies the layout to use and the HTML Template objects to put 
 * into each column on the page
 * Most code in this file (if not all) will manipulate the $this->Page object
 * which has already been instantiated.
 *
 * @file		<Class>_<MethodName>.php
 * @language	PHP
 * @package		ui_app
 * @author		[INSERT AUTHOR HERE]
 * @version		7.09  <-- [UPDATE THIS]
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName('<PageName>');

$this->Page->SetLayout('1Column');

// Add each html object to the appropriate column
$id = $this->Page->AddObject('AccountDetails', COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>

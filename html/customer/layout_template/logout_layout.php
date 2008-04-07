<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// logout_layout.php
//----------------------------------------------------------------------------//
/**
 * logout_layout
 *
 * Layout Template defining how to display the "logged out" page
 *
 * Layout Template defining how to display the "logged out" page
 * Specificly for the Client Web Application
 * This will display a single column
 *
 * @file		logout_layout.php
 * @language	PHP
 * @package		web_app
 * @author		Joel Dawkins
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once dirname(__FILE__)."/common_layout.php";

$this->RenderClientHeader();

CommonLayout::OpenPageBody($this, FALSE, FALSE);

$this->RenderColumn(COLUMN_ONE);

CommonLayout::ClosePageBody($this);

$this->RenderFooter();


?>

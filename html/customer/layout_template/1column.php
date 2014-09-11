<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 1column.php
//----------------------------------------------------------------------------//
/**
 * 1column
 *
 * Layout Template defining how to display a page that has only a single column of HTML Template objects
 *
 * Layout Template defining how to display a page that has only a single column of HTML Template objects
 * Specificly for the Client Web Application
 * This will display a single column
 *
 * @file		1column.php
 * @language	PHP
 * @package		web_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once dirname(__FILE__)."/common_layout.php";

$this->RenderClientHeader();

CommonLayout::OpenPageBody($this, TRUE, TRUE);

$this->RenderColumn(COLUMN_ONE);

CommonLayout::ClosePageBody($this);

$this->RenderFooter();


?>

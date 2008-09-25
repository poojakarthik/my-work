<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// popup.php
//----------------------------------------------------------------------------//
/**
 * popup
 *
 * Layout Template defining how to display a page that is a popup
 *
 * Specificly for the Client Web Application
 *
 * @file		popup.php
 * @language	PHP
 * @package		web_app
 * @author		Ryan Forrester
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once dirname(__FILE__)."/common_layout.php";
$this->RenderClientPOPHeader();
$this->RenderColumn(COLUMN_ONE);
?>
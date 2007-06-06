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
 *
 * @file		1column.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// include the following lines, when they work
//$this->RenderHeader();
//$this->RenderContextMenu();

//var_dump($this->Page->arrObjects);
?>
<!--  ABOUT TO RENDER COLUMN ONE  -->
<?php
$this->RenderHeader();
$this->RenderBreadCrumbMenu();
$this->RenderContextMenu();
$this->RenderColumn(COLUMN_ONE);
$this->RenderFooter();
//TODO!!!Joel make sure you finish this	

?>
<!--  FINISHED RENDERING COLUMN ONE  -->
<!--  INSERT HTML HERE (if you like)-->
<?php

?>

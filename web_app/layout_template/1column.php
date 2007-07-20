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


$this->RenderHeader();
$this->RenderClientAppHeader();
//$this->RenderBreadCrumbMenu();
$this->RenderContextMenu();
?>

<div id='PageBody'>
	<?php $this->RenderColumn(COLUMN_ONE); ?>
</div>

<?php
$this->RenderFooter();


?>

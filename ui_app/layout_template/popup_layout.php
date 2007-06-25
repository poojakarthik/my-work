<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// popup_layout.php
//----------------------------------------------------------------------------//
/**
 * popup_layout
 *
 * Layout Template defining how to display a page that will be displayed in a popup window
 *
 * Layout Template defining how to display a page that will be displayed in a popup window
 *
 * @file		popup_layout.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


$this->RenderHeader();
//$this->RenderVixenHeader();
?>

<div id='PopupPageBody'>
	<?php $this->RenderColumn(COLUMN_ONE); ?>
</div>

<?php
//$this->RenderFooter();


?>

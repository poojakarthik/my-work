<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// full_area.php
//----------------------------------------------------------------------------//
/**
 * full_area
 *
 * Layout Template defining how to display a page that requires the maximum available display area (has one full width column)
 *
 * Layout Template defining how to display a page that requires the maximum available display area (has one full width column)
 *
 * @file		full_area.php
 * @language	PHP
 * @package		ui_app
 * @author		Hadrian Oliver
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->RenderHeader();
$this->RenderFlexHeader(TRUE, TRUE, TRUE);
//$this->RenderBreadCrumbMenu();
//$this->RenderContextMenu();
?>
		<div id='content' class='maximum-area-body'>
			<?php $this->RenderColumn(COLUMN_ONE); ?>
		</div>

<?php
$this->RenderFooter();

?>

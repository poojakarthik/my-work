<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 2column.php
//----------------------------------------------------------------------------//
/**
 * 2column
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects
 *
 * @file		2column.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


$this->RenderHeader();
$this->RenderVixenHeader();
$this->RenderBreadCrumbMenu();
$this->RenderContextMenu();

// Render the columns
//TODO! how do I specify the widths of the columns?
?>

<div id='PageBody'>
<h1> <?php echo $this->_strPageName; ?></h1>
<table width='100%' border='0'>
	<tr>
		<td width='49%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_ONE); ?>
			
		</td>
		<td width='2%'></td>
		<td width='49%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_TWO); ?>
			
		</td>
	</tr>
</table>
</div>

<?php
$this->RenderFooter();


?>

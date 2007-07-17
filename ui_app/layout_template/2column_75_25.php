<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 2column_75_25.php
//----------------------------------------------------------------------------//
/**
 * 2column_75_25
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects; where the first is 75% wide and the second is 25%
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects; where the first is 75% wide and the second is 25%
 *
 * @file		2column_75_25.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


$this->RenderHeader();
$this->RenderVixenHeader();
$this->RenderBreadCrumbMenu();
$this->RenderContextMenu();

// Render the columns
?>

<div id='PageBody'>
<h1> <?php echo $this->_strPageName; ?></h1>
<table width='100%' border='0'>
	<tr>
		<td width='74%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_ONE); ?>
			
		</td>
		<td width='2%'></td>
		<td width='24%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_TWO); ?>
			
		</td>
	</tr>
</table>
</div>

<?php
$this->RenderFooter();


?>

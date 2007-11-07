<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 3column_65_35.php
//----------------------------------------------------------------------------//
/**
 * 3column_65_35
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects, and a footer column; where the first is 65% wide and the second is 35%
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects, and a footer column; where the first is 65% wide and the second is 35%
 *
 * @file		3column_65_35.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.10
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
		<td width='64%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_ONE); ?>
			
		</td>
		<td width='1%'></td>
		<td width='35%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_TWO); ?>
			
		</td>
	</tr>
</table>
<?php $this->RenderColumn(COLUMN_THREE); ?>
</div>

<?php
$this->RenderFooter();


?>

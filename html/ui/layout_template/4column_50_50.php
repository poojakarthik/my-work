<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 4column_50_50.php
//----------------------------------------------------------------------------//
/**
 * 4column_50_50
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects, and a header column and a footer column; The side by side columns are the same width
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects, and a header column and a footer column; The side by side columns are the same width
 *
 * @file		4column_50_50.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


$this->RenderHeader();
$this->RenderFlexHeader(TRUE, TRUE, TRUE);

// Render the columns
?>

<div id='PageBody'>
	<div id='PageTitle' name='PageTitle'>
		<h1> <?php echo $this->_strPageName; ?></h1>
	</div>
	<table width='100%' border='0'>
		<tr>
			<td valign='top' colspan='3'>
				<?php $this->RenderColumn(COLUMN_ONE); ?>
			</td>
		</tr>
		<tr>
			<td width='50%' valign='top'>
				
				<?php $this->RenderColumn(COLUMN_TWO); ?>
				
			</td>
			<td width='0%'></td>
			<td width='50%' valign='top'>
				
				<?php $this->RenderColumn(COLUMN_THREE); ?>
				
			</td>
		</tr>
		<tr>
			<td valign='top' colspan='3'>
				<?php $this->RenderColumn(COLUMN_FOUR); ?>
			</td>
		</tr>
	</table>
</div>

<?php
$this->RenderFooter();


?>

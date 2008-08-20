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
$this->RenderFlexHeader(TRUE, TRUE, TRUE);

// Render the columns
?>

<div id='PageBody'>
	<div id='PageTitle' name='PageTitle'>
		<h1> <?php echo $this->_strPageName; ?></h1>
	</div>
	<table width='100%' border='0'>
		<tr>
			<td width='74%' valign='top'>
				
				<?php $this->RenderColumn(COLUMN_ONE); ?>
				
			</td>
			<td width='1%'></td>
			<td width='25%' valign='top'>
				
				<?php $this->RenderColumn(COLUMN_TWO); ?>
				
			</td>
		</tr>
	</table>
</div>

<?php
$this->RenderFooter();


?>

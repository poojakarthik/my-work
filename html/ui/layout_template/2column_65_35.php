<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 2column_65_35.php
//----------------------------------------------------------------------------//
/**
 * 2column_65_35
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects; where the first is 65% wide and the second is 35%
 *
 * Layout Template defining how to display a page that has two columns of HTML Template objects; where the first is 65% wide and the second is 35%
 *
 * @file		2column_65_35.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
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
			<td width='64%' valign='top'>
				
				<?php $this->RenderColumn(COLUMN_ONE); ?>
				
			</td>
			<td width='1%'></td>
			<td width='35%' valign='top'>
				
				<?php $this->RenderColumn(COLUMN_TWO); ?>
				
			</td>
		</tr>
	</table>
</div>

<?php
$this->RenderFooter();


?>

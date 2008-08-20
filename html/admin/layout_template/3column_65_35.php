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
$this->RenderFlexHeader(TRUE, TRUE, TRUE);

// Render the columns
?>

<div id='PageBody'>
	<div id='PageTitle' name='PageTitle'>
		<h1> <?php echo $this->_strPageName; ?></h1>
	</div>
	<div id='Container_Columns_1_And_2' style='width:100%;height:auto'>
		<div id='Column1' style='width:64%;height:auto;float:left'>
			<?php $this->RenderColumn(COLUMN_ONE); ?>
		</div>
		<div id='Column2' style='width:35%;height:auto;float:right;'>
			<?php $this->RenderColumn(COLUMN_TWO); ?>
		</div>
	</div>
	<div id='Column3' style='width:100%;clear:both'>
		<?php $this->RenderColumn(COLUMN_THREE); ?>
	</div>
</div>

<?php
$this->RenderFooter();


?>

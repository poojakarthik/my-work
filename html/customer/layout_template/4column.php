<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 4column.php
//----------------------------------------------------------------------------//
/**
 * 4column
 *
 * Layout Template defining how to display a page that has 4 columns of HTML Template objects
 *
 * Layout Template defining how to display a page that has 4 columns of HTML Template objects
 * Specificly for the Client Web Application
 * This will display the first column as a header, the next 2 columns side by side, and the 4th column as a footer
 *
 * @file		4column.php
 * @language	PHP
 * @package		web_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

require_once dirname(__FILE__)."/common_layout.php";

$this->RenderClientHeader();

CommonLayout::OpenPageBody($this, TRUE, TRUE);

$this->RenderColumn(COLUMN_ONE);

?>
<table width='100%' border='0'>
	<tr>
		<td width='49%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_TWO); ?>
			
		</td>
		<td width='2%'></td>
		<td width='49%' valign='top'>
			
			<?php $this->RenderColumn(COLUMN_THREE); ?>
			
		</td>
	</tr>
</table>

<?php 
$this->RenderColumn(COLUMN_FOUR);

CommonLayout::ClosePageBody($this);

$this->RenderFooter();


?>

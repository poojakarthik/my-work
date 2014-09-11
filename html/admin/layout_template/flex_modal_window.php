<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// flex_modal_window.php
//----------------------------------------------------------------------------//
/**
 * flex_modal_window.php
 *
 * Layout Template defining how to display a page for use in a 'Flex Modal Window'
 *
 * Layout Template defining how to display a page for use in a 'Flex Modal Window'
 *
 * @file		flex_modal_window.php
 * @language	PHP
 * @package		ui_app
 * @author		Hadrian Oliver
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$this->RenderHeader();

?>
<table class='flexModalWrapper'>
	<tr>
		<td class="flexModalWrapper">
			<table class='flexModalContainer'>
				<tr>
					<td class="flexModalContainer">
						<div class="PopupBoxTopBar">
							<img src="img/template/close.png" class="PopupBoxClose" onclick='Vixen.Popup.Close("CloseFlexModalWindow")'>
							<div><?php echo $this->_strPageName; ?></div>
						</div>
						<div id='PopupPageBody'>
							<?php $this->RenderColumn(COLUMN_ONE);	?>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
$this->RenderFooter();


?>

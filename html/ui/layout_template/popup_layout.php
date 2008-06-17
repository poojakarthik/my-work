<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// popup_layout.php
//----------------------------------------------------------------------------//
/**
 * popup_layout
 *
 * Layout Template defining how to display a page that will be displayed in a popup window
 *
 * Layout Template defining how to display a page that will be displayed in a popup window
 *
 * @file		popup_layout.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Define the javascript code used to find the title of the popup and change it to the PageName defined within this Page object
$strChangePopupTitleJsCode = "document.getElementById('VixenPopupTopBarTitle__{$this->_objAjax->strId}').innerHTML = '<PageName>';";

$this->RenderJS();
?>
<div id='PopupPageBody' <?php echo (isset($this->_strStyleOverride)) ? "style='". $this->_strStyleOverride ."'" : ""; ?> >
<?php 
	$this->RenderColumn(COLUMN_ONE);
	
	// Set the title of the popup to the page name, if the page name has been declared
	if (IsSet($this->_strPageName))
	{
		echo "<script type='text/javascript'>". str_replace("<PageName>", $this->_strPageName, $strChangePopupTitleJsCode) ."</script>\n";
	}
?>
	
</div>

<?php


?>

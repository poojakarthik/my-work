<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// logout_layout.php
//----------------------------------------------------------------------------//
/**
 * logout_layout
 *
 * Layout Template defining how to display the "logged out" page
 *
 * Layout Template defining how to display the "logged out" page
 * Specificly for the Client Web Application
 *
 * @file		logout_layout.php
 * @language	PHP
 * @package		web_app
 * @author		Joel Dawkins
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


$this->RenderClientHeader();
?>

<div id="Document" class="documentContainer">

	<div class="documentCurve Left documentCurveTopLeft"></div>
	<div class="documentCurve Right documentCurveTopRight"></div>
	<div class="clear"></div>
	<div class="pageContainer">
	
	<div id="Header" class="sectionContainer">
		<div id="Banner"></div>
		<div class="MenuContainer">
		</div>
	</div>
	<h1> <?php echo $this->_strPageName; ?></h1>
	
	<div class="clear"></div>
		<div id='PageBody'>
			<?php 
				$this->RenderColumn(COLUMN_ONE);
			?>
		</div>

	</div>
	<div class="clear"></div>
	<div class="documentCurve Left documentCurveBottomLeft"></div>
	<div class="documentCurve Right documentCurveBottomRight"></div>
	<div class="clear"></div>
</div>

<?php
$this->RenderFooter();


?>

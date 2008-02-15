<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// 1column.php
//----------------------------------------------------------------------------//
/**
 * 1column
 *
 * Layout Template defining how to display a page that has only a single column of HTML Template objects
 *
 * Layout Template defining how to display a page that has only a single column of HTML Template objects
 * Specificly for the Client Web Application
 *
 * @file		1column.php
 * @language	PHP
 * @package		web_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


$this->RenderClientHeader();
//$this->RenderClientAppHeader();
//$this->RenderContextMenu();
?>

<div id="Document">

	<div class="documentCurve Left documentCurveTopLeft"></div>
	<div class="documentCurve Right documentCurveTopRight"></div>
	<div class="clear"></div>
	<div class="pageContainer">
	
	<div id="Header" class="sectionContainer">
		<div id="Banner"></div>
		<div class="MenuContainer">
			<?php $this->RenderBreadCrumbMenu();?>
			<div class='LogoutContainer'>
				<?php
					//display the logout link
					$strUserName	= AuthenticatedUser()->_arrUser['UserName'];
					$strLogoutHref	= Href()->LogoutUser();
					echo "$strUserName (<a href='$strLogoutHref'>logout</a>)\n";
				?>
			</div>
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

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


$this->RenderClientHeader();
//$this->RenderClientAppHeader();
//$this->RenderContextMenu();
?>

<div id="Document" class="documentContainer">

	<div class="documentCurve Left documentCurveTopLeft"></div>
	<div class="documentCurve Right documentCurveTopRight"></div>
	<div class="clear"></div>
	<div class="pageContainer">
	
	<div id="Header" class="sectionContainer">
		<div id="Logo" class="Left sectionContent">
			<img src="img/header.jpg" width="597" height="95" />
		</div>
	</div>
	<div class="sectionContent">
		<div class="MenuContainer">
			<?php $this->RenderBreadCrumbMenu();?>
			
			<?php
				//display the logout link
				$strUserName	= AuthenticatedUser()->_arrUser['UserName'];
				$strLogoutHref	= Href()->LogoutUser();
				$strLogoutLink	= "<a href='$strLogoutHref' style='color:blue; text-decoration: none;'>logout</a>";
				echo "<span style='float:right;margin-right:1px;'>$strUserName ($strLogoutLink)</span>\n";
			?>
			
		</div>
	</div>
	<h1> <?php echo $this->_strPageName; ?></h1>
	<div class="clear"></div>
		<div id='PageBody'>
			<?php 
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

<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
//common_layout.php
//----------------------------------------------------------------------------//

/**
 * Contains functions for rendering components of templates that are common to all layouts
 */

class CommonLayout
{
	static function OpenPageBody($layoutTemplate, $bolIncludeBreadCrumbMenu=TRUE, $bolIncludeLogout=TRUE, $menuOptions=NULL, $pageName=NULL)
	{
?>		
<div id="Document" class="documentContainer">

	<div class="pageContainer">
	
		<div class="pageContainerLeft"><div class="pageContainerShadow"></div></div>

		<div id="Header" class="sectionContainer">
			<div id="Banner"></div>
			<div class="MenuContainer">
					<?php
						if ($bolIncludeLogout)
						{
?>
<table class="LogoutContainer">
<tr>
<td class="LogoutLeft">&nbsp;</td>
<td class="LogoutMiddle">
<?php

							//display the logout link
							$strUserName	= AuthenticatedUser()->_arrUser['UserName'];
							$strLogoutHref	= Href()->LogoutUser();
							echo "<span class='LoginUserLabel'>Account: </span><span class='LoginUserName'>$strUserName </span><button class='logout' onmouseover='this.tmpClassName=this.className;this.className+=\"Hover\";' onmouseout=\"this.className=this.tmpClassName;\" onclick='document.location=\"$strLogoutHref\"' title='logout'></button>\n";
?>
</td>
<td class="LogoutRight">&nbsp;</td>
</tr>
</table>
<?php
						}
					?>
				<div class='MenuItemsContainer'>
					<div class='MenuOptionsContainer'>
					<?php
					
					if ($menuOptions !== NULL)
					{
						echo "<ul class='CommonHeaderMenu'>";
						
						for ($i = 0, $l = count($menuOptions); $i < $l; $i++)
						{
							$href = Href();

							$url = call_user_func_array(Array($href, $menuOptions[$i]), array());
							
							$normalClass = "CommonHeaderMenuItem";
							$hoverClass = "CommonHeaderMenuItemHover";
							
							echo "<li className='$normalClass' onmouseover='this.className=\"$hoverClass\";' onmouseout='this.className=\"$normalClass\";' onclick='document.location=\"$url\";'>" . $href->objMenuItems->strLabel . "</li>";
							
						}

						echo "</ul>";
					}
					
					?>
					</div>
					<div class='MenuBreadCrumbContainer'>
				<?php 
					if ($bolIncludeBreadCrumbMenu)
					{
						$layoutTemplate->RenderBreadCrumbMenu();
					}
				?>
					</div>
				</div>
				
			</div>
		</div>
		<div class="pageBodyWrapper">
			<h1> <?php echo $pageName == NULL ? $layoutTemplate->GetName() : $pageName; ?></h1>
			<div class="clear"></div>

			<div id='PageBody'>
				<div class="pageContainerLeft" style="border-color: yellow;"><div class="pageContainerShadow"></div></div>

<?php
	}
	
	static function ClosePageBody($layoutTemplate)
	{
?>
				<div class="pageContainerRight" style="border-color: yellow;"><div class="pageContainerShadow"></div></div>
			</div>
		</div>
		<div class="pageContainerRight"><div class="pageContainerShadow"></div></div>
	</div>
</div>
<?php
	}

}

?>

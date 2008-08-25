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
	//------------------------------------------------------------------------//
	// OpenPageBody()
	//------------------------------------------------------------------------//
	/**
	 * OpenPageBody
	 * 
	 * Opens the common page template elements, excluding <html> and <body> 
	 * 
	 * @param Page		$objLayoutTemplate 			Page object being rendered (NULL allowed)
	 * @param boolean	$bolIncludeBreadCrumbMenu 	Whether or not to include a 
	 * 												breadcrumb menu (default=TRUE)
	 * @param boolean	$bolIncludeLogout			Whether or not to include a 
	 * 												logout option (default=TRUE)
	 * @param array		$arrMenuOptions				Menu options to display in menu
	 * 												(array of Menu_Item functions)
	 * 												(default=NULL)
	 * @param String	$strPageName				The page name to be rendered 
	 * 												(optional, but should be provided 
	 * 												if $objLayoutTemplate is null)
	 * 												(default=NULL)
	 * 
	 * @return void
	 * 
	 * @method
	 */
	static function OpenPageBody($objLayoutTemplate, $bolIncludeBreadCrumbMenu=TRUE, $bolIncludeLogout=TRUE, $arrMenuOptions=NULL, $strPageName=NULL)
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

					if ($arrMenuOptions !== NULL && count($arrMenuOptions))
					{
						echo "<ul class='CommonHeaderMenu'>";
						
						for ($i = 0, $l = count($arrMenuOptions); $i < $l; $i++)
						{
							$href = Href();

							$url = call_user_func_array(Array($href, $arrMenuOptions[$i]), array());
							
							$label = $href->GetLastMenuItemLabel();
							if ($label === NULL)
							{
								$label = "&nbsp;";
							}
							
							$normalClass = "CommonHeaderMenuItem";
							$hoverClass = "CommonHeaderMenuItemHover";
							
							echo "<li className='$normalClass' onmouseover='this.className=\"$hoverClass\";' onmouseout='this.className=\"$normalClass\";' onclick='document.location=\"$url\";'>$label</li>";
							
						}

						echo "</ul>";
					}

					?>
					</div>
					<div class='MenuBreadCrumbContainer'>
				<?php 
					if ($bolIncludeBreadCrumbMenu)
					{
						$objLayoutTemplate->RenderBreadCrumbMenu();
					}
				?>
					</div>
				</div>
				
			</div>
		</div>
		<div class="pageBodyWrapper">
			<?php
				$pagePane = $strPageName == NULL ? ($objLayoutTemplate == NULL ? "" : $objLayoutTemplate->GetName()) : $strPageName;
				if ($pagePane)
				{
					echo "<h1>$pagePane</h1>\n";
				}
			?>
			<div class="clear"></div>

			<div id='PageBody'>
				<div class="pageContainerLeft" style="border-color: yellow;"><div class="pageContainerShadow"></div></div>

<?php
	}
	
	
	//------------------------------------------------------------------------//
	// ClosePageBody()
	//------------------------------------------------------------------------//
	/**
	 * ClosePageBody
	 * 
	 * Closes the common page template elements, excluding <body> and <html> 
	 * 
	 * @param $objLayoutTemplate Page object being rendered (null allowed)
	 * 
	 * @return void
	 * 
	 * @method
	 */
	static function ClosePageBody($objLayoutTemplate)
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

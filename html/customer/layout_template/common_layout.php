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
		<link rel="stylesheet" type="text/css" media="screen" href="ie.css" />
		<TABLE height=90 width=100% cellpadding=0 cellspacing=0>
		<TR>
			<TD width=10></TD>
			<TD><img src="logo.php" WIDTH="320" HEIGHT="60" BORDER="0" ALT=""></TD>
		</TR>
		</TABLE>
			<!-- <div id="Banner"></div> -->
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
				echo "<div class='MenuItemsContainer'>";
				// Get version.
				$strShowIE6Code=FALSE;
				if(eregi("MSIE 6.0",$_SERVER['HTTP_USER_AGENT']))
				{
					$strShowIE6Code=TRUE;
				}

				// $strCustomerPrimaryColor
				if(!$strShowIE6Code)
				{
					echo "<div class='MenuOptionsContainer'>\n";
					if ($arrMenuOptions !== NULL && count($arrMenuOptions))
					{
						echo "<ul class='CommonHeaderMenu' style=\"line-height: 33px\">";
						
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
					print "</div>";
				}
				// IE Compatible links table.
				if($strShowIE6Code)
				{
					$mixLinksTable = "
					<table width=100% align=left cellpadding=0 cellspacing=0 bgcolor=#000000 height=33>
					<tr class='LinksTable'>
						<td width=15></td>
						<td>
						<table align=left cellpadding=2 cellspacing=1 bgcolor=#696969 height=33>
						<tr class='LinksTable'>";
						for ($i = 0, $l = count($arrMenuOptions); $i < $l; $i++)
						{
							$href = Href();

							$url = call_user_func_array(Array($href, $arrMenuOptions[$i]), array());
							
							$label = $href->GetLastMenuItemLabel();
							if ($label === NULL)
							{
								$label = "&nbsp;";
							}
							$mixTdWidth = number_format(strlen($label)*7, 2, '.', ''); // the td width will change depending on the word size.
							$mixLinksTable .= "
								<TD width=130 align=center>&nbsp;<a href=\"$url\">$label</a></TD>";
							
						}
						$mixLinksTable .= "
						</tr>
						</table></td>
					</tr>
					</table>";
					echo "$mixLinksTable";
				}
				echo "<div class='MenuBreadCrumbContainer'>";
					
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

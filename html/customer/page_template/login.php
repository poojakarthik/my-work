<?php
	//Carry over all GET and POST information
	//$strPageURL=$_SERVER['HTTP_REFERER'];
	//echo $strPageURL;
	

	$arrScript = explode('.php', $_SERVER['REQUEST_URI'], 2);
	$intLastSlash = strrpos($arrScript[0], "/");
	$strBaseDir = substr($arrScript[0], 0, $intLastSlash + 1);
	if ($_SERVER['HTTPS'])
	{
		$strBaseDir = "https://{$_SERVER['SERVER_NAME']}$strBaseDir";
	}
	else
	{
		$strBaseDir = "http://{$_SERVER['SERVER_NAME']}$strBaseDir";
	}
	
	$strMd5 = md5_file(TEMPLATE_BASE_DIR."css/default.css");
?>

<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Flex Systems Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<base href='<?php echo $strBaseDir ?>'/>
<link rel='stylesheet' type='text/css' href='css.php?v=<?php echo $strMd5; ?>' />
</head>

			<body onload='document.getElementById("VixenUserNameId").focus()'>

<?php

	// Load the common layout for this app
	require_once dirname(__FILE__) . "/../layout_template/common_layout.php";
	
	// Connect to database
	$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);

	// Load Style Configuration based on domain name 
	$arrFetchCustomerStyleConfiguration = $dbConnection->fetchone("SELECT * FROM `CustomerGroup` WHERE flex_url LIKE \"%{$_SERVER['HTTP_HOST']}%\" LIMIT 1");
	DBO()->customer_style_configuration->Array = $arrFetchCustomerStyleConfiguration;

	# I couldnt find the style for the URL you are using?
	if($arrFetchCustomerStyleConfiguration == "")
	{
		$external_name = DEFAULT_CUSTOMER_EXTERNAL_NAME;
	}
	# I could find something?
	if($arrFetchCustomerStyleConfiguration != "")
	{
		$arrFetchCustomerStyleConfiguration = DBO()->customer_style_configuration->Array->Value;
		foreach($arrFetchCustomerStyleConfiguration as $mixKey=>$mixVal)
		{
			$$mixKey = $mixVal;
		}
	}
	// Open the pageBody container
	 // CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"ManagementConsole"), "$external_name Customer System");
	// CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"ResetPassword"), "$external_name Customer System");
	CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console",1=>"ResetPassword",2=>"SetupAccount"), "");
	
	echo "<form method='POST' action='" . $_SERVER['REQUEST_URI'] . "'>";
	
	// Not sure what this is for. Looks like something to do with a PDF download
	if (DBO()->Login->ShowLink->Value)
	{
		echo "<div style='float:left; width:700px; height:50px;'>\n";
		// Render a link back to the console page
		$strConsoleHref = Href()->Console();
		echo "<span id='VixenLinkToConsole' class='DefaultOutputSpan' style='display:none;'>Your PDF should begin downloading soon.  After which, please follow the link back to the console page.<br /><a href='$strConsoleHref' style='color:blue; text-decoration: none;'>Back to console</a></span>\n";
		echo "</div>\n";
	}

	// Get version.
	$strShowIE6Code=FALSE;
	// The ereg function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 6.0.0.
	// if(eregi("MSIE 6.0",$_SERVER['HTTP_USER_AGENT']))
	if(preg_match("/MSIE 6.0/i",$_SERVER['HTTP_USER_AGENT']))
	{
		$strShowIE6Code=TRUE;
	}

	// Render the invisible link to the Management Console which will be located in the top right corner of the window
	$strManagementAppLink = Href()->ManagementConsole();
	echo "<div id='ManagementAppLink' onclick='window.location = \"$strManagementAppLink\"' style='position:fixed;width:2em;height:1em;top:0px;right:0px;cursor:pointer;'></div>";

	// Render the login table
		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>$external_name Customer System - Login</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD align=\"center\">";
?>
		<table id='LoginTable'>
			<tr>
				<td colspan=2>
					<?php
						if (DBO()->Login->Failed->Value)
						{
							echo "<span class='DefaultOutputSpan Default IncorrectLogin'>Incorrect login details.  Please try again.</span>";
						}
						else
						{
							echo "<span class='DefaultOutputSpan Default'><!-- &nbsp; --></span>";
						}
					?>
				</td>
			</tr>
			<tr>
				<td>
					<label for="UserName" style='font-size: 10pt;'>Username:</label>
				</td>
				<td>
					<input type="text" id='VixenUserNameId' name="VixenUserName" class="LoginBox" maxlength="255"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for="PassWord" style='font-size: 10pt;'>Password:</label>
				</td>
				<td>
					<input type="password" name="VixenPassword" class="LoginBox"/>
				</td>
			</tr>
			<tr>
				<td colspan=2>
					<input type="submit" id='VixenSubmit' value="Login &#xBB;" class="Right"/>
					<?php
						if (DBO()->Login->ShowLink->Value)
						{
							// display the link back to the console, when the submit button has been clicked
							$strDisplayLink = 	"function(){setTimeout(function(){var elmLink = document.getElementById('VixenLinkToConsole');" .
												"elmLink.style.display = 'inline';}, 1000);}";
							echo "<script type='text/javascript'>document.getElementById('VixenSubmit').onclick = $strDisplayLink</script>";
						}
					?>
				</td>
			</tr>
		</table>


	<?php
				print "
			</TD>
		</TR>
		</TABLE>
		<br/>";
	// Hide $_POST values in the form for subsequent use...
	foreach ($_POST as $strKey=>$strValue)
	{
		if (($strKey != 'VixenUserName') && ($strKey != 'VixenPassword'))
		{
			echo "<input type='hidden' name='$strKey' value='$strValue' />";
		}
	}
	
	?>
	</form>
<?php

	// Close the pageBody
	CommonLayout::ClosePageBody(NULL);
	
?>

</body>
</html>

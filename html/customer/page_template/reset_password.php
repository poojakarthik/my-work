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

			<!-- <body onload='document.getElementById("VixenUserNameId").focus()'> -->
			<body>

<?php

	// Load the common layout for this app
	require_once dirname(__FILE__) . "/../layout_template/common_layout.php";
	
	// CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console"), "$external_name Customer System");
	CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console",1=>"ResetPassword",2=>"SetupAccount"), "");
	
	echo "<form method='POST' action='" . $_SERVER['REQUEST_URI'] . "'>";
	
	// Render the reset password page
	if(!array_key_exists('mixUserName', $_POST))
	{
		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>Customer System - Reset Password</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD align=\"center\">";
		print "
		<table id='LoginTable'>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Email:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixUserName\" class=\"LoginBox\" maxlength=\"255\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'></label>
				</td>
				<td>
					<INPUT TYPE=\"submit\" VALUE=\"Send Password >>\">
				</td>
			</tr>
		</table>";

			print "
			</TD>
		</TR>
		</TABLE>";

	print "
	</form>";
	}
	else{

		# I could find something?
		if(!DBO()->Fail)
		{
		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>$external_name Customer System - Reset Password Success</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>";
			print "A new password has been issued.<br/>
			Please allow a few minutes for the e-mail to arrive.";
			echo "<br /><br />\n";
			echo "<a href='$strLoginHref' ><span>Customer System Login</span></a>\n";
			print "
			</TD>
		</TR>
		</TABLE>
		<br/>";
		}
		if(DBO()->Fail)
		{
		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>$external_name Customer System - Reset Password Failure</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>";
			print "The email entered does not exist.";
			echo "<br /><br />\n";
			echo "<a href='" . Href()->ResetPassword() . "' ><span>Please Try Again</span></a>\n";
			print "
			</TD>
		</TR>
		</TABLE>
		<br/>";
		}

	}
	// Close the pageBody
	CommonLayout::ClosePageBody(NULL);
	
?>

</body>
</html>

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
	
	$strMd5 = md5_file(TEMPLATE_BASE_DIR."default.css");
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
	
	// CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console"), "$ExternalName Customer System");
	CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console",1=>"ResetPassword",2=>"ResendUsername"), "");
	
	echo "<form method='POST' action='" . $_SERVER['REQUEST_URI'] . "'>";
	
	// Render the reset password page
	if(!array_key_exists('mixFirstName', $_POST))
	{
		print "
		<br/><br/>
		<TABLE align=center class=login-table-style-main-title>
		<TR>
			<TD>Customer System - Resend Username</TD>
		</TR>
		</TABLE>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD align=\"center\">";
		print "
		<table id='LoginTable'>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>First Name:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixFirstName\" class=\"LoginBox\" maxlength=\"21\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Last Name:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixLastName\" class=\"LoginBox\" maxlength=\"21\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Email:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixEmail\" class=\"LoginBox\" maxlength=\"21\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'></label>
				</td>
				<td>
					<INPUT TYPE=\"submit\" VALUE=\"Send Username >>\">
				</td>
			</tr>
		</table>";

			print "
			</TD>
		</TR>
		</TABLE>
		<A HREF=\"./flex.php/Console/Password/\">Click here to retrieve your password</A><br/>";

	print "
	</form>";
	}
	else{

		# I could find something?
		if(!DBO()->Fail)
		{
		print "
		<br/><br/>
		<TABLE align=center class=login-table-style-main-title>
		<TR>
			<TD>Customer System - Resend Username Success</TD>
		</TR>
		</TABLE>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>";
			print "Your username has been sent.<br/>
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
		<TABLE align=center class=login-table-style-main-title>
		<TR>
			<TD>Customer System - Resend Username Failure</TD>
		</TR>
		</TABLE>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>";
			print "The details entered were invalid.";
			echo "<br /><br />\n";
			echo "<a href='" . Href()->ResendUsername() . "' ><span>Please Try Again</span></a>\n";
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

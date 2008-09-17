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
	
	// CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console"), "$ExternalName Customer System");
	CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console",1=>"ResetPassword",2=>"ResendUsername",3=>"SetupAccount"), "");
	
	echo "<form method='POST' action='" . $_SERVER['REQUEST_URI'] . "'>";
	
	// Render the reset password page
	if(!array_key_exists('mixFirstName', $_POST))
	{
		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>Customer System - Setup New Account</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD align=\"center\">";
		print "
		<table id='LoginTable'>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Account Number:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixAccountNumber\" class=\"LoginBox\" maxlength=\"21\"/>
				</td>
			</tr>
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
				<td valign=\"bottom\" style=\"padding-bottom: 5px;\">
					<label for=\"UserName\" style='font-size: 10pt;'>Birth date:</label>
				</td>
				<td>
				
				<TABLE>
				<TR>
					<TD>day</TD>
					<TD>month</TD>
					<TD>year</TD>
				</TR>
				<TR>";
					print "<TD><select name=\"mixBirthDay\">";
					for($i=1; $i<32; $i++)
					{
						$show_i=$i;
						if(strlen($show_i)=="1")
						{
							$show_i = "0$show_i";
						}
						print "<option name=\"$show_i\">$show_i</option>";
					}
					print "</select></TD>";
					print "<TD><select name=\"mixBirthMonth\">";
					for($i=1; $i<13; $i++)
					{
						$show_i=$i;
						if(strlen($show_i)=="1")
						{
							$show_i = "0$show_i";
						}
						print "<option name=\"$show_i\">$show_i</option>";
					}
					print "</select></TD>";
					print "<TD><select name=\"mixBirthYear\">";
					$oldest_person = date("Y")-100;
					for($i=$oldest_person; $i<date("Y"); $i++)
					{
						$show_i=$i;
						if(strlen($show_i)=="1")
						{
							$show_i = "0$show_i";
						}
						print "<option name=\"$show_i\">$show_i</option>";
					}
					print "</select></TD>";
					print "
				</TR>
				</TABLE>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>ABN:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixABN\" class=\"LoginBox\" maxlength=\"21\" size=\"30\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Type a new password:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixNewPass1\" class=\"LoginBox\" maxlength=\"21\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Repeat new password:</label>
				</td>
				<td>
					<input type=\"text\" name=\"mixNewPass2\" class=\"LoginBox\" maxlength=\"21\"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'></label>
				</td>
				<td>
					<INPUT TYPE=\"submit\" VALUE=\"Setup Account >>\">
				</td>
			</tr>
		</table>";

			print "
			</TD>
		</TR>
		</TABLE>
		If you have already activated your account, <A HREF=\"" . Href()->ResendPassword() . "\">click here to retrieve your password</A><br/>";

	print "
	</form>";
	}
	else{

		# I could find something?
		if(!DBO()->Fail)
		{
		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>Customer System - Please Confirm Details</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>
			<input type=\"hidden\" name=\"mixAccountNumber\" value=\"$_POST[mixAccountNumber]\">
			<input type=\"hidden\" name=\"mixFirstName\" value=\"$_POST[mixFirstName]\">
			<input type=\"hidden\" name=\"mixLastName\" value=\"$_POST[mixLastName]\">
			<input type=\"hidden\" name=\"mixBirthDay\" value=\"$_POST[mixBirthDay]\">
			<input type=\"hidden\" name=\"mixBirthMonth\" value=\"$_POST[mixBirthMonth]\">
			<input type=\"hidden\" name=\"mixBirthYear\" value=\"$_POST[mixBirthYear]\">
			<input type=\"hidden\" name=\"mixABN\" value=\"$_POST[mixABN]\">
			<input type=\"hidden\" name=\"mixNewPass1\" value=\"$_POST[mixNewPass1]\">
			<input type=\"hidden\" name=\"mixNewPass2\" value=\"$_POST[mixNewPass2]\">
			The details entered appear valid, please confirm your email address.<br/><br/>";
			echo "<table id='LoginTable'>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Email:</label>
				</td>
				<td><INPUT TYPE=\"text\" NAME=\"mixEmail\" VALUE=\"" . DBO()->Contact->Email->Value . "\" size=\"35\"></td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Account Number:</label>
				</td>
				<td>" . DBO()->Contact->Account->Value . "</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>First Name:</label>
				</td>
				<td>" . DBO()->Contact->FirstName->Value . "</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>Last Name:</label>
				</td>
				<td>" . DBO()->Contact->LastName->Value . "</td>
			</tr>
			<tr>
				<td valign=\"bottom\" style=\"padding-bottom: 5px;\">
					<label for=\"UserName\" style='font-size: 10pt;'>Birth date:</label>
				</td>
				<td>" . DBO()->Contact->DOB->Value . "</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>ABN:</label>
				</td>
				<td>" . DBO()->Account->ABN->Value . "</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'>New password:</label>
				</td>
				<td>Hidden</td>
			</tr>
			<tr>
				<td>
					<label for=\"UserName\" style='font-size: 10pt;'></label>
				</td>
				<td>
					<INPUT TYPE=\"submit\" VALUE=\"Confirm Changes >>\">
				</td>
			</tr>
		</table>\n";
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
		<center><div class='customer-standard-table-style-menu-options-login'>Customer System - Setup New Account Failure</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD>";
			print "Your request has failed due to one or more errors, details below:";
			echo "<br /><br />" . DBO()->ErrorMessage . "<br />\n";
			echo "<a href=\"javascript: window.history.go(-1)\"><span>Please Try Again</span></a>\n";
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

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
	
	// CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console"), "$ExternalName Customer System");
	CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console",1=>"ResetPassword",2=>"ResendUsername",3=>"SetupAccount"), "");
	
	echo "<form method='POST' action='" . $_SERVER['REQUEST_URI'] . "'>";

		print "
		<br/><br/>
		<center><div class='customer-standard-table-style-menu-options-login'>Customer System - Setup New Account Completed</div></center>
		<TABLE align=center class=login-table-style-main>
		<TR VALIGN=\"TOP\">
			<TD align=center>";
			echo "<br />Thank you, password has been set, you can now access the system.<br /><br />\n";
			echo "<a href=\"" . Href()->Console() . "\"><span>Please click here to login</span></a>\n";
			print "
			</TD>
		</TR>
		</TABLE>
		<br/>";
	// Close the pageBody
	CommonLayout::ClosePageBody(NULL);
	
?>

</body>
</html>

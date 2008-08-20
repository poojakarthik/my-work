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
	

?>

<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Flex Customer Management System</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<base href='<?php echo $strBaseDir ?>'/>
</head>

<style type="text/css" media="screen">
				
				body {margin: 0; font-family: "Nimbus Sans L", "Tahoma"; background-color: #fff; }
			    #topContainer { padding-top: 6em; background: #FFF; }
			    #logoImage {width:250px; height:91px; float: left; }
			    #loginContainer{padding: 0px 0px 18em 210px;}
			    #loginForm {float:left;height: 150px;width:500px; border-left: 1px solid #CCCCCC; padding:0px 8px 8px 30px;text-align:left;}
			    h3 { margin: 0; }
			    
			    div.Seperator { height: 5px; }
			    .Right { float: right; }
			    
			    .LoginBox { width: 200px; border: solid 1px #666666; padding: 3px; font-family: "Nimbus Sans L", "Tahoma";}
			    
			    h1 { font-size: 14pt; color: #006599; font-family: "Nimbus Sans L", "Tahoma"; }
			    
			    label { font-size: 10pt; }
			    
			    .MsgError {
					border:              solid 1px #CC0000;
					background-color:    #f2dbdb;
					
					background-image:    url('img/template/MsgError.png');
					background-repeat:   no-repeat;
					background-position: left;
					
					padding-top:         5px;
					padding-left:        40px;
					padding-right:       5px;
					padding-bottom:      5px;
					
					height:              25px;
					
					font-size: 10pt;
					
					line-height:         25px;
					
					margin-bottom:       10px;
			    }
			    
			    td.clientInterfaceLink
			    {
			    	font-size: smaller;
			    	vertical-align: bottom;
			    }
			    
			    a.clientInterfaceLink
			    {
			    	font-size: smaller;
			    	text-decoration: underline;
			    	color:	#000;
			    	vertical-align: bottom;
			    }
			     
			</style>
			
			<body onload='document.getElementById("VixenUserNameId").focus()'>
			<div id="topContainer">
				<div id="loginContainer">
				<div id="logoImage">
				<img src="img/login/yellow_billing_logo.png"/>
				</div>
				<div id="loginForm">
				<h1>Flex Customer Management System</h1>
				<div class="Seperator"/>
				<?php 
				echo "<form method='POST' action='" . $_SERVER['REQUEST_URI'] . "'>";
				?>
				
				<table>
					<tr>
					<td>
					<label for="UserName">Username:</label>
					</td>
					<td>
					<input type="text" id='VixenUserNameId' name="VixenUserName" class="LoginBox" maxlength="21"/>
					</td>
					</tr>
					<tr>
					<td>
					<label for="PassWord">Password:</label>
					</td>
					<td>
					<input type="password" name="VixenPassword" class="LoginBox"/>
					</td>
					</tr>
					<tr>
						<td/>
						<td>
							<input type="submit" value="Continue &#xBB;" class="Right"/>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="clientInterfaceLink"><a class="clientInterfaceLink" href="../customer/">Customer System</a></td>
					</tr>
				</table>
				<?php
				
				foreach ($_POST as $strKey=>$strValue)
				{
					if (($strKey != 'VixenUserName') && ($strKey != 'VixenPassword'))
					{
						echo "<input type='hidden' name='$strKey' value='$strValue' />";
					}
				}
				
				?>
				</form>
				</div>
				<div class="Clear"/></div>
				</div>

			
			</body>
		</html>

<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			  <title>Vixen Internal Systems Login</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			</head>
			<style type="text/css" media="screen">
				
				body {margin: 0; font-family: "Nimbus Sans L", "Tahoma"; }
			    #topContainer { padding-top: 3em; background: #FFF; }
			    #logoImage {width:300px; height:91px; float: left; }
			    #loginContainer{padding: 0px 0px 18em 70px;}
			    #loginForm {float:left;height: 150px;width:500px; border-left: 1px solid #CCCCCC; padding:0px 8px 8px 30px;text-align:left;}
			    h3 { margin: 0; }
			    
			    div.Seperator { height: 5px; }
			    .Right { float: right; }
			    
			    .LoginBox { width: 200px; border: solid 1px #666666; padding: 3px; font-family: "Nimbus Sans L", "Tahoma"; height: 25px; }
			    
			    h1 { font-size: 14pt; color: #006599; font-family: "Nimbus Sans L", "Tahoma"; }
			    
			    label { font-size: 10pt; }
			    
			    .MsgError {
					border:              solid 1px #CC0000;
					background-color:    #f2dbdb;
					
					background-image:    url('img/template/MsgError.png');
					background-repeat:   no-repeat;
					background-position: top left;
					
					padding-top:         5px;
					padding-left:        40px;
					padding-right:       5px;
					padding-bottom:      5px;
					
					font-size: 10pt;
					
					line-height:         25px;
					
					margin-bottom:       10px;
			    }
			    
			    
			</style>
			<body>
				<div id="topContainer">
					<div id="loginContainer">
						<div id="logoImage">
							<img src="img/login/TCB_Logo.png" width="300" height="91" />
						</div>
						<div id="loginForm">
							<h1>Vixen Internal System</h1>
							
							<div class="Seperator"></div>
							
							<div class="MsgError">
								Your session has expired or you have been logged out.<br/>
								Please close this window and log in again.
							</div>
						</div>
						
						<div class="Clear"></div>
					</div>
				</div>
			</body>
		</html>	
	</xsl:template>
</xsl:stylesheet>

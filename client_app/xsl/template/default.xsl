<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="iso-8859-1" indent="yes" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	
	<xsl:template match="/">
		<html>
			<head>
				<title>Welcome to TelcoBlue.com.au</title>
				<link rel="stylesheet" type="text/css" href="/client_app/css/default.css" media="screen" />
			</head>
			<body>
				<div id="Document" class="documentContainer">
					<div class="documentCurve Left documentCurveTopLeft"></div>
					<div class="documentCurve Right documentCurveTopRight"></div>
					<div class="clear"></div>
					
					<div class="pageContainer">
						<div id="Header" class="sectionContainer">
							<div id="Logo" class="Left sectionContent">
								<a href="/"><img src="img/sky.jpg" width="250" height="100" /></a>
							</div>
							
							<div id="Links" class="Right sectionContent">
								.
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
						
						<div id="Dash" class="sectionContainer">
							<div id="Crumbs" class="Left sectionContent">
								Telco<span class="Blue">Blue</span> - You know who!
							</div>
							
							<div id="Login" class="Right sectionContent">
								<xsl:choose>
									<xsl:when test="/Response/Authentication/AuthenticatedContact">
										Welcome, 
										<xsl:value-of select="/Response/Authentication/AuthenticatedContact/FirstName" />
										(<a href="console.php">Console</a>)
									</xsl:when>
									<xsl:otherwise>
										Welcome, Guest (<a href="login.php">Login</a>)
									</xsl:otherwise>
								</xsl:choose>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
						
						<div id="Page" class="sectionContainer">
							<div id="Navigation" class="Left sectionContent">
								<ul>
									<li><a href="/">Homepage</a></li>
									<li><a href="/">Mobile Services</a></li>
									<li><a href="/">Landline Services</a></li>
									<li><a href="/">ADSL Services</a></li>
									<li><a href="/">About TelcoBlue</a></li>
									<li><a href="/">Opertunities</a></li>
									<li><a href="/">Company News</a></li>
								</ul>
								
								<div id="Console" class="Left sectionContent">
									<xsl:choose>
										<xsl:when test="/Response/Authentication/AuthenticatedContact">
											<p class="sectionHeading">My Account</p>
											<ul>
												<li><a href="console.php">My Console</a></li>
												<xsl:if test="/Response/Authentication/AuthenticatedContact/CustomerContact = 1">
													<li><a href="contacts.php">All Contact Details</a></li>
												</xsl:if>
												<li><a href="contact.php">Edit My Details</a></li>
												<xsl:if test="/Response/Authentication/AuthenticatedContact/CustomerContact = 1">
													<li><a href="accounts.php">View All Accounts</a></li>
												</xsl:if>
												<li><a href="account.php">My Main Account</a></li>
												<li><a href="logout.php">Account Logout</a></li>
											</ul>
										</xsl:when>
										<xsl:otherwise>
											<p class="sectionHeading">Customer Login</p>
											<form method="post" action="login.php">
												<table border="0" cellpadding="0" cellspacing="0">
													<tr>
														<td>Username:</td>
													</tr>
													<tr>
														<td><input type="text" name="UserName" class="text" /></td>
													</tr>
													<tr>
														<td>Password:</td>
													</tr>
													<tr>
														<td><input type="password" name="PassWord" class="text" /></td>
													</tr>
													<tr>
														<td><input type="submit" value="Login" class="button" /></td>
													</tr>
												</table>
											</form>
										</xsl:otherwise>
									</xsl:choose>
								</div>
							</div>
							<div id="Information" class="Left sectionContent">
								<xsl:call-template name="Content" />
							</div>
							<div class="clear"></div>
						</div>
					</div>
					
					<div class="clear"></div>
					
					<div class="documentCurve Left documentCurveBottomLeft"></div>
					<div class="documentCurve Right documentCurveBottomRight"></div>
					<div class="clear"></div>
				</div>
				
				<div class="documentContainer">
					<div id="Footer">
						<div class="sectionContent">
							<ul>
								<li><a href="/">About TelcoBlue</a></li>
								<li><a href="/">My Account</a></li>
							</ul>
						</div>
						<div class="Right sectionContent">
							Copyright TelcoBlue.com.au, 2006-2007
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

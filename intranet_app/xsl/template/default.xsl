<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="iso-8859-1" indent="yes" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	
	<xsl:template match="/">
		<html>
			<head>
				<title>Employee Intranet System</title>
				<link rel="stylesheet" type="text/css" href="css/default.css" />
				<script language="javascript" src="js/init.js"></script>
			</head>
			<body>
				<div id="Header" class="sectionContainer">
					<div class="sectionContent">
						<div class="Left">
							TelcoBlue Internal Management System
						</div>
						
						<div class="Right">
							Version 7.01
							
							<xsl:if test="/Response/SystemDebug">
								<script language="javascript" src="js/dimmingdiv.js"></script>
								<div id="Debug_Button">
									<a href="javascript:displayDebugWindow()">
										<img src="img/template/debug.png" border="0" />
									</a>
								</div>
								
								<div id="windowcontent" style="display: none">
									<div id="windowcontentmain">
										<textarea readonly="readonly"><xsl:value-of select="/Response/SystemDebug" disable-output-escaping="yes" /></textarea>
									</div>
								</div>
							</xsl:if>
						</div>
						
						<div class="Clear"></div>
					</div>
					<div class="Clear"></div>
				</div>
				<div id="Controller" class="sectionContainer">
					<table border="0" width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="top" width="300">
								<div id="Navigation" class="Left sectionContent">
									<ul id="Navigation-Root">
										<li>
											Accounts
											<ul>
												<li><a href="account_add.php">Create an Account</a></li>
												<li><a href="account_list.php">Find an Account</a></li>
												<li>Recently Viewed
													<ul>
														<xsl:for-each select="/Response/Authentication/AuthenticatedEmployee/Session/AuditList/Accounts/Account">
															<li>
																<a>
																	<xsl:attribute name="href">
																		<xsl:text>account_view.php?Id=</xsl:text>
																		<xsl:value-of select="./Id" />
																	</xsl:attribute>
																	<xsl:value-of select="./BusinessName" disable-output-escaping="yes" />
																</a>
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</ul>
										</li>
										<li>
											Contacts
											<ul>
												<li><a href="contact_list.php">Find a Contact</a></li>
												<li>Recently Viewed
													<ul>
														<xsl:for-each select="/Response/Authentication/AuthenticatedEmployee/AuditList/AuditItem/Contact">
															<li>
																<a>
																	<xsl:attribute name="href">
																		<xsl:text>contact_view.php?Id=</xsl:text>
																		<xsl:value-of select="./Id" />
																	</xsl:attribute>
																	<xsl:value-of select="./LastName" disable-output-escaping="yes" />
																	<xsl:text>, </xsl:text>
																	<xsl:value-of select="./FirstName" disable-output-escaping="yes" />
																</a>
															</li>
														</xsl:for-each>
													</ul>
												</li>
											</ul>
										</li>
										<li>
											Rates
											<ul>
												<li><a href="rates_plan_list.php">List Rate Plans</a></li>
												<li><a href="rates_plan_add.php">Create Rate Plan</a></li>
												<li><a href="rates_group_list.php">List Rate Groups</a></li>
												<li><a href="rates_group_add.php">Create Rate Group</a></li>
												<li><a href="rates_rate_list.php">List Rates</a></li>
												<li><a href="rates_rate_add.php">Create Rate</a></li>
											</ul>
										</li>
										<li>
											Charges
											<ul>
												<li><a href="charges_recurringcharge_add.php">Add Recurring Charge Type</a></li>
												<li><a href="charges_recurringcharge_list.php">List Recurring Charge Types</a></li>
												<li><a href="charges_charge_add.php">Add Single Charge Type</a></li>
												<li><a href="charges_charge_list.php">List Single Charge Types</a></li>
											</ul>
										</li>
										<li>
											My Account
											<ul>
												<li><a href="logout.php">Logout</a></li>
											</ul>
										</li>
									</ul>
								</div>
							</td>
							<td valign="top">
								<div id="Content" class="Left sectionContent">
									<xsl:call-template name="Content" />
								</div>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				<div class="Clear"></div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

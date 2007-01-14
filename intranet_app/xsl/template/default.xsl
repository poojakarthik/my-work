<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../lib/date-time.xsl" />
	
	<xsl:output method="xml" version="1.0" encoding="iso-8859-1" indent="yes" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	
	<xsl:template match="/">
		<html>
			<head>
				<title>Employee Intranet System</title>
				<link rel="stylesheet" type="text/css" href="css/default.css" />
				<script language="javascript" src="js/init.js"></script>
				
				<!-- Popup Control -->
				<script language="javascript" src="js/lightbox/jquery-latest.js"></script>
				<script language="javascript" src="js/lightbox/dimensions.js"></script>
				<script language="javascript" src="js/lightbox/jquery-modalContent.js"></script>
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
								<div id="Debug_Button">
									<a href="#" onclick="$('#modalContent-systemDebug').modalContent(null, 'show', 'slow');">
										<img src="img/template/debug.png" border="0" />
									</a>
								</div>
								
								<div id="modalContent-systemDebug">
									<div class="modalContainer">

										<div class="modalContent">
											<pre>
												<xsl:value-of select="/Response/SystemDebug" />
											</pre>
										</div>
										<div class="modalTitle">
											<div class="modalIcon Left">
												<img src="img/template/lady-debug.png" />
											</div>
											<div class="modalLabel Left">
												<strong>System Debug</strong><br />
												A trace of information passing through the system
											</div>
											<div class="modalClose Right">
												<img src="img/template/closelabel.gif" class="close" />
											</div>
											<div class="Clear"></div>
										</div>
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
							<td valign="top" width="100">
								<div id="Navigation" class="Left sectionContent">
									<!--
											<ul id="Navigation-Root">
												<li>
													Accounts
													<ul>
														<li><a href="account_add.php">Create an Account</a></li>
														<li><a href="account_list.php">Find an Account</a></li>
														<li>Recently Viewed
															<ul>
																<xsl:for-each select="/Response/Authentication/AuthenticatedEmployee/Session/AuditList/Accounts/Account">
																	<xsl:sort order="descending" />
																	<li>
																		<a>
																			<xsl:attribute name="href">
																				<xsl:text>account_view.php?Id=</xsl:text>
																				<xsl:value-of select="./Id" />
																			</xsl:attribute>
																			<xsl:value-of select="./BusinessName" />
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
																<xsl:for-each select="/Response/Authentication/AuthenticatedEmployee/Session/AuditList/Contacts/Contact">
																	<xsl:sort order="descending" />
																	<li>
																		<a>
																			<xsl:attribute name="href">
																				<xsl:text>contact_view.php?Id=</xsl:text>
																				<xsl:value-of select="./Id" />
																			</xsl:attribute>
																			<xsl:value-of select="./FirstName" />
																			<xsl:text> </xsl:text>
																			<xsl:value-of select="./LastName" />
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
														<li><a href="charges_approve.php">Approve Unbilled Charges</a></li>
													</ul>
												</li>
												<li>
													My Account
													<ul>
														<li><a href="logout.php">Logout</a></li>
													</ul>
												</li>
											</ul>
										-->
										
									<table border="0" cellpadding="5" cellspacing="0">
										<tr>
											<td>
												<a href="account_add.php">
													<img src="img/template/contact_add.png" title="Add Customer" class="MenuIcon" />
												</a>
											</td>
										</tr>
										<tr>
											<td>
												<a href="contact_list.php">
													<img src="img/template/contact_retrieve.png" title="Find Customer" class="MenuIcon" />
												</a>
											</td>
										</tr>
										<tr>
											<td>
												<a href="#" onclick="$('#modalContent-recentCustomers').modalContent(null, 'show', 'slow');">
													<img src="img/template/history.png" title="Recent Customers" class="MenuIcon" />
												</a>
											</td>
										</tr>
										<tr>
											<td>
												<a href="rates_plan_list.php">
													<img src="img/template/plans.png" title="View Plan Details" class="MenuIcon" />
												</a>
											</td>
										</tr>
										<tr>
											<td>
												<a href="logout.php">
													<img src="img/template/logout.png" title="Account Logout" class="MenuIcon" />
												</a>
											</td>
										</tr>
									</table>
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

				<div id="modalContent-recentCustomers">
					<div class="modalContainer">
						<div class="modalContent">
							<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
								<thead>
									<tr class="First">
										<th>Primary Account Name</th>
										<th>First Name</th>
										<th>Last Name</th>
									</tr>
								</thead>
								<tbody>
									<xsl:for-each select="/Response/Authentication/AuthenticatedEmployee/Session/AuditList/Contacts/Contact">
										<xsl:sort order="descending" />
										
										<tr>
											<xsl:attribute name="class">
												<xsl:choose>
													<xsl:when test="position () mod 2 = 1">
														<xsl:text>Odd</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>Even</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											<xsl:attribute name="onclick">
												<xsl:text>window.location='contact_view.php?Id=</xsl:text>
												<xsl:value-of select="./Id" />
												<xsl:text>'</xsl:text>
											</xsl:attribute>
											
											<td width="50%">
												<xsl:choose>
													<xsl:when test="./PrimaryAccount/Account/BusinessName != ''">
														<xsl:value-of select="./PrimaryAccount/Account/BusinessName" />
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of select="./PrimaryAccount/Account/TradingName" />
													</xsl:otherwise>
												</xsl:choose>
											</td>
											<td width="25%"><xsl:value-of select="./FirstName" /></td>
											<td width="25%"><xsl:value-of select="./LastName" /></td>
										</tr>
									</xsl:for-each>
								</tbody>
							</table>
						</div>
						<div class="modalTitle">
							<div class="modalIcon Left">
								<img src="img/template/history.png" />
							</div>
							<div class="modalLabel Left">
								<strong>Recent Customers</strong><br />
								Your 5 most recently verified customers
							</div>
							<div class="modalClose Right">
								<img src="img/template/closelabel.gif" class="close" />
							</div>
							<div class="Clear"></div>
						</div>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

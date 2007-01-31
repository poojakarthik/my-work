<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../lib/date-time.xsl" />

	<xsl:output
	method="xml" 
	indent="yes" 
	version="1.0" 
	encoding="iso-8859-1" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" 
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />

	<xsl:template match="/">
		<html>
			<head>
					<title>viXen : Employee Intranet System</title>
					<link rel="stylesheet" type="text/css" href="css/default.css" />
					<script language="javascript" src="js/init.js"></script>
					
					<!-- Load Javascript : Popup Control -->
					<script language="javascript" src="js/lightbox/jquery-latest.js"></script>
					<script language="javascript" src="js/lightbox/dimensions.js"></script>
					<script language="javascript" src="js/lightbox/jquery-modalContent.js"></script>
			</head>
			<body>
				<div class="Logo">
					<img src="img/template/vixen_logo.png" border="0" />
				</div>
				<div id="Header" class="sectionContainer">
						<span class="LogoSpacer"></span>
							<div class="sectionContent">
								<div class="Left">
									TelcoBlue Internal Management System
								</div>
								<div class="Right">
									Version 7.01
									
									<!-- Report Bug Button & PopUp -->
									<div class="Menu_Button">
										<a href="#" onclick="return ModalDisplay ('#modalContent-ReportBug')">
											<img src="img/template/bug.png" border="0" alt="Report Bug" title="Report Bug" />
										</a>
									</div>
									
									<div id="modalContent-ReportBug">
										<div class="modalContainer">
											<div class="modalContent">
												<form method="post" action="bug_report.php" onsubmit="return BugSubmit(this)">
													<input type="hidden" name="SerialisedGET">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/DataSerialised/GET" />
														</xsl:attribute>
													</input>
													<input type="hidden" name="SerialisedPOST">
														<xsl:attribute name="value">
															<xsl:text></xsl:text>
															<xsl:value-of select="/Response/DataSerialised/POST" />
														</xsl:attribute>
													</input>
													<table border="0" cellpadding="0" cellspacing="0">
														<tr>
															<td valign="top" width="100%">
																<h1>Bug Report</h1>
																Please describe the problem that occurred :
																
																<textarea name="Comment" style="width: 725px; height: 225px;" class="input-summary-note" />
																
																<div class="Right">
																	<input type="submit" value="Report Bug &#0187;" class="input-submit" />
																</div>
															</td>
														</tr>
													</table>
												</form>
											</div>
											<div class="modalTitle">
											<div class="modalIcon Left">
												<img src="img/template/lady-debug.png" />
											</div>
											<div class="modalLabel Left">
												<strong>Report a System Bug</strong><br />
												Let us know when something isn't working the way you expect
											</div>
											<div class="modalClose Right">
												<img src="img/template/closelabel.gif" class="close" />
											</div>
											<div class="Clear"></div>
										</div>
									</div>
								</div>
								
								<!-- System Debug Button & PopUp -->
								<xsl:if test="/Response/SystemDebug">
									<span class="Debug_Button">
										<a href="#" onclick="return ModalDisplay ('#modalContent-systemDebug')">
											<img src="img/template/debug.png" border="0" />
										</a>
									</span>
									<div id="modalContent-systemDebug">
										<div class="modalContainer">
											<div class="modalContent">
												<pre><xsl:value-of select="/Response/SystemDebug" /></pre>
											</div>
											<div class="modalTitle">
												<div class="modalIcon Left">
													<img src="img/template/lady-debug.png" />
												</div>
												<div class="modalLabel Left">
													<strong>System Debug</strong><br />
													System Debug Information
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
					<div class="Clear"></div>
					<div class="Seperator"></div>
					
					<div id="Content">
						<table border="0" width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td valign="top" width="75" nowrap="nowrap">
									<!-- Navigation Menu -->
									<div id="Navigation" class="Left sectionContent Navigation">
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
															
										<table border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td>
													<a href="console.php">
														<img src="img/template/home.png" title="Employee Console" class="MenuIcon" />
													</a>
												</td>
											</tr>
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
													<a href="#" onclick="return ModalDisplay ('#modalContent-recentCustomers')">
														<img src="img/template/history.png" title="Recent Customers" class="MenuIcon" />
													</a>
												</td>
											</tr>
											<tr>
												<td>
													<a href="rates_plan_list.php">
														<img src="img/template/plans.png" title="View Available Plans" class="MenuIcon" />
													</a>
												</td>
											</tr>
											<tr>
												<td>
													<a href="#">
														<xsl:attribute name="onclick">
															<xsl:text>return Logout()</xsl:text>
														</xsl:attribute>
														<img src="img/template/logout.png" title="Logout" class="MenuIcon" />
													</a>
												</td>
											</tr>
										</table>
									</div>
								</td>
								<td valign="top">
								
								<!-- Popup Window Controller -->
								<div id="modalContent-Popup">
									<div class="modalContainer">
										<div class="modalContent" id="Modal-Popup-Content"></div>
										<div class="modalTitle">
											<div class="modalIcon Left">
												<img id="Modal-Popup-Icon" src="" />
											</div>
											<div class="modalLabel Left">
												<strong id="Modal-Popup-Title"></strong><br />
												<span id="Modal-Popup-Summary"></span>
											</div>
											<div class="modalClose Right">
												<img src="img/template/closelabel.gif" class="close" />
											</div>
											<div class="Clear"></div>
										</div>
									</div>
								</div>
								
								<!-- Top Menu -->
								<ul id="QuickList" class="Right">
									<xsl:for-each select="/Response/QuickList/*">
										<li>
											<a>
												<xsl:choose>
													<xsl:when test="name(.) = 'Account'">
														<xsl:attribute name="href">
																<xsl:text>account_view.php?Id=</xsl:text>
																<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:text>View Account</xsl:text>
													</xsl:when>
													
													<xsl:when test="name(.) = 'Invoice'">
														<xsl:attribute name="href">
																<xsl:text>invoice_view.php?Invoice=</xsl:text>
																<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:text>View Invoice</xsl:text>
													</xsl:when>
													
													<xsl:when test="name(.) = 'Contact'">
														<xsl:attribute name="href">
																<xsl:text>contact_view.php?Id=</xsl:text>
																<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:text>View Contact</xsl:text>
													</xsl:when>
													
													<xsl:when test="name(.) = 'Service'">
														<xsl:attribute name="href">
																<xsl:text>service_view.php?Id=</xsl:text>
																<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:text>View Service</xsl:text>
													</xsl:when>
												</xsl:choose>
											</a>
										</li>
									</xsl:for-each>
								</ul>
								
								<xsl:call-template name="Content" />
							</td>
						</tr>
					</table>
				</div>

				<!-- Recent Customers PopUp -->
				<div id="modalContent-recentCustomers">
					<div class="modalContainer">
						<div class="modalContent">
							<h1>Recent Customers</h1>
							<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
								<thead>
									<tr class="First">
										<th width="30">#</th>
										<th>Primary Account Name</th>
										<th>First Name</th>
										<th>Last Name</th>
									</tr>
								</thead>
								<tbody>
									<xsl:for-each select="/Response/Authentication/AuthenticatedEmployee/Session/AuditList/Contacts/Contact">
										<xsl:sort select="position()" order="descending" />
										
										<!-- TODO!bash! [  DONE  ]		Alert msg when this list is empty, just like there is every other place -->
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
												<xsl:text> RecentCustomers</xsl:text>
											</xsl:attribute>
											<xsl:attribute name="onclick">
												<xsl:text>RecentCustomerGo (this, '</xsl:text><xsl:value-of select="./Id" /><xsl:text>')</xsl:text>
											</xsl:attribute>
											
											<td><xsl:value-of select="position()" />.</td>
											<td>
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
							
							<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/Session/AuditList/Contacts/Contact) = 0">
								<div class="MsgNoticeModal">
									You have no Recently Verified Customers.
								</div>
							</xsl:if>
						</div>
						<div class="modalTitle">
							<div class="modalIcon Left">
								<img src="img/template/history.png" />
							</div>
							<div class="modalLabel Left">
								<strong>Recent Customers</strong><br />
								Your 20 most recently verified customers
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

<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../lib/date-time.xsl" />

	<xsl:output
	method="xml" 
	indent="yes" 
	version="1.0" 
	encoding="utf-8" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" 
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />

	<xsl:template match="/">
		<html>
			<head>
					<title>Flex Customer Management System</title>
					<link rel="stylesheet" type="text/css" href="css/default.css" />
					<script language="javascript" src="js/init.js"></script>
					
					<!-- Load Javascript : Popup Control -->
					<script language="javascript" src="js/lightbox/jquery-latest.js"></script>
					<script language="javascript" src="js/lightbox/dimensions.js"></script>
					<script language="javascript" src="js/lightbox/jquery-modalContent.js"></script>
					<script language="javascript" src="js/lightbox/flexModalContent.js"></script>
			</head>
			<body>
				<div id="Header">
					<div class="Logo">
						<a href='console.php'><img src='img/template/yellow_billing_logo_small.png' border='0'></img></a>
					</div>
					<div class='Left' style='padding:12px 0px 0px 5px'>
						Flex Customer Management System
					</div>
					<div class="Right"  style='padding-top:12px'>
						<!-- Report Bug Button & PopUp -->
						<div class="Menu_Button">
							<a href="#" onclick="return ModalDisplay ('#modalContent-ReportBug')">
								<img src="img/template/bug.png" border="0" alt="Report Bug" title="Report Bug" />
							</a>
						</div>
						<div id="modalContent-ReportBug">
							<div class="modalContainer">
								<div class="modalContent">
									<form method="post" name="bugreport" id="bugreport" action="bug_report.php" onsubmit="return BugSubmit(this)">
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
														<input type="button" value="Report Bug &#0187;" onclick="javascript:document.forms['bugreport'].submit()" class="input-submit" />
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
						
						<!-- System Debug Button & PopUp -->
						<xsl:if test="/Response/DOMDebug">
							<span class="Debug_Button">
								<a href="#" onclick="return ModalDisplay ('#modalContent-domDebug')">
									<img src="img/template/debug_dom.png" border="0" />
								</a>
							</span>
							<div id="modalContent-domDebug">
								<div class="modalContainer">
									<div class="modalContent">
										<pre><xsl:value-of select="/Response/DOMDebug" /></pre>
									</div>
									<div class="modalTitle">
										<div class="modalIcon Left">
											<img src="img/template/lady-debug.png" />
										</div>
										<div class="modalLabel Left">
											<strong>DOM Debug</strong><br />
											DOM Debug Information
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
				<div class="Seperator"></div>
				
					<!-- PageBody content -->
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
														<li><a href="../admin/flex.php/Plan/AvailablePlans/">List Rate Plans</a></li>
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
											<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
												<!-- User needs OPERATOR privileges to add a customer -->
												<tr>
													<td>
														<a href="account_add.php">
															<img src="img/template/contact_add.png" title="Add Customer" class="MenuIcon" />
														</a>
													</td>
												</tr>
											</xsl:if>
											<!-- This functionality is only available in Frameworks 2 and 3 now -->
											<!--
											<tr>
												<td>
													<a href="contact_verify.php">
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
											-->
											<tr>
												<td>
													<a href="../admin/flex.php/Plan/AvailablePlans/">
														<img src="img/template/plans.png" title="View Available Plans" class="MenuIcon" />
													</a>
												</td>
											</tr>
											<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Admin']) = 1">
												<tr>
													<td>
														<a href="console_admin.php">
															<img src="img/template/admin_console.png" title="Administrative Console" class="MenuIcon" />
														</a>
													</td>
												</tr>
											</xsl:if>
											<xsl:choose>
												<xsl:when test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Super Admin']) = 1">
												<tr>
													<td>
														<a href="../admin/reflex.php/Ticketing/System/Mine">
															<img src="img/template/ticketing_system.png" title="Ticketing System" class="MenuIcon" />
														</a>
													</td>
												</tr>
												</xsl:when>
												<xsl:when test="/Response/Ticketing = 1">
												<tr>
													<td>
														<a href="../admin/reflex.php/Ticketing/System/Mine">
															<img src="img/template/ticketing_system.png" title="Ticketing System" class="MenuIcon" />
														</a>
													</td>
												</tr>
												</xsl:when>
											</xsl:choose>
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
													
													<xsl:when test="name(.) = 'Employees'">
														<xsl:attribute name="href">
															<xsl:text>../admin/flex.php/Employee/EmployeeList/</xsl:text>
														</xsl:attribute>
														<xsl:text>View Employees</xsl:text>
													</xsl:when>
													
													<xsl:when test="name(.) = 'Employee'">
														<xsl:attribute name="href">
															<xsl:text>employee_edit.php?Id=</xsl:text>
															<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:text>Edit Employee</xsl:text>
													</xsl:when>
													
													<xsl:when test="name(.) = 'Adjustments-Account'">
														<xsl:attribute name="href">
															<xsl:text>account_charges_unbilled.php?Account=</xsl:text>
															<xsl:value-of select="." />
														</xsl:attribute>
														<xsl:text>List Adjustments</xsl:text>
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

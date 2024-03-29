<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:import href="../includes/init.xsl" />
	
	<xsl:template name="Content">
		
		<!-- Heading 1-->	
		<h1>Employee Console</h1>
		
		<!--Welcome Message-->
		<p>
			Welcome, <xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/FirstName" />.
			You are currently logged into your Employee Account.
		</p>
		<div class="TinySeperator"></div>
		
		<!-- Menu -->
		<table border="0" cellpadding="3" cellspacing="0">
			<!-- Add Customer -->
			<xsl:if test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Operator']) = 1">
				<!-- User needs OPERATOR privileges to add a customer -->
				<tr>
					<td>
						<a href="account_add.php">
							<img src="img/template/contact_add.png" title="Add Customer" class="MenuIcon" />
						</a>
					</td>
					<td>
						<strong>
							Add Customer
						</strong>
						<br />
						Add a new Customer to the system.
					</td>
				</tr>
			</xsl:if>
			<!-- Find Customer -->
			<tr>
				<td>
					<a href="contact_verify.php">
						<img src="img/template/contact_retrieve.png" title="Find Customer" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Find Customer
					</strong>
					<br />
					Find a Customer and access their account.
				</td>
			</tr>
			<!-- Recent Customers -->
			<tr>
				<td>
					<a href="#" onclick="return ModalDisplay ('#modalContent-recentCustomers')">
						<img src="img/template/history.png" title="Recent Customers" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Recent Customers
					</strong>
					<br />
					View recently accesed Customers.
				</td>
			</tr>
			<!-- View Available Plans -->
			<tr>
				<td>
					<a href="../admin/flex.php/Plan/AvailablePlans/">
						<img src="img/template/plans.png" title="View Plan Details" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						View Available Plans
					</strong>
					<br />
					View details of available Plans.
				</td>
			</tr>
			<!-- View Personal Details -->
			<!-- 
			<tr>
				<td>
					<a href="#" onclick="return FlexModalContent.display('./flex_modal_link.php/Employee/EmployeeDetails/', '400px', '500px')">
						<img src="img/template/contact.png" title="View Personal Details" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						View Personal Details
					</strong>
					<br />
					View and edit personal details.
				</td>
			</tr>
			-->
			<!-- If Admin... -->
			<xsl:choose>
				<xsl:when test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Admin']) = 1">
					 <!-- Administrative Console --> 
					 <tr>
						<td>
							<a href="console_admin.php">
								<img src="img/template/admin_console.png" title="Administrative Console" class="MenuIcon" />
							</a>
						</td>
						<td>
							<strong>
								Administrative Console
							</strong>
							<br />
							Additional Administrative Options.
						</td>
					</tr>
				</xsl:when>
				<!-- If Accounts... -->
				<xsl:when test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Accounts']) = 1"> 
					<!-- Administrative Console --> 
					<tr>
						<td>
							<a href="console_admin.php">
								<img src="img/template/admin_console.png" title="Accounts Console" class="MenuIcon" />
							</a>
						</td>
						<td>
							<strong>
								Accounts Console
							</strong>
							<br />
							Additional Accounts Options.
						</td>
					</tr> 
				</xsl:when> 
			</xsl:choose>
			<!-- User Manual -->
			<!--
			<tr>
				<td>
					<a href="user_manual">
						<img src="img/template/pdf.png" title="User Manual" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						User Manual
					</strong>
					<br />
					Need Help? Check the Manual.
				</td>
			</tr>
			-->
			<!-- Logout -->
			<tr>
				<td>
					<a href="#">
						<xsl:attribute name="onclick">
							<xsl:text>
								return Logout()
							</xsl:text>
						</xsl:attribute>
						<img src="img/template/logout.png" title="Logout" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Logout
					</strong>
					<br />
					Logout of the system.
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>

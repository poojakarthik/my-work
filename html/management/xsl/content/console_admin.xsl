<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Administrative Console</h1>
		
		<p>
			Welcome, <xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/FirstName" />.
			You are currently logged into your Administration Account.
		</p>
		
		<div class="TinySeperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td>
					<a href="account_list.php">
						<img src="img/template/contact_retrieve.png" title="Advanced Account Search" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Advanced Account Search
					</strong><br />
					 Search for Accounts without Verification
				</td>
			</tr>
			<tr>
				<td>
					<a href="contact_list.php">
						<img src="img/template/contact_retrieve.png" title="Advanced Contact Search" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Advanced Contact Search
					</strong><br />
					 Search for Contacts without Verification
				</td>
			</tr>
			<tr>
				<td>
					<a href="service_list.php">
						<img src="img/template/contact_retrieve.png" title="Advanced Service Search" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Advanced Service Search
					</strong><br />
					 Search for Services without Verification
				</td>
			</tr>
			<tr>
				<td>
					<a href="charges_approve.php">
						<img src="img/template/charge.png" title="Approve Unbilled Adjustments" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Manage Adjustment
					</strong><br />
					 Approve and Decline Adjustments.
				</td>
			</tr>
			<tr>
				<td>
					<a href="charges_charge_list.php">
						<img src="img/template/charge.png" title="Single Adjustment Types" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Manage Single Adjustment Types
					</strong><br />
					View and Add Single Adjustment Types.
				</td>
			</tr>
			<tr>
				<td>
					<a href="charges_recurringcharge_list.php">
						<img src="img/template/charge.png" title="Recurring Adjustment Types" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Manage Recurring Adjustment Types
					</strong><br />
					View and Add Recurring Adjustment Types.
				</td>
			</tr>
			<tr>
				<td>
					<a href="payment_download.php">
						<img src="img/template/charge.png" title="Payment Download" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Payment Download
					</strong><br />
					Download a list of Payments made on a particular date.
				</td>
			</tr>
			<tr>
				<td>
					<a href="../admin/flex.php/Misc/MoveDelinquentCDRs/">
						<img src="img/template/charge.png" title="Move Delinquent CDRs" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Move Delinquent CDRs
					</strong><br />
					Allocate delinquent CDRs to the appropriate services
				</td>
			</tr>
			<tr>
				<td>
					<a href="datareport_list.php">
						<img src="img/template/report.png" title="Data Reports" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Data Reports
					</strong><br />
					Run reports to return information on data in the System.
				</td>
			</tr>
			<tr>
				<td>
					<a href="../admin/flex.php/Employee/EmployeeList/">
						<img src="img/template/contact.png" title="Employees" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong> Manage Employees</strong><br />
					View and Add Employees.
				</td>
			</tr>
			<tr>
				<td>
					<a href="../admin/flex.php/InvoiceRunEvents/Manage/">
						<img src="img/template/schedule.png" title="Invoice Run Events" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong> Schedule Invoice Run Events</strong><br />
					View and Schedule Invoice Run Events.
				</td>
			</tr>
			<!-- Conditional Options-->
			<xsl:choose>
				<!-- If SuperAdmin -->
				<xsl:when test="count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Super Admin']) = 1">
					<!-- System Settings Menu -->
					<tr>
						<td>
							<a href="../admin/flex.php/Config/SystemSettingsMenu/">
								<img src="img/template/system_settings_menu_item.png" title="System Settings Menu" class="MenuIcon" />
							</a>
						</td>
						<td>
							<strong> System Settings Menu</strong><br />
							Manage system settings.
						</td>
					</tr>
				</xsl:when>
				<!-- If Not SuperAdmin, but has CustomerGroupAdmin permission -->
				<xsl:when test="(count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Customer Group Admin']) = 1) and (count(/Response/Authentication/AuthenticatedEmployee/AuthenticatedEmployeePrivileges/Permissions/Permission[Name='Super Admin']) = 0)">
					<!-- Customer Group Menu -->
					<tr>
						<td>
							<a href="../admin/flex.php/CustomerGroup/ViewAll/">
								<img src="img/template/customer_groups_menu_item.png" title="Manage Customer Groups" class="MenuIcon" />
							</a>
						</td>
						<td>
							<strong>Manage Customer Groups</strong><br />
							Upload new marketing images.
						</td>
					</tr>
				</xsl:when>
			</xsl:choose>
		</table>
	</xsl:template>
</xsl:stylesheet>

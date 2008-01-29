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
		
		<h2>Menu</h2>
		<div class="SmallSeperator"></div>
		
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
					<a href="employee_list.php">
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
					<a href="vixen.php/CustomerGroup/ViewAll/">
						<img src="img/template/customer_group.png" title="Customer Groups" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong> Manage Customer Groups</strong><br />
					Edit and Add Customer Groups.
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>

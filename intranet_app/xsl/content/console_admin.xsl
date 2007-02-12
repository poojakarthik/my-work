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
					<a href="charges_approve.php">
						<img src="img/template/charge.png" title="Approve Unbilled Charges" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Manage Charges &amp; Credits
					</strong><br />
					 Approve and Decline Charges and Credits.
				</td>
			</tr>
			<tr>
				<td>
					<a href="charges_charge_list.php">
						<img src="img/template/charge.png" title="Single Charge Types" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Manage Single Charge Types
					</strong><br />
					View and Add Single Charge Types.
				</td>
			</tr>
			<tr>
				<td>
					<a href="charges_recurringcharge_list.php">
						<img src="img/template/charge.png" title="Recurring Charge Types" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Manage Recurring Charge Types
					</strong><br />
					View and Add Recurring Charge Types.
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
		</table>
	</xsl:template>
</xsl:stylesheet>

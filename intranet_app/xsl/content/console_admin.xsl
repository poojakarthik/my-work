<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Administrative Console</h1>
		
		<p>
			Welcome, <xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/FirstName" />.
			This is your Administration Console. From here, you can perform tasks that are only accessible
			to Administrative Employees.
		</p>
		<div class="TinySeperator">
		</div>
		
		<h2 >
			Menu
		</h2>
		<div class="SmallSeperator">
		</div>
		<table border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td>
					<a href="charges_approve.php">
						<img src="img/template/charge.png" title="Approve Unbilled Charges" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>
						Unbilled Charges
					</strong><br />
					Approve Unbilled Charges
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
						Single Charge Types
					</strong><br />
					List and Add Single Charge Types
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
						Recurring Charge Types
					</strong><br />
					List and Add Recurring Charge Types
				</td>
			</tr>
				<tr>
					<td>
						<a href="employee_list.php">
							<img src="img/template/contact.png" title="Employees" class="MenuIcon" />
						</a>
					</td>
					<td>
						<strong>Employees</strong><br />
						List and Add Employees
					</td>
				</tr>
		</table>
		<!--
		<ul>
			<li>
				Charges and Charge Types
				<ul>
					<li><a href="charges_approve.php">Approve Unbilled Charges</a></li>
					<li>
						Single Charges
						<ul>
							<li><a href="charges_charge_add.php">Add Single Charge Type</a></li>
							<li><a href="charges_charge_list.php">List Single Charge Types</a></li>
						</ul>
					</li>
					<li>
						Recurring Charges
						<ul>
							<li><a href="charges_recurringcharge_add.php">Add Recurring Charge Type</a></li>
							<li><a href="charges_recurringcharge_list.php">List Recurring Charge Types</a></li>
						</ul>
					</li>
				</ul>
				<div class="Seperator"></div>
			</li>
			<li>
				Employees
				<ul>
					<li><a href="employee_add.php">Add Employee</a></li>
					<li><a href="employee_list.php">List Employees</a></li>
				</ul>
				<div class="Seperator"></div>
			</li>
		</ul>
		-->
	</xsl:template>
</xsl:stylesheet>

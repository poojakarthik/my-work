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
		<div class="TinySeperator"></div>
		
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
	</xsl:template>
</xsl:stylesheet>

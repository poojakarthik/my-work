<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Administrative Console</h1>
		
		<p>
			Welcome, <xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/FirstName" />.
			You are currently logged into your Employee Account.
		</p>
		
		<div class="Seperator"></div>
		
		<h2>Menu Description</h2>
		<div class="Seperator"></div>
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td>
					<a href="account_add.php">
						<img src="img/template/contact_add.png" title="Add Customer" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>Add New Customer</strong><br />
					Allows you to add a new Customer to the database.
				</td>
			</tr>
			<tr>
				<td>
					<a href="contact_list.php">
						<img src="img/template/contact_retrieve.png" title="Find Customer" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>Find Customer</strong><br />
					Authenticates a telephone call to view Customer information.
				</td>
			</tr>
			<tr>
				<td>
					<a href="#" onclick="$('#modalContent-recentCustomers').modalContent(null, 'show', 'slow');">
						<img src="img/template/history.png" title="Recent Customers" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>Recent Customers</strong><br />
					Allows you to view your history of recently viewed Customers.
				</td>
			</tr>
			<tr>
				<td>
					<a href="rates_plan_list.php">
						<img src="img/template/plans.png" title="View Plan Details" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>View Plan Details</strong><br />
					Shows information relating to available plans in the database.
				</td>
			</tr>
			<tr>
				<td>
					<a href="logout.php">
						<img src="img/template/logout.png" title="Employee Logout" class="MenuIcon" />
					</a>
				</td>
				<td>
					<strong>Employee Logout</strong><br />
					Safely and securely logout of your employee account.
				</td>
			</tr>
		</table>
		
		<div id="modalContent-PabloSays">
			<div class="modalContainer">
				<div class="modalContent">
					<div class="Pablo-Section">
						<div class="Pablo-Section-Container">
							<div class="Pablo-Section-Content">
								<h2>Pablo's Rants ... </h2>
								<p>
									<xsl:value-of select="/Response/Tip/TipText" disable-output-escaping="yes" />
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="modalTitle">
					<div class="modalClose Right">
						<img src="img/template/closelabel.gif" class="close" />
					</div>
					<div class="Clear"></div>
				</div>
			</div>
		</div>
		
		<script language="javascript">
			
			window.addEventListener (
				"load",
				function ()
				{
					$('#modalContent-PabloSays').modalContent(null, 'show', 'slow');
				},
				true
			);
			
		</script>
	</xsl:template>
</xsl:stylesheet>

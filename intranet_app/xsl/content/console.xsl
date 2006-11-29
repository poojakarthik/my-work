<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Administrative Console</h1>
		<div class="Clear"></div>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<div id="Navigation">
					<ul>
						<li>
							Accounts
							<ul>
								<li><a href="account_list.php">List Accounts</a></li>
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
					</ul>
				</div>
				
				<div id="Page" class="Left">
					Welcome, <xsl:value-of select="/Response/Authentication/AuthenticatedEmployee/UserName" />.
					You are currently logged into your Employee Account.
				</div>
				
				<div class="Clear"></div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

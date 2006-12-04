<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="iso-8859-1" indent="yes" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	
	<xsl:template match="/">
		<html>
			<head>
				<title>Employee Intranet System</title>
				<link rel="stylesheet" type="text/css" href="css/default.css" />
			</head>
			<body>
				<div id="Header" class="sectionContainer">
					<div class="sectionContent">
						<div class="Left">
							TelcoBlue Internal Management System
						</div>
						
						<div class="Right">
							Version 6.11
						</div>
						
						<div class="Clear"></div>
					</div>
					<div class="Clear"></div>
				</div>
				<div id="Controller" class="sectionContainer">
					<table border="0" width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="top" width="250">
								<div id="Navigation" class="Left sectionContent">
									<ul id="Navigation-Root">
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
										<li>
											My Account
											<ul>
												<li><a href="logout.php">Logout</a></li>
											</ul>
										</li>
									</ul>
								</div>
							</td>
							<td valign="top">
								<div id="Content" class="Left sectionContent">
									<xsl:call-template name="Content" />
								</div>
							</td>
						</tr>
					</table>
					
					<div class="Clear"></div>
				</div>
				<div class="Clear"></div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

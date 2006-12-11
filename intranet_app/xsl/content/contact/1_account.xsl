<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<h2>Stage 1: Identify the Account</h2>
		<div class="Seperator"></div>
		
		<form method="post" action="contact_list.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					To Authenticate a Contact, you must Identify the Account
					which they are requesting. This can be done by identifying
					the Service Number they are using or the Account Number (Id)
					listed on the top of their bill.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td colspan="2">
								You must match <strong>at least one</strong> of the following fields:
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="Account" class="input-string" />
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="FNN" class="input-string" />
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" class="input-submit" value="Find Contact &#0187;" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

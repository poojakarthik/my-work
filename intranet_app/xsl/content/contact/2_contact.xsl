<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<h2>Stage 2: Identify the Contact</h2>
		<div class="Seperator"></div>
		
		<form method="post" action="contact_list.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<input type="hidden" name="FNN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Service/FNN" />
				</xsl:attribute>
			</input>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					Now that you have Identified the Account, you must
					Identify the Contact that is requesting their 
					information to be Viewed.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<xsl:if test="/Response/Account">
							<tr>
								<th>
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Id')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/Id" /></td>
							</tr>
						</xsl:if>
						<xsl:if test="/Response/Service">
							<tr>
								<th>
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Service')" />
										<xsl:with-param name="field" select="string('FNN')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Service/FNN" /></td>
							</tr>
						</xsl:if>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="Contact">
									<option value=""></option>
									<xsl:for-each select="/Response/Contacts/Results/rangeSample/Contact">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./LastName" />
											<xsl:text>, </xsl:text>
											<xsl:value-of select="./FirstName" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" class="input-submit" value="Continue &#0187;" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

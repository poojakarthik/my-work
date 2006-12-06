<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>View Account Information</h1>
		
		<div class="Left">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<form method="POST" action="account_edit.php">
						<input type="hidden" name="Id">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:value-of select="/Response/Account/Id" disable-output-escaping="yes" />
							</xsl:attribute>
						</input>
						<table border="0" cellpadding="5" cellspacing="0" class="Somebody_doesn_t_know_about_spacing">
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Id')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:value-of select="/Response/Account/Id" disable-output-escaping="yes" />
								</td>
							</tr>
							<tr>
								<td colspan="2"><div class="Seperator"></div></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('BusinessName')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/BusinessName" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('TradingName')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/TradingName" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<td colspan="2"><div class="Seperator"></div></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('ABN')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/ABN" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('ACN')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/ACN" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<td colspan="2"><div class="Seperator"></div></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Address1')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/Address1" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Address2')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/Address2" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Suburb')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/Suburb" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Postcode')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/Postcode" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<td colspan="2"><div class="Seperator"></div></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('State')" />
									</xsl:call-template>
								</th>
								<td><xsl:value-of select="/Response/Account/State" disable-output-escaping="yes" /></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Country')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:value-of select="/Response/Account/Country" disable-output-escaping="yes" />
								</td>
							</tr>
							<tr>
								<td colspan="2"><div class="Seperator"></div></td>
							</tr>
							<tr>
								<th class="JustifiedWidth">
									<xsl:call-template name="Label">
										<xsl:with-param name="entity" select="string('Account')" />
										<xsl:with-param name="field" select="string('Archived')" />
									</xsl:call-template>
								</th>
								<td>
									<xsl:choose>
										<xsl:when test="/Response/Account/Archived = 0">
											<strong><span class="Green">Currently Available</span></strong>
										</xsl:when>
										<xsl:otherwise>
											<strong><span class="Red">Currently Archived</span></strong>
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</div>
		<div class="Right">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<h2>Account Actions</h2>
					
					<ul>
						<li>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_edit.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:text>Edit Account Information</xsl:text>
							</a>
						</li>
					</ul>
					
					<div class="Seperator"></div>
					
					<h2>Authenticated Contacts</h2>
					
					<div class="Seperator"></div>
				</div>
			</div>
		</div>
		<div class="Clear"></div>
	</xsl:template>
</xsl:stylesheet>

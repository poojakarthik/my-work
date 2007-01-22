<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Find Customer</h1>
		
		<h2 class="Account">Select an Account</h2>
		<div class="sectionContainer">
			<div class="sectionContent">
				<form method="post" action="contact_list.php">
					<input type="hidden" name="ui-BusinessName">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/ui-values/BusinessName" />
						</xsl:attribute>
					</input>
					<input type="hidden" name="ui-ABN">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/ui-values/ABN" />
						</xsl:attribute>
					</input>
					<input type="hidden" name="ui-ACN">
						<xsl:attribute name="value">
							<xsl:text></xsl:text>
							<xsl:value-of select="/Response/ui-values/ACN" />
						</xsl:attribute>
					</input>
					
					<div class="Filter-Form">
						<div class="Filter-Form-Content">
							<table border="0" cellpadding="5" cellspacing="0">
								<xsl:if test="/Response/ui-values/BusinessName != ''">
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('BusinessName')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/ui-values/BusinessName" /></td>
									</tr>
								</xsl:if>
								<xsl:if test="/Response/ui-values/ABN != ''">
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('ABN')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/ui-values/ABN" /></td>
									</tr>
								</xsl:if>
								<xsl:if test="/Response/ui-values/ACN != ''">
									<tr>
										<th class="JustifiedWidth">
											<xsl:call-template name="Label">
												<xsl:with-param name="entity" select="string('Account')" />
												<xsl:with-param name="field" select="string('ACN')" />
											</xsl:call-template>
										</th>
										<td><xsl:value-of select="/Response/ui-values/ACN" /></td>
									</tr>
								</xsl:if>
							</table>
						</div>
					</div>
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
						<tr class="First">
							<th width="30">#</th>
							<th></th>
							<th>Business Name</th>
							<th>Trading Name</th>
							<th>Suburb / Postcode</th>
						</tr>
						<xsl:for-each select="/Response/ui-answers/Accounts/Account">
							<tr>
								<xsl:attribute name="class">
									<xsl:choose>
										<xsl:when test="position() mod 2 = 1">
											<xsl:text>Odd</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>Even</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
								
								<td><xsl:value-of select="position()" />.</td>
								<td width="30">
									<input type="radio" name="ui-Account-Sel">
										<xsl:attribute name="value">
											<xsl:value-of select="./Id" />
										</xsl:attribute>
										
									</input>
								</td>
								<td><xsl:value-of select="./BusinessName" /></td>
								<td><xsl:value-of select="./TradingName" /></td>
								<td><xsl:value-of select="./Suburb" />,  <xsl:value-of select="./Postcode" /></td>
							</tr>
						</xsl:for-each>
					</table>
				</form>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

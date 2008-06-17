<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
	
		<!-- This page is used in find a customer -->
		<!-- Select a contact following search OTHER THAN NAME -->
		
		<h1>Find Customer</h1>
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Unselected'">
							Please select a Contact.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
		
		<!--Select a Contact -->
		<h2 class="Contact">Select a Contact</h2>
		
		<form method="post" action="contact_verify.php">
			<input type="hidden" name="ui-Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Account" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-Account-Sel">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Account-Sel" />
				</xsl:attribute>
			</input>
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
			<input type="hidden" name="ui-Invoice">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/Invoice" />
				</xsl:attribute>
			</input>
			<input type="hidden" name="ui-FNN">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ui-values/FNN" />
				</xsl:attribute>
			</input>
			

			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td><xsl:value-of select="/Response/ui-answers/Account/BusinessName" /></td>
						</tr>
						<!--Check for Trading Name-->
						<xsl:choose>
							<xsl:when test="/Response/ui-answers/Account/TradingName = ''">
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Account')" />
											<xsl:with-param name="field" select="string('TradingName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/ui-answers/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="ui-Contact-Sel">
									<option value=""></option>
									<xsl:for-each select="/Response/ui-answers/Contacts/Contact">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											
											<xsl:choose>
												<xsl:when test="./FirstName = '' and ./LastName = ''">
													<xsl:text>[No Name]</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:value-of select="./FirstName" />
													<xsl:text> </xsl:text>
													<xsl:value-of select="./LastName" />
												</xsl:otherwise>
											</xsl:choose>
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" name="ContinueContact" class="input-submit" value="Continue &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

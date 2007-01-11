<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<h2>Stage 2: Identify the Contact</h2>
		<div class="Seperator"></div>
		
		<form method="post" action="contact_list.php">
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
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								Please use the contact listed in this field below:
							</td>
						</tr>
						<tr>
							<td>
								<table border="0" cellpadding="5" cellspacing="0">
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
														<xsl:value-of select="./FirstName" />
														<xsl:text> </xsl:text>
														<xsl:value-of select="./LastName" />
													</option>
												</xsl:for-each>
											</select>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" class="input-submit" value="Continue &#0187;" />
		</form>
	</xsl:template>
</xsl:stylesheet>

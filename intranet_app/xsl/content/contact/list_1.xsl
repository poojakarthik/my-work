<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<h2>Stage 1: Initial Identification</h2>
		<div class="Seperator"></div>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<form method="post" action="contact_list.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					To authenticate a Customer, you must match <strong>only one</strong> of the following fields:
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-BusinessName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/BusinessName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-ContactName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/ContactName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="Seperator"></div>
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
								<input type="text" name="ui-Account" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Account" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-ABN" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/ABN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-ACN" class="input-ACN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/ACN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Invoice')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-Invoice" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Invoice" />
									</xsl:attribute>
								</input>
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
								<input type="text" name="ui-FNN" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/FNN" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th></th>
							<td width="500">
								<strong><span class="Attention">Attention</span> :</strong>
								You can only identify by a Line Number if the Line Number itself
								is currently unarchived. Otherwise the customer is required to 
								identify themselves by quoting their Account Number printed on 
								the top of all their bills.
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="Seperator"></div>
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

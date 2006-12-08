<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Search</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<form method="post" action="contact_list.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					Now that you have entered one piece of Primary Information, you must now
					validate the following information Verbally.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0" class="Listing" width="600" id="InformationSelection">
						<tr class="Odd">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Id" />
							</td>
							<td></td>
						</tr>
						<tr class="Even">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/BusinessName" />
							</td>
							<td>
								<input type="checkbox" name="match[]" value="BusinessName" />
							</td>
						</tr>
						<tr class="Odd">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/TradingName" />
							</td>
							<td>
								<input type="checkbox" name="match[]" value="TradingName" />
							</td>
						</tr>
						<tr class="Even">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/ABN" />
							</td>
							<td></td>
						</tr>
						<tr class="Odd">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/ACN" />
							</td>
						</tr>
						<tr class="Even">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address1')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Address1" />
							</td>
							<td>
								<input type="checkbox" name="match[]" value="Address1" />
							</td>
						</tr>
						<tr class="Odd">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Address2')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Address2" />
							</td>
							<td>
								<input type="checkbox" name="match[]" value="Address2" />
							</td>
						</tr>
						<tr class="Even">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Suburb')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Suburb" />
							</td>
							<td>
								<input type="checkbox" name="match[]" value="Suburb" />
							</td>
						</tr>
						<tr class="Odd">
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Postcode')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:value-of select="/Response/Account/Postcode" />
							</td>
							<td>
								<input type="checkbox" name="match[]" value="Postcode" />
							</td>
						</tr>
					</table>
					
					<div class="Seperator"></div>
					
					<input type="submit" class="input-submit-disabled" value="Finalise Verification &#0187;" disabled="disabled" />
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

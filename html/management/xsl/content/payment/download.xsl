<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Download Payment Summary</h1>
		
		<h2 class="Payment">Payment Details</h2>
		<form method="post" action="payment_download.php">
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('CustomerGroup')" />
									<xsl:with-param name="field" select="string('CustomerGroup')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="CustomerGroup[]" multiple="multiple">
									<xsl:for-each select="/Response/CustomerGroups/CustomerGroup">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaymentType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="PaymentType">
									<xsl:for-each select="/Response/PaymentTypes/PaymentType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaidOn')" />
								</xsl:call-template>
							</th>
							<td>
								<table border="0" cellpadding="3" cellspacing="0">
									<tr>
										<td>
											<input type="radio" name="PaidOn" value="TODAY" id="PaidOn:TODAY" checked="checked" />
										</td>
										<th>
											<label for="PaidOn:TODAY">
												Entered Today
											</label>
										</th>
									</tr>
									<tr>
										<td>
											<input type="radio" name="PaidOn" value="YESTERDAY" id="PaidOn:YESTERDAY" />
										</td>
										<th>
											<label for="PaidOn:YESTERDAY">
												Entered Yesterday
											</label>
										</th>
									</tr>
									<tr>
										<td>
											<input type="radio" name="PaidOn" value="CUSTOM" id="PaidOn:CUSTOM" />
										</td>
										<th>
											<label for="PaidOn:CUSTOM">
												Entered on this Date:
											</label>
										</th>
									</tr>
									<tr>
										<td></td>
										<td>
											<xsl:call-template name="NearPast">
												<xsl:with-param name="Name-Day"			select="string('PaidOn:CUSTOM:day')" />
												<xsl:with-param name="Name-Month"		select="string('PaidOn:CUSTOM:month')" />
												<xsl:with-param name="Name-Year"		select="string('PaidOn:CUSTOM:year')" />
											</xsl:call-template>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<div class="Right">
				<input type="submit" value="Download Payments &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

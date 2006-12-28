<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Recurring Charge Type Details</h1>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Rec. Charge Id:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/Id" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Charge Code:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/ChargeType" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Description:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Recursion Amount:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/RecursionCharge" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Nature:</th>
						<td>
							<strong>
								<span>
									<xsl:attribute name="class">
										<xsl:choose>
											<xsl:when test="/Response/RecurringChargeType/Nature = 'DR'">
												<xsl:text>Blue</xsl:text>
											</xsl:when>
											<xsl:when test="/Response/RecurringChargeType/Nature = 'CR'">
												<xsl:text>Green</xsl:text>
											</xsl:when>
										</xsl:choose>
									</xsl:attribute>
									<xsl:value-of select="/Response/RecurringChargeType/Nature" />
								</span>
							</strong>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Recursion Period:</th>
						<td>
							<xsl:text>Every </xsl:text>
							<xsl:value-of select="/Response/RecurringChargeType/RecurringDate" />
							<xsl:text> </xsl:text>
							<xsl:value-of select="/Response/RecurringChargeType/BillingFreqTypes/BillingFreqType[@selected='selected']/Name" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Fixation:</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/RecurringChargeType/Fixed = 1">
									<xsl:text>Fixed and cannot be changed at application time.</xsl:text>
								</xsl:when>
								<xsl:when test="/Response/RecurringChargeType/Fixed = 0">
									<xsl:text>Not fixed, (flexible) and can be changed at application time.</xsl:text>
								</xsl:when>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Minimum Charge:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/MinCharge" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Cancellation Fee:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/CancellationFee" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Continuation:</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Continuable = 1">
										<span class="Green">
											Yes, this charge continues even after the Minimum Charge is reached.
										</span>
									</xsl:when>
									<xsl:otherwise>
										<span class="Blue">
											No, after the Minimum Charged is reached, this charge is cancelled.
										</span>
									</xsl:otherwise>
								</xsl:choose>
							</strong>
						</td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>

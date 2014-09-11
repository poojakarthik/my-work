<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Recurring Adjustment Type Details</h1>
		
		<div class="FormPopup">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Rec. Adjustment Id:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/Id" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Adjustment Code:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/ChargeType" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Description:</th>
						<td><xsl:value-of select="/Response/RecurringChargeType/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Recursion Amount:</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/RecursionCharge" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
	       				</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Nature:</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="/Response/RecurringChargeType/Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
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
							<div class="MicroSeperator"></div>
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
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Minimum Charge:</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/MinCharge" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
	       				</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Cancellation Fee:</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/RecurringChargeType/CancellationFee" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
	       				</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Continuation:</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/Continuable = 1">
										<span class="Green">
											Yes.  This Adjustment continues even after the Minimum Charge is reached.
										</span>
									</xsl:when>
									<xsl:otherwise>
										<span class="Blue">
											No.  After the Minimum Charged is reached, this Adjustment is cancelled.
										</span>
									</xsl:otherwise>
								</xsl:choose>
							</strong>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Requires Approval:</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/RecurringChargeType/approval_required = 0">
										<span class="Blue">
											No.  Requests for the recurring adjustment are automatically approved.
										</span>
									</xsl:when>
									<xsl:otherwise>
										<span class="Green">
											Yes.  Requests for the recurring adjustment have to go through the approval process.
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

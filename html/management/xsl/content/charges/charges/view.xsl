<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Adjustment Type Details</h1>
		
		<div class="FormPopup">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Adjustment Code:</th>
						<td><xsl:value-of select="/Response/ChargeType/ChargeType" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Description:</th>
						<td><xsl:value-of select="/Response/ChargeType/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Amount:</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/ChargeType/Amount" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
	       				</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Nature:</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/ChargeType/Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="/Response/ChargeType/Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
							</strong>
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
								<xsl:when test="/Response/ChargeType/Fixed = 1">
									<xsl:text>Fixed and cannot be changed at application time.</xsl:text>
								</xsl:when>
								<xsl:when test="/Response/ChargeType/Fixed = 0">
									<xsl:text>Not fixed, (flexible) and can be changed at application time.</xsl:text>
								</xsl:when>
							</xsl:choose>
						</td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

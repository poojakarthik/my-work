<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Charge Type Details</h1>
		
		<div class="Filter-Form">
			<div class="Filter-Form-Content">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Charge Code:</th>
						<td><xsl:value-of select="/Response/ChargeType/ChargeType" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Description:</th>
						<td><xsl:value-of select="/Response/ChargeType/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Amount:</th>
						<td><xsl:value-of select="/Response/ChargeType/Amount" /></td>
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
							<div class="Seperator"></div>
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

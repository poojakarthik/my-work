<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/popup.xsl" />
	<xsl:import href="../../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>View Adjustment Details</h1>
		
		<div class="FormPopup">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">Adjustment Code :</th>
						<td><xsl:value-of select="/Response/Charge/ChargeType" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Description :</th>
						<td><xsl:value-of select="/Response/Charge/Description" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Amount :</th>
						<td>
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Charge/Amount" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
	       				</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Nature :</th>
						<td>
							<strong>
								<xsl:choose>
									<xsl:when test="/Response/Charge/Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="/Response/Charge/Nature = 'CR'">
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
						<th class="JustifiedWidth">Service :</th>
						<td>
							<xsl:choose>
								<xsl:when test="not(/Response/Charge/Service)">
									No Association
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/Charge/Service/FNN" />
									<xsl:text> [</xsl:text>
									<a>
										<xsl:attribute name="href">
											<xsl:text>service_view.php?Id=</xsl:text>
											<xsl:value-of select="/Response/Charge/Service/Id" />
										</xsl:attribute>
										<xsl:text>View Service</xsl:text>
									</a>
									<xsl:text>]</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Invoice :</th>
						<td>
							<xsl:choose>
								<xsl:when test="/Response/Charge/Invoice = ''">
									No Association
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/Charge/Invoice" />
									<xsl:text> [</xsl:text>
									<a>
										<xsl:attribute name="href">
											<xsl:text>invoice_view.php?Invoice=</xsl:text>
											<xsl:value-of select="/Response/Charge/Invoice" />
										</xsl:attribute>
										<xsl:text>View Invoice</xsl:text>
									</a>
									<xsl:text>]</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">Notes:</th>
						<td class="monospace">
							<xsl:value-of select="/Response/Charge/Notes" />
						</td>
					</tr>
				</table>
				<div class="Clear"></div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

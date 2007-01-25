<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Resolve Invoice Dispute</h1>
		<div class="Seperator"></div>
		
		<form method="post" action="invoice_dispute_resolve.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
			</input>
			
			<h2 class="Invoice">Dispute Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Invoice/Id" />
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Disputed')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/Invoice/Disputed" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Resolve')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="checkbox" name="Resolve" value="1" />
							Yes, Resolve this Dispute
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Apply Dispute &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>

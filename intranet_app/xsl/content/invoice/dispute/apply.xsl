<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--Page for disputing an Invoice -->
		
		<h1>Dispute Invoice </h1>
		
		<form method="post" action="invoice_dispute_apply.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
			</input>
			
			<!-- Dispute Details -->
			<h2 class="Invoice">Dispute Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
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
						<td>
							<input type="text" name="Disputed" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/Invoice/Disputed" />
										<xsl:with-param name="Decimal" select="number('4')" />
			       					</xsl:call-template>
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			<div class="Small Seperator"></div>
			
			<div class="Right">
				<input type="submit" value="Continue &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

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
			
			<!--TODO!bash! [  DONE  ]		This needs an error when disputed = 0!!!  Error==="Please enter a Disputed Amount."-->
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Amount Blank'">
							Please enter a Disputed Amount.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Amount Zero'">
							Please enter a valid Dispute Amount.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Invalid Amount'">
							Please enter a valid Dispute Amount.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Dispute High'">
							You can only Dispute an Amount less than the Value of the Invoice.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td></td>
						<td class="Right">
							<xsl:value-of select="/Response/Invoice/Id" />
						</td>
					</tr>
					<tr>
					<td colspan="3">
						<div class="MicroSeperator"></div>
					</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Debits')" />
							</xsl:call-template>
						</th>
						<td></td>
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Debits" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr> 
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Credits')" />
							</xsl:call-template>
						</th>
						<td>-</td>
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Credits" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Tax')" />
							</xsl:call-template>
						</th>
						<td>+</td>
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Tax" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<hr />
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Amount')" />
							</xsl:call-template>
						</th>
						<td></td>
						<td class="Currency">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Balance" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Disputed')" />
							</xsl:call-template>
						</th>
						<td></td>
						<td>
							<input type="text" name="Disputed" class="input-string2 Currency">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
					       			<xsl:call-template name="Currency">
					       				<xsl:with-param name="Number" select="/Response/Invoice/Disputed" />
										<xsl:with-param name="Decimal" select="number('2')" />
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

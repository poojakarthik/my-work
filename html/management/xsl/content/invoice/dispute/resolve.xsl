<?xml version="1.0" encoding="utf-8"?>


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!--Page for resolving Invoice Disputes-->
		
		<h1>Resolve Disputed Invoice</h1>
		
		<form method="post" action="invoice_dispute_resolve.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Invoice/Id" />
				</xsl:attribute>
			</input>
			
			<!--Dispute Details -->
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
						<td class="Required"></td>
						<td class="Right">
							<xsl:value-of select="/Response/Invoice/Id" />
						</td>
						<td ></td>
					</tr>
					<tr>
						<td colspan="4">
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
						<td class="Required"></td>
						<td class="Currency" width="65">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Debits" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
						<td class="JustifiedWidth"></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Credits')" />
							</xsl:call-template>
						</th>
						<td class="Required">-</td>
						<td class="Currency" width="65">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Credits" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
						<td class="JustifiedWidth"></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Tax')" />
							</xsl:call-template>
						</th>
						<td class="Required">+</td>
						<td class="Currency" width="65">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Tax" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
						<td class="JustifiedWidth">                                                                                 
						</td>
					</tr>
					<tr>
						<td colspan="4">
							_________________________________
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Amount')" />
							</xsl:call-template>
						</th>
						<td class="Required"></td>
						<td class="Currency" width="65">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Balance" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</td>
						<td class="JustifiedWidth"></td>
					</tr>
					<tr>
						<td colspan="4">
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
						<td class="Required"></td>
						<td class="Currency" width="65">
						<div class="Red">
			       			<xsl:call-template name="Currency">
			       				<xsl:with-param name="Number" select="/Response/Invoice/Disputed" />
								<xsl:with-param name="Decimal" select="number('2')" />
	       					</xsl:call-template>
						</div>
	       				</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="4">
							<div class="SmallSeperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth" valign="top">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Invoice')" />
								<xsl:with-param name="field" select="string('Resolve')" />
							</xsl:call-template>
						</th>
						<td colspan="4">
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="radio" name="ResolveMethod" value="1" id="Resolve:1" />
									</td>
									<th colspan="3">
										<label for="Resolve:1">Customer to pay full amount</label>
									</th>
								</tr>
								<tr>
									<td>
										<input type="radio" name="ResolveMethod" value="2" id="Resolve:2" />
									</td>
									<th colspan="3">
										<label for="Resolve:2">Customer to pay   </label>
									
																	
									
										<input type="text" name="ResolveAmount" class="input-string2 Currency">
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
								       			<xsl:call-template name="Currency">
								       				<xsl:with-param name="Number" select="/Response/Invoice/Disputed" />
													<xsl:with-param name="Decimal" select="number('2')" />
						       					</xsl:call-template>
											</xsl:attribute>
										</input>
									</th>
								</tr>
								<tr>
									<td>
										<input type="radio" name="ResolveMethod" value="3" id="Resolve:3" />
									</td>
									<th colspan="3">
										<label for="Resolve:3">Payment NOT required</label>
									</th>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div class="SmallSeperator"></div>
			
			<div class="Right">
				<input type="submit" value="Continue &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

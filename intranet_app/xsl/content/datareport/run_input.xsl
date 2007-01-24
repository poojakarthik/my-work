<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../lib/date-time.xsl" />
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
		<h1>Run Data Report</h1>
		
		<form method="post" action="datareport_run.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/DataReport/Id" />
				</xsl:attribute>
			</input>
			
			<h2>Report Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Report')" />
								<xsl:with-param name="field" select="string('Name')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/DataReport/Name" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Report')" />
								<xsl:with-param name="field" select="string('Summary')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/DataReport/Summary" /></td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2>Report Constraint Input</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<xsl:for-each select="/Response/Inputs/Input">
						<tr>
							<th class="JustifiedWidth" valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="./Documentation-Entity" />
									<xsl:with-param name="field" select="./Documentation-Field" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:choose>
									<xsl:when test="./Type = 'dataBoolean'">
										<table border="0" cellpadding="3" cellspacing="0">
											<tr>
												<td>
													<input type="radio" value="1">
														<xsl:attribute name="name">
															<xsl:text>input[</xsl:text>
															<xsl:value-of select="./Name" />
															<xsl:text>]</xsl:text>
														</xsl:attribute>
													</input>
												</td>
												<th>
													Yes
												</th>
											</tr>
											<tr>
												<td>
													<input type="radio" value="0">
														<xsl:attribute name="name">
															<xsl:text>input[</xsl:text>
															<xsl:value-of select="./Name" />
															<xsl:text>]</xsl:text>
														</xsl:attribute>
													</input>
												</td>
												<th>
													No
												</th>
											</tr>
										</table>
									</xsl:when>
								</xsl:choose>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2>Report Return</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th valign="top" class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Report')" />
								<xsl:with-param name="field" select="string('ReturnType')" />
							</xsl:call-template>
						</th>
						<td>
							<table border="0" cellpadding="3" cellspacing="0">
								<tr>
									<td>
										<input type="radio" name="ReturnType" id="ReturnType:CSV" value="CSV" checked="checked" />
									</td>
									<th>
										<label for="ReturnType:CSV">
											Save Report as a CSV (Comma Seperated Values) file
										</label>
									</th>
								</tr>
								<tr>
									<td>
										<input type="radio" name="ReturnType" id="ReturnType:HTML" value="HTML" />
									</td>
									<th>
										<label for="ReturnType:HTML">
											Tabulate the Report and show me the results
										</label>
									</th>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" class="input-submit" name="Confirm" value="Run Report &#0187;" />
		</form>
	</xsl:template>
</xsl:stylesheet>

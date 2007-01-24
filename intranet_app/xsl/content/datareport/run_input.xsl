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
			
			<h2>Report Select Options</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<xsl:for-each select="/Response/Selects/Select">
						<tr>
							<td>
								<input type="checkbox" name="select[]" checked="checked">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="./Name" />
									</xsl:attribute>
								</input>
							</td>
							<th><xsl:value-of select="./Name" /></th>
						</tr>
					</xsl:for-each>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<xsl:if test="count(/Response/Inputs/Input) != 0">
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
													<th>Yes</th>
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
													<th>No</th>
												</tr>
											</table>
										</xsl:when>
										<xsl:when test="./Type = 'dataDate'">
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][day]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('1')" />
													<xsl:with-param name="cease" select="number('31')" />
												</xsl:call-template>
											</select> /
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][month]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('1')" />
													<xsl:with-param name="cease" select="number('12')" />
												</xsl:call-template>
											</select> /
											<select>
												<xsl:attribute name="name">
													<xsl:text>input[</xsl:text>
													<xsl:value-of select="./Name" />
													<xsl:text>][year]</xsl:text>
												</xsl:attribute>
												
												<xsl:call-template name="DateLoop">
													<xsl:with-param name="start" select="number('2000')" />
													<xsl:with-param name="cease" select="number('2010')" />
												</xsl:call-template>
											</select>
										</xsl:when>
									</xsl:choose>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			
			<input type="submit" class="input-submit" name="Confirm" value="Run Report &#0187;" />
		</form>
	</xsl:template>
</xsl:stylesheet>

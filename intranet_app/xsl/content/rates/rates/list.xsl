<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Rates</h1>
		
		<script language="javascript" src="js/rates_rate_list.js"></script>
		
		<form method="GET" action="rates_rate_list.php">
			<div class="Filter-Form">
				<div class="Filter-Form-Content Left">
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate')" />
									<xsl:with-param name="field" select="string('Name')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[Name][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[Name][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Rates/Constraints/Constraint[Name=string('Name')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					
					<input type="submit" value="Search" class="input-submit" />
					
					<div class="Clear"></div>
				</div>
				
				<div class="Clear"></div>
			</div>
		</form>
		
		<div class="Seperator"></div>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
					<tr class="First">
						<th width="30">#</th>
						<th>Rate Id</th>
						<th>Rate Name</th>
						<th>Archive</th>
						<th>Actions</th>
					</tr>
					<xsl:for-each select="/Response/Rates/Results/rangeSample/Rate">
						<tr>
							<xsl:attribute name="class">
								<xsl:choose>
									<xsl:when test="position() mod 2 = 1">
										<xsl:text>Odd</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>Even</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							<td><xsl:value-of select="position()" />.</td>
							<td><xsl:value-of select="./Id" /></td>
							<td><xsl:value-of select="./Name" /></td>
							<td>
								<xsl:choose>
									<xsl:when test="./Archived = 1">
										<xsl:text>Unassignable</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>Available</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:text>rates_rate_view.php?Id=</xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:attribute name="onclick">
										<xsl:text>return ViewRateDetails ('</xsl:text>
										<xsl:value-of select="./Id" />
										<xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View Details</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/Rates/Results/collationLength = 0">
						<div class="MsgError">
							No Rates were found with the criteria you searched for.
						</div>
					</xsl:when>
					<xsl:when test="count(/Response/Rates/Results/rangeSample/Rate) = 0">
						<div class="MsgNotice">
							There are no Rates in the Range that you wish to display.
						</div>
					</xsl:when>
				</xsl:choose>
				
				<p>
					<a href="rates_rate_add.php">Add a New Rate</a>
				</p>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

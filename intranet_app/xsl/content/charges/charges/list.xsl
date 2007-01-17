<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Charge Types</h1>
		<div class="Seperator"></div>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
					<tr class="First">
						<th width="30">#</th>
						<th>Code</th>
						<th>Description</th>
						<th>Amount</th>
						<th>Actions</th>
					</tr>
					
					<xsl:for-each select="/Response/ChargeTypes/Results/rangeSample/ChargeType">
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
							<td><xsl:value-of select="./ChargeType" /></td>
							<td><xsl:value-of select="./Description" /></td>
							<td>
								<xsl:value-of select="./Amount" />
								<xsl:text> </xsl:text>
								<xsl:value-of select="./Nature" />
							</td>
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:text>javascript:nohref()</xsl:text>
									</xsl:attribute>
									<xsl:attribute name="onclick">
										<xsl:text>return openPopup(</xsl:text>
											<xsl:text>'charges_charge_view.php?Id=</xsl:text>
											<xsl:value-of select="./Id" />
											<xsl:text>'</xsl:text>
										<xsl:text>)</xsl:text>
									</xsl:attribute>
									<xsl:text>View Details</xsl:text>
								</a>,
								<a>
									<xsl:attribute name="href">
										<xsl:text>charges_charge_archive.php?Id=</xsl:text>
										<xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:text>Archive Charge</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/ChargeTypes/Results/collationLength = 0">
						<div class="MsgError">
							No Charge Types were found with the criteria you searched for.
						</div>
					</xsl:when>
					<xsl:when test="count(/Response/ChargeTypes/Results/rangeSample/ChargeType) = 0">
						<div class="MsgNotice">
							There are no Charge Types in the Range that you wish to display.
						</div>
					</xsl:when>
				</xsl:choose>
				
				<p>
					<a href="charges_charge_add.php">Add a New Charge Type</a>
				</p>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

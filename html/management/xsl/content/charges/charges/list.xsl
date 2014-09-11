<?xml version="1.0" encoding="utf-8"?>
<!-- TODO!bash! [  DONE  ]		change class of errors and notices MsgNotice & MsgError no longer exist-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>View Adjustment Types</h1>

		<h2 class="Adjustment">Adjustment Details</h2>
		<div class="sectionContainer">
			<div class="sectionContent">
				<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
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
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="./Amount" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
								<xsl:text> </xsl:text>
								
								<xsl:choose>
									<xsl:when test="./Nature = 'DR'">
										<span class="Blue">Debit</span>
									</xsl:when>
									<xsl:when test="./Nature = 'CR'">
										<span class="Green">Credit</span>
									</xsl:when>
								</xsl:choose>
							</td>
							<td>
								<a href="#" title="Viewing Adjustment Information" alt="Information about this Particular Adjustment">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, </xsl:text>
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
									<xsl:text>Archive Adjustment</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/ChargeTypes/Results/collationLength = 0">
						<div class="MsgNoticeWide">
							There are currently no Adjustment Types in the System.
						</div>
					</xsl:when>
					<xsl:when test="count(/Response/ChargeTypes/Results/rangeSample/ChargeType) = 0">
						<div class="MsgErrorWide">
							There are Results matching the Criteria you searched for.
						</div>
					</xsl:when>
				</xsl:choose>
				
				<div class= "Right">
					<a href="charges_charge_add.php">Add Adjustment Type</a>
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

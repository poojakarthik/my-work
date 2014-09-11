<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Rate Groups</h1>
		
		<form method="GET" action="rates_group_list.php">
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Rate Group')" />
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
										<xsl:value-of select="/Response/RateGroups/Constraints/Constraint[Name=string('Name')]/Value" />
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
				<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
					<tr class="First">
						<th width="30">#</th>
						<th>Rate Group Id</th>
						<th>Rate Group Name</th>
						<th>Archive</th>
						<th>Actions</th>
					</tr>
					<xsl:for-each select="/Response/RateGroups/Results/rangeSample/RateGroup">
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
								<strong>
									<span>
										<xsl:choose>
											<xsl:when test="./Archived = 1">
												<xsl:attribute name="class">
													<xsl:text>Red</xsl:text>
												</xsl:attribute>
												<xsl:text>Archived</xsl:text>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="class">
													<xsl:text>Green</xsl:text>
												</xsl:attribute>
												<xsl:text>Available</xsl:text>
											</xsl:otherwise>
										</xsl:choose>
									</span>
								</strong>
							</td>
							<td>
								<a href="#" title="Rate Group Details" alt="Information about this Rate Group">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, </xsl:text>
										<xsl:text>'rates_group_view.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View Details</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/RateGroups/Results/collationLength = 0">
						<div class="MsgErrorWide">
							There were no results matching your search. Please change your search and try again.

						</div>
					</xsl:when>
					<xsl:when test="count(/Response/RateGroups/Results/rangeSample/RateGroup) = 0">
						<div class="MsgNoticeWide">
							There were no results matching your search. Please change your search and try again.

						</div>
					</xsl:when>
				</xsl:choose>
				
				<p>
					<a href="rates_group_add.php">Add a New Rate Group</a>
				</p>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

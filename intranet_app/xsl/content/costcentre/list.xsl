<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<!-- ADMIN PAGE -->
		<!-- Page for searching for Customers (NO VERIFICATION) -->
		
		<h1>Cost Centres</h1>
		
		<!-- Search Details -->
		<h2 class="CostCentre">Cost Centre Listing</h2>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">
					#
				</th>
				<th>
					<a>
						<xsl:attribute name="href">
							<xsl:text>costcentre_list.php</xsl:text>
							<xsl:text>?Order[Column]=Id</xsl:text>
							
							<xsl:if test="/Response/CostCentres/Order/Column = string('Id')">
								<xsl:choose>
									<xsl:when test="/Response/CostCentres/Order/Method = 0">
										<xsl:text>&amp;Order[Method]=1</xsl:text>
									</xsl:when>
									<xsl:when test="/Response/CostCentres/Order/Method = 1">
										<xsl:text>&amp;Order[Method]=0</xsl:text>
									</xsl:when>
								</xsl:choose>
							</xsl:if>
							
							<xsl:text>&amp;rangeLength=</xsl:text>
							<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
							
							<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
								<xsl:text>&amp;constraint[</xsl:text>
								<xsl:value-of select="./Name" />
								<xsl:text>][Value]=</xsl:text>
								<xsl:value-of select="./Value" />
								
								<xsl:text>&amp;constraint[</xsl:text>
								<xsl:value-of select="./Name" />
								<xsl:text>][Operator]=</xsl:text>
								<xsl:value-of select="./Operator" />
							</xsl:for-each>
						</xsl:attribute>
						Cost Centre ID
					</a>
				</th>
				<th>
					<a>
						<xsl:attribute name="href">
							<xsl:text>costcentre_list.php</xsl:text>
							<xsl:text>?Order[Column]=BusinessName</xsl:text>
							
							<xsl:if test="/Response/CostCentres/Order/Column = string('BusinessName')">
								<xsl:choose>
									<xsl:when test="/Response/CostCentres/Order/Method = 0">
										<xsl:text>&amp;Order[Method]=1</xsl:text>
									</xsl:when>
									<xsl:when test="/Response/CostCentres/Order/Method = 1">
										<xsl:text>&amp;Order[Method]=0</xsl:text>
									</xsl:when>
								</xsl:choose>
							</xsl:if>
							
							<xsl:text>&amp;rangeLength=</xsl:text>
							<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
							
							<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
								<xsl:text>&amp;constraint[</xsl:text>
								<xsl:value-of select="./Name" />
								<xsl:text>][Value]=</xsl:text>
								<xsl:value-of select="./Value" />
								
								<xsl:text>&amp;constraint[</xsl:text>
								<xsl:value-of select="./Name" />
								<xsl:text>][Operator]=</xsl:text>
								<xsl:value-of select="./Operator" />
							</xsl:for-each>
						</xsl:attribute>
						Business Name
					</a>
				</th>
				<th>
					<a>
						<xsl:attribute name="href">
							<xsl:text>costcentre_list.php</xsl:text>
							<xsl:text>?Order[Column]=TradingName</xsl:text>
							
							<xsl:if test="/Response/CostCentres/Order/Column = string('TradingName')">
								<xsl:if test="/Response/CostCentres/Order/Method = 1">
									<xsl:text>&amp;Order[Method]=0</xsl:text>
								</xsl:if>
							</xsl:if>
							
							<xsl:text>&amp;rangeLength=</xsl:text>
							<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
							
							<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
								<xsl:text>&amp;constraint[</xsl:text>
								<xsl:value-of select="./Name" />
								<xsl:text>][Value]=</xsl:text>
								<xsl:value-of select="./Value" />
								
								<xsl:text>&amp;constraint[</xsl:text>
								<xsl:value-of select="./Name" />
								<xsl:text>][Operator]=</xsl:text>
								<xsl:value-of select="./Operator" />
							</xsl:for-each>
						</xsl:attribute>
						Trading Name
					</a>
				</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/CostCentres/Results/rangeSample/CostCentre">
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
					<td>
						<xsl:value-of select="/Response/CostCentres/Results/rangeStart + position()" />.
					</td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>costcentre_view.php</xsl:text>
								<xsl:text>?Id=</xsl:text><xsl:value-of select="./Id" />
							</xsl:attribute>
							<xsl:value-of select="./Id" />
						</a>
					</td>
					<td>
						<xsl:value-of select="./BusinessName" />
					</td>
					<td>
						<xsl:value-of select="./TradingName" />
					</td>
					<td></td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="/Response/CostCentres/Results/collationLength = 0">
				<div class="MsgErrorWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/CostCentres/Results/rangeSample/CostCentre) = 0">
				<div class="MsgNoticeWide">
					There were no results matching your search. Please change your search and try again.
				</div>
			</xsl:when>
		</xsl:choose>

		<xsl:if test="/Response/CostCentres/Results/rangePages != 0">
			<p>
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CostCentres/Results/rangePage != 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>costcentre_list.php</xsl:text>
											
											<xsl:text>?rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=1</xsl:text>
											
											
											<xsl:if test="/Response/CostCentres/Order/Column != ''">
												<xsl:text>&amp;Order[Column]=</xsl:text>
												<xsl:value-of select="/Response/CostCentres/Order/Column" />
											</xsl:if>
											
											<xsl:choose>
												<xsl:when test="/Response/CostCentres/Order/Method = 1">
													<xsl:text>&amp;Order[Ascending]=1</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>&amp;Order[Ascending]=0</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
											
											<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Value]=</xsl:text>
												<xsl:value-of select="./Value" />
												
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Operator]=</xsl:text>
												<xsl:value-of select="./Operator" />
											</xsl:for-each>
										</xsl:attribute>
										<xsl:text>&#124;&lt;- First</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- &#124;&lt;- First -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="left">
							<xsl:choose>
								<xsl:when test="/Response/CostCentres/Results/rangePage &gt; 1">
									<a>
										<xsl:attribute name="href">
											<xsl:text>costcentre_list.php</xsl:text>
											
											<xsl:text>?rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangePage - 1" />
											
											
											<xsl:if test="/Response/CostCentres/Order/Column != ''">
												<xsl:text>&amp;Order[Column]=</xsl:text>
												<xsl:value-of select="/Response/CostCentres/Order/Column" />
											</xsl:if>
											
											<xsl:choose>
												<xsl:when test="/Response/CostCentres/Order/Method = 1">
													<xsl:text>&amp;Order[Ascending]=1</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>&amp;Order[Ascending]=0</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
											
											<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Value]=</xsl:text>
												<xsl:value-of select="./Value" />
												
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Operator]=</xsl:text>
												<xsl:value-of select="./Operator" />
											</xsl:for-each>
										</xsl:attribute>
										<xsl:text>&lt;- Prev</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- &lt;- Prev -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="60%" align="center">
							Page <xsl:value-of select="/Response/CostCentres/Results/rangePage" />
							of <xsl:value-of select="/Response/CostCentres/Results/rangePages" /><br />
							Showing  
							<xsl:value-of select="/Response/CostCentres/Results/rangeStart + 1" />
							to
							<xsl:choose>
								<xsl:when test="/Response/CostCentres/Results/rangeLength + /Response/CostCentres/Results/rangeStart &gt; /Response/CostCentres/Results/collationLength">
									<xsl:value-of select="/Response/CostCentres/Results/collationLength" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/Response/CostCentres/Results/rangeStart + /Response/CostCentres/Results/rangeLength" />
								</xsl:otherwise>
							</xsl:choose>
							of
							<xsl:value-of select="/Response/CostCentres/Results/collationLength" />
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CostCentres/Results/rangePage &lt; /Response/CostCentres/Results/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>costcentre_list.php</xsl:text>
											
											<xsl:text>?rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangePage + 1" />
											
											<xsl:if test="/Response/CostCentres/Order/Column != ''">
												<xsl:text>&amp;Order[Column]=</xsl:text>
												<xsl:value-of select="/Response/CostCentres/Order/Column" />
											</xsl:if>
											
											<xsl:choose>
												<xsl:when test="/Response/CostCentres/Order/Method = 1">
													<xsl:text>&amp;Order[Ascending]=1</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>&amp;Order[Ascending]=0</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
											
											<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Value]=</xsl:text>
												<xsl:value-of select="./Value" />
												
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Operator]=</xsl:text>
												<xsl:value-of select="./Operator" />
											</xsl:for-each>
										</xsl:attribute>
										<xsl:text>Next -&gt;</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- Next -&gt; -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td width="10%" align="right">
							<xsl:choose>
								<xsl:when test="/Response/CostCentres/Results/rangePage &lt; /Response/CostCentres/Results/rangePages">
									<a>
										<xsl:attribute name="href">
											<xsl:text>costcentre_list.php</xsl:text>
											
											<xsl:text>?rangeLength=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangeLength" />
											
											<xsl:text>&amp;rangePage=</xsl:text>
											<xsl:value-of select="/Response/CostCentres/Results/rangePages" />
											
											<xsl:if test="/Response/CostCentres/Order/Column != ''">
												<xsl:text>&amp;Order[Column]=</xsl:text>
												<xsl:value-of select="/Response/CostCentres/Order/Column" />
											</xsl:if>
											
											<xsl:choose>
												<xsl:when test="/Response/CostCentres/Order/Method = 1">
													<xsl:text>&amp;Order[Ascending]=1</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>&amp;Order[Ascending]=0</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
											
											<xsl:for-each select="/Response/CostCentres/Constraints/Constraint">
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Value]=</xsl:text>
												<xsl:value-of select="./Value" />
												
												<xsl:text>&amp;constraint[</xsl:text>
												<xsl:value-of select="./Name" />
												<xsl:text>][Operator]=</xsl:text>
												<xsl:value-of select="./Operator" />
											</xsl:for-each>
										</xsl:attribute>
										<xsl:text>Last -&gt;&#124;</xsl:text>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<!-- Last -&gt;&#124; -->
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</table>
			</p>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>

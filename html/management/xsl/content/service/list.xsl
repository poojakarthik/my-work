<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<!-- ADMIN PAGE -->
		<!-- Page for searching for Services (NO VERIFICATION) -->
		
		<h1>Service Listing</h1>
		
		<!-- Search Details -->
		<h2 class="Service">Search for a Service</h2>
		<form method="GET" action="service_list.php">
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[Id][Operator]')" />
									<xsl:with-param name="DataType" select="string('Id')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[Id][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Services/Constraints/Constraint[Name=string('Id')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[FNN][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[FNN][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Services/Constraints/Constraint[Name=string('FNN')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
				</div>
				<div class="Form-Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th valign="top">
								Results Per Page :
							</th>
							<td>
								<select name="rangeLength">
									<option selected="selected">
										<xsl:attribute name="value">
											<xsl:text></xsl:text>
											<xsl:value-of select="/Response/Services/Results/rangeLength" />
										</xsl:attribute>
										<xsl:value-of select="/Response/Services/Results/rangeLength" />
										<option disabled="disabled">-------</option>
										<option value="10">10</option>
										<option value="20">20</option>
										<option value="30">30</option>
										<option value="50">50</option>
										<option value="100">100</option>
									</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				
				<div class="Clear"></div>
				
				
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" value="Search" class="input-submit" />
			</div>
		</form>
		
		<div class="Clear"></div>
		<div class="Seperator"></div>
		
		<xsl:if test="/Response/Services">
			<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
				<tr class="First">
					<th width="30">
						#
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_list.php</xsl:text>
								<xsl:text>?Order[Column]=Id</xsl:text>
								
								<xsl:if test="/Response/Services/Order/Column = string('Id')">
									<xsl:choose>
										<xsl:when test="/Response/Services/Order/Method = 0">
											<xsl:text>&amp;Order[Method]=1</xsl:text>
										</xsl:when>
										<xsl:when test="/Response/Services/Order/Method = 1">
											<xsl:text>&amp;Order[Method]=0</xsl:text>
										</xsl:when>
									</xsl:choose>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Services/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
							Service ID
						</a>
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_list.php</xsl:text>
								<xsl:text>?Order[Column]=FNN</xsl:text>
								
								<xsl:if test="/Response/Services/Order/Column = string('FNN')">
									<xsl:choose>
										<xsl:when test="/Response/Services/Order/Method = 0">
											<xsl:text>&amp;Order[Method]=1</xsl:text>
										</xsl:when>
										<xsl:when test="/Response/Services/Order/Method = 1">
											<xsl:text>&amp;Order[Method]=0</xsl:text>
										</xsl:when>
									</xsl:choose>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Services/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
							FNN
						</a>
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_list.php</xsl:text>
								<xsl:text>?Order[Column]=ServiceType</xsl:text>
								
								<xsl:if test="/Response/Services/Order/Column = string('ServiceType')">
									<xsl:if test="/Response/Services/Order/Method = 1">
										<xsl:text>&amp;Order[Method]=0</xsl:text>
									</xsl:if>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Services/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
							Service Type
						</a>
					</th>
					<th>Actions</th>
				</tr>
				<xsl:for-each select="/Response/Services/Results/rangeSample/Service">
					<xsl:variable name="Service" select="." />
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
							<xsl:value-of select="/Response/Services/Results/rangeStart + position()" />.
						</td>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>service_view.php</xsl:text>
									<xsl:text>?Id=</xsl:text><xsl:value-of select="./Id" />
								</xsl:attribute>
								<xsl:value-of select="./Id" />
							</a>
						</td>
						<td>
							<xsl:value-of select="./FNN" />
						</td>
						<td>
							<xsl:value-of select="/Response/ServiceTypes/ServiceType[./Id = $Service/ServiceType]/Name" />
						</td>
						<td></td>
					</tr>
				</xsl:for-each>
			</table>
			<xsl:choose>
				<xsl:when test="/Response/Services/Results/collationLength = 0">
					<div class="MsgErrorWide">
						There were no results matching your search. Please change your search and try again.
					</div>
				</xsl:when>
				<xsl:when test="count(/Response/Services/Results/rangeSample/Service) = 0">
					<div class="MsgNoticeWide">
						There were no results matching your search. Please change your search and try again.
					</div>
				</xsl:when>
			</xsl:choose>
			
			<xsl:if test="/Response/Services/Results/rangePages != 0">
				<p>
					<table border="0" cellpadding="3" cellspacing="0" width="100%">
						<tr>
							<td width="10%" align="left">
								<xsl:choose>
									<xsl:when test="/Response/Services/Results/rangePage != 1">
										<a>
											<xsl:attribute name="href">
												<xsl:text>service_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=1</xsl:text>
												
												
												<xsl:if test="/Response/Services/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Services/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Services/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
									<xsl:when test="/Response/Services/Results/rangePage &gt; 1">
										<a>
											<xsl:attribute name="href">
												<xsl:text>service_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangePage - 1" />
												
												
												<xsl:if test="/Response/Services/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Services/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Services/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
								Page <xsl:value-of select="/Response/Services/Results/rangePage" />
								of <xsl:value-of select="/Response/Services/Results/rangePages" /><br />
								Showing  
								<xsl:value-of select="/Response/Services/Results/rangeStart + 1" />
								to
								<xsl:choose>
									<xsl:when test="/Response/Services/Results/rangeLength + /Response/Services/Results/rangeStart &gt; /Response/Services/Results/collationLength">
										<xsl:value-of select="/Response/Services/Results/collationLength" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="/Response/Services/Results/rangeStart + /Response/Services/Results/rangeLength" />
									</xsl:otherwise>
								</xsl:choose>
								of
								<xsl:value-of select="/Response/Services/Results/collationLength" />
							</td>
							<td width="10%" align="right">
								<xsl:choose>
									<xsl:when test="/Response/Services/Results/rangePage &lt; /Response/Services/Results/rangePages">
										<a>
											<xsl:attribute name="href">
												<xsl:text>service_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangePage + 1" />
												
												<xsl:if test="/Response/Services/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Services/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Services/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
									<xsl:when test="/Response/Services/Results/rangePage &lt; /Response/Services/Results/rangePages">
										<a>
											<xsl:attribute name="href">
												<xsl:text>service_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Services/Results/rangePages" />
												
												<xsl:if test="/Response/Services/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Services/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Services/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Services/Constraints/Constraint">
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
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>

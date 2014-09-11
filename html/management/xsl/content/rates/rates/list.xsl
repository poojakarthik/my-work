<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Listing Rates</h1>
		
		<form method="GET" action="rates_rate_list.php">
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
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
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('ServiceType')" />
								</xsl:call-template>
							</th>
							<td><input type="hidden" name="constraint[ServiceType][Operator]" value="EQUALS" /></td>
							<td>
								<select name="constraint[ServiceType][Value]">
									<xsl:for-each select="/Response/ServiceTypes/ServiceType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:if test="/Response/Rates/Constraints/Constraint[Name=string('ServiceType')]/Value = ./Id">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
											</xsl:if>
											<xsl:text></xsl:text>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
					
				</div>
				<div class="Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Archive')" />
									<xsl:with-param name="field" select="string('Archived')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="hidden" name="constraint[Archived][Operator]" value="EQUALS" />
								<table border="0" cellpadding="2" cellspacing="0">
									<tr>
										<td>
											<input type="radio" name="constraint[Archived][Value]" id="Archived:FALSE" value="0">
												<xsl:if test="number(/Response/Rates/Constraints/Constraint[Name=string('Archived')]/Value) = 0">

													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="Archived:FALSE">Only Show Current Accounts</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="constraint[Archived][Value]" id="Archived:TRUE" value="1">
												<xsl:if test="number(/Response/Rates/Constraints/Constraint[Name=string('Archived')]/Value) = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="Archived:TRUE">Only Show Archived Accounts</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="constraint[Archived][Value]" id="Archived:DONKEY" value="">
												<xsl:if test="not(/Response/Rates/Constraints/Constraint[Name=string('Archived')])">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="Archived:DONKEY">Show Current + Archived Accounts</label>
										</td>
									</tr>
								</table>
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
											<xsl:value-of select="/Response/Rates/Results/rangeLength" />
										</xsl:attribute>
										<xsl:value-of select="/Response/Rates/Results/rangeLength" />
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
				
				<input type="submit" value="Search" class="input-submit" />
			</div>
		</form>
		
		<p>
			<a href="rates_rate_add.php">Add a New Rate</a>
		</p>
		<div class="Seperator"></div>
		
		<xsl:choose>
			<xsl:when test="/Response/Rates">
				<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
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
							<td><xsl:value-of select="position() + /Response/Rates/Results/rangeStart" />.</td>
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
								<a href="#" title="Rate Details" alt="Information about this Rate">
									<xsl:attribute name="onclick">
										<xsl:text>return ModalExternal (this, </xsl:text>
										<xsl:text>'rates_rate_view.php?Id=</xsl:text><xsl:value-of select="./Id" /><xsl:text>')</xsl:text>
									</xsl:attribute>
									<xsl:text>View Details</xsl:text>
								</a>
							</td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/Rates/Results/collationLength = 0">
						<div class="MsgErrorWide">
							There were no results matching your search. Please change your search and try again.

						</div>
					</xsl:when>
					<xsl:when test="count(/Response/Rates/Results/rangeSample/Rate) = 0">
						<div class="MsgNoticeWide">
							There were no results matching your search. Please change your search and try again.

						</div>
					</xsl:when>
				</xsl:choose>
				
				<xsl:if test="/Response/Rates/Results/rangePages != 0">
					<p>
						<table border="0" cellpadding="3" cellspacing="0" width="100%">
							<tr>
								<td width="10%" align="left">
									<xsl:choose>
										<xsl:when test="/Response/Rates/Results/rangePage != 1">
											<a>
												<xsl:attribute name="href">
													<xsl:text>rates_rate_list.php</xsl:text>
													
													<xsl:text>?rangeLength=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangeLength" />
													
													<xsl:text>&amp;rangePage=1</xsl:text>
													
													<xsl:if test="/Response/Rates/Order/Column != ''">
														<xsl:text>&amp;Order[Column]=</xsl:text>
														<xsl:value-of select="/Response/Rates/Order/Column" />
													</xsl:if>
													
													<xsl:choose>
														<xsl:when test="/Response/Rates/Order/Method = 1">
															<xsl:text>&amp;Order[Ascending]=1</xsl:text>
														</xsl:when>
														<xsl:otherwise>
															<xsl:text>&amp;Order[Ascending]=0</xsl:text>
														</xsl:otherwise>
													</xsl:choose>
													
													<xsl:for-each select="/Response/Rates/Constraints/Constraint">
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
										<xsl:when test="/Response/Rates/Results/rangePage &gt; 1">
											<a>
												<xsl:attribute name="href">
													<xsl:text>rates_rate_list.php</xsl:text>
													
													<xsl:text>?rangeLength=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangeLength" />
													
													<xsl:text>&amp;rangePage=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangePage - 1" />
													
													
													<xsl:if test="/Response/Rates/Order/Column != ''">
														<xsl:text>&amp;Order[Column]=</xsl:text>
														<xsl:value-of select="/Response/Rates/Order/Column" />
													</xsl:if>
													
													<xsl:choose>
														<xsl:when test="/Response/Rates/Order/Method = 1">
															<xsl:text>&amp;Order[Ascending]=1</xsl:text>
														</xsl:when>
														<xsl:otherwise>
															<xsl:text>&amp;Order[Ascending]=0</xsl:text>
														</xsl:otherwise>
													</xsl:choose>
													
													<xsl:for-each select="/Response/Rates/Constraints/Constraint">
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
									Page <xsl:value-of select="/Response/Rates/Results/rangePage" />
									of <xsl:value-of select="/Response/Rates/Results/rangePages" /><br />
									Showing  
									<xsl:value-of select="/Response/Rates/Results/rangeStart + 1" />
									to
									<xsl:value-of select="/Response/Rates/Results/rangeStart + /Response/Rates/Results/rangeLength" />
									of
									<xsl:value-of select="/Response/Rates/Results/collationLength" />
								</td>
								<td width="10%" align="right">
									<xsl:choose>
										<xsl:when test="/Response/Rates/Results/rangePage &lt; /Response/Rates/Results/rangePages">
											<a>
												<xsl:attribute name="href">
													<xsl:text>rates_rate_list.php</xsl:text>
													
													<xsl:text>?rangeLength=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangeLength" />
													
													<xsl:text>&amp;rangePage=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangePage + 1" />
													
													<xsl:if test="/Response/Rates/Order/Column != ''">
														<xsl:text>&amp;Order[Column]=</xsl:text>
														<xsl:value-of select="/Response/Rates/Order/Column" />
													</xsl:if>
													
													<xsl:choose>
														<xsl:when test="/Response/Rates/Order/Method = 1">
															<xsl:text>&amp;Order[Ascending]=1</xsl:text>
														</xsl:when>
														<xsl:otherwise>
															<xsl:text>&amp;Order[Ascending]=0</xsl:text>
														</xsl:otherwise>
													</xsl:choose>
													
													<xsl:for-each select="/Response/Rates/Constraints/Constraint">
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
										<xsl:when test="/Response/Rates/Results/rangePage != /Response/Rates/Results/rangePages">
											<a>
												<xsl:attribute name="href">
													<xsl:text>rates_rate_list.php</xsl:text>
													
													<xsl:text>?rangeLength=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangeLength" />
													
													<xsl:text>&amp;rangePage=</xsl:text>
													<xsl:value-of select="/Response/Rates/Results/rangePages" />
													
													<xsl:if test="/Response/Rates/Order/Column != ''">
														<xsl:text>&amp;Order[Column]=</xsl:text>
														<xsl:value-of select="/Response/Rates/Order/Column" />
													</xsl:if>
													
													<xsl:choose>
														<xsl:when test="/Response/Rates/Order/Method = 1">
															<xsl:text>&amp;Order[Ascending]=1</xsl:text>
														</xsl:when>
														<xsl:otherwise>
															<xsl:text>&amp;Order[Ascending]=0</xsl:text>
														</xsl:otherwise>
													</xsl:choose>
													
													<xsl:for-each select="/Response/Rates/Constraints/Constraint">
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
			</xsl:when>
			<xsl:otherwise>
				<div class="MsgNoticeWide">
					<strong><span class="Attention">Attention</span> :</strong>
					Due to the significant number of Rates in the database, you must select a 
					Service Type from the List above before you can list any Rates.
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>

<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<!-- ADMIN PAGE -->
		<!-- Page for searching for Customers (NO VERIFICATION) -->
		
		<h1>Contact List</h1>
		
		<!-- Search Details -->
		<h2 class="Contact">Search for a Contact</h2>
		<form method="GET" action="contact_list.php">
			<div class="Wide-Form">
				<div class="Form-Content Left">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[Account][Operator]')" />
									<xsl:with-param name="DataType" select="string('Id')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[Account][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contacts/Constraints/Constraint[Name=string('Account')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[FirstName][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[FirstName][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contacts/Constraints/Constraint[Name=string('FirstName')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('LastName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[LastName][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td><input type="text" name="constraint[LastName][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Contacts/Constraints/Constraint[Name=string('LastName')]/Value" />
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
												<xsl:if test="number(/Response/Contacts/Constraints/Constraint[Name=string('Archived')]/Value) = 0">

													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="Archived:FALSE">Only Show Current Contacts</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="constraint[Archived][Value]" id="Archived:TRUE" value="1">
												<xsl:if test="number(/Response/Contacts/Constraints/Constraint[Name=string('Archived')]/Value) = 1">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="Archived:TRUE">Only Show Archived Contacts</label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="radio" name="constraint[Archived][Value]" id="Archived:DONKEY" value="">
												<xsl:if test="not(/Response/Contacts/Constraints/Constraint[Name=string('Archived')])">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
										</td>
										<td>
											<label for="Archived:DONKEY">Show Current + Archived Contacts</label>
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
											<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
										</xsl:attribute>
										<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
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
		
		<xsl:if test="/Response/Contacts">
			<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
				<tr class="First">
					<th width="30">
						#
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>contact_list.php</xsl:text>
								<xsl:text>?Order[Column]=Id</xsl:text>
								
								<xsl:if test="/Response/Contacts/Order/Column = string('Id')">
									<xsl:choose>
										<xsl:when test="/Response/Contacts/Order/Method = 0">
											<xsl:text>&amp;Order[Method]=1</xsl:text>
										</xsl:when>
										<xsl:when test="/Response/Contacts/Order/Method = 1">
											<xsl:text>&amp;Order[Method]=0</xsl:text>
										</xsl:when>
									</xsl:choose>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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
							Contact ID
						</a>
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>contact_list.php</xsl:text>
								<xsl:text>?Order[Column]=FirstName</xsl:text>
								
								<xsl:if test="/Response/Contacts/Order/Column = string('FirstName')">
									<xsl:choose>
										<xsl:when test="/Response/Contacts/Order/Method = 0">
											<xsl:text>&amp;Order[Method]=1</xsl:text>
										</xsl:when>
										<xsl:when test="/Response/Contacts/Order/Method = 1">
											<xsl:text>&amp;Order[Method]=0</xsl:text>
										</xsl:when>
									</xsl:choose>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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
							First Name
						</a>
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>contact_list.php</xsl:text>
								<xsl:text>?Order[Column]=LastName</xsl:text>
								
								<xsl:if test="/Response/Contacts/Order/Column = string('LastName')">
									<xsl:if test="/Response/Contacts/Order/Method = 1">
										<xsl:text>&amp;Order[Method]=0</xsl:text>
									</xsl:if>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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
							Last Name
						</a>
					</th>
					<th>Actions</th>
				</tr>
				<xsl:if test="/Response/Contacts/Results/collationLength &lt;= 80">
					<xsl:for-each select="/Response/Contacts/Results/rangeSample/Contact">
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
								<xsl:value-of select="/Response/Contacts/Results/rangeStart + position()" />.
							</td>
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:text>contact_view.php</xsl:text>
										<xsl:text>?Id=</xsl:text><xsl:value-of select="./Id" />
									</xsl:attribute>
									<xsl:value-of select="./Id" />
								</a>
							</td>
							<td>
								<xsl:value-of select="./FirstName" />
							</td>
							<td>
								<xsl:value-of select="./LastName" />
							</td>
							<td></td>
						</tr>
					</xsl:for-each>
				</xsl:if>
			</table>
			<xsl:choose>
				<xsl:when test="/Response/Contacts/Results/collationLength &gt; 80">
					<div class="MsgErrorWide">
						There are too many results to display.  Please refine your search and try again.
					</div>
				</xsl:when>
				<xsl:when test="/Response/Contacts/Results/collationLength = 0">
					<div class="MsgErrorWide">
						There were no results matching your search. Please change your search and try again.
					</div>
				</xsl:when>
				<xsl:when test="count(/Response/Contacts/Results/rangeSample/Contact) = 0">
					<div class="MsgNoticeWide">
						There were no results matching your search. Please change your search and try again.
					</div>
				</xsl:when>
			</xsl:choose>
			
			<xsl:if test="/Response/Contacts/Results/rangePages != 0 and /Response/Contacts/Results/collationLength &lt;= 80">
				<p>
					<table border="0" cellpadding="3" cellspacing="0" width="100%">
						<tr>
							<td width="10%" align="left">
								<xsl:choose>
									<xsl:when test="/Response/Contacts/Results/rangePage != 1">
										<a>
											<xsl:attribute name="href">
												<xsl:text>contact_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=1</xsl:text>
												
												
												<xsl:if test="/Response/Contacts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Contacts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Contacts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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
									<xsl:when test="/Response/Contacts/Results/rangePage &gt; 1">
										<a>
											<xsl:attribute name="href">
												<xsl:text>contact_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangePage - 1" />
												
												
												<xsl:if test="/Response/Contacts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Contacts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Contacts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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
								Page <xsl:value-of select="/Response/Contacts/Results/rangePage" />
								of <xsl:value-of select="/Response/Contacts/Results/rangePages" /><br />
								Showing  
								<xsl:value-of select="/Response/Contacts/Results/rangeStart + 1" />
								to
								<xsl:choose>
									<xsl:when test="/Response/Contacts/Results/rangeLength + /Response/Contacts/Results/rangeStart &gt; /Response/Contacts/Results/collationLength">
										<xsl:value-of select="/Response/Contacts/Results/collationLength" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="/Response/Contacts/Results/rangeStart + /Response/Contacts/Results/rangeLength" />
									</xsl:otherwise>
								</xsl:choose>
								of
								<xsl:value-of select="/Response/Contacts/Results/collationLength" />
							</td>
							<td width="10%" align="right">
								<xsl:choose>
									<xsl:when test="/Response/Contacts/Results/rangePage &lt; /Response/Contacts/Results/rangePages">
										<a>
											<xsl:attribute name="href">
												<xsl:text>contact_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangePage + 1" />
												
												<xsl:if test="/Response/Contacts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Contacts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Contacts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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
									<xsl:when test="/Response/Contacts/Results/rangePage &lt; /Response/Contacts/Results/rangePages">
										<a>
											<xsl:attribute name="href">
												<xsl:text>contact_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Contacts/Results/rangePages" />
												
												<xsl:if test="/Response/Contacts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Contacts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Contacts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Contacts/Constraints/Constraint">
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

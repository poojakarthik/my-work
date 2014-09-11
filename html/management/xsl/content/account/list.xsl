<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<!-- ADMIN PAGE -->
		<!-- Page for searching for Customers (NO VERIFICATION) -->
		
		<h1>Account List</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<!-- Search Details -->
		<h2 class="Account"> Search for an Account </h2>
		<form method="GET" action="account_list.php">
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
									<xsl:with-param name="Name" select="string('constraint[Id][Operator]')" />
									<xsl:with-param name="DataType" select="string('Id')" />
								</xsl:call-template>
							</td>
							<td>
								<input type="text" name="constraint[Id][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Accounts/Constraints/Constraint[Name=string('Id')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[BusinessName][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td><input type="text" name="constraint[BusinessName][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Accounts/Constraints/Constraint[Name=string('BusinessName')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('TradingName')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[TradingName][Operator]')" />
									<xsl:with-param name="DataType" select="string('String')" />
								</xsl:call-template>
							</td>
							<td><input type="text" name="constraint[TradingName][Value]" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Accounts/Constraints/Constraint[Name=string('TradingName')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[ABN][Operator]')" />
									<xsl:with-param name="DataType" select="string('ABN')" />
								</xsl:call-template>
							</td>
							<td><input type="text" name="constraint[ABN][Value]" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Accounts/Constraints/Constraint[Name=string('ABN')]/Value" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th valign="top">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<xsl:call-template name="ConstraintOperator">
									<xsl:with-param name="Name" select="string('constraint[ACN][Operator]')" />
									<xsl:with-param name="DataType" select="string('ACN')" />
								</xsl:call-template>
							</td>
							<td><input type="text" name="constraint[ACN][Value]" class="input-ACN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/Accounts/Constraints/Constraint[Name=string('ACN')]/Value" />
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
												<xsl:if test="number(/Response/Accounts/Constraints/Constraint[Name=string('Archived')]/Value) = 0">

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
												<xsl:if test="number(/Response/Accounts/Constraints/Constraint[Name=string('Archived')]/Value) = 1">
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
												<xsl:if test="not(/Response/Accounts/Constraints/Constraint[Name=string('Archived')])">
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
											<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
										</xsl:attribute>
										<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
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
		
		<xsl:if test="/Response/Accounts">
			<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
				<tr class="First">
					<th width="30">
						#
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>account_list.php</xsl:text>
								<xsl:text>?Order[Column]=Id</xsl:text>
								
								<xsl:if test="/Response/Accounts/Order/Column = string('Id')">
									<xsl:choose>
										<xsl:when test="/Response/Accounts/Order/Method = 0">
											<xsl:text>&amp;Order[Method]=1</xsl:text>
										</xsl:when>
										<xsl:when test="/Response/Accounts/Order/Method = 1">
											<xsl:text>&amp;Order[Method]=0</xsl:text>
										</xsl:when>
									</xsl:choose>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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
							Account ID
						</a>
					</th>
					<th>
						<a>
							<xsl:attribute name="href">
								<xsl:text>account_list.php</xsl:text>
								<xsl:text>?Order[Column]=BusinessName</xsl:text>
								
								<xsl:if test="/Response/Accounts/Order/Column = string('BusinessName')">
									<xsl:choose>
										<xsl:when test="/Response/Accounts/Order/Method = 0">
											<xsl:text>&amp;Order[Method]=1</xsl:text>
										</xsl:when>
										<xsl:when test="/Response/Accounts/Order/Method = 1">
											<xsl:text>&amp;Order[Method]=0</xsl:text>
										</xsl:when>
									</xsl:choose>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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
								<xsl:text>account_list.php</xsl:text>
								<xsl:text>?Order[Column]=TradingName</xsl:text>
								
								<xsl:if test="/Response/Accounts/Order/Column = string('TradingName')">
									<xsl:if test="/Response/Accounts/Order/Method = 1">
										<xsl:text>&amp;Order[Method]=0</xsl:text>
									</xsl:if>
								</xsl:if>
								
								<xsl:text>&amp;rangeLength=</xsl:text>
								<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
								
								<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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
				<xsl:if test="/Response/Accounts/Results/collationLength &lt;= 800">
					<xsl:for-each select="/Response/Accounts/Results/rangeSample/Account">
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
								<xsl:value-of select="/Response/Accounts/Results/rangeStart + position()" />.
							</td>
							<td>
								<a>
									<xsl:attribute name="href">
										<xsl:text>account_view.php</xsl:text>
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
				</xsl:if>
			</table>
			<xsl:choose>
				<xsl:when test="/Response/Accounts/Results/collationLength &gt; 800">
					<div class="MsgErrorWide">
						There are too many results to display.  Please refine your search and try again.
					</div>
				</xsl:when>
				<xsl:when test="/Response/Accounts/Results/collationLength = 0">
					<div class="MsgErrorWide">
						There were no results matching your search. Please change your search and try again.
					</div>
				</xsl:when>
				<xsl:when test="count(/Response/Accounts/Results/rangeSample/Account) = 0">
					<div class="MsgNoticeWide">
						There were no results matching your search. Please change your search and try again.
					</div>
				</xsl:when>
			</xsl:choose>
			
			<xsl:if test="/Response/Accounts/Results/rangePages != 0 and /Response/Accounts/Results/collationLength &lt;= 800">
				<p>
					<table border="0" cellpadding="3" cellspacing="0" width="100%">
						<tr>
							<td width="10%" align="left">
								<xsl:choose>
									<xsl:when test="/Response/Accounts/Results/rangePage != 1">
										<a>
											<xsl:attribute name="href">
												<xsl:text>account_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=1</xsl:text>
												
												
												<xsl:if test="/Response/Accounts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Accounts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Accounts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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
									<xsl:when test="/Response/Accounts/Results/rangePage &gt; 1">
										<a>
											<xsl:attribute name="href">
												<xsl:text>account_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangePage - 1" />
												
												
												<xsl:if test="/Response/Accounts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Accounts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Accounts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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
								Page <xsl:value-of select="/Response/Accounts/Results/rangePage" />
								of <xsl:value-of select="/Response/Accounts/Results/rangePages" /><br />
								Showing  
								<xsl:value-of select="/Response/Accounts/Results/rangeStart + 1" />
								to
								<xsl:choose>
									<xsl:when test="/Response/Accounts/Results/rangeLength + /Response/Accounts/Results/rangeStart &gt; /Response/Accounts/Results/collationLength">
										<xsl:value-of select="/Response/Accounts/Results/collationLength" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="/Response/Accounts/Results/rangeStart + /Response/Accounts/Results/rangeLength" />
									</xsl:otherwise>
								</xsl:choose>
								of
								<xsl:value-of select="/Response/Accounts/Results/collationLength" />
							</td>
							<td width="10%" align="right">
								<xsl:choose>
									<xsl:when test="/Response/Accounts/Results/rangePage &lt; /Response/Accounts/Results/rangePages">
										<a>
											<xsl:attribute name="href">
												<xsl:text>account_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangePage + 1" />
												
												<xsl:if test="/Response/Accounts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Accounts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Accounts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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
									<xsl:when test="/Response/Accounts/Results/rangePage &lt; /Response/Accounts/Results/rangePages">
										<a>
											<xsl:attribute name="href">
												<xsl:text>account_list.php</xsl:text>
												
												<xsl:text>?rangeLength=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangeLength" />
												
												<xsl:text>&amp;rangePage=</xsl:text>
												<xsl:value-of select="/Response/Accounts/Results/rangePages" />
												
												<xsl:if test="/Response/Accounts/Order/Column != ''">
													<xsl:text>&amp;Order[Column]=</xsl:text>
													<xsl:value-of select="/Response/Accounts/Order/Column" />
												</xsl:if>
												
												<xsl:choose>
													<xsl:when test="/Response/Accounts/Order/Method = 1">
														<xsl:text>&amp;Order[Ascending]=1</xsl:text>
													</xsl:when>
													<xsl:otherwise>
														<xsl:text>&amp;Order[Ascending]=0</xsl:text>
													</xsl:otherwise>
												</xsl:choose>
												
												<xsl:for-each select="/Response/Accounts/Constraints/Constraint">
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

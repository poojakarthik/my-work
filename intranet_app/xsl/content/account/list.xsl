<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Account Listing</h1>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		
		<div class="sectionContainer">
			<div class="sectionContent">
				<form method="GET" action="account_list.php">
					<div class="Filter-Form">
						<div class="Filter-Form-Content Left">
							<table border="0" cellpadding="5" cellspacing="0">
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
						<div class="Filter-Form-Content Left">
							<table border="0" cellpadding="5" cellspacing="0">
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
						<div class="Filter-Form-Content Left">
							<table border="0" cellpadding="5" cellspacing="0">
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
						
						<input type="submit" value="Submit" class="input-submit" />
					</div>
				</form>
				
				<div class="Seperator"></div>
				
				<table border="0" cellpadding="5" cellspacing="0" width="100%" class="Listing">
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
									<xsl:value-of select="./Id" disable-output-escaping="yes" />
								</a>
							</td>
							<td>
								<xsl:value-of select="./BusinessName" disable-output-escaping="yes" />
							</td>
							<td>
								<xsl:value-of select="./TradingName" disable-output-escaping="yes" />
							</td>
							<td></td>
						</tr>
					</xsl:for-each>
				</table>
				
				<xsl:choose>
					<xsl:when test="/Response/Accounts/Results/collationLength = 0">
						<div class="MsgError">
							There are no Accounts with the Search Criteria that you Specified.
						</div>
					</xsl:when>
					<xsl:when test="count(/Response/Accounts/Results/rangeSample/Account) = 0">
						<div class="MsgNotice">
							There are no Records for the Range that you Searched for.
						</div>
					</xsl:when>
				</xsl:choose>
				
				<xsl:if test="/Response/Accounts/Results/rangePages != 0">
					<p>
						<table border="0" cellpadding="3" cellspacing="0" width="100%">
							<tr>
								<td width="33%" align="left">
									<xsl:if test="/Response/Accounts/Results/rangePage &gt; 1">
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
											<xsl:text>- Prev</xsl:text>
										</a>
									</xsl:if>
								</td>
								<td width="34%" align="center">
									Page <xsl:value-of select="/Response/Accounts/Results/rangePage" />
									of <xsl:value-of select="/Response/Accounts/Results/rangePages" /><br />
									Showing  
									<xsl:value-of select="/Response/Accounts/Results/rangeStart + 1" />
									to
									<xsl:value-of select="/Response/Accounts/Results/rangeStart + /Response/Accounts/Results/rangeLength" />
									of
									<xsl:value-of select="/Response/Accounts/Results/collationLength" />
								</td>
								<td width="33%" align="right">
									<xsl:if test="/Response/Accounts/Results/rangePage &lt; /Response/Accounts/Results/rangePages">
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
											<xsl:text>Next -</xsl:text>
										</a>
									</xsl:if>
								</td>
							</tr>
						</table>
					</p>
				</xsl:if>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Find Customer</h1>
		
		<h2 class="Contact">Search for a Customer</h2>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		<script language="javascript" src="js/contact_verify_1.js"></script>
		
		<!-- TODO!bash! [  DONE  ]		This page has a popup error when fields are not filled in correctly!!!  It needs to have a msgerror saying EXACTLY "You did not fill in this form correctly.  Please try again."-->
		<!-- do NOT change the wording of the error-->
		
		<form method="post" action="contact_verify.php" onsubmit="return contactList1 (this)">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Account'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'ABN'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'ACN'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Invoice'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'FNN'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'BusinessName None'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'BusinessName Refine'">
						    There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Contact'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Contact-OneFill'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Empty'">
							There were no results matching your search. Please change your search and try again.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Wide-Form">
				<div class="Form-Content">
					To search for a Customer, enter <strong>only one</strong> of the following:
					<div class="SmallSeperator"></div>
					
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-Account" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Account" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ABN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-ABN" class="input-ABN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/ABN" />
									</xsl:attribute>
								</input>
							</td>
							<td>
								<sup><strong><span class="Attention">1</span></strong></sup>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('ACN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-ACN" class="input-ACN">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/ACN" />
									</xsl:attribute>
								</input>
							</td>
							<td>
								<sup><strong><span class="Attention">1</span></strong></sup>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Invoice')" />
									<xsl:with-param name="field" select="string('Id')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-Invoice" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Invoice" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Service')" />
									<xsl:with-param name="field" select="string('FNN')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-FNN" class="input-string" maxlength="25">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/FNN" />
									</xsl:attribute>
								</input>
							</td>
							<td>
								<sup><strong><span class="Attention">1</span></strong></sup>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="MicroSeperator"></div>
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Account')" />
									<xsl:with-param name="field" select="string('BusinessName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-BusinessName" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/BusinessName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
					<div class="Seperator"></div>

					Or, enter <strong>both</strong> of the following:
					<div class="SmallSeperator"></div>
					
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-Contact-First" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Contact-First" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('LastName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-Contact-Last" class="input-string" maxlength="255">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Contact-Last" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red"><sup>1</sup> </span></strong>: This field searches for Current Accounts only, not Archived Accounts.
			</div>
			<div class="Right">
				<input type="submit" class="input-submit" value="Search &#0187;" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

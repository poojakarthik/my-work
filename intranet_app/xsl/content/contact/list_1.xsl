<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Find Customer</h1>
		
		<h2 class="Contact">Search for a Customer</h2>
		
		<script language="javascript" src="js/ABN.js"></script>
		<script language="javascript" src="js/ACN.js"></script>
		<script language="javascript" src="js/contact_list_1.js"></script>
		
		<form method="post" action="contact_list.php" onsubmit="return contactList1 (this)">
			<xsl:if test="/Response/Error != ''">
				<div class="MsgError">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'Account'">
							The Account# you entered was not found in the database.
						</xsl:when>
						<xsl:when test="/Response/Error = 'ABN'">
							The ABN# you entered was not found in the database.
						</xsl:when>
						<xsl:when test="/Response/Error = 'ACN'">
							The ACN# you entered was not found in the database.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Invoice'">
							The Invoice# you entered was not found in the database.
						</xsl:when>
						<xsl:when test="/Response/Error = 'FNN'">
							The Line Number you entered was not found in the database.
						</xsl:when>
						<xsl:when test="/Response/Error = 'BusinessName'">
							The Business Name you entered did not return any possible Accounts.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Contact'">
							The First/Last Name you entered did not return any possible Contacts.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Contact-OneFill'">
							You must search for a First and Last name for a Contact.
						</xsl:when>
						<xsl:when test="/Response/Error = 'Empty'">
							Your search details are blank. Please enter criteria to search for.
						</xsl:when>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div class="Filter-Form">
				<div class="Filter-Form-Content">
					To search for a Customer, enter <strong>only one</strong> of the following:
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
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
								<input type="text" name="ui-FNN" class="input-string">
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
								<div class="Seperator"></div>
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
								<input type="text" name="ui-BusinessName" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/BusinessName" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Contact')" />
									<xsl:with-param name="field" select="string('FirstName')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="text" name="ui-Contact-First" class="input-string">
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
								<input type="text" name="ui-Contact-Last" class="input-string">
									<xsl:attribute name="value">
										<xsl:text></xsl:text>
										<xsl:value-of select="/Response/ui-values/Contact-Last" />
									</xsl:attribute>
								</input>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="Seperator"></div>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" class="input-submit" value="Find Customer &#0187;" />
							</td>
						</tr>
					</table>
					
					<div class="Seperator"></div>
					
					<div style="width: 500px">
						<strong><span class="Attention"><sup>1</sup></span> :</strong>
						This field searches for Current Accounts only, not Archived Accounts.
					</div>
				</div>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

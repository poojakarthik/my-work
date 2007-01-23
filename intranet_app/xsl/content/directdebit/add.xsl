<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add Bank Account Details</h1>
		
		<xsl:if test="/Response/Error != ''">
			<div class="MsgError">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'BankName'">
						You did not enter a Bank Name. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'BSB'">
						You did not enter a BSB#. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'AccountNumber'">
						You did not enter an Account Number. Please try again.
					</xsl:when>
					<xsl:when test="/Response/Error = 'AccountName'">
						You did not enter an Account Name. Please try again.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="directdebit_add.php">
			<input type="hidden" name="AccountGroup">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/AccountGroup/Id" />
				</xsl:attribute>
			</input>
			
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('AccountGroup')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/AccountGroup/Id" />
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Payment">Bank Account Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="5" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Direct Debit')" />
								<xsl:with-param name="field" select="string('BankName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="DirectDebit[BankName]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/DirectDebit/BankName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Direct Debit')" />
								<xsl:with-param name="field" select="string('BSB')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="DirectDebit[BSB]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/DirectDebit/BSB" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<td coslpan="2">
							<div class="Seperator"></div>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Direct Debit')" />
								<xsl:with-param name="field" select="string('AccountNumber')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="DirectDebit[AccountNumber]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/DirectDebit/AccountNumber" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Direct Debit')" />
								<xsl:with-param name="field" select="string('AccountName')" />
							</xsl:call-template>
						</th>
						<td>
							<input type="text" name="DirectDebit[AccountName]" class="input-string">
								<xsl:attribute name="value">
									<xsl:text></xsl:text>
									<xsl:value-of select="/Response/ui-values/DirectDebit/AccountName" />
								</xsl:attribute>
							</input>
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Create Bank Account Details &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>

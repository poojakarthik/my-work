<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Add Bank Account Details</h1>
		
		<!--TODO!Bash! [  DONE  ]		URGENT - This page only comes from Change Payment Method now, so it needs to return there.  We don't want to use Direct Debit Details page anymore-->
		<xsl:if test="/Response/Error != ''">
			<div class="MsgErrorWide">
				<xsl:choose>
					<xsl:when test="/Response/Error = 'BankName'">
						Please enter a Direct Debit Bank Name.
					</xsl:when>
					<xsl:when test="/Response/Error = 'BSB'">
						Please enter a Direct Debit BSB #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'AccountNumber'">
						Please enter a Direct Debit Account #.
					</xsl:when>
					<xsl:when test="/Response/Error = 'AccountName'">
						Please enter a Direct Debit Account Name.
					</xsl:when>
				</xsl:choose>
			</div>
		</xsl:if>
		
		<form method="POST" action="directdebit_add.php">
			<input type="hidden" name="Account">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<h2 class="Account">Account Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Account/Id" />
						</td>
					</tr>
				</table>
			</div>
			<div class="Seperator"></div>
			
			<!-- Bank Account Details -->
			<h2 class="Payment">Bank Account Details</h2>
			<div class="Wide-Form">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td class="Required"><span class="Red"><strong>*</strong></span></td>
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
					<!--TODO!bash! URGENT! verify bsb - 6 digit number only -->
					<tr>
					<td class="Required"><span class="Red"><strong>*</strong></span></td>
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
							<div class="MicroSeperator"></div>
						</td>
					</tr>
					<!--TODO!bash! URGENT verify - 9 digit number only -->
					<tr>
					<td class="Required"><span class="Red"><strong>*</strong></span></td>
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
					<td class="Required"><span class="Red"><strong>*</strong></span></td>
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
			<div class="SmallSeperator"></div>
			<div class="Left">
				<strong><span class="Red">* </span></strong>: Required field<br/>
			</div>
			<div class="Right">
				<input type="submit" value="Add Bank Account Details &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

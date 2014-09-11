<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../../lib/date-time.xsl" />
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	
	<xsl:template name="Content">
		<!--we are not using this page anymore link to change payment details instead-->
		<h1>Direct Debit Details</h1>
		<div class="Seperator"></div>
		
		<h2 class="Account">Account Details</h2>
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('AccountGroup')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/AccountGroup/Id" /></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="Seperator"></div>
		
		<h2 class="Invoice">Bank Account Details</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Bank Name</th>
				<th>BSB#</th>
				<th>Account Number</th>
				<th>Account Name</th>
			</tr>
			<xsl:for-each select="/Response/DirectDebits/DirectDebit">
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
					
					<td><xsl:value-of select="position()" />.</td>
					<td><xsl:value-of select="./BankName" /></td>
					<td><xsl:value-of select="./BSB" /></td>
					<td><xsl:value-of select="./AccountNumber" /></td>
					<td><xsl:value-of select="./AccountName" /></td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="count(/Response/DirectDebits/DirectDebit) = 0">
				<div class="MsgNoticeWide">
					There are no Direct Debit details associated with this Account Group.
				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="LinkAdd">
			<a>
				<xsl:attribute name="href">
					<xsl:text>directdebit_add.php?AccountGroup=</xsl:text>
					<xsl:value-of select="/Response/AccountGroup/Id" />
				</xsl:attribute>
				Add Bank Account Details
			</a>
		</div>
		
		<div class="Seperator"></div>
		
		<h2 class="Invoice">Credit Card Details</h2>
		<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
			<tr class="First">
				<th width="30">#</th>
				<th>Card Type</th>
				<th>Card Holder Name</th>
				<th>Card Number</th>
				<th>Expiry Date</th>
				<th>CVV</th>
			</tr>
			<xsl:for-each select="/Response/CreditCards/CreditCard">
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
					
					<td><xsl:value-of select="position()" />.</td>
					<td>
						<xsl:value-of select="./CreditCardTypes/CreditCardType[@selected='selected']/Name" />
					</td>
					<td><xsl:value-of select="./Name" /></td>
					<td><xsl:value-of select="./CardNumber" /></td>
					<td>
						<xsl:value-of select="./ExpMonth" /> /
						<xsl:value-of select="./ExpYear" />
					</td>
					<td>
						<strong>
							<span>
								<xsl:choose>
									<xsl:when test="./CVV != ''">
										<xsl:attribute name="class">
											<xsl:text>Green</xsl:text>
										</xsl:attribute>
										CVV Exists
									</xsl:when>
									<xsl:otherwise>
										<xsl:attribute name="class">
											<xsl:text>Red</xsl:text>
										</xsl:attribute>
										No CVV Defined
									</xsl:otherwise>
								</xsl:choose>
							</span>
						</strong>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<xsl:choose>
			<xsl:when test="count(/Response/CreditCards/CreditCard) = 0">
				<div class="MsgNoticeWide">
					There are no Credit Card details associated with this Account Group.

				</div>
			</xsl:when>
		</xsl:choose>
		
		<div class="LinkAdd">
			<a>
				<xsl:attribute name="href">
					<xsl:text>creditcard_add.php?AccountGroup=</xsl:text>
					<xsl:value-of select="/Response/AccountGroup/Id" />
				</xsl:attribute>
				Add Credit Card Details
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>

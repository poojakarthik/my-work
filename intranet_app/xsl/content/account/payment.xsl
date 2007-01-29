<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	
	<xsl:template name="Content">
	
		<!-- This page is used to Change the payment Method for an account -->
		<h1>Change Payment Method</h1>
		
		<form method="POST" action="account_payment.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
			</input>
			
			<!--Account Details -->
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
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('BusinessName')" />
							</xsl:call-template>
						</th>
						<td>
							<xsl:value-of select="/Response/Account/BusinessName" />
						</td>
					</tr>
					<!--Check for Trading Name-->
						<xsl:choose>
							<xsl:when test="/Response/Account/TradingName = ''">
							</xsl:when>
							<xsl:otherwise>
								<tr>
									<th>
										<xsl:call-template name="Label">
											<xsl:with-param name="entity" select="string('Account')" />
											<xsl:with-param name="field" select="string('TradingName')" />
										</xsl:call-template>
									</th>
									<td>
										<xsl:value-of select="/Response/Account/TradingName" />
									</td>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
				</table>
				
				<div class="Clear"></div>
			</div>
			<div class="Seperator"></div>
			
			<h2 class="Payment">Payment Method</h2>
			
			<xsl:if test="/Response/Error != ''">
				<div class="MsgErrorWide">
					<xsl:choose>
						<xsl:when test="/Response/Error = 'DDR Not Found'">
							You did not select a valid Direct Debit Bank Account. 
							Please select a valid Direct Debit Bank Account option from the list and try again.
						</xsl:when>
						<xsl:when test="/Response/Error = 'CC Not Found'">
							You did not select a valid Credit Card. 
							Please select a valid Credit Card option from the list and try again.
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="/Response/Error" />
						</xsl:otherwise>
					</xsl:choose>
				</div>
				<div class="Seperator"></div>
			</xsl:if>
			<div class="Seperator"></div>

			<!-- TODO!bash! [  DONE  ]		This page needs to have just 1 set of radio buttons-->
			<!-- TODO!bash! [  DONE  ]		you can select account, or 1 direct debit account-->
			<input type="radio" id="BillingType:AC" name="BillingType" value="AC">
				<xsl:if test="/Response/ui-values/BillingType = 'AC'">
					<xsl:attribute name="checked">
						<xsl:text>checked</xsl:text>
					</xsl:attribute>
				</xsl:if>
			</input>

			<label for="BillingType:AC">
				Make all charges against the Account
			</label>

			<div class="SmallSeperator"></div>
			<label for="BillingType:1">
				Direct Debit from a Bank Account :
			</label>

			<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
				<tr class="First">
					<th width="30">#</th>
					<th width="30"></th>
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
						
						<xsl:variable name="Name">
							<xsl:text>DD</xsl:text>
							<xsl:value-of select="./Id" />
						</xsl:variable>
						
						<td><xsl:value-of select="position()" />.</td>
						<td>
							<input type="radio" name="BillingType">
								<xsl:attribute name="value">
									<xsl:text>DD</xsl:text>
									<xsl:value-of select="./Id" />
								</xsl:attribute>
								<xsl:if test="/Response/ui-values/BillingType = $Name">
									<xsl:attribute name="checked">
										<xsl:text>checked</xsl:text>
									</xsl:attribute>
								</xsl:if>
							</input>
						</td>
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
						There are no Direct Debit Details attached to this Account Group.
					</div>
				</xsl:when>
			</xsl:choose>
			
			<div class="LinkAdd">
				<a>
					<xsl:attribute name="href">
						<xsl:text>directdebit_add.php?AccountGroup=</xsl:text>
						<xsl:value-of select="/Response/AccountGroup/Id" />
					</xsl:attribute>
					<xsl:text>Add Bank Account Details</xsl:text>
				</a>
			</div>

			<div class="SmallSeperator"></div>
			<label for="BillingType:2">
				Direct Debit from a Credit Card :
			</label>

			<table border="0" cellpadding="3" cellspacing="0" class="Listing" width="100%">
				<tr class="First">
					<th width="30">#</th>
					<th width="30"></th>
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
						
						<xsl:variable name="Name">
							<xsl:text>CC</xsl:text>
							<xsl:value-of select="./Id" />
						</xsl:variable>
						
						<td><xsl:value-of select="position()" />.</td>
						<td>
							<input type="radio" name="BillingType">
								<xsl:attribute name="value">
									<xsl:text>CC</xsl:text>
									<xsl:value-of select="./Id" />
								</xsl:attribute>
								<xsl:if test="/Response/ui-values/BillingType = $Name">
									<xsl:attribute name="checked">
										<xsl:text>checked</xsl:text>
									</xsl:attribute>
								</xsl:if>
							</input>
						</td>
						<td><xsl:value-of select="./CreditCardTypes/CreditCardType[@selected='selected']/Name" /></td>
						<td><xsl:value-of select="./Name" /></td>
						<td><xsl:value-of select="./CardNumber" /></td>
						<td><xsl:value-of select="./ExpMonth" /> / <xsl:value-of select="./ExpYear" /></td>
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
											No CVV Entered
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
						There are no Credit Card Details attached to this Account Group.
					</div>
				</xsl:when>
			</xsl:choose>
			
			<div class="LinkAdd">
				<a>
					<xsl:attribute name="href">
						<xsl:text>creditcard_add.php?AccountGroup=</xsl:text>
						<xsl:value-of select="/Response/AccountGroup/Id" />
					</xsl:attribute>
					<xsl:text>Add Credit Card Details</xsl:text>
				</a>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" value="Change Payment Method &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

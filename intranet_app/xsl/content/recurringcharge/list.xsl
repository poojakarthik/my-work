<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Recurring Charge Listing</h1>
		
		<p>
			Below is a list of Recurring Charges in the System. To add a new Recurring
			Charge, select the Service you would like to add the Recurring Charge to
			from the 
			<a>
				<xsl:attribute name="href">
					<xsl:text>account_view.php?Id=</xsl:text>
					<xsl:value-of select="/Response/Account/Id" />
				</xsl:attribute>
				<xsl:text>Account Details Page</xsl:text>
			</a>.
		</p>
		<div class="Seperator"></div>
		
		<div class="Wide-Form">
			<div class="Form-Content">
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('Id')" />
							</xsl:call-template>
						</th>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>account_view.php?Id=</xsl:text>
									<xsl:value-of select="/Response/Account/Id" />
								</xsl:attribute>
								<xsl:value-of select="/Response/Account/Id" />
							</a>
						</td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('BusinessName')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/Account/BusinessName" /></td>
					</tr>
					<tr>
						<th class="JustifiedWidth">
							<xsl:call-template name="Label">
								<xsl:with-param name="entity" select="string('Account')" />
								<xsl:with-param name="field" select="string('TradingName')" />
							</xsl:call-template>
						</th>
						<td><xsl:value-of select="/Response/Account/TradingName" /></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="Seperator"></div>
		
		<table border="0" cellpadding="3" cellspacing="0" width="100%" class="Listing">
			<tr class="First">
				<th width="30">#</th>
				<th>Charge Type</th>
				<th>Description</th>
				<th>Service</th>
				<th>Amount</th>
				<th>Frequency</th>
				<th>Archive</th>
				<th>Actions</th>
			</tr>
			<xsl:for-each select="/Response/RecurringCharges/Results/rangeSample/RecurringCharge">
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
					
					<td><xsl:value-of select="/Response/RecurringCharges/Results/rangeStart + position()" />.</td>
					<td><xsl:value-of select="./ChargeType" /></td>
					<td><xsl:value-of select="./Description" /></td>
					<td>
						<a>
							<xsl:attribute name="href">
								<xsl:text>service_view.php?Id=</xsl:text>
								<xsl:value-of select="./Service/Id" />
							</xsl:attribute>
							<xsl:value-of select="./Service/FNN" />
						</a>
					</td>
					<td>
						<xsl:value-of select="./RecursionCharge" />
						<xsl:text> </xsl:text>
						<xsl:value-of select="./Nature" />
					</td>
					<td>
						Every <xsl:value-of select="./RecurringFreq" />
						<xsl:text> </xsl:text>
						<xsl:value-of select="./BillingFreqTypes/BillingFreqType[@selected='selected']/Name" />(s)
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="./Archived = 1">
								<strong><span class="Blue">Current Archived</span></strong>
							</xsl:when>
							<xsl:otherwise>
								<strong><span class="Green">Current Applied</span></strong>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<a href="#" title="Recurring Charge Details" alt="Information about the attached Recurring Charge">
							<xsl:attribute name="onclick">
								<xsl:text>return ModalExternal (this, 'recurring_charge_view.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
								<xsl:text>')</xsl:text>
							</xsl:attribute>
							View Details
						</a>, 
						<a>
							<xsl:attribute name="href">
								<xsl:text>recurring_charge_cancel.php?Id=</xsl:text>
								<xsl:value-of select="./Id" />
							</xsl:attribute>
							Cancel Charge
						</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		
		<xsl:choose>
			<xsl:when test="/Response/RecurringCharges/Results/collationLength = 0">
				<div class="MsgNoticeWide">
					No Recurring Charges have been made against this Account.
				</div>
			</xsl:when>
			<xsl:when test="count(/Response/RecurringCharges/Results/rangeSample/RecurringCharge) = 0">
				<div class="MsgErrorWide">
					No Recurring Charges were found between the range you searched for.
				</div>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>

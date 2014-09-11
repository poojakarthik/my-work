<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
	<!--TODO!Bash! [  DONE  ]		Urgent - This page needs a menu back to view account-->
	<!--TODO!Bash! [  DONE  ]		Urgent - Continuing does not work!!!!-->
		<h1>Cancel Recurring Adjustment</h1>
		
		<h2 class="Adjustment">Cancellation Details</h2>
		
		<form method="post" action="recurring_charge_cancel.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">				<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RecurringCharge/Id" />
				</xsl:attribute>
			</input>

			
			<xsl:if test="/Response/RecurringCharge/CancellationAmount">
				<div class="MsgErrorWide">
					<strong>Warning :</strong>
					Cancelling this account will incur a cost to the Customer.
				</div>
				<div class = "Wide-Form">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">Minimum Charge :</th>
							<td></td>
							<td class="Currency">
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="/Response/RecurringCharge/MinCharge" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
							</td>
						</tr>
						<tr>
							<th></th>
							<td class="Currency"></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">Additional Charge :</th>
							<td>+</td>
							<td class="Currency">
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="/Response/RecurringCharge/TotalCharged" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
							</td>
						</tr>
						<tr>
							<th></th>
							<td class="Currency"></td>
						</tr>
						<tr>
							<th class="JustifiedWidth">Cancellation Fee :</th>
							<td>+</td>
							<td class="Currency">
				       			<xsl:call-template name="Currency">
				       				<xsl:with-param name="Number" select="/Response/RecurringCharge/CancellationFee" />
									<xsl:with-param name="Decimal" select="number('2')" />
		       					</xsl:call-template>
							</td>
						</tr>
						<tr>
							<th colspan="3">
								<hr />
							</th>
						</tr>
						<tr>
							<th class="JustifiedWidth">Total Cancellation Cost:</th>
							<td></td>
							<th class="Currency">
								<strong>
									<div class="Red">
						       			<xsl:call-template name="Currency">
						       				<xsl:with-param name="Number" select="/Response/RecurringCharge/CancellationAmount" />
											<xsl:with-param name="Decimal" select="number('2')" />
				       					</xsl:call-template>
			    	   				</div>
			    	   			</strong>
							</th>
						</tr>
					</table>
				</div>
			</xsl:if>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="Submit" name="Confirm" value="Confirm Cancellation &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Cancelling a Recurring Charge</h1>
		
		<form method="post" action="recurring_charge_cancel.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/RecurringCharge/Id" />
				</xsl:attribute>
			</input>
			
			<p>
				If you would like to cancel this recurring charge, please confirm the details below:
			</p>
			
			<div class="Seperator"></div>
			
			<xsl:if test="/Response/RecurringCharge/CancellationAmount">
				<div class="MsgError">
					<strong>Warning :</strong>
					Cancelling this account will incur a cost to the Customer.
					
					<div class="Seperator"></div>
					
					<table border="0" cellpadding="5" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">Minimum Charge :</th>
							<td class="Currency">
								<xsl:value-of select="/Response/RecurringCharge/MinCharge" />
							</td>
						</tr>
						<tr>
							<th></th>
							<td class="Currency">-</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">Total Charged :</th>
							<td class="Currency">
								<xsl:value-of select="/Response/RecurringCharge/TotalCharged" />
							</td>
						</tr>
						<tr>
							<th></th>
							<td class="Currency">+</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">Cancellation Fee :</th>
							<td class="Currency">
								<xsl:value-of select="/Response/RecurringCharge/CancellationFee" />
							</td>
						</tr>
						<tr>
							<th></th>
							<td class="Currency">=</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">Cancellation Amount :</th>
							<th class="Currency">
								<xsl:value-of select="/Response/RecurringCharge/CancellationAmount" />
							</th>
						</tr>
					</table>
				</div>
				
				<div class="Seperator"></div>
			</xsl:if>
			
			<input type="Submit" name="Confirm" value="Confirm Cancellation &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>

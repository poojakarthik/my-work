<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Upload Payment Details</h1>
		
		<h2 class="Payment">Payment Details</h2>
		<form method="post" action="payment_upload.php" enctype="multipart/form-data">
			<div class="Wide-Form">
				<div class="Form-Content">
					<table border="0" cellpadding="3" cellspacing="0">
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('UploadFilename')" />
								</xsl:call-template>
							</th>
							<td>
								<input type="file" name="BillFile" class="input-string" />
							</td>
						</tr>
						<tr>
							<th class="JustifiedWidth">
								<xsl:call-template name="Label">
									<xsl:with-param name="entity" select="string('Payment')" />
									<xsl:with-param name="field" select="string('PaymentType')" />
								</xsl:call-template>
							</th>
							<td>
								<select name="PaymentType">
									<xsl:for-each select="/Response/PaymentTypes/PaymentType">
										<option>
											<xsl:attribute name="value">
												<xsl:text></xsl:text>
												<xsl:value-of select="./Id" />
											</xsl:attribute>
											<xsl:value-of select="./Name" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="Seperator"></div>
			
			<input type="submit" value="Upload Payment Data &#0187;" class="input-submit" />
		</form>
	</xsl:template>
</xsl:stylesheet>

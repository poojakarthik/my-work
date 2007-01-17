<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Confirm Charge Type Archive</h1>
		
		<p>
			Are you sure you would like to Archive this charge type?
		</p>
		
		<form method="post" action="charges_charge_archive.php">
			<input type="hidden" name="Id">
				<xsl:attribute name="value">
					<xsl:text></xsl:text>
					<xsl:value-of select="/Response/ChargeType/Id" />
				</xsl:attribute>
			</input>
			
			<table border="0" cellpadding="3" cellspacing="0">
				<tr>
					<td><input type="radio" id="Confirm:TRUE" name="Confirm" value="1" /></td>
					<th>
						<label for="Confirm:TRUE">
							Yes, please Archive this Charge Type, making it unavailable for use.
						</label>
					</th>
				</tr>
				<tr>
					<td><input type="radio" id="Confirm:FALSE" name="Confirm" value="0" /></td>
					<th>
						<label for="Confirm:FALSE">
							No, cancel the Archive of this Charge Type and keep its availability.
						</label>
					</th>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="submit" value="Continue &#0187;" class="input-submit" />
					</td>
				</tr>
			</table>
		</form>
	</xsl:template>
</xsl:stylesheet>

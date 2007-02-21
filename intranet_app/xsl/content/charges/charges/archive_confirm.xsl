<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../includes/init.xsl" />
	<xsl:import href="../../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Archive Adjustment Type</h1>
		<!--TODO!bash! [  DONE  ]		URGENT this page needs to link to a confirmation page-->
		<!--TODO!flame! Please keep indenting consistent. I notice that the pages I format correctly come back badly indented-->
		
		<h2 class="Archive">Archive Status</h2>
		<form method="post" action="charges_charge_archive.php">
			<div class= "Wide-Form">
				<input type="hidden" name="Id">
					<xsl:attribute name="value">
						<xsl:text></xsl:text>
						<xsl:value-of select="/Response/ChargeType/Id" />
					</xsl:attribute>
				</input>
				
				Are you sure you would like to Archive this Adjustment Type?
				<div class="SmallSeperator"></div>
				
				<table border="0" cellpadding="3" cellspacing="0">
					<tr>
						<td><input type="radio" id="Confirm:TRUE" name="Confirm" value="1" /></td>
						<th>
							<label for="Confirm:TRUE">
								Yes, please Archive this Adjustment Type, making it unavailable for use.
							</label>
						</th>
					</tr>
					<tr>
						<td><input type="radio" id="Confirm:FALSE" name="Confirm" value="0" /></td>
						<th>
							<label for="Confirm:FALSE">
								No, cancel the Archive of this Adjustment Type and keep its availability.
							</label>
						</th>
					</tr>
				</table>
			</div>
			<div class="SmallSeperator"></div>
			<div class="Right">
				<input type="submit" value="Continue &#0187;" class="input-submit" />
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>

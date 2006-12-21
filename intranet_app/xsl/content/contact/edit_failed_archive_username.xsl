<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Contact Reactivation Failed</h1>
		<div class="Seperator"></div>
		
		<h2>Error Information</h2>
		<div class="Seperator"></div>
		<p>
			Your attempt to reactivate this particular contact has failed because
			of the following reason:
		</p>
		
		<ul>
			<li>
				A Contact (most likely from another account) exists with the same
				Username that this user was associated with.
			</li>
		</ul>
		
		<div class="Seperator"></div>
		
		<h2>Reconstitution</h2>
		<div class="Seperator"></div>
		<p>This problem can be disconstituted be following the following proceedure:</p>
		
		<ol>
			<li>
				Change the Username of the Contact and then try to Unarchive the Contact
			</li>
		</ol>
		
		<div class="Seperator"></div>
		
		<h2>Additional Information</h2>
		<div class="Seperator"></div>
		
		<div class="MsgNotice">
			<strong>Notice :</strong>
			The other information relating to this account <strong>has been successfully updated</strong>.
		</div>
		<div class="Seperator"></div>
		
		<h2>Following On</h2>
		<p>
			You can now return to
			<a>
				<xsl:attribute name="href">
					<xsl:text>contact_edit.php?Id=</xsl:text>
					<xsl:value-of select="/Response/Contact/Id" />
				</xsl:attribute>
				<xsl:text>editing this contact</xsl:text>
			</a>.
		</p>
	</xsl:template>
</xsl:stylesheet>

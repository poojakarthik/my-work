<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/default.xsl" />
	<xsl:template name="Content">
	
	<!--NOT USING THIS PAGE-->
	<!-- I am under ther impression that this page is not in use-->
		<h1>Problem Creating New Note</h1>
		
		<p>
			Your new note could not be added to the Database because the Contents
			of the note was empty.  
		</p>
		
		<ul>
			<xsl:if test="/Response/Account">
				<li>
					Return to 
					<a>
						<xsl:attribute name="href">
							<xsl:text>account_view.php?Id=</xsl:text>
							<xsl:value-of select="/Response/Account/Id" />
						</xsl:attribute>
						<xsl:text>Details about this Account</xsl:text>
					</a>.
				</li>
			</xsl:if>
			
			<xsl:if test="/Response/Service">
				<li>
					Return to 
					<a>
						<xsl:attribute name="href">
							<xsl:text>service_view.php?Id=</xsl:text>
							<xsl:value-of select="/Response/Service/Id" />
						</xsl:attribute>
						<xsl:text>Details about this Service</xsl:text>
					</a>.
				</li>
			</xsl:if>
			
			<xsl:if test="/Response/Contact">
				<li>
					Return to 
					<a>
						<xsl:attribute name="href">
							<xsl:text>contact_view.php?Id=</xsl:text>
							<xsl:value-of select="/Response/Contact/Id" />
						</xsl:attribute>
						<xsl:text>Details about this Contact</xsl:text>
					</a>.
				</li>
			</xsl:if>
		</ul>
	</xsl:template>
</xsl:stylesheet>

<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dt="http://xsltsl.org/date-time">
	<xsl:import href="../../includes/init.xsl" />
	<xsl:import href="../../template/popup.xsl" />
	<xsl:import href="../../lib/date-time.xsl" />
	
	<xsl:template name="Content">
		<h1>Pablo 'the helpful donkey' says:</h1>
		<div class="Pablo-Section">
			<div class="Pablo-Section-Container">
				<div class="Pablo-Section-Content">
					<h2><xsl:value-of select="/Response/DocumentationDetails/Field/Title" /></h2>
					<p style="line-height: 200%; font-family: monospace;">
						<xsl:value-of select="/Response/DocumentationDetails/Field/Description" disable-output-escaping="yes" />
					</p>
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

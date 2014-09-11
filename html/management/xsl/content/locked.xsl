<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../includes/init.xsl" />
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Page Locked</h1>
		<div class="MsgErrorWide">
			This page has been temporarily locked, please try again later.
		</div>

		<div class="Wide-Form">
			<div class="Form-Content">
			The page may have been locked to allow a system upgrade to be performed 
			or an automated process (like invoice generation) to be run.  The period 
			of time that a page remains locked for may vary from several minutes to 
			several hours depending on the reason that it was locked.  If you require 
			more information, or if the page remains locked for an extended period of 
			time, please contact the Vixen help desk.
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

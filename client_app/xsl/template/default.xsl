<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="iso-8859-1" indent="yes" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	
	<xsl:template match="/">
		<html>
			<head>
				<link rel="stylesheet" type="text/css" href="/client_app/css/default.css" media="screen" />
			</head>
			<body>
				<h1>VOIPTEL</h1>
				
				<div id="Content">
					<xsl:call-template name="Content" />
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

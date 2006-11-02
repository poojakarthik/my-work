<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
			<head>
				<link rel="stylesheet" type="text/css" href="/client_app/css/default.css" media="screen" />
			</head>
			<body>
				<h1>VOIPTEL</h1>
				<xsl:call-template name="Content" />
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

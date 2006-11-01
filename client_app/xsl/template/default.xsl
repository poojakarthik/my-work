<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
			<head>
				<style type="text/css">
					
					input.userName {
						background-color: #FF00FF;
					}
					
table tr.odd td {
	background-color: #FF00FF;
}

table tr.even td {
	background-color: #00FF00;
}
	
				</style>
			</head>
			<body>
				<h1>VOIPTEL</h1>
				<xsl:call-template name="Content" />
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

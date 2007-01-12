<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="iso-8859-1" indent="yes" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />
	
	<xsl:template match="/">
		<html>
			<head>
				<title>Employee Intranet System</title>
				<link rel="stylesheet" type="text/css" href="css/default.css" />
				<script language="javascript" src="js/init.js"></script>
			</head>
			<body>
				<div id="Header" class="sectionContainer">
					<div class="sectionContent">
						<div class="Left">
							TelcoBlue Internal Management System
						</div>
						
						<div class="Clear"></div>
					</div>
					<div class="Clear"></div>
				</div>
				<div id="Controller" class="sectionContainer">
					<div id="Content" class="sectionContent">
						<xsl:call-template name="Content" />
					</div>
				</div>
				<div class="Clear"></div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

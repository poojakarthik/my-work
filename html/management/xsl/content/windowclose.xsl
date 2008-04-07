<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
			<head>
				<script language="javascript">
					if (window.opener)
					{
						window.opener.location.href = 'login.php';
					}
					else
					{
						window.open ('login.php');
					}
					
					window.close ();
				</script>
			</head>
			<body>
			
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

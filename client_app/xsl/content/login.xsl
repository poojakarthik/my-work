<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Login</h1>
		
		<p>
			You are currently not logged into an account. In order for you to log into an account, 
			please enter your account crudentials into the fields below.
		</p>
		
		<form method="post" action="./login.php">
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td>Username:</td>
					<td>
						<input type="text" name="UserName" />
					</td>
				</tr>
			</table>
		</form>
	</xsl:template>
</xsl:stylesheet>
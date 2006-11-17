<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h2>Login</h2>
		
		<p>
			You are currently not logged into an account. In order for you to log into an account, 
			please enter your account crudentials into the fields below.
		</p>
		
		<xsl:if test="/Response/AuthenticationAttempt">
			<div class="MsgError">
				The Username and Password that you entered were incorrect. Please try
				again.
			</div>
		</xsl:if>
		
		<form method="post" action="./login.php">
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<th>Username:</th>
					<td>
						<input type="text" name="UserName" class="text" />
					</td>
				</tr>
				<tr>
					<th>Password:</th>
					<td>
						<input type="password" name="PassWord" class="text" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="submit" value="Continue &#0187;" class="button" />
					</td>
				</tr>
			</table>
		</form>
	</xsl:template>
</xsl:stylesheet>

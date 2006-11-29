<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../template/default.xsl" />
	<xsl:template name="Content">
		<h1>Employee Login</h1>
		<p>
			Please enter your Employee Login Crudentials in order to log into the Internet Management System.
		</p>
		
		<form method="post" action="login.php">
			<xsl:if test="/Response/AuthenticationAttempt">
				<div class="MsgNotice">
					The Username and Password that you entered were incorrect. Please try again.
				</div>
			</xsl:if>
			
			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<th>Username:</th>
					<td>
						<input type="text" name="UserName" class="input-string">
							<xsl:attribute name="value">
								<xsl:text></xsl:text>
								<xsl:if test="/Response/AuthenticationAttempt">
									<xsl:value-of select="/Response/AuthenticationAttempt/UserName" />
								</xsl:if>
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>Password:</th>
					<td><input type="password" name="PassWord" class="input-string" /></td>
					<td><input type="submit" value="Login &#0187;" class="input-submit" /></td>
				</tr>
			</table>
		</form>
	</xsl:template>
</xsl:stylesheet>
